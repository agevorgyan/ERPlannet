<?php

declare(strict_types=1);

namespace App\Domain\Integration\Drivers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Integration\Contracts\ECommerceDriverInterface;
use App\Domain\Integration\Models\TenantIntegration;
use Illuminate\Support\Facades\Http;

class WooCommerceDriver implements ECommerceDriverInterface
{
    public function getProviderName(): string
    {
        return 'woocommerce';
    }

    public function testConnection(TenantIntegration $integration): array
    {
        $creds = $integration->getCredentials();
        $siteUrl = rtrim($creds['url'] ?? '', '/');
        $key = $creds['consumer_key'] ?? '';
        $secret = $creds['consumer_secret'] ?? '';

        if (empty($siteUrl) || empty($key) || empty($secret)) {
            return [
                'success' => false,
                'message' => 'Missing WooCommerce URL, Consumer Key, or Consumer Secret.',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withBasicAuth($key, $secret)
                ->get("{$siteUrl}/wp-json/wc/v3/system_status");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connection established successfully.',
                    'details' => [
                        'environment' => $data['environment']['version'] ?? 'WooCommerce',
                        'server_time' => $data['environment']['server_time'] ?? null,
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'WooCommerce returned error code: ' . $response->status(),
                'details' => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Could not connect to WooCommerce: ' . $e->getMessage(),
            ];
        }
    }

    public function pushProduct(TenantIntegration $integration, Product $product, ?string $externalId = null): array
    {
        $creds = $integration->getCredentials();
        $siteUrl = rtrim($creds['url'] ?? '', '/');
        $key = $creds['consumer_key'] ?? '';
        $secret = $creds['consumer_secret'] ?? '';

        $name = is_array($product->name) ? ($product->name['en'] ?? $product->name['hy'] ?? reset($product->name)) : (string) $product->name;
        $description = is_array($product->description) ? ($product->description['en'] ?? $product->description['hy'] ?? reset($product->description)) : (string) ($product->description ?? '');

        $payload = [
            'name' => $name,
            'sku' => $product->sku,
            'regular_price' => (string) $product->sale_price,
            'description' => $description,
            'manage_stock' => (bool) $product->track_stock,
            'status' => $product->is_active ? 'publish' : 'draft',
        ];

        $checksum = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        $client = Http::timeout(15)->withBasicAuth($key, $secret);

        if ($externalId) {
            $res = $client->put("{$siteUrl}/wp-json/wc/v3/products/{$externalId}", $payload);
        } else {
            $res = $client->post("{$siteUrl}/wp-json/wc/v3/products", $payload);
        }

        if (!$res->successful()) {
            throw new \RuntimeException('Failed to push product to WooCommerce: ' . $res->body());
        }

        $resData = $res->json();
        $newExternalId = (string) ($resData['id'] ?? $externalId);

        return [
            'external_id' => $newExternalId,
            'checksum' => $checksum,
            'response' => $resData,
        ];
    }

    public function pushStock(TenantIntegration $integration, Product $product, float $quantity, ?string $externalId = null): array
    {
        if (!$externalId) {
            throw new \InvalidArgumentException('External product ID is required to update stock on WooCommerce.');
        }

        $creds = $integration->getCredentials();
        $siteUrl = rtrim($creds['url'] ?? '', '/');
        $key = $creds['consumer_key'] ?? '';
        $secret = $creds['consumer_secret'] ?? '';

        $payload = [
            'manage_stock' => true,
            'stock_quantity' => (int) $quantity,
        ];

        $res = Http::timeout(10)
            ->withBasicAuth($key, $secret)
            ->put("{$siteUrl}/wp-json/wc/v3/products/{$externalId}", $payload);

        return [
            'external_id' => $externalId,
            'stock_quantity' => $quantity,
            'success' => $res->successful(),
        ];
    }

    public function pushPrice(TenantIntegration $integration, Product $product, float $price, ?string $externalId = null): array
    {
        if (!$externalId) {
            throw new \InvalidArgumentException('External product ID is required to update price on WooCommerce.');
        }

        $creds = $integration->getCredentials();
        $siteUrl = rtrim($creds['url'] ?? '', '/');
        $key = $creds['consumer_key'] ?? '';
        $secret = $creds['consumer_secret'] ?? '';

        $payload = [
            'regular_price' => (string) $price,
        ];

        $res = Http::timeout(10)
            ->withBasicAuth($key, $secret)
            ->put("{$siteUrl}/wp-json/wc/v3/products/{$externalId}", $payload);

        return [
            'external_id' => $externalId,
            'price' => $price,
            'success' => $res->successful(),
        ];
    }

    public function fetchOrders(TenantIntegration $integration, array $params = []): array
    {
        $creds = $integration->getCredentials();
        $siteUrl = rtrim($creds['url'] ?? '', '/');
        $key = $creds['consumer_key'] ?? '';
        $secret = $creds['consumer_secret'] ?? '';

        $queryParams = array_merge([
            'per_page' => 20,
            'status' => 'processing,completed,on-hold',
        ], $params);

        $res = Http::timeout(20)
            ->withBasicAuth($key, $secret)
            ->get("{$siteUrl}/wp-json/wc/v3/orders", $queryParams);

        if (!$res->successful()) {
            throw new \RuntimeException('Failed to fetch orders from WooCommerce: ' . $res->body());
        }

        return $res->json() ?? [];
    }

    public function updateOrderStatus(TenantIntegration $integration, string $externalOrderId, string $status): bool
    {
        $creds = $integration->getCredentials();
        $siteUrl = rtrim($creds['url'] ?? '', '/');
        $key = $creds['consumer_key'] ?? '';
        $secret = $creds['consumer_secret'] ?? '';

        // Map ERP status to WooCommerce status if needed
        $wcStatus = match ($status) {
            'dispatched', 'shipped' => 'completed',
            'confirmed', 'paid' => 'processing',
            'cancelled', 'voided' => 'cancelled',
            'refunded' => 'refunded',
            default => $status,
        };

        $res = Http::timeout(10)
            ->withBasicAuth($key, $secret)
            ->put("{$siteUrl}/wp-json/wc/v3/orders/{$externalOrderId}", [
                'status' => $wcStatus,
            ]);

        return $res->successful();
    }

    public function verifyWebhookSignature(string $rawPayload, string $signature, string $secret): bool
    {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $rawPayload, $secret, true));
        return hash_equals($expected, $signature);
    }
}
