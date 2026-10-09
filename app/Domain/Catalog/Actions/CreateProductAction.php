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
                'subcategory_id' => $data['subcategory_id'] ?? null,
                'unit_id' => $data['unit_id'],
                'type' => $data['type'] ?? Product::TYPE_FINISHED_PRODUCT,
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'hs_code' => $data['hs_code'] ?? null,
                'packaging' => $data['packaging'] ?? null,
                'net_quantity' => $data['net_quantity'] ?? null,
                'name' => is_array($data['name']) ? $data['name'] : ['hy' => $data['name']],
                'description' => isset($data['description']) ? (is_array($data['description']) ? $data['description'] : ['hy' => $data['description']]) : null,
                'cost_price' => $data['cost_price'] ?? 0.00,
                'sale_price' => $data['sale_price'],
                'special_price' => $data['special_price'] ?? null,
                'currency' => $data['currency'] ?? $tenant->currency,
                'vat_rate' => $data['vat_rate'] ?? 20.00,
                'has_vat' => $data['has_vat'] ?? true,
                'discount_percent' => $data['discount_percent'] ?? 0.00,
                'allow_discount' => $data['allow_discount'] ?? true,
                'allow_price_edit' => $data['allow_price_edit'] ?? false,
                'transaction_type' => $data['transaction_type'] ?? 'local_purchase',
                'min_stock_level' => $data['min_stock_level'] ?? 0.000,
                'shelf_life_days' => $data['shelf_life_days'] ?? null,
                'shelf_life_info' => $data['shelf_life_info'] ?? null,
                'track_stock' => $data['track_stock'] ?? true,
                'is_produced' => $data['is_produced'] ?? false,
                'allow_modifiers' => $data['allow_modifiers'] ?? false,
                'is_ungrouped_in_order' => $data['is_ungrouped_in_order'] ?? false,
                'is_excise' => $data['is_excise'] ?? false,
                'is_marked' => $data['is_marked'] ?? false,
                'is_stop_list' => $data['is_stop_list'] ?? false,
                'is_active' => $data['is_active'] ?? true,
                'calories' => $data['calories'] ?? null,
                'nutritional_info' => $data['nutritional_info'] ?? [],
                'allergens' => $data['allergens'] ?? [],
                'dietary_tags' => $data['dietary_tags'] ?? [],
                'available_branch_ids' => $data['available_branch_ids'] ?? [],
                'time_availability' => $data['time_availability'] ?? [],
                'discount_hours' => $data['discount_hours'] ?? [],
                'images' => $data['images'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ]);

            if (! empty($data['variants'])) {
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
