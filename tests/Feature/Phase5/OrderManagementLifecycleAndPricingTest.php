<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Product;
use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementLifecycleAndPricingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Branch $branch;

    protected Warehouse $warehouse;

    protected Product $product;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->warehouse = Warehouse::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->product = Product::where('tenant_id', $this->tenant->id)->firstOrFail();
        $this->customer = Customer::where('tenant_id', $this->tenant->id)->firstOrFail();

        // Ensure stock balance exists
        StockLevel::updateOrCreate(
            [
                'tenant_id' => $this->tenant->id,
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
            ],
            [
                'quantity_on_hand' => 100,
                'quantity_reserved' => 0,
            ]
        );
    }

    public function test_can_create_order_from_pos_source(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'source' => 'pos',
            'order_type' => 'standard',
            'fulfillment_method' => 'pickup',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 2500,
                    'tax_rate' => 20,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'source' => 'pos',
            'status' => 'new',
        ]);

        $order = Order::find($response->json('data.id'));
        $this->assertNotNull($order);
        $this->assertEquals(2, $order->items->first()->quantity);
        $this->assertEquals(2500, (float) $order->items->first()->unit_price);
        $this->assertEquals(5000, (float) $order->total);
    }

    public function test_can_create_order_from_online_store_and_manual_backoffice(): void
    {
        $sources = ['online_store', 'manual_backoffice', 'external_integration'];

        foreach ($sources as $source) {
            $payload = [
                'branch_id' => $this->branch->id,
                'warehouse_id' => $this->warehouse->id,
                'source' => $source,
                'order_type' => 'standard',
                'fulfillment_method' => 'delivery',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1800,
                    ],
                ],
            ];

            $response = $this->actingAs($this->user)
                ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
                ->postJson('/api/v1/orders', $payload);

            $response->assertStatus(201);
            $this->assertEquals($source, $response->json('data.source'));
        }
    }

    public function test_pricing_engine_calculates_item_discounts_order_discount_promo_tax_and_total_precisely(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'source' => 'manual_backoffice',
            'fulfillment_method' => 'delivery',
            'delivery_fee' => 1000,
            'order_discount_type' => 'fixed',
            'order_discount_value' => 500,
            'promo_code' => 'SAVE10',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'unit_price' => 5000, // Gross 10,000
                    'discount_type' => 'percent',
                    'discount_rate' => 10, // Item discount: 1,000 -> line total 9,000
                    'tax_rate' => 20,
                ],
            ],
        ];

        // 1. Calculate pricing preview
        $calcResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/orders/calculate-pricing', $payload);

        $calcResponse->assertStatus(200);
        $pricing = $calcResponse->json('data');

        $this->assertEquals(10000.0, (float) $pricing['subtotal']);
        $this->assertEquals(1000.0, (float) $pricing['item_discounts_total']);
        $this->assertEquals(500.0, (float) $pricing['order_discount']);
        // after item disc (9,000) - order disc (500) = 8,500. SAVE10 promo gives 10% = 850
        $this->assertEquals(850.0, (float) $pricing['promo_discount']);
        $this->assertEquals(1000.0, (float) $pricing['delivery_fee']);
        // Taxable base = 8,500 - 850 = 7,650. Grand total = 7,650 + 1,000 delivery = 8,650
        $this->assertEquals(8650.0, (float) $pricing['grand_total']);

        // 2. Persist order with same calculation
        $orderResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/orders', $payload);

        $orderResponse->assertStatus(201);
        $order = Order::find($orderResponse->json('data.id'));
        $this->assertEquals(8650.0, (float) $order->total);
        $this->assertEquals(8650.0, (float) $order->balance_due);
        $this->assertEquals('SAVE10', $order->promo_code);
    }

    public function test_order_prices_and_customer_data_are_snapshotted_immutably(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'source' => 'pos',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 3000,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201);
        $order = Order::find($response->json('data.id'));

        $this->assertNotEmpty($order->customer_snapshot);
        $this->assertEquals($this->customer->name, $order->customer_snapshot['name']);

        // Mutate original customer and product
        $this->customer->update(['name' => 'Changed Customer Name 999']);
        $this->product->update(['retail_price' => 99999]);

        $order->refresh();
        // Snapshots must remain unchanged
        $this->assertNotEquals('Changed Customer Name 999', $order->customer_snapshot['name']);
        $this->assertEquals(3000.0, (float) $order->items->first()->unit_price);
    }

    public function test_order_lifecycle_state_machine_transitions_and_inventory_reservation_on_confirmed(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-TEST-001',
            'source' => 'manual_backoffice',
            'status' => 'new',
            'payment_status' => 'pending',
            'currency' => 'AMD',
            'subtotal' => 5000,
            'total' => 5000,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'product_name' => 'Test Item',
            'product_sku' => 'SKU-001',
            'quantity' => 5,
            'unit_price' => 1000,
            'subtotal' => 5000,
            'total' => 5000,
        ]);

        $stockBefore = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(0, (float) $stockBefore->quantity_reserved);

        // Transition: new -> confirmed
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);
        $this->assertNotNull($order->confirmed_at);

        // Verify stock is reserved
        $stockAfter = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(5, (float) $stockAfter->quantity_reserved);
    }

    public function test_order_cancellation_releases_reserved_stock_and_records_reason(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-TEST-002',
            'source' => 'manual_backoffice',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'currency' => 'AMD',
            'subtotal' => 3000,
            'total' => 3000,
            'placed_at' => now(),
            'confirmed_at' => now(),
        ]);

        $order->items()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'product_name' => 'Test Item',
            'product_sku' => 'SKU-001',
            'quantity' => 3,
            'unit_price' => 1000,
            'subtotal' => 3000,
            'total' => 3000,
        ]);

        // Manually reserve 3
        StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->update(['quantity_reserved' => 3]);

        // Cancel order
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'cancelled',
                'reason' => 'Customer changed mind',
            ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('Customer changed mind', $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);

        // Stock reserved should be released
        $stock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(0, (float) $stock->quantity_reserved);
    }

    public function test_order_completion_deducts_stock_via_outbound_inventory_movement(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-TEST-003',
            'source' => 'manual_backoffice',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'currency' => 'AMD',
            'subtotal' => 4000,
            'total' => 4000,
            'placed_at' => now(),
            'confirmed_at' => now(),
        ]);

        $order->items()->create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'product_name' => 'Test Item',
            'product_sku' => 'SKU-001',
            'quantity' => 4,
            'unit_price' => 1000,
            'subtotal' => 4000,
            'total' => 4000,
        ]);

        StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->update(['quantity_on_hand' => 100, 'quantity_reserved' => 4]);

        // Transition: confirmed -> in_progress -> ready -> completed
        $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'in_progress']);

        $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'ready']);

        $compRes = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'completed']);

        $compRes->assertStatus(200);
        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->completed_at);

        // Check stock balance deducted
        $stock = StockLevel::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(96, (float) $stock->quantity_on_hand);
        $this->assertEquals(0, (float) $stock->quantity_reserved);
    }

    public function test_rescheduling_preliminary_order_changes_scheduled_for_without_altering_placed_at_or_created_at(): void
    {
        $originalPlacedAt = Carbon::parse('2026-10-01 10:00:00');
        $initialScheduled = Carbon::parse('2026-10-05 15:00:00');
        $newScheduled = '2026-10-15 18:30:00';

        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-PRELIM-001',
            'order_type' => 'preliminary',
            'source' => 'manual_backoffice',
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'placed_at' => $originalPlacedAt,
            'scheduled_for' => $initialScheduled,
            'currency' => 'AMD',
            'subtotal' => 1000,
            'total' => 1000,
        ]);

        $createdAtTimestamp = $order->created_at->toDateTimeString();

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/orders/{$order->id}/reschedule", [
                'scheduled_for' => $newScheduled,
                'note' => 'Rescheduled upon customer request',
            ]);

        $response->assertStatus(200);
        $order->refresh();

        // Target fulfillment date must be updated
        $this->assertEquals('2026-10-15 18:30:00', $order->scheduled_for->format('Y-m-d H:i:s'));

        // placed_at and created_at MUST NEVER BE ALTERED
        $this->assertEquals($originalPlacedAt->toDateTimeString(), $order->placed_at->toDateTimeString());
        $this->assertEquals($createdAtTimestamp, $order->created_at->toDateTimeString());
    }

    public function test_enforces_tenant_and_branch_isolation_on_orders(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-ISOLATION-01',
            'source' => 'pos',
            'status' => 'new',
            'payment_status' => 'pending',
            'currency' => 'AMD',
            'subtotal' => 1000,
            'total' => 1000,
            'placed_at' => now(),
        ]);

        // Attempt to access with different tenant
        $otherTenant = Tenant::where('id', '!=', $this->tenant->id)->first();
        if ($otherTenant) {
            $response = $this->actingAs($this->user)
                ->withHeaders(['X-Tenant-Slug' => $otherTenant->slug])
                ->getJson("/api/v1/orders/{$order->id}");

            // Either 404 (not found under other tenant scope) or 403
            $this->assertTrue(in_array($response->status(), [403, 404]));
        }
    }

    public function test_sales_orders_endpoint_supports_listing_filtering_and_show(): void
    {
        $order = Order::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'order_number' => 'ORD-SEARCH-TEST-99',
            'customer_snapshot' => [
                'name' => 'Արմեն Պետրոսյան',
                'tax_id' => '02589631',
            ],
            'source' => 'xml_import',
            'status' => 'confirmed',
            'payment_status' => 'partially_paid',
            'currency' => 'AMD',
            'subtotal' => 25000,
            'total' => 25000,
            'placed_at' => now(),
        ]);

        // 1. Test GET /api/v1/sales/orders
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->json('GET', '/api/v1/sales/orders', [
                'search' => 'Պետրոսյան',
                'source' => 'xml_import',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $response->json('data');
        $this->assertNotEmpty($items);
        $this->assertEquals('ORD-SEARCH-TEST-99', $items[0]['order_number']);

        // 2. Test GET /api/v1/sales/orders/{id}
        $showResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->getJson("/api/v1/sales/orders/{$order->id}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', 'ORD-SEARCH-TEST-99');

        // 3. Test PATCH /api/v1/sales/orders/{id}/status
        $statusResponse = $this->actingAs($this->user)
            ->withHeaders(['X-Tenant-Slug' => $this->tenant->slug])
            ->patchJson("/api/v1/sales/orders/{$order->id}/status", [
                'status' => 'processing',
                'reason' => 'Kitchen started preparing',
            ]);

        $statusResponse->assertStatus(200);
        $this->assertEquals('processing', $order->fresh()->status);
    }
}
