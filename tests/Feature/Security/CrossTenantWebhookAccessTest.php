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

class CrossTenantWebhookAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected User $userA;
    protected Tenant $tenantB;
    protected Order $orderA;
    protected PaymentTransaction $transactionA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenantA = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->userA = User::where('tenant_id', $this->tenantA->id)->where('is_owner', true)->firstOrFail();

        $this->tenantB = Tenant::create([
            'name' => 'Competitor Webhooks',
            'slug' => 'competitor-webhooks',
            'subdomain' => 'competitor-webhooks',
            'currency' => 'AMD',
        ]);

        app(TenantContext::class)->setCurrentTenant($this->tenantA);

        $branchA = Branch::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'BR-WH-A'],
            ['name' => 'Branch WH A', 'is_active' => true, 'is_headquarters' => true]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $cat = Category::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'slug' => 'wh-cat-a'],
            ['name' => 'Webhook Goods A', 'is_active' => true]
        );

        $prodA = Product::firstOrCreate(
            ['tenant_id' => $this->tenantA->id, 'sku' => 'WH-TEST-01'],
            [
                'name' => ['hy' => 'Webhook Product'],
                'category_id' => $cat->id,
                'unit_id' => $pcs->id,
                'sale_price' => 7500,
                'cost_price' => 3000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $custA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'Suren',
            'last_name' => 'Surenyan',
            'phone' => '+37498765432',
        ]);

        $this->orderA = app(CreateOrderAction::class)->execute([
            'branch_id' => $branchA->id,
            'customer_id' => $custA->id,
            'source' => 'direct',
            'items' => [
                ['product_id' => $prodA->id, 'quantity' => 1],
            ],
        ]);

        $res = app(InitiateOrderPaymentAction::class)->execute(
            orderId: $this->orderA->id,
            gatewayIdentifier: 'telcell',
            returnUrl: 'https://example.com/ret',
            cancelUrl: 'https://example.com/can'
        );

        $this->transactionA = $res['transaction'];
    }

    public function test_tenant_b_cannot_hijack_tenant_a_transaction_via_scoped_webhook(): void
    {
        // When request comes with X-Tenant-Slug for Tenant B, it must not find or mutate Tenant A's transaction
        $response = $this->withHeader('X-Tenant-Slug', $this->tenantB->slug)
            ->postJson('/api/v1/payments/webhooks/telcell', [
                'transaction_id' => $this->transactionA->transaction_id,
                'status' => 'successful',
            ]);

        $response->assertStatus(404);
        $this->assertEquals('pending', $this->transactionA->fresh()->status);
        $this->assertEquals('unpaid', $this->orderA->fresh()->payment_status);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $response = $this->withHeader('X-Tenant-Slug', $this->tenantA->slug)
            ->withHeader('X-Telcell-Signature', 'invalid_signature_hash')
            ->withHeader('X-Secret', 'secret_key')
            ->postJson('/api/v1/payments/webhooks/telcell', [
                'transaction_id' => $this->transactionA->transaction_id,
                'status' => 'successful',
            ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $this->transactionA->fresh()->status);
    }

    public function test_webhook_replay_protection_prevents_duplicate_processing(): void
    {
        // 1. First webhook arrives and completes payment
        $response1 = $this->withHeader('X-Tenant-Slug', $this->tenantA->slug)
            ->postJson('/api/v1/payments/webhooks/telcell', [
                'transaction_id' => $this->transactionA->transaction_id,
                'status' => 'successful',
            ]);

        $response1->assertStatus(200)
            ->assertJsonPath('data.status', 'successful');

        $this->assertEquals('successful', $this->transactionA->fresh()->status);
        $this->assertEquals('paid', $this->orderA->fresh()->payment_status);

        // 2. Second duplicate/replay webhook arrives for same transaction
        $response2 = $this->withHeader('X-Tenant-Slug', $this->tenantA->slug)
            ->postJson('/api/v1/payments/webhooks/telcell', [
                'transaction_id' => $this->transactionA->transaction_id,
                'status' => 'successful',
            ]);

        $response2->assertStatus(200)
            ->assertJsonPath('data.idempotent', true);
    }
}
