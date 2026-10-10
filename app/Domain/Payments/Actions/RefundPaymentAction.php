<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundPaymentAction
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $transactionId,
        float $amount,
        string $reason = 'Customer return',
        ?string $userId = null
    ): PaymentTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Refund amount must be greater than zero.');
        }

        return DB::transaction(function () use ($transactionId, $amount, $reason, $userId) {
            $transaction = PaymentTransaction::with('order')
                ->where('id', $transactionId)
                ->orWhere('transaction_id', $transactionId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($transaction->status, ['successful', 'completed'], true)) {
                throw new InvalidArgumentException("Cannot refund transaction with status {$transaction->status}.");
            }

            $currentRefunded = (float) ($transaction->refunded_amount ?? 0.0);
            if (($currentRefunded + $amount) > (float) $transaction->amount) {
                throw new InvalidArgumentException('Refund amount exceeds remaining transaction balance.');
            }

            $gatewayKey = $transaction->gateway ?: 'cash';
            $gateway = $this->gatewayManager->gateway($gatewayKey);
            $dto = new RefundDTO(
                transactionId: $transaction->transaction_id,
                amount: $amount,
                currency: $transaction->currency ?? 'AMD',
                reason: $reason
            );

            $refundResult = $gateway->refund($dto);
            if (! $refundResult->success) {
                throw new \RuntimeException($refundResult->errorMessage ?? 'Gateway rejected refund.');
            }

            $newRefundedTotal = $currentRefunded + $amount;
            $transaction->refunded_amount = $newRefundedTotal;
            if ($newRefundedTotal >= (float) $transaction->amount) {
                $transaction->status = 'refunded';
            }
            $transaction->save();

            // Update order payment status and recalculate balances
            if ($transaction->order) {
                $transaction->order->recalculateBalances();
            }

            // Create Audit Log
            AuditLog::create([
                'tenant_id' => $transaction->tenant_id,
                'user_id' => $userId,
                'action' => 'payment.refund',
                'entity_type' => PaymentTransaction::class,
                'entity_id' => $transaction->id,
                'old_values' => ['refunded_amount' => $currentRefunded, 'status' => 'successful'],
                'new_values' => ['refunded_amount' => $newRefundedTotal, 'status' => $transaction->status, 'reason' => $reason],
                'created_at' => now(),
            ]);

            return $transaction;
        });
    }
}
