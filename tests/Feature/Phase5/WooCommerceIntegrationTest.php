<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\IAM\Models\User;
use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Models\TenantIntegrationEntityMap;
use App\Domain\Integration\Models\TenantIntegrationSyncLog;
use App\Domain\Integration\Services\WooCommerceSyncService;
use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WooCommerceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);

        $category = Category::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'slug' => 'breads-test'],
            ['name' => ['en' => 'Breads', 'hy' => 'Հացաբուլկեղեն'], 'is_active' => true]
        );

        $unit = Unit::firstOrCreate(
            ['tenant_id' => $this->tenant->id, 'code' => 'pcs-test'],
            ['name' => ['en' => 'Piece', 'hy' => 'Հատ'], 'symbol' => 'pcs']
        );

        $this->product = Product::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => 'TEST-WC-001',
            'name' => ['en' => 'Artisan Sourdough', 'hy' => 'Թթխմորով Հաց'],
            'sale_price' => 1200.00,
            'cost_price' => 600.00,
            'track_stock' => true,
            'is_active' => true,
        ]);
    }

    public function test_can_connect_and_test_woocommerce_integration(): void
    {
        Http::fake([
            'https://shop.example.com/wp-json/wc/v3/system_status' => Http::response([
                'environment' => [
                    'version' => '8.5.1',
                    'server_time' => '2026-10-08T15:00:00Z',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/v1/integrations', [
                'provider' => 'woocommerce',
                'name' => 'Main Online Shop',
                'credentials' => [
                    'url' => 'https://shop.example.com',
                    'consumer_key' => 'ck_test123456789',
                    'consumer_secret' => 'cs_test987654321',
                    'webhook_secret' => 'whsec_secret_123',
                ],
                'settings' => [
                    'auto_stock_sync' => true,
                    'order_prefix' => 'WC-',
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Main Online Shop')
            ->assertJsonPath('data.provider', 'woocommerce');

        $integrationId = $response->json('data.id');

        // Test connection endpoint
        $testRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/v1/integrations/{$integrationId}/test-connection");

        $testRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Connection established successfully.');
    }

    public function test_can_sync_products_to_woocommerce_with_checksum_and_mapping(): void
    {
        Http::fake([
            'https://shop.example.com/wp-json/wc/v3/products*' => Http::response([
                'id' => 9012,
                'name' => 'Artisan Sourdough',
                'sku' => 'TEST-WC-001',
                'price' => '1200',
            ], 201),
        ]);

        $integration = new TenantIntegration([
            'tenant_id' => $this->tenant->id,
            'provider' => 'woocommerce',
            'name' => 'Store 1',
            'status' => 'active',
            'settings' => ['order_prefix' => 'WC-'],
        ]);
        $integration->setCredentials([
            'url' => 'https://shop.example.com',
            'consumer_key' => 'ck_test',
            'consumer_secret' => 'cs_test',
        ]);
        $integration->save();

        $syncService = app(WooCommerceSyncService::class);
        $result = $syncService->syncProducts($integration, [$this->product->id]);

        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(0, $result['failed']);

        // Verify entity map
        $map = TenantIntegrationEntityMap::where('tenant_id', $this->tenant->id)
            ->where('integration_id', $integration->id)
            ->where('internal_id', $this->product->id)
            ->first();

        $this->assertNotNull($map);
        $this->assertEquals('9012', $map->external_id);
        $this->assertNotEmpty($map->checksum);

        // Verify sync log created
        $log = TenantIntegrationSyncLog::where('integration_id', $integration->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('product', $log->entity_type);
        $this->assertEquals('success', $log->status);
    }

    public function test_can_ingest_orders_from_woocommerce_idempotently(): void
    {
        Http::fake([
            'https://shop.example.com/wp-json/wc/v3/orders*' => Http::response([
                [
                    'id' => 7741,
                    'status' => 'processing',
                    'currency' => 'AMD',
                    'total' => '2400.00',
                    'total_tax' => '0.00',
                    'billing' => [
                        'first_name' => 'Tigran',
                        'last_name' => 'Hakobyan',
                        'email' => 'tigran@example.am',
                        'phone' => '+37491123456',
                    ],
                    'line_items' => [
                        [
                            'id' => 101,
                            'name' => 'Artisan Sourdough',
                            'sku' => 'TEST-WC-001',
                            'quantity' => 2,
                            'price' => '1200.00',
                            'total' => '2400.00',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $integration = new TenantIntegration([
            'tenant_id' => $this->tenant->id,
            'provider' => 'woocommerce',
            'name' => 'Store 1',
            'status' => 'active',
            'settings' => ['order_prefix' => 'WC-'],
        ]);
        $integration->setCredentials([
            'url' => 'https://shop.example.com',
            'consumer_key' => 'ck_test',
            'consumer_secret' => 'cs_test',
        ]);
        $integration->save();

        $syncService = app(WooCommerceSyncService::class);
        $result = $syncService->syncOrders($integration);

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['skipped']);

        // Verify order created in ERP
        $order = Order::where('tenant_id', $this->tenant->id)
            ->where('order_number', 'WC-7741')
            ->first();

        $this->assertNotNull($order);
        $this->assertEquals(2400.00, $order->total);
        $this->assertCount(1, $order->items);

        // Run sync second time -> must skip already imported order (Idempotency)
        $secondResult = $syncService->syncOrders($integration);
        $this->assertEquals(0, $secondResult['imported']);
        $this->assertEquals(1, $secondResult['skipped']);
    }

    public function test_inbound_webhook_verifies_hmac_signature_correctly(): void
    {
        $integration = new TenantIntegration([
            'tenant_id' => $this->tenant->id,
            'provider' => 'woocommerce',
            'name' => 'Store Webhook',
            'status' => 'active',
        ]);
        $secret = 'super_secret_webhook_key_2026';
        $integration->setCredentials([
            'webhook_secret' => $secret,
            'url' => 'https://shop.example.com',
        ]);
        $integration->save();

        $payload = json_encode(['id' => 8852, 'status' => 'completed']);
        $validSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        // 1. Invalid signature must return 401
        $badResponse = $this->call(
            'POST',
            "/api/v1/webhooks/ingress/woocommerce/{$integration->id}",
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WC_WEBHOOK_SIGNATURE' => 'invalid_signature_hash',
                'HTTP_X_WC_WEBHOOK_TOPIC' => 'order.updated',
            ],
            $payload
        );

        $badResponse->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid HMAC signature.');

        // 2. Valid signature must succeed
        Http::fake([
            'https://shop.example.com/wp-json/wc/v3/orders*' => Http::response([], 200),
        ]);

        $goodResponse = $this->call(
            'POST',
            "/api/v1/webhooks/ingress/woocommerce/{$integration->id}",
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WC_WEBHOOK_SIGNATURE' => $validSignature,
                'HTTP_X_WC_WEBHOOK_TOPIC' => 'order.updated',
            ],
            $payload
        );

        $goodResponse->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
