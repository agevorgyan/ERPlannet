<?php

namespace App\Http\Controllers\Api\V1\Tenant\Procurement;

use App\Domain\Procurement\Actions\CreateSupplierAction;
use App\Domain\Procurement\Models\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $isTrash = $request->boolean('trash') || $status === 'trash';

        $query = $isTrash ? Supplier::onlyTrashed() : Supplier::query();

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'ilike', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! $isTrash) {
            if ($status === 'suspended') {
                $query->where('is_active', false);
            } elseif ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }
        }

        $counts = [
            'all' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'suspended' => Supplier::where('is_active', false)->count(),
            'trash' => Supplier::onlyTrashed()->count(),
        ];

        $suppliers = $query->with(['couriers', 'products:id,name,sku,type'])
            ->latest()
            ->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $suppliers->items(),
            'counts' => $counts,
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'total' => $suppliers->total(),
            ],
        ]);
    }

    public function store(Request $request, CreateSupplierAction $action): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'legal_address' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'couriers' => ['nullable', 'array'],
            'couriers.*.name' => ['required_with:couriers', 'string', 'max:255'],
            'couriers.*.phone' => ['nullable', 'string', 'max:50'],
            'couriers.*.vehicle_model' => ['nullable', 'string', 'max:100'],
            'couriers.*.license_plate' => ['nullable', 'string', 'max:50'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $supplier = $action->execute($validated);

        if (! empty($validated['couriers'])) {
            foreach ($validated['couriers'] as $courierData) {
                if (! empty($courierData['name'])) {
                    $supplier->couriers()->create([
                        'tenant_id' => $supplier->tenant_id,
                        'name' => $courierData['name'],
                        'phone' => $courierData['phone'] ?? null,
                        'vehicle_model' => $courierData['vehicle_model'] ?? null,
                        'license_plate' => $courierData['license_plate'] ?? null,
                    ]);
                }
            }
        }

        if (! empty($validated['product_ids'])) {
            $pivotData = [];
            foreach ($validated['product_ids'] as $productId) {
                $pivotData[$productId] = ['tenant_id' => $supplier->tenant_id];
            }
            $supplier->products()->sync($pivotData);
        }

        $supplier->load(['couriers', 'products:id,name,sku,type']);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully.',
            'data' => $supplier,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()
            ->with(['couriers', 'products:id,name,sku,type', 'purchaseOrders' => fn ($q) => $q->latest()->limit(10)])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $supplier,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'company_name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'legal_address' => ['nullable', 'string'],
            'shipping_address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'couriers' => ['nullable', 'array'],
            'couriers.*.name' => ['required_with:couriers', 'string', 'max:255'],
            'couriers.*.phone' => ['nullable', 'string', 'max:50'],
            'couriers.*.vehicle_model' => ['nullable', 'string', 'max:100'],
            'couriers.*.license_plate' => ['nullable', 'string', 'max:50'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $supplier->update($validated);

        if ($request->has('couriers')) {
            $supplier->couriers()->delete();
            foreach ($request->input('couriers', []) as $courierData) {
                if (! empty($courierData['name'])) {
                    $supplier->couriers()->create([
                        'tenant_id' => $supplier->tenant_id,
                        'name' => $courierData['name'],
                        'phone' => $courierData['phone'] ?? null,
                        'vehicle_model' => $courierData['vehicle_model'] ?? null,
                        'license_plate' => $courierData['license_plate'] ?? null,
                    ]);
                }
            }
        }

        if ($request->has('product_ids')) {
            $pivotData = [];
            foreach ($request->input('product_ids', []) as $productId) {
                $pivotData[$productId] = ['tenant_id' => $supplier->tenant_id];
            }
            $supplier->products()->sync($pivotData);
        }

        $supplier->load(['couriers', 'products:id,name,sku,type']);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully.',
            'data' => $supplier,
        ]);
    }

    public function toggleSuspend(string $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->is_active = ! $supplier->is_active;
        $supplier->save();

        return response()->json([
            'success' => true,
            'message' => $supplier->is_active ? 'Supplier activated.' : 'Supplier suspended (կասեցված է).',
            'data' => $supplier,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier moved to trash (տեղափոխվել է զամբյուղ).',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();

        return response()->json([
            'success' => true,
            'message' => 'Supplier restored from trash (վերականգնվել է զամբյուղից).',
            'data' => $supplier,
        ]);
    }

    public function forceDelete(string $id): JsonResponse
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);

        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Մատակարարը չի կարող վերջնական հեռացվել, քանի որ առկա են կապված գնման պատվերներ։ Խորհուրդ է տրվում պահել այն զամբյուղում կամ կասեցնել։',
            ], 422);
        }

        $supplier->couriers()->delete();
        $supplier->products()->detach();
        $supplier->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier permanently deleted (վերջնական հեռացված է).',
        ]);
    }
}
