<?php

namespace App\Infrastructure\Payments\Gateways;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;
use Illuminate\Support\Str;

class IdramGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'idram';
    }

    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO
    {
        $billNo = 'IDRAM_' . Str::upper(Str::random(10));

        // Idram standard checkout redirection
        $redirectUrl = "https://banking.idram.am/Payment/GetPayment?EDP_BILL_NO={$billNo}&EDP_AMOUNT={$intent->amount}";

        return new PaymentResultDTO(
            status: 'redirect',
            transactionId: $billNo,
            redirectUrl: $redirectUrl,
            gatewayResponse: [
                'bill_no' => $billNo,
                'gateway' => 'idram',
                'amount' => $intent->amount,
                'currency' => $intent->currency,
            ]
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO
    {
        $status = ($payload['EDP_PRECHECK'] ?? '') === 'YES' || ($payload['status'] ?? '') === 'success';

        return new PaymentResultDTO(
            status: $status ? 'successful' : 'failed',
            transactionId: $transactionId,
            gatewayResponse: $payload
        );
    }

    public function refund(RefundDTO $dto): RefundResultDTO
    {
        return new RefundResultDTO(
            success: true,
            refundId: 'IDRAM_REF_' . Str::upper(Str::random(10)),
            gatewayResponse: ['refunded_amount' => $dto->amount]
        );
    }

    public function handleWebhook(array $payload): WebhookResultDTO
    {
        $billNo = $payload['EDP_BILL_NO'] ?? null;
        $isSuccess = isset($payload['EDP_PAYER_ACCOUNT']);

        return new WebhookResultDTO(
            verified: true,
            transactionId: $billNo,
            status: $isSuccess ? 'successful' : 'failed',
            amount: isset($payload['EDP_AMOUNT']) ? (float) $payload['EDP_AMOUNT'] : null,
            currency: 'AMD',
            rawPayload: $payload
        );
    }
}
