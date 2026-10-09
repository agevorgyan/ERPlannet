<?php

namespace App\Http\Controllers\Api\V1\Tenant\Catalog;

use App\Domain\Catalog\Actions\CreateProductAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Manufacturing\Models\Recipe;
use App\Domain\Manufacturing\Models\RecipeItem;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with([
            'category',
            'subcategory',
            'unit',
            'variants',
            'stockLevels.warehouse',
            'recipes.yieldUnit',
        ]);

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->has('is_stop_list')) {
            $query->where('is_stop_list', $request->boolean('is_stop_list'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%")
                    ->orWhere('hs_code', 'ilike', "%{$search}%")
                    ->orWhereRaw('name::text ILIKE ?', ["%{$search}%"])
                    ->orWhereRaw('description::text ILIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = $request->integer('per_page', 25);
        $products = $query->latest()->paginate($perPage);

        // Map live stock balance and active recipe info
        $items = collect($products->items())->map(function (Product $product) {
            $data = $product->toArray();
            $currentStock = (float) $product->stockLevels->sum('quantity_on_hand');
            $data['current_stock'] = $currentStock;
            $data['is_low_stock'] = $product->track_stock && $currentStock <= (float) ($product->min_stock_level ?? 0);

            $activeRecipe = $product->recipes->firstWhere('is_active', true);
            $data['has_recipe'] = (bool) $activeRecipe;
            if ($activeRecipe) {
                $breakdown = $activeRecipe->calculateCostBreakdown();
                $data['recipe_id'] = $activeRecipe->id;
                $data['recipe_code'] = $activeRecipe->code;
                $data['calculated_unit_cost'] = $breakdown['unit_cost'];
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request, CreateProductAction $action): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'type' => ['nullable', 'string', 'in:finished_product,semi_finished,raw_material,ingredient,packaging,service,modifier'],
            'sku' => ['required', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'hs_code' => ['nullable', 'string', 'max:50'],
            'packaging' => ['nullable', 'string', 'max:100'],
            'net_quantity' => ['nullable', 'string', 'max:100'],
            'name' => ['required'], // string or {"hy": "...", "en": "..."}
            'description' => ['nullable'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'special_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'has_vat' => ['nullable', 'boolean'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'allow_discount' => ['nullable', 'boolean'],
            'allow_price_edit' => ['nullable', 'boolean'],
            'transaction_type' => ['nullable', 'string', 'max:50'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'shelf_life_days' => ['nullable', 'integer', 'min:0'],
            'shelf_life_info' => ['nullable', 'string', 'max:150'],
            'track_stock' => ['nullable', 'boolean'],
            'is_produced' => ['nullable', 'boolean'],
            'allow_modifiers' => ['nullable', 'boolean'],
            'is_ungrouped_in_order' => ['nullable', 'boolean'],
            'is_excise' => ['nullable', 'boolean'],
            'is_marked' => ['nullable', 'boolean'],
            'is_stop_list' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'calories' => ['nullable', 'numeric', 'min:0'],
            'nutritional_info' => ['nullable', 'array'],
            'allergens' => ['nullable', 'array'],
            'dietary_tags' => ['nullable', 'array'],
            'available_branch_ids' => ['nullable', 'array'],
            'time_availability' => ['nullable', 'array'],
            'discount_hours' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.name' => ['required_with:variants'],
            'variants.*.sale_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.barcode' => ['nullable', 'string', 'max:100'],
            'variants.*.attributes' => ['nullable', 'array'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
        ]);

        $product = $action->execute($validated);

        // Record initial stock if provided
        if (! empty($validated['initial_stock']) && (float) $validated['initial_stock'] > 0) {
            $warehouseId = $validated['warehouse_id'] ?? Warehouse::where('tenant_id', $product->tenant_id)->value('id');
            if ($warehouseId) {
                StockLevel::updateOrCreate(
                    [
                        'tenant_id' => $product->tenant_id,
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity_on_hand' => (float) $validated['initial_stock'],
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ապրանքը հաջողությամբ ստեղծվել է:',
            'data' => $product->load(['category', 'subcategory', 'unit', 'variants', 'stockLevels.warehouse']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with([
            'category',
            'subcategory',
            'unit',
            'variants',
            'stockLevels.warehouse',
            'recipes.yieldUnit',
            'recipes.items.product',
            'recipes.items.unit',
        ])->findOrFail($id);

        $data = $product->toArray();
        $data['current_stock'] = (float) $product->stockLevels->sum('quantity_on_hand');
        $activeRecipe = $product->activeRecipe();
        if ($activeRecipe) {
            $data['active_recipe'] = $activeRecipe;
            $data['cost_breakdown'] = $activeRecipe->calculateCostBreakdown();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'unit_id' => ['sometimes', 'uuid', 'exists:units,id'],
            'type' => ['nullable', 'string', 'in:finished_product,semi_finished,raw_material,ingredient,packaging,service,modifier'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'hs_code' => ['nullable', 'string', 'max:50'],
            'packaging' => ['nullable', 'string', 'max:100'],
            'net_quantity' => ['nullable', 'string', 'max:100'],
            'name' => ['sometimes'],
            'description' => ['nullable'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0'],
            'special_price' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'has_vat' => ['nullable', 'boolean'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'allow_discount' => ['nullable', 'boolean'],
            'allow_price_edit' => ['nullable', 'boolean'],
            'transaction_type' => ['nullable', 'string', 'max:50'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'shelf_life_days' => ['nullable', 'integer', 'min:0'],
            'shelf_life_info' => ['nullable', 'string', 'max:150'],
            'track_stock' => ['nullable', 'boolean'],
            'is_produced' => ['nullable', 'boolean'],
            'allow_modifiers' => ['nullable', 'boolean'],
            'is_ungrouped_in_order' => ['nullable', 'boolean'],
            'is_excise' => ['nullable', 'boolean'],
            'is_marked' => ['nullable', 'boolean'],
            'is_stop_list' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'calories' => ['nullable', 'numeric', 'min:0'],
            'nutritional_info' => ['nullable', 'array'],
            'allergens' => ['nullable', 'array'],
            'dietary_tags' => ['nullable', 'array'],
            'available_branch_ids' => ['nullable', 'array'],
            'time_availability' => ['nullable', 'array'],
            'discount_hours' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'adjust_stock' => ['nullable', 'numeric'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
        ]);

        if (isset($validated['name']) && ! is_array($validated['name'])) {
            $validated['name'] = array_merge($product->name ?? [], ['hy' => $validated['name']]);
        }

        if (isset($validated['description']) && ! is_array($validated['description'])) {
            $validated['description'] = array_merge($product->description ?? [], ['hy' => $validated['description']]);
        }

        $product->update($validated);

        // Adjust stock if requested
        if ($request->filled('adjust_stock') && (float) $request->input('adjust_stock') != 0) {
            $warehouseId = $validated['warehouse_id'] ?? Warehouse::where('tenant_id', $product->tenant_id)->value('id');
            if ($warehouseId) {
                $stockLevel = StockLevel::firstOrCreate(
                    [
                        'tenant_id' => $product->tenant_id,
                        'warehouse_id' => $warehouseId,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'reorder_point' => (float) ($product->min_stock_level ?? 0),
                    ]
                );
                $stockLevel->quantity_on_hand += (float) $request->input('adjust_stock');
                $stockLevel->updated_at = now();
                $stockLevel->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ապրանքը հաջողությամբ թարմացվել է:',
            'data' => $product->load(['category', 'subcategory', 'unit', 'variants', 'stockLevels.warehouse']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ապրանքը հեռացվել է:',
        ]);
    }

    /**
     * Get or build technical card (BOM/Recipe) with real-time weighted cost breakdown.
     */
    public function getTechnicalCard(string $id): JsonResponse
    {
        $product = Product::with(['unit', 'recipes.yieldUnit', 'recipes.items.product', 'recipes.items.unit'])->findOrFail($id);

        $recipe = $product->activeRecipe();

        if (! $recipe) {
            // Return template for creating new technical card
            $defaultYieldUnit = $product->unit;

            return response()->json([
                'success' => true,
                'has_recipe' => false,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->getLocalizedName(),
                    'sku' => $product->sku,
                    'type' => $product->type,
                    'cost_price' => (float) $product->cost_price,
                    'sale_price' => (float) $product->sale_price,
                    'unit_id' => $product->unit_id,
                    'unit_code' => $product->unit?->code,
                    'unit_name' => $product->unit?->name['hy'] ?? $product->unit?->code,
                ],
                'data' => [
                    'code' => 'RCP-'.strtoupper(Str::slug($product->sku, '-')).'-V1',
                    'name' => $product->getLocalizedName().' — Տեխնիկական Քարտ',
                    'version' => '1.0',
                    'yield_quantity' => 1.0,
                    'yield_unit_id' => $defaultYieldUnit->id,
                    'scrap_percentage' => 0.0,
                    'labor_cost' => 0.0,
                    'overhead_cost' => 0.0,
                    'instructions' => '',
                    'items' => [],
                ],
                'breakdown' => [
                    'raw_material_cost' => 0.0,
                    'scrap_cost' => 0.0,
                    'labor_cost' => 0.0,
                    'overhead_cost' => 0.0,
                    'total_cost' => 0.0,
                    'unit_cost' => (float) $product->cost_price,
                    'yield_quantity' => 1.0,
                    'items' => [],
                ],
            ]);
        }

        $breakdown = $recipe->calculateCostBreakdown();

        // Calculate margin and markup relative to product's sale price
        $salePrice = (float) $product->sale_price;
        $unitCost = $breakdown['unit_cost'];
        $marginPercent = $salePrice > 0 ? round((($salePrice - $unitCost) / $salePrice) * 100, 1) : 0;
        $markupPercent = $unitCost > 0 ? round((($salePrice - $unitCost) / $unitCost) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'has_recipe' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->getLocalizedName(),
                'sku' => $product->sku,
                'type' => $product->type,
                'cost_price' => (float) $product->cost_price,
                'sale_price' => $salePrice,
                'unit_id' => $product->unit_id,
                'unit_code' => $product->unit?->code,
                'unit_name' => $product->unit?->name['hy'] ?? $product->unit?->code,
            ],
            'data' => $recipe,
            'breakdown' => array_merge($breakdown, [
                'sale_price' => $salePrice,
                'margin_percent' => $marginPercent,
                'markup_percent' => $markupPercent,
            ]),
        ]);
    }

    /**
     * Store or update technical card (BOM/Recipe) with items (both ingredients and semi-finished products).
     */
    public function saveTechnicalCard(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:20'],
            'yield_quantity' => ['required', 'numeric', 'min:0.0001'],
            'yield_unit_id' => ['required', 'uuid', 'exists:units,id'],
            'scrap_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'instructions' => ['nullable', 'string'],
            'apply_to_cost_price' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.gross_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_id' => ['required', 'uuid', 'exists:units,id'],
            'items.*.waste_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($product, $validated, $request) {
            // Deactivate older active recipes for this product
            Recipe::where('tenant_id', $product->tenant_id)
                ->where('product_id', $product->id)
                ->update(['is_active' => false]);

            $recipe = Recipe::updateOrCreate(
                [
                    'tenant_id' => $product->tenant_id,
                    'product_id' => $product->id,
                    'code' => $validated['code'],
                ],
                [
                    'name' => $validated['name'],
                    'version' => $validated['version'] ?? '1.0',
                    'yield_quantity' => (float) $validated['yield_quantity'],
                    'yield_unit_id' => $validated['yield_unit_id'],
                    'scrap_percentage' => (float) ($validated['scrap_percentage'] ?? 0),
                    'labor_cost' => (float) ($validated['labor_cost'] ?? 0),
                    'overhead_cost' => (float) ($validated['overhead_cost'] ?? 0),
                    'instructions' => $validated['instructions'] ?? null,
                    'is_active' => true,
                ]
            );

            // Replace recipe items
            RecipeItem::where('recipe_id', $recipe->id)->delete();

            foreach ($validated['items'] as $index => $itemData) {
                $component = Product::find($itemData['product_id']);
                $unitCost = isset($itemData['cost_per_unit']) && (float) $itemData['cost_per_unit'] > 0
                    ? (float) $itemData['cost_per_unit']
                    : (float) ($component?->cost_price ?? 0);

                $qty = (float) $itemData['quantity'];
                $waste = (float) ($itemData['waste_percentage'] ?? 0);
                $gross = isset($itemData['gross_quantity']) && (float) $itemData['gross_quantity'] > 0
                    ? (float) $itemData['gross_quantity']
                    : round($qty * (1 + ($waste / 100)), 4);

                RecipeItem::create([
                    'tenant_id' => $product->tenant_id,
                    'recipe_id' => $recipe->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $qty,
                    'gross_quantity' => $gross,
                    'unit_id' => $itemData['unit_id'],
                    'waste_percentage' => $waste,
                    'cost_per_unit' => $unitCost,
                    'sort_order' => $index + 1,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Flag product as manufactured / is_produced
            $product->is_produced = true;

            // Recalculate cost
            $breakdown = $recipe->load(['items.product', 'items.unit'])->calculateCostBreakdown();

            // Optionally sync product cost_price with calculated unit cost
            if ($request->boolean('apply_to_cost_price', true)) {
                $product->cost_price = $breakdown['unit_cost'];
            }

            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Տեխնիկական քարտը հաջողությամբ պահպանվել է:',
                'data' => $recipe->load(['items.product', 'items.unit', 'yieldUnit']),
                'breakdown' => $breakdown,
                'product_cost_price' => (float) $product->cost_price,
            ]);
        });
    }

    /**
     * Trigger immediate production run: deducts ingredients from warehouse and yields finished product.
     */
    public function produce(Request $request, string $id, RecordStockMovementAction $recordMovement): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
            'source_warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $recipe = $product->activeRecipe();
        if (! $recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Ապրանքի համար ակտիվ տեխնիկական քարտ (բաղադրատոմս) առկա չէ։ Նախ ստեղծեք տեխնիկական քարտը:',
            ], 422);
        }

        $producedQty = (float) $validated['quantity'];
        $warehouseId = $validated['warehouse_id'] ?? Warehouse::where('tenant_id', $product->tenant_id)->value('id');
        $sourceWarehouseId = $validated['source_warehouse_id'] ?? $warehouseId;

        if (! $warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Պահեստը չի գտնվել:',
            ], 422);
        }

        $recipeYield = max(0.0001, (float) $recipe->yield_quantity);
        $batchesCount = $producedQty / $recipeYield;

        return DB::transaction(function () use ($product, $recipe, $producedQty, $batchesCount, $warehouseId, $sourceWarehouseId, $recordMovement, $request) {
            $consumedItems = [];

            // 1. Deduct all components from source warehouse
            foreach ($recipe->items as $item) {
                $component = $item->product;
                if (! $component) {
                    continue;
                }

                $grossPerBatch = $item->gross_quantity ?: ($item->quantity * (1 + ($item->waste_percentage / 100)));
                $totalToConsume = round($grossPerBatch * $batchesCount, 4);

                $movement = $recordMovement->execute(
                    warehouseId: $sourceWarehouseId,
                    productId: $item->product_id,
                    productVariantId: $item->product_variant_id,
                    type: 'production_consume',
                    quantity: $totalToConsume,
                    unitCost: (float) $component->cost_price,
                    userId: $request->user()?->id,
                    referenceType: Recipe::class,
                    referenceId: $recipe->id,
                    notes: "Արտադրական դուրսգրում՝ {$product->getLocalizedName()} ({$producedQty} միավորի համար)"
                );

                $consumedItems[] = [
                    'product_id' => $item->product_id,
                    'name' => $component->getLocalizedName(),
                    'consumed_quantity' => $totalToConsume,
                    'unit_cost' => (float) $component->cost_price,
                ];
            }

            // 2. Yield finished product into target warehouse
            $yieldMovement = $recordMovement->execute(
                warehouseId: $warehouseId,
                productId: $product->id,
                productVariantId: null,
                type: 'production_yield',
                quantity: $producedQty,
                unitCost: (float) $product->cost_price,
                userId: $request->user()?->id,
                referenceType: Recipe::class,
                referenceId: $recipe->id,
                notes: "Արտադրված պատրաստի արտադրանք՝ {$product->getLocalizedName()}"
            );

            $newStock = StockLevel::where('warehouse_id', $warehouseId)
                ->where('product_id', $product->id)
                ->value('quantity_on_hand');

            return response()->json([
                'success' => true,
                'message' => "Արտադրությունը հաջողությամբ կատարվեց: Բաղադրիչները դուրս գրվեցին պահեստից, իսկ {$producedQty} միավոր մուտքագրվեց պատրաստի արտադրանքի պահեստ:",
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->getLocalizedName(),
                    'produced_quantity' => $producedQty,
                    'warehouse_id' => $warehouseId,
                    'current_stock' => (float) $newStock,
                    'consumed_components' => $consumedItems,
                ],
            ]);
        });
    }
}
