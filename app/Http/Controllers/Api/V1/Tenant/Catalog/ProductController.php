<?php

namespace App\Http\Controllers\Api\V1\Tenant\Catalog;

use App\Domain\Catalog\Actions\CreateProductAction;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'unit', 'variants']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereRaw("name::text ILIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $products->items(),
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
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'sku' => ['required', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name' => ['required'], // string or {"hy": "...", "en": "..."}
            'description' => ['nullable'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'track_stock' => ['nullable', 'boolean'],
            'is_produced' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.name' => ['required_with:variants'],
            'variants.*.sale_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
        ]);

        $product = $action->execute($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with(['category', 'unit', 'variants'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'unit_id' => ['sometimes', 'uuid', 'exists:units,id'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name' => ['sometimes'],
            'description' => ['nullable'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0'],
            'track_stock' => ['nullable', 'boolean'],
            'is_produced' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['name']) && !is_array($validated['name'])) {
            $validated['name'] = array_merge($product->name ?? [], ['hy' => $validated['name']]);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product->load(['category', 'unit', 'variants']),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}
