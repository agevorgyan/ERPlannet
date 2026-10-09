<?php

namespace Tests\Feature\Phase4;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\Delivery\Actions\AssignDeliveryDriverAction;
use App\Domain\Delivery\Actions\CreateDeliveryShipmentAction;
use App\Domain\Delivery\Actions\DispatchShipmentAction;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\IAM\Models\User;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConcurrentCheckoutAndPaymentTest extends TestCase
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
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-CONCURR'],
            ['name' => 'Concurrency Hub', 'is_active' => true, 'is_headquarters' => false]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'WH-CONCURR'],
            ['branch_id' => $branch->id, 'name' => 'Concurrency WH', 'is_active' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'conc-goods'],
            ['name' => 'Concurrency Goods', 'is_active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'CONCURR-01'],
            [
                'name' => ['hy' => 'Limited Edition Box'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 5000,
                'cost_price' => 2000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        app(RecordStockMovementAction::class)->execute(
            warehouseId: $this->warehouse->id,
            productId: $this->product->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 10.0,
            unitCost: 2000.0,
            referenceType: 'test_seed',
            referenceId: (string) Str::uuid(),
            notes: 'Concurrency seed stock'
        );

        $this->terminal = PosTerminal::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $branch->id,
            'warehouse_id' => $this->warehouse->id,
            'code' => 'POS-CONC-01',
            'name' => 'Concurrency Desk',
            'is_active' => true,
        ]);
    }

    public function test_idempotent_checkout_prevents_duplicate_orders_and_stock_deduction(): void
    {
        $session = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminal->id,
            cashierId: $this->user->id,
            openingCash: 10000.0
        );

        $idempotencyKey = 'IDEMP-'.Str::uuid();

        // First checkout request
        $response1 = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/pos/checkout', [
                'pos_session_id' => $session->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 5000],
                ],
                'payments' => [
                    ['gateway' => 'cash', 'method' => 'cash', 'amount' => 10000],
                ],
            ]);

        $response1->assertStatus(201);
        $orderId1 = $response1->json('data.order.id');

        // Stock after 1st checkout = 10 - 2 = 8
        $stockAfterFirst = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first()->quantity_on_hand;
        $this->assertEquals(8.0, (float) $stockAfterFirst);

        // Immediate duplicate request with same X-Idempotency-Key (simulating network retry or double-click)
        $response2 = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson('/api/v1/pos/checkout', [
                'pos_session_id' => $session->id,
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 5000],
                ],
                'payments' => [
                    ['gateway' => 'cash', 'method' => 'cash', 'amount' => 10000],
                ],
            ]);

        $response2->assertStatus(201);
        $orderId2 = $response2->json('data.order.id');

        // Must return the exact same order
        $this->assertEquals($orderId1, $orderId2);

        // Crucial: Stock must NOT be deducted twice (must remain 8, not 6)
        $stockAfterSecond = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first()->quantity_on_hand;
        $this->assertEquals(8.0, (float) $stockAfterSecond);

        // Total orders created in database must be exactly 1
        $this->assertEquals(1, Order::where('idempotency_key', $idempotencyKey)->count());
    }

    public function test_state_machine_prevents_invalid_shipment_status_transitions(): void
    {
        $branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'State',
            'last_name' => 'Tester',
            'phone' => '+37499999999',
        ]);

        $order = app(CreateOrderAction::class)->execute([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'source' => 'direct',
            'delivery_type' => 'delivery',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ]);

        $shipment = app(CreateDeliveryShipmentAction::class)->execute(
            orderId: $order->id,
            deliveryAddress: 'Yerevan Center',
            codAmount: 5000.0
        );

        $driver = DeliveryDriver::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Fast',
            'last_name' => 'Driver',
            'phone' => '+37491888777',
            'status' => 'available',
            'is_active' => true,
        ]);

        // 1. Assign
        app(AssignDeliveryDriverAction::class)->execute($shipment->id, $driver->id);
        $this->assertEquals('assigned', $shipment->fresh()->status);

        // 2. Dispatch
        app(DispatchShipmentAction::class)->execute($shipment->id);
        $this->assertEquals('in_transit', $shipment->fresh()->status);

        // 3. Invalid Transition: Attempting to assign a driver while shipment is in_transit must throw InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        app(AssignDeliveryDriverAction::class)->execute($shipment->id, $driver->id);
    }
}
