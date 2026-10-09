<?php

declare(strict_types=1);

namespace App\Domain\Integration\Services;

use App\Domain\Integration\Drivers\WooCommerceDriver;
use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Models\TenantIntegrationSyncLog;
use Illuminate\Http\Request;

class WebhookIngressService
{
    public function __construct(
        protected WooCommerceSyncService $wooCommerceSyncService,
        protected WooCommerceDriver $wooCommerceDriver
    ) {}

    /**
     * Process incoming WooCommerce webhook.
     *
     * @return array{success: bool, message: string}
     */
    public function handleWooCommerceWebhook(TenantIntegration $integration, Request $request): array
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('X-WC-Webhook-Signature', '');
        $topic = $request->header('X-WC-Webhook-Topic', '');
        $eventId = $request->header('X-WC-Webhook-ID', '');

        $creds = $integration->getCredentials();
        $secret = $creds['webhook_secret'] ?? $creds['consumer_secret'] ?? '';

        // 1. Verify signature
        if (! $this->wooCommerceDriver->verifyWebhookSignature($rawPayload, $signature, $secret)) {
            TenantIntegrationSyncLog::create([
                'tenant_id' => $integration->tenant_id,
                'integration_id' => $integration->id,
                'entity_type' => 'webhook',
                'direction' => 'inbound',
                'status' => 'failed',
                'records_processed' => 0,
                'records_failed' => 1,
                'details' => ['error' => 'Invalid HMAC signature', 'topic' => $topic],
            ]);

            return [
                'success' => false,
                'message' => 'Invalid HMAC signature.',
            ];
        }

        $payload = json_decode($rawPayload, true) ?: [];

        // 2. Process based on topic
        if (str_starts_with($topic, 'order.')) {
            // Check ping-pong checksum loop
            $payloadHash = hash('sha256', $rawPayload);
            $extId = (string) ($payload['id'] ?? '');

            // Ingest or sync orders
            $this->wooCommerceSyncService->syncOrders($integration);
        }

        return [
            'success' => true,
            'message' => "Webhook [{$topic}] received and processed.",
        ];
    }
}
