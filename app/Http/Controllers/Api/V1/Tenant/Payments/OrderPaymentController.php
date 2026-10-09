<?php

namespace App\Http\Controllers\Api\V1\Tenant\Payments;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Payments\Actions\InitiateOrderPaymentAction;
use App\Domain\Payments\Actions\ReconcilePaymentAction;
use App\Domain\Payments\Actions\RefundPaymentAction;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Sales\Models\Order;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function refund(
        Request $request,
        string $transactionId,
        RefundPaymentAction $action
    ): JsonResponse {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction = $action->execute(
            transactionId: $transactionId,
            amount: (float) $validated['amount'],
            reason: $validated['reason'] ?? 'Customer refund',
            userId: $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment refunded successfully.',
            'data' => $transaction,
        ]);
    }

    public function reconcile(
        Request $request,
        string $transactionId,
        ReconcilePaymentAction $action
    ): JsonResponse {
        $result = $action->execute($transactionId, $request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => 'Payment reconciled successfully.',
            'data' => $result,
        ]);
    }

    public function webhook(Request $request, string $gateway): JsonResponse
    {
        return DB::transaction(function () use ($request, $gateway) {
            $gatewayInstance = $this->gatewayManager->gateway($gateway);

            // Pass headers for signature validation
            $headers = array_change_key_case($request->headers->all(), CASE_LOWER);
            $normalizedHeaders = [];
            foreach ($headers as $k => $v) {
                $normalizedHeaders[$k] = is_array($v) ? reset($v) : $v;
            }

            $webhookResult = $gatewayInstance->handleWebhook($request->all(), $normalizedHeaders);

            if (! $webhookResult->verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature or unverified payload.',
                ], 403);
            }

            $txIdentifier = $webhookResult->transactionId;
            if (! $txIdentifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction ID missing from webhook payload.',
                ], 400);
            }

            // Find transaction (scoped by tenant if tenant context is present, otherwise global lookup)
            $query = PaymentTransaction::with('order')->where('transaction_id', $txIdentifier);
            if (! $this->tenantContext->hasTenant()) {
                $query = $query->withoutTenantScope();
            }
            $transaction = $query->lockForUpdate()->first();

            if (! $transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found for webhook notification.',
                ], 404);
            }

            // IDEMPOTENCY / REPLAY PROTECTION:
            // If already processed and successful, return 200 without duplicate action
            if ($transaction->status === 'successful') {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook already processed (idempotent replay).',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'status' => $transaction->status,
                        'idempotent' => true,
                    ],
                ]);
            }

            $oldStatus = $transaction->status;
            $transaction->status = $webhookResult->status === 'successful' ? 'successful' : 'failed';
            $transaction->gateway_response = array_merge((array) $transaction->gateway_response, $request->all());
            $transaction->webhook_received_at = now();

            if ($transaction->status === 'successful') {
                $transaction->paid_at = now();
                if ($transaction->order) {
                    $transaction->order->payment_status = 'paid';
                    $transaction->order->save();
                }
            }
            $transaction->save();

            // Record Audit Log
            AuditLog::create([
                'tenant_id' => $transaction->tenant_id,
                'user_id' => null,
                'action' => 'payment.webhook_processed',
                'entity_type' => PaymentTransaction::class,
                'entity_id' => $transaction->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $transaction->status, 'gateway' => $gateway],
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated from webhook.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status,
                ],
            ]);
        });
    }
}
