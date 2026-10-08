<?php

namespace Tests\Feature\Security;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\POS\Actions\OpenPosSessionAction;
use App\Domain\POS\Actions\PosCheckoutAction;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantPosAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected User $userA;
    protected Tenant $tenantB;
    protected User $userB;
    protected PosTerminal $terminalA;
    protected PosSession $sessionA;
    protected Order $posOrderA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenantA = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->userA = User::where('tenant_id', $this->tenantA->id)->where('is_owner', true)->firstOrFail();

        $this->tenantB = Tenant::create([
            'name' => 'Competitor Market',
            'slug' => 'competitor-pos',
            'subdomain' => 'competitor-pos',
            'currency' => 'AMD',
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Intruder Cashier',
            'email' => 'intruder@competitor-pos.am',
            'password' => bcrypt('secret123'),
            'is_owner' => true,
        ]);

        $plan = Plan::where('code', 'enterprise')->firstOrFail();
        Subscription::create([
            'tenant_id' => $this->tenantB->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        app(TenantContext::class)->setCurrentTenant($this->tenantA);

        $branchA = Branch::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'BR-POS-A'],
            ['name' => 'Branch POS A', 'is_active' => true, 'is_headquarters' => true]
        );

        $warehouseA = Warehouse::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'WH-POS-A'],
            ['branch_id' => $branchA->id, 'name' => 'Warehouse POS A', 'is_active' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'slug' => 'pos-sec-cat'],
            ['name' => 'Security Test Goods', 'is_active' => true]
        );

        $prodA = Product::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'sku' => 'POS-SEC-01'],
            [
                'name' => ['hy' => 'Security Coffee'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 1500,
                'cost_price' => 500,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        app(RecordStockMovementAction::class)->execute(
            warehouseId: $warehouseA->id,
            productId: $prodA->id,
            productVariantId: null,
            type: 'adjustment_plus',
            quantity: 20.0,
            unitCost: 500.0,
            referenceType: 'test_seed',
            referenceId: (string) \Illuminate\Support\Str::uuid(),
            notes: 'Seed stock'
        );

        $this->terminalA = PosTerminal::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $branchA->id,
            'warehouse_id' => $warehouseA->id,
            'code' => 'POS-TERM-A',
            'name' => 'Main POS Terminal A',
            'is_active' => true,
        ]);

        $this->sessionA = app(OpenPosSessionAction::class)->execute(
            posTerminalId: $this->terminalA->id,
            cashierId: $this->userA->id,
            openingCash: 10000.0
        );

        $this->posOrderA = app(PosCheckoutAction::class)->execute(
            posSessionId: $this->sessionA->id,
            items: [
                ['product_id' => $prodA->id, 'quantity' => 1, 'unit_price' => 1500],
            ],
            payments: [
                ['gateway' => 'cash', 'method' => 'cash', 'amount' => 1500],
            ]
        );
    }

    public function test_tenant_b_cannot_open_session_on_tenant_a_terminal(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson('/api/v1/pos/sessions/open', [
                'pos_terminal_id' => $this->terminalA->id,
                'opening_cash' => 5000.0,
            ]);

        $response->assertStatus(404);
    }

    public function test_tenant_b_cannot_record_cash_movement_on_tenant_a_session(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson('/api/v1/pos/sessions/cash-movement', [
                'pos_session_id' => $this->sessionA->id,
                'type' => 'cash_out',
                'amount' => 5000.0,
                'reason' => 'Unauthorized theft attempt',
            ]);

        $response->assertStatus(404);
    }

    public function test_tenant_b_cannot_close_tenant_a_session(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson('/api/v1/pos/sessions/close', [
                'pos_session_id' => $this->sessionA->id,
                'closing_cash_declared' => 1000.0,
            ]);

        $response->assertStatus(404);
        $this->assertEquals('open', $this->sessionA->fresh()->status);
    }

    public function test_tenant_b_cannot_view_tenant_a_z_report(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->getJson("/api/v1/pos/sessions/{$this->sessionA->id}/z-report");

        $response->assertStatus(404);
    }

    public function test_tenant_b_cannot_refund_tenant_a_pos_order(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/pos/orders/{$this->posOrderA->id}/refund", [
                'amount' => 1500.0,
                'reason' => 'Cross-tenant malicious refund',
            ]);

        $response->assertStatus(404);
        $this->assertEquals('paid', $this->posOrderA->fresh()->payment_status);
    }

    public function test_tenant_b_cannot_void_tenant_a_pos_order(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/pos/orders/{$this->posOrderA->id}/void", [
                'reason' => 'Cross-tenant malicious void',
            ]);

        $response->assertStatus(404);
        $this->assertNotEquals('cancelled', $this->posOrderA->fresh()->status);
    }
}
