<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EntitlementManagerInterface $entitlementManager
    ) {}

    public function execute(array $data): Product
    {
        // 1. Quota Check
        $this->entitlementManager->assertCan('limit.products', 1);

        $tenant = $this->tenantContext->getTenant();

        return DB::transaction(function () use ($data, $tenant) {
            $product = Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $data['category_id'] ?? null,
                'unit_id' => $data['unit_id'],
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'name' => is_array($data['name']) ? $data['name'] : ['hy' => $data['name']],
                'description' => isset($data['description']) ? (is_array($data['description']) ? $data['description'] : ['hy' => $data['description']]) : null,
                'cost_price' => $data['cost_price'] ?? 0.00,
                'sale_price' => $data['sale_price'],
                'currency' => $data['currency'] ?? $tenant->currency,
                'track_stock' => $data['track_stock'] ?? true,
                'is_produced' => $data['is_produced'] ?? false,
                'is_active' => $data['is_active'] ?? true,
                'images' => $data['images'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ]);

            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    ProductVariant::create([
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'barcode' => $variantData['barcode'] ?? null,
                        'name' => is_array($variantData['name']) ? $variantData['name'] : ['hy' => $variantData['name']],
                        'cost_price' => $variantData['cost_price'] ?? null,
                        'sale_price' => $variantData['sale_price'] ?? $product->sale_price,
                        'attributes' => $variantData['attributes'] ?? [],
                        'is_active' => $variantData['is_active'] ?? true,
                    ]);
                }
            }

            return $product->load(['variants', 'unit', 'category']);
        });
    }
}
