<?php

namespace App\Http\Controllers\Api\V1\Tenant\Warehouse;

use App\Domain\Warehouse\Actions\AdjustStockAction;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use App\Domain\Warehouse\Models\StockMovement;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with(['warehouse', 'product.unit', 'productVariant', 'batch', 'user']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->query('product_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->query('date_to'));
        }

        $movements = $query->latest('created_at')->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $movements->items(),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'total' => $movements->total(),
            ],
        ]);
    }

    public function adjust(Request $request, AdjustStockAction $action): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'counted_quantity' => ['required', 'numeric', 'min:0'],
            'batch_id' => ['nullable', 'uuid', 'exists:stock_batches,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $movement = $action->execute(
            warehouseId: $validated['warehouse_id'],
            productId: $validated['product_id'],
            productVariantId: $validated['product_variant_id'] ?? null,
            countedQuantity: (float) $validated['counted_quantity'],
            userId: $request->user()?->id,
            notes: $validated['notes'] ?? null,
            batchId: $validated['batch_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => $movement ? 'Stock adjusted successfully.' : 'No difference detected. Stock balance unchanged.',
            'data' => $movement,
        ], $movement ? 200 : 200);
    }

    public function scrap(Request $request, RecordStockMovementAction $action): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'reason' => ['required', 'string', 'in:expired,spoilage,production_loss,kitchen_waste,damaged,unknown_loss'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $movement = $action->execute(
            warehouseId: $validated['warehouse_id'],
            productId: $validated['product_id'],
            productVariantId: null,
            type: 'scrap',
            quantity: (float) $validated['quantity'],
            unitCost: 0.0,
            stockBatchId: null,
            userId: $request->user()?->id,
            referenceType: 'WasteScrap',
            referenceId: null,
            notes: "[Waste: {$validated['reason']}] ".($validated['notes'] ?? ''),
        );

        return response()->json([
            'success' => true,
            'message' => 'Խոտանագրումը հաջողությամբ գրանցվեց (Waste Logged):',
            'data' => $movement,
        ]);
    }
}
