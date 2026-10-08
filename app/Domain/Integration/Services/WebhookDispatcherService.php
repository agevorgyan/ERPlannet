<?php

declare(strict_types=1);

namespace App\Domain\Integration\Services;

use App\Domain\Integration\Exceptions\SecurityException;
use App\Domain\Integration\Models\TenantWebhookDelivery;
use App\Domain\Integration\Models\TenantWebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookDispatcherService
{
    /**
     * Dispatch an outbound event to all matching tenant subscriptions.
     *
     * @param array<string, mixed> $payload
     * @return array<int, TenantWebhookDelivery>
     */
    public function dispatch(string $tenantId, string $eventName, array $payload): array
    {
        $subscriptions = TenantWebhookSubscription::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $deliveries = [];

        foreach ($subscriptions as $sub) {
            $events = $sub->events ?? [];
            if (!in_array('*', $events, true) && !in_array($eventName, $events, true)) {
                continue;
            }

            $deliveries[] = $this->deliver($sub, $eventName, $payload);
        }

        return $deliveries;
    }

    /**
     * Deliver payload to a single subscription endpoint with SSRF guard.
     */
    public function deliver(TenantWebhookSubscription $subscription, string $eventName, array $payload): TenantWebhookDelivery
    {
        $this->validateSsrf($subscription->target_url);

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        $secret = $subscription->getSecret();
        $signature = hash_hmac('sha256', $jsonPayload, $secret);
        $deliveryId = (string) Str::uuid();

        $delivery = TenantWebhookDelivery::create([
            'id' => $deliveryId,
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'event_name' => $eventName,
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 1,
            'last_attempt_at' => now(),
        ]);

        try {
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'X-ERPlannet-Event' => $eventName,
                'X-ERPlannet-Signature' => $signature,
                'X-ERPlannet-Delivery' => $deliveryId,
            ], $subscription->headers ?? []);

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->withBody($jsonPayload, 'application/json')
                ->post($subscription->target_url);

            $status = $response->successful() ? 'delivered' : 'failed';
            $delivery->update([
                'status' => $status,
                'response_status_code' => $response->status(),
                'response_body' => Str::limit($response->body(), 1000),
            ]);
        } catch (\Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'response_body' => Str::limit($e->getMessage(), 1000),
            ]);
        }

        return $delivery;
    }

    /**
     * SSRF Protection: Block attempts to hit internal networks or cloud metadata APIs.
     */
    public function validateSsrf(string $url): void
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if (empty($host)) {
            throw new \InvalidArgumentException("Invalid webhook URL: {$url}");
        }

        // Prohibit localhost names
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true)) {
            throw new SecurityException("Webhook delivery to loopback is blocked for security.");
        }

        $ip = gethostbyname($host);

        // Block private, loopback, and metadata ranges
        if (
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false ||
            $ip === '169.254.169.254'
        ) {
            throw new SecurityException("Webhook delivery to private/reserved IP [{$ip}] is blocked for security.");
        }
    }
}
