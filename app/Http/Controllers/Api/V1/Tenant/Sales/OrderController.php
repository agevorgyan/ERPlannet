<?php

namespace App\Http\Controllers\Api\V1\Tenant\Sales;

use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Services\OrderStatusStateMachine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['branch', 'customer', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->query('branch_id'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->query('date_to'));
        }

        $orders = $query->latest('placed_at')->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request, CreateOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'customer_address_id' => ['nullable', 'uuid', 'exists:customer_addresses,id'],
            'source' => ['nullable', 'string', 'max:50'],
            'delivery_type' => ['nullable', 'string', 'in:delivery,pickup,dine_in'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'customer_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'scheduled_for' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $order = $action->execute($validated, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::with(['branch', 'customer.addresses', 'address', 'items.product', 'statusHistories.user', 'payments'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function transitionStatus(Request $request, string $id, OrderStatusStateMachine $stateMachine): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,processing,packed,delivery,delivered,cancelled'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedOrder = $stateMachine->transition(
            order: $order,
            newStatus: $validated['status'],
            userId: $request->user()?->id,
            comment: $validated['comment'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => "Order status updated to '{$validated['status']}'.",
            'data' => $updatedOrder,
        ]);
    }

    public function cancel(Request $request, string $id, OrderStatusStateMachine $stateMachine): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $cancelledOrder = $stateMachine->transition(
            order: $order,
            newStatus: 'cancelled',
            userId: $request->user()?->id,
            comment: $validated['reason']
        );

        return response()->json([
            'success' => true,
            'message' => 'Order has been cancelled.',
            'data' => $cancelledOrder,
        ]);
    }
}
