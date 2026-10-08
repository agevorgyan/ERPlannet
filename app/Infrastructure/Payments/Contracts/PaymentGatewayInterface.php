<?php

namespace App\Infrastructure\Payments\Contracts;

use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\DTOs\PaymentResultDTO;
use App\Infrastructure\Payments\DTOs\RefundDTO;
use App\Infrastructure\Payments\DTOs\RefundResultDTO;
use App\Infrastructure\Payments\DTOs\WebhookResultDTO;

interface PaymentGatewayInterface
{
    /**
     * Unique gateway identifier (e.g., 'ameriabank', 'idram', 'stripe', 'cash', 'bank_transfer')
     */
    public function getIdentifier(): string;

    /**
     * Initiate a payment and return payment result (redirect URL or completion details).
     */
    public function initiatePayment(PaymentIntentDTO $intent): PaymentResultDTO;

    /**
     * Verify payment status using transaction ID and raw gateway payload.
     */
    public function verifyPayment(string $transactionId, array $payload = []): PaymentResultDTO;

    /**
     * Refund a completed payment.
     */
    public function refund(RefundDTO $dto): RefundResultDTO;

    /**
     * Parse and validate incoming webhook from the payment provider.
     */
    public function handleWebhook(array $payload): WebhookResultDTO;
}
