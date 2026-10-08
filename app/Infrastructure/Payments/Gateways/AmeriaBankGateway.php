<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class AmeriaBankGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'ameriabank';
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        // Generates payment order ID for Ameriabank vPOS
        $orderId = 'AMERIA_' . Str::upper(Str::random(12));

        // In production: calls Ameriabank vPOS InitPayment API endpoint with ClientID, Username, Password, Description, Amount, OrderID, BackURL
        $redirectUrl = "https://services.ameriabank.am/VPOS/Payments/Pay?id={$orderId}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $orderId,
            redirectUrl: $redirectUrl,
            gatewayResponse: [
                'order_id' => $orderId,
                'gateway' => 'ameriabank',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        // In production: calls Ameriabank vPOS GetPaymentDetails API endpoint
        $resCode = $payload['response_code'] ?? '00';
        $isSuccess = ($resCode === '00');

        return new PaymentResultDTO(
            status: $isSuccess ? 'successful' : 'failed',
            transactionId: $transactionId,
            gatewayResponse: $payload,
            errorMessage: $isSuccess ? null : 'Ameriabank transaction rejected.'
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 'AMERIA_REF_' . Str::upper(Str::random(10)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload): WebhookResultDTO
    {
        $orderId = $payload['order_id'] ?? null;
        $status = ($payload['payment_status'] ?? '') === 'paid' ? 'successful' : 'failed';

        return new WebhookResultDTO(
            verified: true,
            transactionId: $orderId,
            status: $status,
            amount: isset($payload['amount']) ? (float) $payload['amount'] : null,
            currency: $payload['currency'] ?? 'AMD',
            rawPayload: $payload
        );
    }
}
