<?php

namespace App\Http\Controllers\Api\V1\Tenant\Sales;

use App\Domain\Sales\Actions\CreateOrderAction;
use App\Domain\Sales\Actions\RescheduleOrderAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Services\OrderStatusStateMachine;
use App\Domain\Sales\Services\PricingEngine;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['branch', 'warehouse', 'customer', 'items.unit']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $paymentStatus = $request->input('payment_status');
            if (in_array($paymentStatus, ['partial', 'partially_paid'], true)) {
                $query->whereIn('payment_status', ['partial', 'partially_paid']);
            } elseif (in_array($paymentStatus, ['unpaid', 'pending'], true)) {
                $query->whereIn('payment_status', ['unpaid', 'pending']);
            } else {
                $query->where('payment_status', $paymentStatus);
            }
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->input('order_type'));
        }

        if ($request->filled('source')) {
            $source = $request->input('source');
            if ($source === 'manual') {
                $query->whereIn('source', ['manual', 'manual_backoffice']);
            } elseif ($source === 'web') {
                $query->whereIn('source', ['web', 'online_store', 'storefront']);
            } else {
                $query->where('source', $source);
            }
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'ilike', "%{$term}%")
                    ->orWhere('external_reference', 'ilike', "%{$term}%")
                    ->orWhere('customer_notes', 'ilike', "%{$term}%")
                    ->orWhereRaw("customer_snapshot->>'name' ilike ?", ["%{$term}%"])
                    ->orWhereRaw("customer_snapshot->>'tax_id' ilike ?", ["%{$term}%"])
                    ->orWhereRaw("customer_snapshot->>'phone' ilike ?", ["%{$term}%"])
                    ->orWhereRaw('customer_snapshot::text ilike ?', ["%{$term}%"])
                    ->orWhereHas('customer', function ($cq) use ($term) {
                        $cq->where('first_name', 'ilike', "%{$term}%")
                            ->orWhere('last_name', 'ilike', "%{$term}%")
                            ->orWhere('phone', 'ilike', "%{$term}%")
                            ->orWhere('tax_id', 'ilike', "%{$term}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($term) {
                        $iq->where('product_name', 'ilike', "%{$term}%")
                            ->orWhere('product_sku', 'ilike', "%{$term}%");
                    });
            });
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
                'summary' => [
                    'total_revenue' => (float) Order::sum('total'),
                    'total_count' => Order::count(),
                    'pending_count' => Order::whereIn('status', ['new', 'confirmed', 'processing', 'in_progress'])->count(),
                ],
            ],
        ]);
    }

    public function store(Request $request, CreateOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'warehouse_id' => ['nullable', 'uuid', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'customer_address_id' => ['nullable', 'uuid', 'exists:customer_addresses,id'],
            'source' => ['nullable', 'string', 'max:50'],
            'order_type' => ['nullable', 'string', 'max:50'],
            'external_reference' => ['nullable', 'string', 'max:100'],
            'delivery_type' => ['nullable', 'string', 'in:delivery,pickup,dine_in'],
            'fulfillment_method' => ['nullable', 'string', 'in:delivery,pickup,dine_in'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'customer_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'scheduled_for' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,new,confirmed'],
            'allow_price_override' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:fixed,percent'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['delivery_type']) && ! empty($validated['fulfillment_method'])) {
            $validated['delivery_type'] = $validated['fulfillment_method'];
        }

        if (empty($validated['customer_notes']) && ! empty($validated['notes'])) {
            $validated['customer_notes'] = $validated['notes'];
        }

        if (empty($validated['order_discount']) && $request->filled('order_discount_value')) {
            $validated['order_discount'] = (float) $request->input('order_discount_value');
        }

        $order = $action->execute($validated, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::with([
            'branch',
            'warehouse',
            'customer.addresses',
            'address',
            'items.product',
            'items.unit',
            'statusHistories.user',
            'paymentTransactions',
            'payments',
            'deliveryNotes',
            'printJobs.printer',
            'responsibleEmployee',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function transitionStatus(Request $request, string $id, OrderStatusStateMachine $stateMachine): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,new,confirmed,processing,in_progress,packed,ready,delivery,delivered,completed,cancelled'],
            'comment' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $comment = $validated['reason'] ?? ($validated['comment'] ?? null);

        $updatedOrder = $stateMachine->transition(
            order: $order,
            newStatus: $validated['status'],
            userId: $request->user()?->id,
            comment: $comment
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

    public function reschedule(Request $request, string $id, RescheduleOrderAction $action): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'scheduled_for' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = $validated['reason'] ?? ($validated['note'] ?? null);

        $updatedOrder = $action->execute(
            order: $order,
            newScheduledFor: $validated['scheduled_for'],
            userId: $request->user()?->id,
            reason: $reason
        );

        return response()->json([
            'success' => true,
            'message' => "Order scheduled fulfillment date updated to {$updatedOrder->scheduled_for->toFormattedDateString()}.",
            'data' => $updatedOrder,
        ]);
    }

    public function calculatePricing(Request $request, PricingEngine $pricingEngine): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:fixed,percent'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'promo_code' => ['nullable', 'string'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'delivery_type' => ['nullable', 'string', 'in:delivery,pickup,dine_in'],
            'allow_price_override' => ['nullable', 'boolean'],
        ]);

        $orderDiscount = $validated['order_discount'] ?? ($request->input('order_discount_value') ?? 0.00);

        $calculation = $pricingEngine->calculate(
            $validated['items'],
            [
                'order_discount' => (float) $orderDiscount,
                'promo_code' => $validated['promo_code'] ?? $request->input('promo_code'),
                'delivery_fee' => (float) ($validated['delivery_fee'] ?? ($request->input('delivery_fee') ?? 0.00)),
                'delivery_type' => $validated['delivery_type'] ?? ($request->input('delivery_type') ?? 'delivery'),
                'allow_price_override' => (bool) ($validated['allow_price_override'] ?? ($request->input('allow_price_override') ?? false)),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $calculation,
        ]);
    }
}
