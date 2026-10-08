<?php

namespace App\Http\Controllers\Api\V1\Tenant\Manufacturing;

use App\Domain\Manufacturing\Actions\CancelProductionOrderAction;
use App\Domain\Manufacturing\Actions\CompleteProductionOrderAction;
use App\Domain\Manufacturing\Actions\CreateProductionOrderAction;
use App\Domain\Manufacturing\Actions\StartProductionOrderAction;
use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductionOrder::with(['product.unit', 'recipe', 'sourceWarehouse', 'targetWarehouse']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        if ($request->filled('source_warehouse_id')) {
            $query->where('source_warehouse_id', $request->query('source_warehouse_id'));
        }

        if ($request->filled('target_warehouse_id')) {
            $query->where('target_warehouse_id', $request->query('target_warehouse_id'));
        }

        $orders = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(Request $request, CreateProductionOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'recipe_id' => ['required', 'uuid', 'exists:recipes,id'],
            'planned_quantity' => ['required', 'numeric', 'min:0.0001'],
            'source_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'target_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'planned_start_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = $action->execute(
            recipeId: $validated['recipe_id'],
            plannedQuantity: (float) $validated['planned_quantity'],
            sourceWarehouseId: $validated['source_warehouse_id'],
            targetWarehouseId: $validated['target_warehouse_id'],
            userId: $request->user()->id,
            plannedStartDate: $validated['planned_start_date'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Production order created successfully.',
            'data' => $order,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = ProductionOrder::with([
            'product.unit',
            'productVariant',
            'recipe.yieldUnit',
            'sourceWarehouse',
            'targetWarehouse',
            'user',
            'batch',
            'items.product.unit',
            'inspections.items',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function start(Request $request, string $id, StartProductionOrderAction $action): JsonResponse
    {
        $order = $action->execute($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Production started. Raw materials deducted from warehouse.',
            'data' => $order,
        ]);
    }

    public function complete(Request $request, string $id, CompleteProductionOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'actual_quantity' => ['required', 'numeric', 'min:0.0001'],
            'waste_quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = $action->execute(
            productionOrderId: $id,
            actualQuantity: (float) $validated['actual_quantity'],
            wasteQuantity: (float) ($validated['waste_quantity'] ?? 0.0),
            userId: $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Production order completed. Finished goods batch yielded to warehouse.',
            'data' => $order,
        ]);
    }

    public function cancel(string $id, CancelProductionOrderAction $action): JsonResponse
    {
        $order = $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Production order cancelled successfully.',
            'data' => $order,
        ]);
    }
}
