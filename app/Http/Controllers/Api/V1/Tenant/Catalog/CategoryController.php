<?php

namespace App\Http\Controllers\Api\V1\Tenant\Catalog;

use App\Domain\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::with(['parent', 'children'])
            ->withCount('products')
            ->orderBy('sort_order')
            ->latest('id');

        if ($request->boolean('tree')) {
            $query->whereNull('parent_id');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'ilike', "%{$search}%")
                    ->orWhereRaw('name::text ILIKE ?', ["%{$search}%"])
                    ->orWhereRaw('description::text ILIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('type') && in_array($request->query('type'), [Category::TYPE_PRODUCT, Category::TYPE_INGREDIENT])) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'type' => ['nullable', 'string', 'in:product,ingredient'],
            'slug' => ['nullable', 'string', 'max:100'],
            'name' => ['required'], // can be string or {"hy": "...", "en": "...", "ru": "..."}
            'description' => ['nullable'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'file', 'image', 'max:10240', 'mimes:jpeg,png,jpg,webp,svg,gif'],
        ]);

        $imageUrl = $validated['image_url'] ?? null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = Str::random(24).'.'.$extension;
            $path = $file->storeAs('uploads/categories', $filename, 'public');
            $imageUrl = '/storage/'.$path;
        }

        $name = is_array($validated['name']) ? $validated['name'] : ['hy' => $validated['name']];
        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($name['en'] ?? $name['hy'] ?? Str::random(8));

        // Ensure unique slug per tenant
        $baseSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $type = $validated['type'] ?? Category::TYPE_PRODUCT;

        $category = Category::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'type' => $type,
            'slug' => $slug,
            'name' => $name,
            'description' => isset($validated['description']) ? (is_array($validated['description']) ? $validated['description'] : ['hy' => $validated['description']]) : null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'image_url' => $imageUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Կատեգորիան հաջողությամբ ստեղծվեց:',
            'data' => $category->load(['parent', 'children'])->loadCount('products'),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'products'])
            ->withCount('products')
            ->findOrFail($id);

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
            'type' => ['nullable', 'string', 'in:product,ingredient'],
            'slug' => ['nullable', 'string', 'max:100'],
            'name' => ['sometimes'],
            'description' => ['nullable'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'file', 'image', 'max:10240', 'mimes:jpeg,png,jpg,webp,svg,gif'],
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = Str::random(24).'.'.$extension;
            $path = $file->storeAs('uploads/categories', $filename, 'public');
            $validated['image_url'] = '/storage/'.$path;
        }

        if (isset($validated['name']) && ! is_array($validated['name'])) {
            $validated['name'] = array_merge($category->name ?? [], ['hy' => $validated['name']]);
        }

        if (isset($validated['description']) && ! is_array($validated['description'])) {
            $validated['description'] = array_merge($category->description ?? [], ['hy' => $validated['description']]);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Կատեգորիան հաջողությամբ թարմացվեց:',
            'data' => $category->load(['parent', 'children'])->loadCount('products'),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Կատեգորիան հեռացվել է:',
        ]);
    }
}
