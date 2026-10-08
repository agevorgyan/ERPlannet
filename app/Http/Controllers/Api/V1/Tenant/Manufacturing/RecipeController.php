<?php

namespace App\Http\Controllers\Api\V1\Tenant\Manufacturing;

use App\Domain\Manufacturing\Actions\CreateRecipeAction;
use App\Domain\Manufacturing\Models\Recipe;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Recipe::with(['product.unit', 'yieldUnit']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $recipes = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $recipes->items(),
            'meta' => [
                'current_page' => $recipes->currentPage(),
                'last_page' => $recipes->lastPage(),
                'total' => $recipes->total(),
            ],
        ]);
    }

    public function store(Request $request, CreateRecipeAction $action): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:20'],
            'yield_quantity' => ['required', 'numeric', 'min:0.0001'],
            'yield_unit_id' => ['required', 'uuid', 'exists:units,id'],
            'scrap_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_id' => ['required', 'uuid', 'exists:units,id'],
            'items.*.waste_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $recipe = $action->execute($validated);

        return response()->json([
            'success' => true,
            'message' => 'Recipe created successfully.',
            'data' => $recipe,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $recipe = Recipe::with([
            'product.unit',
            'productVariant',
            'yieldUnit',
            'items.product.unit',
            'items.productVariant',
            'items.unit',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $recipe,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'version' => ['nullable', 'string', 'max:20'],
            'yield_quantity' => ['sometimes', 'numeric', 'min:0.0001'],
            'scrap_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $recipe->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Recipe updated successfully.',
            'data' => $recipe,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recipe deleted successfully.',
        ]);
    }
}
