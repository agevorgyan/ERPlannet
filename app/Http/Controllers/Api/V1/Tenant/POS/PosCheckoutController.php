<?php

namespace App\Http\Controllers\Api\V1\Tenant\POS;

use App\Domain\POS\Actions\PosCheckoutAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosCheckoutController extends Controller
{
    public function checkout(Request $request, PosCheckoutAction $action): JsonResponse
    {
        $validated = $request->validate([
            'pos_session_id' => ['required', 'uuid', 'exists:pos_sessions,id'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.gateway' => ['required', 'string'],
            'payments.*.method' => ['required', 'string'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $order = $action->execute(
            posSessionId: $validated['pos_session_id'],
            items: $validated['items'],
            payments: $validated['payments'],
            customerId: $validated['customer_id'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'POS checkout successful. Receipt generated.',
            'data' => [
                'order' => $order,
                'receipt_number' => $order->receipt_number,
                'receipt_payload' => [
                    'store' => $order->branch?->name,
                    'terminal' => $order->posTerminal?->code,
                    'receipt_number' => $order->receipt_number,
                    'order_number' => $order->order_number,
                    'date' => $order->placed_at?->toDateTimeString(),
                    'items_count' => $order->items->count(),
                    'subtotal' => $order->subtotal,
                    'discount' => $order->discount,
                    'total' => $order->total,
                    'currency' => $order->currency,
                    'payment_status' => $order->payment_status,
                ],
            ],
        ], 201);
    }
}
