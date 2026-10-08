<?php

declare(strict_types=1);

namespace App\Domain\Integration\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\CRM\Models\Customer;
use App\Domain\Integration\Contracts\ECommerceDriverInterface;
use App\Domain\Integration\Models\TenantIntegration;
use App\Domain\Integration\Models\TenantIntegrationEntityMap;
use App\Domain\Integration\Models\TenantIntegrationSyncLog;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class WooCommerceSyncService
{
    public function __construct(
        protected IntegrationManager $integrationManager
    ) {}

    /**
     * Push tenant products to WooCommerce and record entity maps.
     *
     * @param array<int, string>|null $productIds
     * @return array{processed: int, failed: int, details: array}
     */
    public function syncProducts(TenantIntegration $integration, ?array $productIds = null): array
    {
        $startTime = microtime(true);
        /** @var ECommerceDriverInterface $driver */
        $driver = $this->integrationManager->driverFor($integration);

        $query = Product::where('tenant_id', $integration->tenant_id)
            ->where('is_active', true);

        if (!empty($productIds)) {
            $query->whereIn('id', $productIds);
        }

        $products = $query->get();

        $processed = 0;
        $failed = 0;
        $errors = [];

        foreach ($products as $product) {
            try {
                // Find existing mapping if any
                $map = TenantIntegrationEntityMap::where('tenant_id', $integration->tenant_id)
                    ->where('integration_id', $integration->id)
                    ->where('entity_type', 'product')
                    ->where('internal_id', $product->id)
                    ->first();

                $result = $driver->pushProduct($integration, $product, $map?->external_id);

                // Update or create map
                TenantIntegrationEntityMap::updateOrCreate(
                    [
                        'tenant_id' => $integration->tenant_id,
                        'integration_id' => $integration->id,
                        'entity_type' => 'product',
                        'internal_id' => $product->id,
                    ],
                    [
                        'external_id' => $result['external_id'],
                        'checksum' => $result['checksum'],
                        'last_synced_at' => now(),
                    ]
                );

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        TenantIntegrationSyncLog::create([
            'tenant_id' => $integration->tenant_id,
            'integration_id' => $integration->id,
            'entity_type' => 'product',
            'direction' => 'outbound',
            'status' => $failed === 0 ? 'success' : ($processed > 0 ? 'partial' : 'failed'),
            'records_processed' => $processed,
            'records_failed' => $failed,
            'details' => ['errors' => $errors],
            'duration_ms' => $durationMs,
        ]);

        $integration->update(['last_sync_at' => now()]);

        return [
            'processed' => $processed,
            'failed' => $failed,
            'details' => $errors,
        ];
    }

    /**
     * Push current stock levels to WooCommerce for mapped products.
     */
    public function syncStock(TenantIntegration $integration): array
    {
        $startTime = microtime(true);
        /** @var ECommerceDriverInterface $driver */
        $driver = $this->integrationManager->driverFor($integration);

        $maps = TenantIntegrationEntityMap::where('tenant_id', $integration->tenant_id)
            ->where('integration_id', $integration->id)
            ->where('entity_type', 'product')
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($maps as $map) {
            $product = Product::where('tenant_id', $integration->tenant_id)->find($map->internal_id);
            if (!$product) continue;

            $totalStock = (float) StockLevel::where('tenant_id', $integration->tenant_id)
                ->where('product_id', $product->id)
                ->sum('quantity_on_hand');

            try {
                $driver->pushStock($integration, $product, $totalStock, $map->external_id);
                $processed++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        TenantIntegrationSyncLog::create([
            'tenant_id' => $integration->tenant_id,
            'integration_id' => $integration->id,
            'entity_type' => 'stock',
            'direction' => 'outbound',
            'status' => $failed === 0 ? 'success' : 'partial',
            'records_processed' => $processed,
            'records_failed' => $failed,
            'details' => [],
            'duration_ms' => $durationMs,
        ]);

        return ['processed' => $processed, 'failed' => $failed];
    }

    /**
     * Ingest orders from WooCommerce into ERP.
     */
    public function syncOrders(TenantIntegration $integration): array
    {
        $startTime = microtime(true);
        /** @var ECommerceDriverInterface $driver */
        $driver = $this->integrationManager->driverFor($integration);

        $externalOrders = $driver->fetchOrders($integration, ['per_page' => 50]);
        $processed = 0;
        $skipped = 0;

        foreach ($externalOrders as $extOrder) {
            $extId = (string) ($extOrder['id'] ?? '');
            if (empty($extId)) continue;

            // Check if already imported
            $exists = TenantIntegrationEntityMap::where('tenant_id', $integration->tenant_id)
                ->where('integration_id', $integration->id)
                ->where('entity_type', 'order')
                ->where('external_id', $extId)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($integration, $extOrder, $extId, &$processed) {
                // 1. Resolve or create customer
                $billing = $extOrder['billing'] ?? [];
                $email = $billing['email'] ?? "customer_{$extId}@online.com";
                $firstName = trim($billing['first_name'] ?? '') ?: 'Online';
                $lastName = trim($billing['last_name'] ?? '') ?: 'Customer';
                $phone = $billing['phone'] ?? null;

                $customer = Customer::firstOrCreate(
                    [
                        'tenant_id' => $integration->tenant_id,
                        'email' => $email,
                    ],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone,
                        'source' => 'woocommerce',
                    ]
                );

                // 2. Create Order
                $orderNumber = ($integration->settings['order_prefix'] ?? 'WC-') . $extId;
                $total = (float) ($extOrder['total'] ?? 0);
                $branchId = $integration->settings['default_branch_id'] ?? \App\Domain\Branch\Models\Branch::where('tenant_id', $integration->tenant_id)->value('id');

                $order = Order::create([
                    'tenant_id' => $integration->tenant_id,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'order_number' => $orderNumber,
                    'status' => 'confirmed',
                    'source' => 'woocommerce',
                    'payment_status' => $extOrder['status'] === 'completed' ? 'paid' : 'unpaid',
                    'currency' => $extOrder['currency'] ?? 'AMD',
                    'subtotal' => $total,
                    'tax' => (float) ($extOrder['total_tax'] ?? 0),
                    'total' => $total,
                    'placed_at' => now(),
                    'internal_notes' => 'Imported from WooCommerce order #' . $extId,
                ]);

                // 3. Import Order Items
                $lineItems = $extOrder['line_items'] ?? [];
                foreach ($lineItems as $item) {
                    $sku = $item['sku'] ?? null;
                    $product = null;
                    if ($sku) {
                        $product = Product::where('tenant_id', $integration->tenant_id)->where('sku', $sku)->first();
                    }

                    if ($product) {
                        $pName = is_array($product->name) ? ($product->name['en'] ?? $product->name['hy'] ?? reset($product->name)) : (string) $product->name;
                        OrderItem::create([
                            'tenant_id' => $integration->tenant_id,
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => $pName,
                            'product_sku' => $product->sku,
                            'quantity' => (float) ($item['quantity'] ?? 1),
                            'unit_price' => (float) ($item['price'] ?? 0),
                            'total' => (float) ($item['total'] ?? 0),
                            'created_at' => now(),
                        ]);
                    }
                }

                // 4. Record Entity Map
                TenantIntegrationEntityMap::create([
                    'tenant_id' => $integration->tenant_id,
                    'integration_id' => $integration->id,
                    'entity_type' => 'order',
                    'internal_id' => $order->id,
                    'external_id' => $extId,
                    'checksum' => hash('sha256', json_encode($extOrder)),
                    'last_synced_at' => now(),
                ]);

                $processed++;
            });
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        TenantIntegrationSyncLog::create([
            'tenant_id' => $integration->tenant_id,
            'integration_id' => $integration->id,
            'entity_type' => 'order',
            'direction' => 'inbound',
            'status' => 'success',
            'records_processed' => $processed,
            'records_failed' => 0,
            'details' => ['skipped' => $skipped],
            'duration_ms' => $durationMs,
        ]);

        return [
            'imported' => $processed,
            'skipped' => $skipped,
        ];
    }
}
