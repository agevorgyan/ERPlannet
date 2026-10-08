<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Billing\Models\Payment;
use App\Http\Controllers\Controller;
use App\Infrastructure\MultiTenancy\TenantContext;
use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected PaymentGatewayManager $gatewayManager
    ) {}

    public function initiate(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->getTenant();

        $validated = $request->validate([
            'invoice_id' => ['nullable', 'uuid', 'exists:invoices,id'],
            'gateway' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'return_url' => ['required', 'url'],
            'cancel_url' => ['required', 'url'],
        ]);

        $gateway = $this->gatewayManager->gateway($validated['gateway']);

        $intent = new PaymentIntentDTO(
            tenantId: $tenant->id,
            invoiceId: $validated['invoice_id'] ?? null,
            amount: (float) $validated['amount'],
            currency: $validated['currency'] ?? $tenant->currency,
            description: "Payment for {$tenant->name}",
            returnUrl: $validated['return_url'],
            cancelUrl: $validated['cancel_url']
        );

        $result = $gateway->initiatePayment($intent);

        // Store payment transaction
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'invoice_id' => $validated['invoice_id'] ?? null,
            'gateway' => $gateway->getIdentifier(),
            'transaction_id' => $result->transactionId,
            'amount' => $intent->amount,
            'currency' => $intent->currency,
            'status' => $result->status === 'successful' ? 'successful' : 'pending',
            'gateway_response' => $result->gatewayResponse,
            'paid_at' => $result->status === 'successful' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'transaction_id' => $payment->transaction_id,
                'redirect_url' => $result->redirectUrl,
            ],
        ]);
    }

    public function availableGateways(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->gatewayManager->getAvailableGateways(),
        ]);
    }
}
