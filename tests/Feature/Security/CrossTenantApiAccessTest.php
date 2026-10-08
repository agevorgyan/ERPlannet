<?php

namespace Tests\Feature\Security;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Billing\Models\Invoice;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantApiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected User $userA;
    protected Tenant $tenantB;
    protected User $userB;
    protected Order $orderA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenantA = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->userA = User::where('tenant_id', $this->tenantA->id)->where('is_owner', true)->firstOrFail();

        $this->tenantB = Tenant::create([
            'name' => 'Competitor Corp',
            'slug' => 'competitor',
            'subdomain' => 'competitor',
            'currency' => 'AMD',
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Hacker Bob',
            'email' => 'bob@competitor.am',
            'password' => bcrypt('secret123'),
            'is_owner' => true,
        ]);

        // Setup Tenant A data
        app(TenantContext::class)->setCurrentTenant($this->tenantA);

        $branchA = Branch::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'BR-A'],
            ['name' => 'Branch A', 'is_active' => true, 'is_headquarters' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'slug' => 'goods-a'],
            ['name' => 'Goods A', 'is_active' => true]
        );

        $prodA = Product::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'sku' => 'PROD-A-01'],
            [
                'name' => ['hy' => 'Product A'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 5000,
                'cost_price' => 2000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $custA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'VIP',
            'last_name' => 'Customer A',
            'phone' => '+37499000001',
        ]);

        $this->orderA = app(CreateOrderAction::class)->execute([
            'branch_id' => $branchA->id,
            'customer_id' => $custA->id,
            'source' => 'direct',
            'items' => [
                [
                    'product_id' => $prodA->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        AuditLog::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->userA->id,
            'action' => 'order.created',
            'entity_type' => Order::class,
            'entity_id' => $this->orderA->id,
            'new_values' => ['order_number' => $this->orderA->order_number],
            'created_at' => now(),
        ]);
    }

    public function test_tenant_b_cannot_view_tenant_a_order(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->getJson("/api/v1/orders/{$this->orderA->id}");

        $response->assertStatus(404);
    }

    public function test_tenant_b_order_listing_never_includes_tenant_a_orders(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200);
        $orderIds = collect($response->json('data'))->pluck('id');
        $this->assertFalse($orderIds->contains($this->orderA->id));
    }

    public function test_tenant_b_cannot_transition_tenant_a_order_status(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/orders/{$this->orderA->id}/status", [
                'status' => 'cancelled',
            ]);

        $response->assertStatus(404);
        $this->assertNotEquals('cancelled', $this->orderA->fresh()->status);
    }

    public function test_tenant_b_cannot_cancel_tenant_a_order(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/orders/{$this->orderA->id}/cancel", [
                'reason' => 'Malicious cancellation attempt',
            ]);

        $response->assertStatus(404);
        $this->assertNotEquals('cancelled', $this->orderA->fresh()->status);
    }

    public function test_tenant_isolation_on_audit_logs_query(): void
    {
        app(TenantContext::class)->setCurrentTenant($this->tenantB);
        $auditLogsForB = AuditLog::where('entity_id', $this->orderA->id)->get();
        $this->assertCount(0, $auditLogsForB);

        app(TenantContext::class)->setCurrentTenant($this->tenantA);
        $auditLogsForA = AuditLog::where('entity_id', $this->orderA->id)->get();
        $this->assertCount(1, $auditLogsForA);
    }
}
