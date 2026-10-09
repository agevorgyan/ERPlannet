<?php

namespace Tests\Feature\Phase4;

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
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\Gateways\AmeriaBankGateway;
use App\Infrastructure\Payments\Gateways\CashGateway;
use App\Infrastructure\Payments\Gateways\IdramGateway;
use App\Infrastructure\Payments\Gateways\TelcellGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewaysIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $branch = Branch::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'BR-PAY'],
            ['name' => 'Payment Test Branch', 'is_active' => true, 'is_headquarters' => false]
        );

        $pcs = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'PCS'],
            ['name' => 'Pieces', 'symbol' => 'pcs', 'is_fractional' => false]
        );

        $category = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'pay-goods'],
            ['name' => 'Payment Goods', 'is_active' => true]
        );

        $product = Product::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'sku' => 'PAY-TEST-01'],
            [
                'name' => ['hy' => 'Test Product', 'en' => 'Test Product'],
                'category_id' => $category->id,
                'unit_id' => $pcs->id,
                'sale_price' => 15000,
                'cost_price' => 8000,
                'currency' => 'AMD',
                'is_active' => true,
            ]
        );

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Armen',
            'last_name' => 'Petrosyan',
            'phone' => '+37477112233',
        ]);

        $this->order = app(CreateOrderAction::class)->execute([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'source' => 'direct',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);
    }

    public function test_telcell_gateway_payment_initiation(): void
    {
        $gateway = app(TelcellGateway::class);
        $this->assertEquals('telcell', $gateway->getIdentifier());

        $intent = new PaymentIntentDTO(
            tenantId: $this->tenant->id,
            invoiceId: null,
            amount: 15000.0,
            currency: 'AMD',
            description: 'Test Order Payment',
            returnUrl: 'https://example.com/return',
            cancelUrl: 'https://example.com/cancel'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertEquals('redirect', $result->status);
        $this->assertNotEmpty($result->transactionId);
        $this->assertStringContainsString('telcell.am', $result->redirectUrl);
        $this->assertArrayHasKey('qr_code', $result->gatewayResponse);
        $this->assertArrayHasKey('deep_link', $result->gatewayResponse);
    }

    public function test_idram_gateway_payment_initiation(): void
    {
        $gateway = app(IdramGateway::class);
        $this->assertEquals('idram', $gateway->getIdentifier());

        $intent = new PaymentIntentDTO(
            tenantId: $this->tenant->id,
            invoiceId: null,
            amount: 15000.0,
            currency: 'AMD',
            description: 'Idram Payment',
            returnUrl: 'https://example.com/return',
            cancelUrl: 'https://example.com/cancel'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertEquals('redirect', $result->status);
        $this->assertStringContainsString('idram.am', $result->redirectUrl);
    }

    public function test_ameriabank_gateway_payment_initiation(): void
    {
        $gateway = app(AmeriaBankGateway::class);
        $this->assertEquals('ameriabank', $gateway->getIdentifier());

        $intent = new PaymentIntentDTO(
            tenantId: $this->tenant->id,
            invoiceId: null,
            amount: 15000.0,
            currency: 'AMD',
            description: 'Ameria vPOS Payment',
            returnUrl: 'https://example.com/return',
            cancelUrl: 'https://example.com/cancel'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertEquals('redirect', $result->status);
        $this->assertStringContainsString('ameriabank.am', $result->redirectUrl);
    }

    public function test_cash_gateway_immediate_completion(): void
    {
        $gateway = app(CashGateway::class);
        $this->assertEquals('cash', $gateway->getIdentifier());

        $intent = new PaymentIntentDTO(
            tenantId: $this->tenant->id,
            invoiceId: null,
            amount: 15000.0,
            currency: 'AMD',
            description: 'Cash Payment',
            returnUrl: 'https://example.com/return',
            cancelUrl: 'https://example.com/cancel'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertEquals('successful', $result->status);
    }

    public function test_order_payment_initiation_via_api(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/orders/{$this->order->id}/payments", [
                'gateway' => 'telcell',
                'return_url' => 'https://app.erplannet.am/checkout/success',
                'cancel_url' => 'https://app.erplannet.am/checkout/cancel',
                'payment_method' => 'qr',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gateway', 'telcell')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('payment_transactions', [
            'tenant_id' => $this->tenant->id,
            'order_id' => $this->order->id,
            'gateway' => 'telcell',
            'payment_method' => 'qr',
            'amount' => '15000.00',
            'status' => 'pending',
        ]);
    }

    public function test_payment_webhook_updates_transaction_and_order(): void
    {
        // 1. Initiate Telcell payment
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson("/api/v1/orders/{$this->order->id}/payments", [
                'gateway' => 'telcell',
                'return_url' => 'https://app.erplannet.am/checkout/success',
                'cancel_url' => 'https://app.erplannet.am/checkout/cancel',
            ]);

        $externalTxId = $response->json('data.external_transaction_id');

        // 2. Incoming webhook callback from Telcell
        $webhookResponse = $this->withHeader('X-Tenant-Slug', $this->tenant->slug)
            ->postJson('/api/v1/payments/webhooks/telcell', [
                'transaction_id' => $externalTxId,
                'status' => 'successful',
                'payer_phone' => '+37498112233',
            ]);

        $webhookResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'successful');

        $this->assertDatabaseHas('payment_transactions', [
            'tenant_id' => $this->tenant->id,
            'transaction_id' => $externalTxId,
            'status' => 'successful',
        ]);

        $this->assertEquals('paid', $this->order->fresh()->payment_status);
    }
}
