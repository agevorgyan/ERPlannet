<?php

namespace App\Http\Controllers\Api\V1\Tenant\Payments;

use App\Domain\Payments\Actions\InitiateOrderPaymentAction;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Sales\Models\Order;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected PaymentGatewayManager $gatewayManager
    ) {}

    public function initiate(
        Request $request,
        string $orderId,
        InitiateOrderPaymentAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'gateway' => ['required', 'string'],
            'return_url' => ['required', 'url'],
            'cancel_url' => ['required', 'url'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,qr,transfer'],
        ]);

        $result = $action->execute(
            orderId: $orderId,
            gatewayIdentifier: $validated['gateway'],
            returnUrl: $validated['return_url'],
            cancelUrl: $validated['cancel_url'],
            paymentMethod: $validated['payment_method'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment initiated successfully.',
            'data' => [
                'transaction_id' => $result['transaction']->id,
                'external_transaction_id' => $result['transaction']->transaction_id,
                'gateway' => $result['transaction']->gateway,
                'status' => $result['transaction']->status,
                'amount' => $result['transaction']->amount,
                'currency' => $result['transaction']->currency,
                'redirect_url' => $result['payment_result']->redirectUrl,
                'gateway_response' => $result['payment_result']->gatewayResponse,
            ],
        ], 201);
    }

    public function transactions(string $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $transactions = $order->paymentTransactions()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function webhook(Request $request, string $gateway): JsonResponse
    {
        $transactionId = $request->input('transaction_id')
            ?? $request->input('payment_id')
            ?? $request->input('order_id');

        $status = $request->input('status', 'successful');

        $transaction = PaymentTransaction::where('transaction_id', $transactionId)->first();

        if ($transaction) {
            $transaction->status = $status === 'successful' || $status === 'paid' ? 'successful' : 'failed';
            $transaction->gateway_response = array_merge((array) $transaction->gateway_response, $request->all());
            if ($transaction->status === 'successful') {
                $transaction->paid_at = now();
                if ($transaction->order) {
                    $transaction->order->payment_status = 'paid';
                    $transaction->order->save();
                }
            }
            $transaction->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated from webhook.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transaction not found for webhook notification.',
        ], 404);
    }
}
