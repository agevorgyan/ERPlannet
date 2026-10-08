<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;

class ReconcilePaymentAction
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager
    ) {}

    public function execute(string $transactionId, ?string $userId = null): array
    {
        return DB::transaction(function () use ($transactionId, $userId) {
            $transaction = PaymentTransaction::where('id', $transactionId)
                ->orWhere('transaction_id', $transactionId)
                ->lockForUpdate()
                ->firstOrFail();

            $gateway = $this->gatewayManager->gateway($transaction->gateway);
            $gatewayStatus = $gateway->getStatus($transaction->transaction_id);

            $oldStatus = $transaction->status;
            $reconciledStatus = $gatewayStatus->status;

            $discrepancy = ($oldStatus !== $reconciledStatus);

            if ($discrepancy && in_array($reconciledStatus, ['successful', 'failed', 'refunded'], true)) {
                $transaction->status = $reconciledStatus;
                $transaction->save();

                if ($reconciledStatus === 'successful' && $transaction->order) {
                    $transaction->order->payment_status = 'paid';
                    $transaction->order->save();
                }
            }

            AuditLog::create([
                'tenant_id' => $transaction->tenant_id,
                'user_id' => $userId,
                'action' => 'payment.reconcile',
                'entity_type' => PaymentTransaction::class,
                'entity_id' => $transaction->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => $transaction->status,
                    'gateway_status' => $reconciledStatus,
                    'discrepancy' => $discrepancy,
                ],
                'created_at' => now(),
            ]);

            return [
                'transaction' => $transaction,
                'discrepancy' => $discrepancy,
                'gateway_status' => $gatewayStatus,
            ];
        });
    }
}
