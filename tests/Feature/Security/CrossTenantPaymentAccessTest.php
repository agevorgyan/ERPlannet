<?php

namespace Tests\Feature\Security;

use App\Domain\Branch\Models\Branch;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\CRM\Models\Customer;
use App\Domain\IAM\Models\User;
use App\Domain\Payments\Actions\InitiateOrderPaymentAction;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantPaymentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected User $userA;
    protected Tenant $tenantB;
    protected User $userB;
    protected Order $orderA;
    protected PaymentTransaction $transactionA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenantA = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->userA = User::where('tenant_id', $this->tenantA->id)->where('is_owner', true)->firstOrFail();

        $this->tenantB = Tenant::create([
            'name' => 'Evil Corp Payments',
            'slug' => 'evilcorp',
            'subdomain' => 'evilcorp',
            'currency' => 'AMD',
        ]);

        $this->userB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Eve Attacker',
            'email' => 'eve@evilcorp.am',
            'password' => bcrypt('secret123'),
            'is_owner' => true,
        ]);

        app(TenantContext::class)->setCurrentTenant($this->tenantA);

        $branchA = Branch::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'BR-PAY-A'],
            ['name' => 'Branch Pay A', 'is_active' => true, 'is_headquarters' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'slug' => 'pay-goods-a'],
            ['name' => 'Goods Pay A', 'is_active' => true]
        );

        $prodA = Product::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'sku' => 'PROD-PAY-A'],
            [
                'name' => ['hy' => 'Product Pay A'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 20000,
                'cost_price' => 10000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $custA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'Aram',
            'last_name' => 'Aramyan',
            'phone' => '+37499112233',
        ]);

        $this->orderA = app(CreateOrderAction::class)->execute([
            'branch_id' => $branchA->id,
            'customer_id' => $custA->id,
            'source' => 'direct',
            'items' => [
                [
                    'product_id' => $prodA->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $initResult = app(InitiateOrderPaymentAction::class)->execute(
            orderId: $this->orderA->id,
            gatewayIdentifier: 'telcell',
            returnUrl: 'https://example.com/success',
            cancelUrl: 'https://example.com/cancel'
        );

        $this->transactionA = $initResult['transaction'];
    }

    public function test_tenant_b_cannot_initiate_payment_for_tenant_a_order(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/orders/{$this->orderA->id}/payments", [
                'gateway' => 'telcell',
                'return_url' => 'https://example.com/hack',
                'cancel_url' => 'https://example.com/cancel',
            ]);

        $response->assertStatus(404);
    }

    public function test_tenant_b_cannot_view_tenant_a_order_payment_transactions(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->getJson("/api/v1/orders/{$this->orderA->id}/payments");

        $response->assertStatus(404);
    }

    public function test_tenant_b_cannot_refund_tenant_a_payment_transaction(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/payments/{$this->transactionA->id}/refund", [
                'amount' => 5000.0,
                'reason' => 'Unauthorized cross-tenant refund attempt',
            ]);

        $response->assertStatus(404);
        $this->assertEquals(0.0, (float) $this->transactionA->fresh()->refunded_amount);
    }

    public function test_tenant_b_cannot_reconcile_tenant_a_payment_transaction(): void
    {
        $response = $this->actingAs($this->userB)
            ->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson("/api/v1/payments/{$this->transactionA->id}/reconcile");

        $response->assertStatus(404);
    }
}
