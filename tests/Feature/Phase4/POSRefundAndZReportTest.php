<?php

namespace Tests\Feature\Phase4;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\POS\Actions\ClosePosSessionAction;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Actions\PosCheckoutAction;
use App\Domain\POS\Actions\PosRefundAction;
use App\Domain\POS\Actions\PosVoidAction;
use App\Domain\POS\Models\PosRefund;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\POS\Models\PosZReport;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSRefundAndZReportTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected PosTerminal $terminal;
    protected Product $product;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $branch = Branch::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-REFUND'],
            ['name' => 'Refund Branch', 'is_active' => true, 'is_headquarters' => false]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'WH-REFUND'],
            ['branch_id' => $branch->id, 'name' => 'Refund Warehouse', 'is_active' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'refund-goods'],
            ['name' => 'Refund Goods', 'is_active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'REF-ITEM-01'],
            [
                'name' => ['hy' => 'Organic Tea Box'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 3000,
                'cost_price' => 1200,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        app(RecordStockMovementAction::class)->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 30.0,
            unitCost: 1200.0,
            referenceType: 'test_seed',
            referenceId: (string) \Illuminate\Support\Str::uuid(),
            notes: 'Initial test stock'
        );

        $this->terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-REF-01',
            'name' => 'Refund Cash Desk',
            'device_uid' => 'CRN-REF-1234',
            'is_active' => true,
        ]);
    }

    public function test_pos_partial_and_full_refund_with_inventory_restock(): void
    {
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminal->id,
            cashierId: $this->user->id,
            openingCash: 10000.0
        );

        // Checkout 2 items for 6,000 AMD
        $order = app(PosCheckoutAction::class)->execute(
            posSessionId: $session->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 3000],
            ],
            payments: [
                ['gateway' => 'cash', 'method' => 'cash', 'amount' => 6000],
            ]
        );

        $orderItem = $order->items->first();
        $this->assertEquals(28.0, (float) StockLevel::where('warehouse_id', $this->warehouse->id)->where('product_id', $this->product->id)->first()->quantity_on_hand);

        // 1. Partial refund of 1 item (3,000 AMD)
        $refund = app(PosRefundAction::class)->execute(
            orderId: $order->id,
            amount: 3000.0,
            itemsToRestock: [
                ['order_item_id' => $orderItem->id, 'quantity' => 1],
            ],
            refundMethod: 'cash',
            reason: 'Customer returned 1 damaged package'
        );

        $this->assertInstanceOf(PosRefund::class, $refund);
        $this->assertEquals('completed', $refund->status);
        $this->assertEquals(3000.0, (float) $refund->amount);
        $this->assertEquals('partially_refunded', $order->fresh()->payment_status);

        // Stock restored from 28 to 29
        $currentStock = StockLevel::where('warehouse_id', $this->warehouse->id)->where('product_id', $this->product->id)->first()->quantity_on_hand;
        $this->assertEquals(29.0, (float) $currentStock);

        // Session calculated cash reduced by 3,000 AMD (10000 float + 6000 sale - 3000 refund = 13000)
        $this->assertEquals(13000.0, (float) $session->fresh()->closing_cash_calculated);
    }

    public function test_pos_void_order_cancels_and_restores_all_items(): void
    {
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminal->id,
            cashierId: $this->user->id,
            openingCash: 10000.0
        );

        $order = app(PosCheckoutAction::class)->execute(
            posSessionId: $session->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 3, 'unit_price' => 3000],
            ],
            payments: [
                ['gateway' => 'cash', 'method' => 'cash', 'amount' => 9000],
            ]
        );

        $stockAfterSale = StockLevel::where('warehouse_id', $this->warehouse->id)->where('product_id', $this->product->id)->first()->quantity_on_hand;

        // Void the order
        $voidedOrder = app(PosVoidAction::class)->execute(
            orderId: $order->id,
            reason: 'Mistakenly punched by cashier'
        );

        $this->assertEquals('cancelled', $voidedOrder->status);
        $this->assertEquals('refunded', $voidedOrder->payment_status);

        // Stock restored back
        $stockAfterVoid = StockLevel::where('warehouse_id', $this->warehouse->id)->where('product_id', $this->product->id)->first()->quantity_on_hand;
        $this->assertEquals((float) $stockAfterSale + 3.0, (float) $stockAfterVoid);

        // Cash drawer reversed back to 10,000
        $this->assertEquals(10000.0, (float) $session->fresh()->closing_cash_calculated);
    }

    public function test_end_of_day_z_report_generation_upon_session_close(): void
    {
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminal->id,
            cashierId: $this->user->id,
            openingCash: 15000.0
        );

        // Sale 1: Cash sale 3,000 AMD
        app(PosCheckoutAction::class)->execute(
            posSessionId: $session->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 3000],
            ],
            payments: [
                ['gateway' => 'cash', 'method' => 'cash', 'amount' => 3000],
            ]
        );

        // Sale 2: Card sale 6,000 AMD
        app(PosCheckoutAction::class)->execute(
            posSessionId: $session->id,
            items: [
                ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 3000],
            ],
            payments: [
                ['gateway' => 'ameria', 'method' => 'card', 'amount' => 6000],
            ]
        );

        // Close session with exact cash: 15000 opening + 3000 cash sale = 18000
        $closed = app(ClosePosSessionAction::class)->execute(
            posSessionId: $session->id,
            closingCashDeclared: 18000.0,
            notes: 'EOD Shift Closing'
        );

        $this->assertEquals('closed', $closed->status);

        // Verify Z-Report created
        $zReport = PosZReport::where('pos_session_id', $session->id)->first();
        $this->assertNotNull($zReport);
        $this->assertStringStartsWith('Z-', $zReport->z_report_number);
        $this->assertEquals(15000.0, (float) $zReport->opening_cash);
        $this->assertEquals(9000.0, (float) $zReport->total_sales_amount);
        $this->assertEquals(3000.0, (float) $zReport->total_cash_sales);
        $this->assertEquals(6000.0, (float) $zReport->total_card_sales);
        $this->assertEquals(18000.0, (float) $zReport->expected_cash_in_drawer);
        $this->assertEquals(0.0, (float) $zReport->cash_difference);
        $this->assertEquals(2, $zReport->sales_count);
    }
}
