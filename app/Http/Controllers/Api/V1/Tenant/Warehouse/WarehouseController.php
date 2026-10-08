<?php

namespace App\Http\Controllers\Api\V1\Tenant\Warehouse;

use App\Domain\Warehouse\Actions\CreateWarehouseAction;
use App\Domain\Warehouse\Models\StockLevel;
use App\Domain\Warehouse\Models\Warehouse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::with('branch')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $warehouses,
        ]);
    }

    public function store(Request $request, CreateWarehouseAction $action): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:standard,production,retail,cold_storage'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        $warehouse = $action->execute($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully.',
            'data' => $warehouse,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $warehouse = Warehouse::with('branch')->findOrFail($id);

        $stockSummary = [
            'total_items_count' => StockLevel::where('warehouse_id', $warehouse->id)->count(),
            'total_units_on_hand' => (float) StockLevel::where('warehouse_id', $warehouse->id)->sum('quantity_on_hand'),
            'total_units_reserved' => (float) StockLevel::where('warehouse_id', $warehouse->id)->sum('quantity_reserved'),
        ];

        return response()->json([
            'success' => true,
            'data' => array_merge($warehouse->toArray(), ['stock_summary' => $stockSummary]),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:standard,production,retail,cold_storage'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        if (!empty($validated['is_default'])) {
            Warehouse::where('is_default', true)->where('id', '!=', $warehouse->id)->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully.',
            'data' => $warehouse,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $warehouse = Warehouse::findOrFail($id);

        $totalOnHand = (float) StockLevel::where('warehouse_id', $warehouse->id)->sum('quantity_on_hand');
        if ($totalOnHand > 0) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'WAREHOUSE_NOT_EMPTY',
                    'message' => 'Cannot delete warehouse with positive stock on hand. Please transfer or adjust stock first.',
                ],
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully.',
        ]);
    }
}
