<?php

namespace Tests\Feature\Phase4;

use App\Domain\Billing\Models\Feature;
use App\Domain\Billing\Models\Plan;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Branch\Models\Branch;
use App\Domain\CRM\Models\Customer;
use App\Domain\Delivery\Actions\AssignDeliveryDriverAction;
use App\Domain\Delivery\Actions\CompleteDeliveryAction;
use App\Domain\Delivery\Actions\CreateDeliveryShipmentAction;
use App\Domain\Delivery\Actions\DispatchShipmentAction;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryFleetAndDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Customer $customer;
    protected Order $order;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $branch = Branch::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-DELIV'],
            ['name' => 'Delivery Hub Branch', 'is_active' => true, 'is_headquarters' => false]
        );

        $warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'WH-DELIV'],
            ['branch_id' => $branch->id, 'name' => 'Delivery Warehouse', 'is_active' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $category = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'delivery-goods'],
            ['name' => 'Delivery Goods', 'is_active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'DELIV-ITEM-01'],
            [
                'name' => ['hy' => 'Premium Boxed Lunch', 'en' => 'Premium Boxed Lunch'],
                'category_id' => $category->id,
                'unit_id' => $pcs->id,
                'sale_price' => 4500,
                'cost_price' => 2000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Karen',
            'last_name' => 'Sargsyan',
            'phone' => '+37491112233',
            'email' => 'karen@example.com',
        ]);

        $this->order = app(CreateOrderAction::class)->execute([
            'branch_id' => $branch->id,
            'customer_id' => $this->customer->id,
            'source' => 'direct',
            'delivery_type' => 'delivery',
            'customer_notes' => 'Customer requested COD delivery',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ]);
    }

    public function test_can_register_delivery_driver_via_api(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/delivery/drivers', [
                'first_name' => 'Arman',
                'last_name' => 'Melkonyan',
                'phone' => '+37498112233',
                'vehicle_type' => 'motorcycle',
                'license_plate' => '36AA777',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.first_name', 'Arman')
            ->assertJsonPath('data.status', 'available');

        $this->assertDatabaseHas('delivery_drivers', [
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Arman',
            'license_plate' => '36AA777',
        ]);
    }

    public function test_delivery_driver_registration_enforces_plan_limit(): void
    {
        $currentCount = DeliveryDriver::where('tenant_id', $this->tenant->id)->count();

        $plan = Plan::where('code', 'enterprise')->firstOrFail();
        $driverLimitFeature = Feature::where('code', 'limit.delivery_drivers')->firstOrFail();
        $plan->features()->updateExistingPivot($driverLimitFeature->id, ['value' => (string) ($currentCount + 1)]);

        // First driver succeeds
        $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/delivery/drivers', [
                'first_name' => 'Driver',
                'last_name' => 'One',
                'phone' => '+37491000001',
            ])->assertStatus(201);

        // Second driver fails
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/delivery/drivers', [
                'first_name' => 'Driver',
                'last_name' => 'Two',
                'phone' => '+37491000002',
            ]);

        $response->assertStatus(402);
    }

    public function test_can_create_delivery_shipment_via_api(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/delivery/shipments', [
                'order_id' => $this->order->id,
                'delivery_address' => 'Sayat-Nova Ave 12, Apt 45, Yerevan',
                'recipient_name' => 'Karen Sargsyan',
                'recipient_phone' => '+37491112233',
                'cod_amount' => 9000.0,
                'notes' => 'Call 10 minutes before arrival',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.cod_amount', '9000.00');

        $trackingNumber = $response->json('data.tracking_number');
        $this->assertStringStartsWith('DLV-', $trackingNumber);

        $this->assertDatabaseHas('delivery_shipments', [
            'tenant_id' => $this->tenant->id,
            'order_id' => $this->order->id,
            'shipment_number' => $trackingNumber,
            'status' => 'pending',
        ]);
    }

    public function test_full_delivery_dispatch_and_completion_lifecycle_with_cod(): void
    {
        $driver = DeliveryDriver::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Hayk',
            'last_name' => 'Hakobyan',
            'phone' => '+37494556677',
            'vehicle_type' => 'car',
            'license_plate' => '77HH777',
            'status' => 'available',
            'is_active' => true,
        ]);

        // 1. Create Shipment
        $shipment = app(CreateDeliveryShipmentAction::class)->execute(
            orderId: $this->order->id,
            deliveryAddress: 'Sayat-Nova 12, Yerevan',
            recipientName: 'Karen Sargsyan',
            recipientPhone: '+37491112233',
            codAmount: 9000.0
        );

        $this->assertEquals('pending', $shipment->status);

        // 2. Assign Driver
        $assignResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/delivery/shipments/{$shipment->id}/assign", [
                'delivery_driver_id' => $driver->id,
            ]);

        $assignResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'assigned')
            ->assertJsonPath('data.delivery_driver_id', $driver->id);

        $this->assertEquals('on_delivery', $driver->fresh()->status);

        // 3. Dispatch Shipment
        $dispatchResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/delivery/shipments/{$shipment->id}/dispatch");

        $dispatchResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'in_transit');

        $this->assertEquals('delivery', $this->order->fresh()->status);

        // 4. Complete Delivery with Proof of Delivery and COD Collection
        $completeResponse = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/delivery/shipments/{$shipment->id}/complete", [
                'received_by_name' => 'Karen Sargsyan',
                'signature_url' => 'https://storage.erplannet.am/signatures/dlv-sig-01.png',
                'photo_url' => 'https://storage.erplannet.am/proofs/dlv-photo-01.jpg',
                'cod_collected' => 9000.0,
                'latitude' => 40.1811,
                'longitude' => 44.5136,
                'notes' => 'Handed over directly to client with cash payment',
            ]);

        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');

        // Verify driver became available again
        $this->assertEquals('available', $driver->fresh()->status);

        // Verify Order status is delivered and payment status is paid
        $freshOrder = $this->order->fresh();
        $this->assertEquals('delivered', $freshOrder->status);
        $this->assertEquals('paid', $freshOrder->payment_status);

        // Verify Proof of Delivery and Shipment records
        $this->assertDatabaseHas('delivery_shipments', [
            'id' => $shipment->id,
            'cod_collected' => '9000.00',
        ]);

        $this->assertDatabaseHas('delivery_proofs', [
            'tenant_id' => $this->tenant->id,
            'delivery_shipment_id' => $shipment->id,
            'received_by_name' => 'Karen Sargsyan',
        ]);

        // Verify Payment Transaction created for COD
        $this->assertDatabaseHas('payment_transactions', [
            'tenant_id' => $this->tenant->id,
            'order_id' => $this->order->id,
            'gateway' => 'cash',
            'payment_method' => 'cash',
            'amount' => '9000.00',
            'status' => 'successful',
        ]);
    }
}
