<?php

namespace Tests\Feature\Phase4;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\POS\Actions\ClosePosSessionAction;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Actions\PosCheckoutAction;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class POSTerminalAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected Product $product;

    protected Unit $pcs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $this->branch = Branch::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-TEST'],
            ['name' => 'Kentron Test Branch', 'is_active' => true, 'is_headquarters' => false]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'WH-POS-TEST'],
            ['branch_id' => $this->branch->id, 'name' => 'POS Store Warehouse', 'is_active' => true]
        );

        $this->pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $category = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pos-goods'],
            ['name' => 'POS Goods', 'is_active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'POS-COFFEE-01'],
            [
                'name' => ['hy' => 'Espresso Coffee Arabica', 'en' => 'Espresso Coffee Arabica'],
                'category_id' => $category->id,
                'unit_id' => $this->pcs->id,
                'sale_price' => 1200,
                'cost_price' => 450,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        // Pre-fill stock of 50 pcs in warehouse
        app(RecordStockMovementAction::class)->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 50.0,
            unitCost: 450.0,
            referenceType: 'test_seed',
            referenceId: (string) Str::uuid(),
            notes: 'Initial POS test stock'
        );
    }

    public function test_can_create_pos_terminal_via_api(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/terminals', [
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
                'code' => 'POS-FRONT-01',
                'name' => 'Front Counter Cash Desk #1',
                'device_uid' => 'DEV-TERMINAL-9876',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'POS-FRONT-01');

        $this->assertDatabaseHas('pos_terminals', [
            'tenant_id' => $this->tenant->id,
            'code' => 'POS-FRONT-01',
        ]);
    }

    public function test_pos_terminal_creation_enforces_plan_limits(): void
    {
        $currentCount = PosTerminal::where('tenant_id', $this->tenant->id)->count();

        // Limit Enterprise plan to currentCount + 1 for testing limit restriction
        $plan = Plan::where('code', 'enterprise')->firstOrFail();
        $posLimitFeature = Feature::where('code', 'limit.pos_terminals')->firstOrFail();
        $plan->features()->updateExistingPivot($posLimitFeature->id, ['value' => (string) ($currentCount + 1)]);

        // 1st terminal succeeds
        $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/terminals', [
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
                'code' => 'POS-T1',
                'name' => 'First Terminal',
            ])->assertStatus(201);

        // 2nd terminal fails due to limit
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/terminals', [
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
                'code' => 'POS-T2',
                'name' => 'Second Terminal',
            ]);

        $response->assertStatus(402);
    }

    public function test_cashier_can_open_shift_session_with_opening_cash(): void
    {
        $terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-SHIFT-01',
            'name' => 'Shift Terminal',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/sessions/open', [
                'pos_terminal_id' => $terminal->id,
                'opening_cash' => 25000.0,
                'notes' => 'Morning shift opening float',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.opening_cash', '25000.00');

        $this->assertDatabaseHas('pos_sessions', [
            'tenant_id' => $this->tenant->id,
            'pos_terminal_id' => $terminal->id,
            'status' => 'open',
        ]);
    }

    public function test_cashier_cannot_open_duplicate_session_on_same_terminal(): void
    {
        $terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-DUP-01',
            'name' => 'Duplicate Terminal',
            'is_active' => true,
        ]);

        app(OpenPosSessionAction::class)->execute(
            posTerminalId: $terminal->id,
            cashierId: $this->user->id,
            openingCash: 10000.0
        );

        $this->expectException(\InvalidArgumentException::class);
        app(OpenPosSessionAction::class)->execute(
            posTerminalId: $terminal->id,
            cashierId: $this->user->id,
            openingCash: 5000.0
        );
    }

    public function test_can_record_cash_in_and_cash_out_movements(): void
    {
        $terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-CASH-01',
            'name' => 'Cash Test Terminal',
            'is_active' => true,
        ]);

        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $terminal->id,
            cashierId: $this->user->id,
            openingCash: 20000.0
        );

        // Record Cash In
        $inResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/sessions/cash-movement', [
                'pos_session_id' => $session->id,
                'type' => 'cash_in',
                'amount' => 5000.0,
                'reason' => 'Extra change added by manager',
            ]);

        $inResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'cash_in');

        // Record Cash Out
        $outResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/sessions/cash-movement', [
                'pos_session_id' => $session->id,
                'type' => 'cash_out',
                'amount' => 2000.0,
                'reason' => 'Paid courier delivery fuel',
            ]);

        $outResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'cash_out');

        $this->assertCount(2, $session->fresh()->cashMovements);
    }

    public function test_fast_pos_checkout_depletes_stock_and_generates_receipt(): void
    {
        $terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-FAST-01',
            'name' => 'Fast Lane 01',
            'is_active' => true,
        ]);

        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $terminal->id,
            cashierId: $this->user->id,
            openingCash: 10000.0
        );

        $initialStock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->firstOrFail()->quantity_on_hand;

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/pos/checkout', [
                'pos_session_id' => $session->id,
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                        'unit_price' => 1200,
                        'discount_percent' => 0,
                    ],
                ],
                'payments' => [
                    [
                        'gateway' => 'cash',
                        'method' => 'cash',
                        'amount' => 3000, // 2 * 1200 = 2400, tendered 3000 -> change 600
                    ],
                ],
                'notes' => 'POS Walk-in customer',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'order',
                    'receipt_number',
                    'receipt_payload' => [
                        'receipt_number',
                        'order_number',
                        'total',
                        'payment_status',
                    ],
                ],
            ]);

        $orderId = $response->json('data.order.id');
        $receiptNumber = $response->json('data.receipt_number');

        $this->assertStringStartsWith('REC-', $receiptNumber);
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'source' => 'pos',
            'receipt_number' => $receiptNumber,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'total' => '2400.00',
        ]);

        // Verify stock depleted by 2 pcs
        $newStock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->firstOrFail()->quantity_on_hand;

        $this->assertEquals((float) $initialStock - 2.0, (float) $newStock);
    }

    public function test_pos_split_payment_with_cash_and_card_and_session_closing(): void
    {
        $terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-SPLIT-01',
            'name' => 'Split Pay Terminal',
            'is_active' => true,
        ]);

        // Opening float = 10,000 AMD
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $terminal->id,
            cashierId: $this->user->id,
            openingCash: 10000.0
        );

        // Checkout 3 coffees = 3,600 AMD. Paid 2000 cash + 1600 card
        $checkout = app(PosCheckoutAction::class)->execute(
            posSessionId: $session->id,
            items: [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'unit_price' => 1200,
                ],
            ],
            payments: [
                ['gateway' => 'cash', 'method' => 'cash', 'amount' => 2000],
                ['gateway' => 'ameria', 'method' => 'card', 'amount' => 1600],
            ]
        );

        $this->assertEquals('paid', $checkout->payment_status);

        // Expected physical cash in drawer = 10000 (opening) + 2000 (cash sale) = 12000
        // Cashier declares 12000 -> variance 0
        $closedSession = app(ClosePosSessionAction::class)->execute(
            posSessionId: $session->id,
            closingCashDeclared: 12000.0,
            notes: 'Exact reconciliation'
        );

        $this->assertEquals('closed', $closedSession->status);
        $this->assertEquals(12000.0, (float) $closedSession->closing_cash_calculated);
        $this->assertEquals(0.0, (float) $closedSession->cash_difference);
    }
}
