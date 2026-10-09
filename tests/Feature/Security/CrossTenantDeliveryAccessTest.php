<?php

namespace Tests\Feature\Security;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\Delivery\Actions\CreateDeliveryShipmentAction;
use App\Domain\Delivery\Models\CodSettlement;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantDeliveryAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected User $userA;

    protected Tenant $tenantB;

    protected User $userB;

    protected DeliveryDriver $driverA;

    protected DeliveryShipment $shipmentA;

    protected Order $orderA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenantA = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->userA = User::where('tenant_id', $this->tenantA->id)->where('is_owner', true)->firstOrFail();

        $this->tenantB = Tenant::create([
            'name' => 'Competitor Delivery',
            'slug' => 'competitor-deliv',
            'subdomain' => 'competitor-deliv',
            'currency' => 'AMD',
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Courier Bob',
            'email' => 'bob@competitor-deliv.am',
            'password' => bcrypt('secret123'),
            'is_owner' => true,
        ]);

        app(TenantContext::class)->setCurrentTenant($this->tenantA);

        $branchA = Branch::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'BR-DELIV-A'],
            ['name' => 'Branch Delivery A', 'is_active' => true, 'is_headquarters' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'slug' => 'deliv-cat-a'],
            ['name' => 'Delivery Goods A', 'is_active' => true]
        );

        $prodA = Product::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'sku' => 'DELIV-SEC-01'],
            [
                'name' => ['hy' => 'Delivery Sec Item'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 10000,
                'cost_price' => 4000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $custA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'David',
            'last_name' => 'Davidyan',
            'phone' => '+37493112233',
        ]);

        $this->orderA = app(CreateOrderAction::class)->execute([
            'branch_id' => $branchA->id,
            'customer_id' => $custA->id,
            'source' => 'direct',
            'delivery_type' => 'delivery',
            'items' => [
                ['product_id' => $prodA->id, 'quantity' => 1],
            ],
        ]);

        $this->driverA = DeliveryDriver::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'Artur',
            'last_name' => 'Arturyan',
            'phone' => '+37494112233',
            'vehicle_type' => 'car',
            'license_plate' => '11AA111',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->shipmentA = app(CreateDeliveryShipmentAction::class)->execute(
            orderId: $this->orderA->id,
            deliveryAddress: 'Abovyan 10, Yerevan',
            recipientName: 'David Davidyan',
            recipientPhone: '+37493112233',
            codAmount: 10000.0
        );
    }

    public function test_tenant_b_cannot_view_tenant_a_shipment(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->getJson("/api/v1/delivery/shipments/{$this->shipmentA->id}");

        $response->assertStatus(404);
    }

    public function test_tenant_b_cannot_assign_driver_to_tenant_a_shipment(): void
    {
        // Tenant B creates its own driver
        $driverB = DeliveryDriver::create([
            'tenant_id' => $this->tenantB->id,
            'first_name' => 'Bob',
            'last_name' => 'Driver',
            'phone' => '+37491000999',
            'vehicle_type' => 'motorcycle',
            'status' => 'available',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/delivery/shipments/{$this->shipmentA->id}/assign", [
                'delivery_driver_id' => $driverB->id,
            ]);

        $response->assertStatus(404);
        $this->assertNull($this->shipmentA->fresh()->delivery_driver_id);
    }

    public function test_tenant_b_cannot_complete_tenant_a_shipment(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/delivery/shipments/{$this->shipmentA->id}/complete", [
                'received_by_name' => 'Impostor',
                'cod_collected' => 10000.0,
            ]);

        $response->assertStatus(404);
        $this->assertEquals('pending', $this->shipmentA->fresh()->status);
    }

    public function test_tenant_b_cannot_fail_or_return_tenant_a_shipment(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/delivery/shipments/{$this->shipmentA->id}/fail", [
                'reason' => 'Cross-tenant failure exploit',
            ]);

        $response->assertStatus(404);
        $this->assertEquals('pending', $this->shipmentA->fresh()->status);
    }

    public function test_tenant_b_cannot_settle_tenant_a_cod_settlement(): void
    {
        $codSettlementA = CodSettlement::where('delivery_shipment_id', $this->shipmentA->id)->firstOrFail();

        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/delivery/cod-settlements/{$codSettlementA->id}/settle");

        $response->assertStatus(404);
        $this->assertEquals('expected', $codSettlementA->fresh()->status);
    }
}
