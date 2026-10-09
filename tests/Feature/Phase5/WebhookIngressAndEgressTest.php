<?php

declare(strict_types=1);

namespace Tests\Feature\Phase5;

use App\Domain\IAM\Models\User;
use App\Domain\Integration\Exceptions\SecurityException;
use App\Domain\Integration\Models\TenantWebhookDelivery;
use App\Domain\Integration\Models\TenantWebhookSubscription;
use App\Domain\Integration\Services\WebhookDispatcherService;
use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookIngressAndEgressTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::where('slug', 'gourmet')->firstOrFail();
        $this->user = User::where('tenant_id', $this->tenant->id)->where('is_owner', true)->firstOrFail();

        app(TenantContext::class)->setCurrentTenant($this->tenant);
    }

    public function test_can_register_webhook_subscription_and_dispatch_event(): void
    {
        Http::fake([
            'https://webhook.site/my-endpoint' => Http::response(['received' => true], 200),
        ]);

        // 1. Register subscription via API
        $response = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson('/api/v1/webhooks/subscriptions', [
                'name' => 'CRM Order Events',
                'target_url' => 'https://webhook.site/my-endpoint',
                'events' => ['order.created', 'order.dispatched'],
                'secret' => 'whsec_testing_signature_key',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'CRM Order Events');

        $subId = $response->json('data.id');
        $this->assertDatabaseHas('tenant_webhook_subscriptions', [
            'id' => $subId,
            'tenant_id' => $this->tenant->id,
            'name' => 'CRM Order Events',
        ]);

        // 2. Dispatch event
        $dispatcher = app(WebhookDispatcherService::class);
        $deliveries = $dispatcher->dispatch($this->tenant->id, 'order.created', [
            'order_number' => 'ORD-9988',
            'total' => 5000.00,
        ]);

        $this->assertCount(1, $deliveries);
        $this->assertEquals('delivered', $deliveries[0]->status);
        $this->assertEquals(200, $deliveries[0]->response_status_code);

        // Verify request signature was sent
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-ERPlannet-Event', 'order.created') &&
                   $request->hasHeader('X-ERPlannet-Signature');
        });
    }

    public function test_ssrf_protection_blocks_internal_and_cloud_metadata_ips(): void
    {
        $dispatcher = app(WebhookDispatcherService::class);

        // 1. Localhost should throw SecurityException
        $this->expectException(SecurityException::class);
        $dispatcher->validateSsrf('http://127.0.0.1:8000/internal-admin');
    }

    public function test_ssrf_blocks_metadata_ip(): void
    {
        $dispatcher = app(WebhookDispatcherService::class);

        // 2. AWS / Cloud metadata IP 169.254.169.254
        $this->expectException(SecurityException::class);
        $dispatcher->validateSsrf('http://169.254.169.254/latest/meta-data/');
    }

    public function test_can_retry_failed_webhook_delivery(): void
    {
        Http::fake([
            'https://webhook.site/retry-endpoint' => Http::response(['status' => 'ok'], 200),
        ]);

        $sub = new TenantWebhookSubscription([
            'tenant_id' => $this->tenant->id,
            'name' => 'Failed Sub',
            'target_url' => 'https://webhook.site/retry-endpoint',
            'events' => ['order.created'],
            'is_active' => true,
        ]);
        $sub->setSecret('secret_retry');
        $sub->save();

        $delivery = TenantWebhookDelivery::create([
            'tenant_id' => $this->tenant->id,
            'subscription_id' => $sub->id,
            'event_name' => 'order.created',
            'payload' => ['id' => 1],
            'status' => 'failed',
            'attempts' => 1,
            'response_status_code' => 500,
        ]);

        $retryRes = $this->actingAs($this->user)
            ->withHeader('X-Tenant-ID', $this->tenant->id)
            ->postJson("/api/v1/webhooks/deliveries/{$delivery->id}/retry");

        $retryRes->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
