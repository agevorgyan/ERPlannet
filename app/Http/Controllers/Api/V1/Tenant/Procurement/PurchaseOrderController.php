<?php

namespace App\Http\Controllers\Api\V1\Tenant\Procurement;

use App\Domain\Procurement\Actions\CancelPurchaseOrderAction;
use App\Domain\Procurement\Actions\CreatePurchaseOrderAction;
use App\Domain\Procurement\Actions\ReceivePurchaseOrderAction;
use App\Domain\Procurement\Actions\SubmitPurchaseOrderAction;
use App\Domain\Procurement\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->query('warehouse_id'));
        }

        $pos = $query->latest('created_at')->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $pos->items(),
            'meta' => [
                'current_page' => $pos->currentPage(),
                'last_page' => $pos->lastPage(),
                'total' => $pos->total(),
            ],
        ]);
    }

    public function store(Request $request, CreatePurchaseOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'uuid', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'uuid', 'exists:warehouses,id'],
            'order_date' => ['nullable', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $po = $action->execute(
            supplierId: $validated['supplier_id'],
            warehouseId: $validated['warehouse_id'],
            userId: $request->user()->id,
            items: $validated['items'],
            orderDate: $validated['order_date'] ?? null,
            expectedDeliveryDate: $validated['expected_delivery_date'] ?? null,
            taxAmount: (float) ($validated['tax_amount'] ?? 0.0),
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Purchase order created successfully.',
            'data' => $po,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $po = PurchaseOrder::with([
            'supplier',
            'warehouse',
            'user',
            'items.product.unit',
            'items.productVariant',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $po,
        ]);
    }

    public function submit(string $id, SubmitPurchaseOrderAction $action): JsonResponse
    {
        $po = $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order submitted successfully to supplier.',
            'data' => $po,
        ]);
    }

    public function receive(Request $request, string $id, ReceivePurchaseOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'uuid', 'exists:purchase_order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.mfg_date' => ['nullable', 'date'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        $po = $action->execute(
            purchaseOrderId: $id,
            receivedItems: $validated['items'],
            userId: $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Goods receipt completed. Stock levels and batches updated.',
            'data' => $po,
        ]);
    }

    public function cancel(string $id, CancelPurchaseOrderAction $action): JsonResponse
    {
        $po = $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order cancelled successfully.',
            'data' => $po,
        ]);
    }
}
