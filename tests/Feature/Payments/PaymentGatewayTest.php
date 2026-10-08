<?php

namespace Tests\Feature\Payments;

use App\Infrastructure\Payments\DTOs\PaymentIntentDTO;
use App\Infrastructure\Payments\PaymentGatewayManager;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    protected PaymentGatewayManager $gatewayManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gatewayManager = app(PaymentGatewayManager::class);
    }

    public function test_manager_resolves_all_supported_gateways(): void
    {
        $supported = ['ameriabank', 'idram', 'stripe', 'bank_transfer', 'cash'];

        foreach ($supported as $gw) {
            $instance = $this->gatewayManager->gateway($gw);
            $this->assertEquals($gw, $instance->getIdentifier());
        }
    }

    public function test_manager_throws_exception_for_unknown_gateway(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->gatewayManager->gateway('unknown_crypto_pay');
    }

    public function test_ameriabank_gateway_initiates_payment_with_redirect(): void
    {
        $gateway = $this->gatewayManager->gateway('ameriabank');

        $intent = new PaymentIntentDTO(
            tenantId: '01a11acd-0000-0000-0000-000000000001',
            invoiceId: null,
            amount: 25000.00,
            currency: 'AMD',
            description: 'Monthly ERP Subscription',
            returnUrl: 'https://company.erplannet.com/billing/success',
            cancelUrl: 'https://company.erplannet.com/billing/cancel'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertTrue($result->requiresRedirect());
        $this->assertStringContainsString('services.ameriabank.am/VPOS', $result->redirectUrl);
        $this->assertNotNull($result->transactionId);
    }

    public function test_idram_gateway_initiates_payment_with_redirect(): void
    {
        $gateway = $this->gatewayManager->gateway('idram');

        $intent = new PaymentIntentDTO(
            tenantId: '01a11acd-0000-0000-0000-000000000001',
            invoiceId: null,
            amount: 15000.00,
            currency: 'AMD',
            description: 'Starter Plan Renewal',
            returnUrl: 'https://company.erplannet.com/billing/success',
            cancelUrl: 'https://company.erplannet.com/billing/cancel'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertTrue($result->requiresRedirect());
        $this->assertStringContainsString('banking.idram.am/Payment', $result->redirectUrl);
    }

    public function test_cash_gateway_completes_payment_directly(): void
    {
        $gateway = $this->gatewayManager->gateway('cash');

        $intent = new PaymentIntentDTO(
            tenantId: '01a11acd-0000-0000-0000-000000000001',
            invoiceId: null,
            amount: 5000.00,
            currency: 'AMD',
            description: 'Cash on delivery receipt',
            returnUrl: 'https://company.erplannet.com/orders',
            cancelUrl: 'https://company.erplannet.com/orders'
        );

        $result = $gateway->initiatePayment($intent);

        $this->assertTrue($result->isSuccessful());
        $this->assertNotNull($result->transactionId);
    }
}
