<?php

namespace App\Domain\Manufacturing\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Manufacturing\Models\RecipeItem;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateRecipeAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected TenantContext $tenantContext
    ) {}

    public function execute(array $data): Recipe
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        // 1. Feature & Quota checks
        $this->entitlements->assertCan('feature.production');
        $this->entitlements->assertCan('limit.recipes', 1);

        if (empty($data['items'])) {
            throw new \InvalidArgumentException('Recipe must contain at least one ingredient.');
        }

        $product = Product::findOrFail($data['product_id']);
        $yieldUnit = Unit::findOrFail($data['yield_unit_id']);

        return DB::transaction(function () use ($tenant, $data, $product, $yieldUnit) {
            $recipe = Recipe::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'code' => strtoupper($data['code']),
                'name' => $data['name'],
                'version' => $data['version'] ?? '1.0',
                'yield_quantity' => (float) $data['yield_quantity'],
                'yield_unit_id' => $yieldUnit->id,
                'scrap_percentage' => (float) ($data['scrap_percentage'] ?? 0.0),
                'labor_cost' => (float) ($data['labor_cost'] ?? 0.0),
                'overhead_cost' => (float) ($data['overhead_cost'] ?? 0.0),
                'instructions' => $data['instructions'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($data['items'] as $index => $item) {
                $ingredientProduct = Product::findOrFail($item['product_id']);
                $unit = Unit::findOrFail($item['unit_id']);

                RecipeItem::create([
                    'tenant_id' => $tenant->id,
                    'recipe_id' => $recipe->id,
                    'product_id' => $ingredientProduct->id,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit_id' => $unit->id,
                    'waste_percentage' => (float) ($item['waste_percentage'] ?? 0.0),
                    'sort_order' => $item['sort_order'] ?? ($index + 1),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $recipe->load(['items.product.unit', 'items.unit', 'product', 'yieldUnit']);
        });
    }
}
