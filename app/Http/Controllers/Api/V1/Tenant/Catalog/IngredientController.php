<?php

namespace App\Http\Controllers\Api\V1\Tenant\Catalog;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Unit;
use App\Domain\Catalog\Services\InvoiceImportService;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IngredientController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->whereIn('type', [Product::TYPE_INGREDIENT, Product::TYPE_RAW_MATERIAL, Product::TYPE_SEMI_FINISHED])
            ->with([
                'category:id,name',
                'subcategory:id,name',
                'unit:id,name,code',
                'suppliers:id,company_name,tax_id,phone',
                'recipesWhereUsed.recipe.product:id,name,sku',
            ])
            ->withSum('stockLevels as current_stock', 'quantity_on_hand');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('hs_code', 'like', "%{$search}%")
                    ->orWhereRaw('name::text ILIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->query('subcategory_id'));
        }

        if ($request->filled('supplier_id')) {
            $supplierId = $request->query('supplier_id');
            $query->whereHas('suppliers', function ($q) use ($supplierId) {
                $q->where('suppliers.id', $supplierId);
            });
        }

        $allIngredients = (clone $query)->get();

        $lowStockCount = $allIngredients->filter(function ($item) {
            $stock = (float) ($item->current_stock ?? 0);
            $min = (float) ($item->min_stock_level ?? 0);

            return $min > 0 && $stock <= $min;
        })->count();

        if ($request->boolean('low_stock')) {
            $query->where(function ($q) {
                $q->whereRaw('(SELECT COALESCE(SUM(quantity_on_hand), 0) FROM stock_levels WHERE stock_levels.product_id = products.id) <= products.min_stock_level')
                    ->where('products.min_stock_level', '>', 0);
            });
        }

        $totalValuation = $allIngredients->sum(function ($item) {
            return (float) $item->cost_price * (float) ($item->current_stock ?? 0);
        });

        $ingredients = $query->latest()->paginate($request->integer('per_page', 50));

        // Format items with computed values for user request
        $formatted = collect($ingredients->items())->map(function ($item) {
            $stock = (float) ($item->current_stock ?? 0);
            $minStock = (float) ($item->min_stock_level ?? 0);
            $costPrice = (float) ($item->cost_price ?? 0);
            $discountPercent = (float) ($item->discount_percent ?? 0);
            $vatRate = (float) ($item->vat_rate ?? 20.00);

            // Calculation values based on stock or base 1 unit
            $subtotalValue = round($costPrice * max(1, $stock), 2);
            $discountedValue = round($subtotalValue * (1 - ($discountPercent / 100)), 2);
            $vatAmount = round($discountedValue * ($vatRate / 100), 2);

            $usedInProducts = $item->recipesWhereUsed->map(function ($ru) {
                return [
                    'product_id' => $ru->recipe?->product?->id,
                    'product_name' => $ru->recipe?->product?->getLocalizedName(),
                    'product_sku' => $ru->recipe?->product?->sku,
                    'recipe_name' => $ru->recipe?->name,
                    'quantity_needed' => $ru->quantity,
                ];
            })->filter(fn ($p) => ! empty($p['product_id']))->values();

            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'hs_code' => $item->hs_code,
                'name' => $item->getLocalizedName(),
                'name_array' => $item->name,
                'description' => is_array($item->description) ? ($item->description['hy'] ?? '') : ($item->description ?? ''),
                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => is_array($item->category->name) ? ($item->category->name['hy'] ?? '') : $item->category->name,
                ] : null,
                'subcategory' => $item->subcategory ? [
                    'id' => $item->subcategory->id,
                    'name' => is_array($item->subcategory->name) ? ($item->subcategory->name['hy'] ?? '') : $item->subcategory->name,
                ] : null,
                'unit' => $item->unit ? [
                    'id' => $item->unit->id,
                    'name' => is_array($item->unit->name) ? ($item->unit->name['hy'] ?? '') : $item->unit->name,
                    'symbol' => $item->unit->code,
                ] : null,
                'packaging' => $item->packaging ?? 'Առանց տարայի',
                'cost_price' => $costPrice,
                'discount_percent' => $discountPercent,
                'subtotal_value' => $subtotalValue,
                'discounted_value' => $discountedValue,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'transaction_type' => $item->transaction_type ?? 'local_purchase',
                'current_stock' => $stock,
                'min_stock_level' => $minStock,
                'is_low_stock' => ($minStock > 0 && $stock <= $minStock),
                'suppliers' => $item->suppliers->map(fn ($s) => [
                    'id' => $s->id,
                    'company_name' => $s->company_name,
                    'tax_id' => $s->tax_id,
                    'phone' => $s->phone,
                ]),
                'where_used_products' => $usedInProducts,
                'where_used_count' => $usedInProducts->count(),
                'created_at' => $item->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'stats' => [
                'total_count' => $allIngredients->count(),
                'low_stock_count' => $lowStockCount,
                'total_valuation' => round($totalValuation, 2),
            ],
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'last_page' => $ingredients->lastPage(),
                'total' => $ingredients->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant not identified.'], 400);
        }

        $validated = $request->validate([
            'name' => ['required'], // string or array
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'sku' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'hs_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'packaging' => ['nullable', 'string', 'max:100'],
            'transaction_type' => ['nullable', 'string', 'max:50'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['uuid', 'exists:suppliers,id'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
        ]);

        $name = is_array($validated['name'])
            ? $validated['name']
            : ['hy' => $validated['name'], 'en' => $validated['name'], 'ru' => $validated['name']];

        $sku = ! empty($validated['sku'])
            ? trim($validated['sku'])
            : 'ING-'.strtoupper(Str::random(6));

        $costPrice = (float) $validated['cost_price'];
        $quantity = (float) ($validated['quantity'] ?? 0);
        $discountPercent = (float) ($validated['discount_percent'] ?? 0);
        $vatRate = (float) ($validated['vat_rate'] ?? 20.00);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $validated['category_id'] ?? null,
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'type' => Product::TYPE_INGREDIENT,
            'sku' => $sku,
            'barcode' => $validated['barcode'] ?? null,
            'hs_code' => $validated['hs_code'] ?? null,
            'packaging' => $validated['packaging'] ?? 'Առանց տարայի',
            'name' => $name,
            'description' => ! empty($validated['description']) ? ['hy' => $validated['description']] : null,
            'cost_price' => $costPrice,
            'sale_price' => $costPrice * 1.25,
            'currency' => 'AMD',
            'vat_rate' => $vatRate,
            'discount_percent' => $discountPercent,
            'transaction_type' => $validated['transaction_type'] ?? 'local_purchase',
            'min_stock_level' => (float) ($validated['min_stock_level'] ?? 0),
            'track_stock' => true,
            'is_produced' => false,
            'is_active' => true,
        ]);

        if (! empty($validated['supplier_ids'])) {
            $pivot = [];
            foreach ($validated['supplier_ids'] as $sid) {
                $pivot[$sid] = [
                    'tenant_id' => $tenant->id,
                    'supply_price' => $costPrice,
                ];
            }
            $product->suppliers()->sync($pivot);
        }

        if ($quantity > 0) {
            $warehouse = ! empty($validated['warehouse_id'])
                ? Warehouse::find($validated['warehouse_id'])
                : Warehouse::where('tenant_id', $tenant->id)->first();

            if ($warehouse) {
                StockLevel::create([
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'quantity_on_hand' => $quantity,
                    'quantity_reserved' => 0,
                    'reorder_point' => (float) ($validated['min_stock_level'] ?? 0),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Բաղադրիչը հաջողությամբ ավելացվել է:',
            'data' => $product->load(['category', 'subcategory', 'unit', 'suppliers']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $item = Product::whereIn('type', [Product::TYPE_INGREDIENT, Product::TYPE_RAW_MATERIAL, Product::TYPE_SEMI_FINISHED])
            ->with([
                'category',
                'subcategory',
                'unit',
                'suppliers',
                'recipesWhereUsed.recipe.product',
                'stockLevels.warehouse',
            ])
            ->withSum('stockLevels as current_stock', 'quantity_on_hand')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();
        $product = Product::whereIn('type', [Product::TYPE_INGREDIENT, Product::TYPE_RAW_MATERIAL, Product::TYPE_SEMI_FINISHED])
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes'],
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'unit_id' => ['sometimes', 'uuid', 'exists:units,id'],
            'sku' => ['sometimes', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'hs_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'packaging' => ['nullable', 'string', 'max:100'],
            'transaction_type' => ['nullable', 'string', 'max:50'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['uuid', 'exists:suppliers,id'],
            'adjust_stock' => ['nullable', 'numeric'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
        ]);

        if (isset($validated['name']) && ! is_array($validated['name'])) {
            $validated['name'] = array_merge($product->name ?? [], ['hy' => $validated['name']]);
        }
        if (isset($validated['description']) && ! is_array($validated['description'])) {
            $validated['description'] = ['hy' => $validated['description']];
        }

        $product->update($validated);

        if ($request->has('supplier_ids')) {
            $pivot = [];
            foreach ($request->input('supplier_ids', []) as $sid) {
                $pivot[$sid] = [
                    'tenant_id' => $tenant->id,
                    'supply_price' => $product->cost_price,
                ];
            }
            $product->suppliers()->sync($pivot);
        }

        if ($request->filled('adjust_stock')) {
            $adjustAmount = (float) $request->input('adjust_stock');
            $warehouse = ! empty($validated['warehouse_id'])
                ? Warehouse::find($validated['warehouse_id'])
                : Warehouse::where('tenant_id', $tenant->id)->first();

            if ($warehouse) {
                $stockLevel = StockLevel::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'reorder_point' => (float) ($product->min_stock_level ?? 0),
                    ]
                );

                $stockLevel->quantity_on_hand += $adjustAmount;
                $stockLevel->updated_at = now();
                $stockLevel->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Բաղադրիչը հաջողությամբ թարմացվել է:',
            'data' => $product->load(['category', 'subcategory', 'unit', 'suppliers']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Բաղադրիչը հեռացվել է:',
        ]);
    }

    public function importInvoices(Request $request, InvoiceImportService $importService): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'supplier_id' => ['nullable', 'uuid', 'exists:suppliers,id'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
        ]);

        try {
            $result = $importService->import(
                $request->file('file'),
                $request->input('supplier_id'),
                $request->input('warehouse_id')
            );

            return response()->json([
                'success' => true,
                'message' => "Հաջողությամբ ներմուծվել է {$result['imported_count']} բաղադրիչ:",
                'imported_count' => $result['imported_count'],
                'items' => $result['items'],
                'errors' => $result['errors'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ներմուծման սխալ: '.$e->getMessage(),
            ], 422);
        }
    }
}
