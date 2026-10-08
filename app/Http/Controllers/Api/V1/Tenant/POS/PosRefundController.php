<?php

namespace App\Http\Controllers\Api\V1\Tenant\POS;

use App\Domain\POS\Actions\PosRefundAction;
use App\Domain\POS\Actions\PosVoidAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosRefundController extends Controller
{
    public function refund(Request $request, string $orderId, PosRefundAction $action): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'refund_method' => ['nullable', 'string', 'in:cash,card,store_credit'],
            'reason' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.order_item_id' => ['required_with:items', 'uuid'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.0001'],
        ]);

        $refund = $action->execute(
            orderId: $orderId,
            amount: (float) $validated['amount'],
            itemsToRestock: $validated['items'] ?? [],
            refundMethod: $validated['refund_method'] ?? 'cash',
            reason: $validated['reason'] ?? 'Customer return',
            cashierId: $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'POS order refunded successfully.',
            'data' => $refund,
        ]);
    }

    public function void(Request $request, string $orderId, PosVoidAction $action): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $order = $action->execute(
            orderId: $orderId,
            reason: $validated['reason'] ?? 'Cashier void',
            userId: $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'POS order voided successfully.',
            'data' => $order,
        ]);
    }
}
