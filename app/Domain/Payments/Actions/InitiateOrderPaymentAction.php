<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\PaymentGatewayManager;

class InitiateOrderPaymentAction
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $orderId,
        string $gatewayIdentifier,
        string $returnUrl,
        string $cancelUrl,
        ?string $paymentMethod = null,
        ?float $amount = null
    ): array {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        $order = Order::findOrFail($orderId);
        $gateway = $this->gatewayManager->gateway($gatewayIdentifier);

        // Allow partial or split payment amount, default to remaining balance or total
        $payableAmount = $amount !== null ? max(0.01, round($amount, 2)) : ((float) ($order->balance_due > 0 ? $order->balance_due : $order->total));

        $intent = new PaymentIntentDTO(
            tenantId: $tenant->id,
            invoiceId: null,
            amount: $payableAmount,
            currency: $order->currency,
            description: "Payment for Order #{$order->order_number} ({$tenant->name})",
            returnUrl: $returnUrl,
            cancelUrl: $cancelUrl
        );

        $result = $gateway->initiatePayment($intent);

        $determinedMethod = $paymentMethod ?? match ($gateway->getIdentifier()) {
            'cash' => 'cash',
            'idram', 'telcell' => 'qr',
            default => 'card',
        };

        $transaction = PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'order_id' => $order->id,
            'gateway' => $gateway->getIdentifier(),
            'payment_method' => $determinedMethod,
            'transaction_id' => $result->transactionId,
            'amount' => $payableAmount,
            'currency' => $order->currency,
            'status' => $result->status === 'successful' ? 'successful' : 'pending',
            'gateway_response' => $result->gatewayResponse,
            'paid_at' => $result->status === 'successful' ? now() : null,
        ]);

        if ($result->status === 'successful') {
            $order->recalculateBalances();
        }

        return [
            'transaction' => $transaction,
            'payment_result' => $result,
        ];
    }
}
