<?php

namespace App\Http\Controllers\Api\V1\Tenant\Catalog;

use App\Domain\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'slug' => ['nullable', 'string', 'max:100'],
            'name' => ['required'], // can be string or {"hy": "...", "en": "..."}
            'description' => ['nullable'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        $name = is_array($validated['name']) ? $validated['name'] : ['hy' => $validated['name']];
        $slug = $validated['slug'] ?? Str::slug($name['en'] ?? $name['hy'] ?? Str::random(8));

        $category = Category::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => $slug,
            'name' => $name,
            'description' => isset($validated['description']) ? (is_array($validated['description']) ? $validated['description'] : ['hy' => $validated['description']]) : null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::with(['children', 'products'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'name' => ['sometimes'],
            'description' => ['nullable'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        if (isset($validated['name']) && !is_array($validated['name'])) {
            $validated['name'] = array_merge($category->name ?? [], ['hy' => $validated['name']]);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $category,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}
