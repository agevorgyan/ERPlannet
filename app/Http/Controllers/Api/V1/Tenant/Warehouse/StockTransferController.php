<?php

namespace App\Http\Controllers\Api\V1\Tenant\Warehouse;

use App\Domain\Warehouse\Actions\CreateStockTransferAction;
use App\Domain\Warehouse\Actions\ReceiveStockTransferAction;
use App\Domain\Warehouse\Actions\ShipStockTransferAction;
use App\Domain\Warehouse\Models\StockTransfer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('source_warehouse_id')) {
            $query->where('source_warehouse_id', $request->query('source_warehouse_id'));
        }

        if ($request->filled('destination_warehouse_id')) {
            $query->where('destination_warehouse_id', $request->query('destination_warehouse_id'));
        }

        $transfers = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $transfers->items(),
            'meta' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'total' => $transfers->total(),
            ],
        ]);
    }

    public function store(Request $request, CreateStockTransferAction $action): JsonResponse
    {
        $validated = $request->validate([
            'source_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id', 'different:destination_warehouse_id'],
            'destination_warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.stock_batch_id' => ['nullable', 'uuid', 'exists:stock_batches,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $transfer = $action->execute(
            sourceWarehouseId: $validated['source_warehouse_id'],
            destinationWarehouseId: $validated['destination_warehouse_id'],
            userId: $request->user()->id,
            items: $validated['items'],
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock transfer created successfully.',
            'data' => $transfer,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $transfer = StockTransfer::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'user',
            'items.product.unit',
            'items.productVariant',
            'items.batch',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transfer,
        ]);
    }

    public function ship(Request $request, string $id, ShipStockTransferAction $action): JsonResponse
    {
        $transfer = $action->execute($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Stock transfer dispatched. Goods are now in transit.',
            'data' => $transfer,
        ]);
    }

    public function receive(Request $request, string $id, ReceiveStockTransferAction $action): JsonResponse
    {
        $transfer = $action->execute($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Stock transfer received and added to destination warehouse stock.',
            'data' => $transfer,
        ]);
    }
}
