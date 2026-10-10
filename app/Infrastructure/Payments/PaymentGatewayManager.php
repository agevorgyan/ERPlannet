<?php

namespace App\Infrastructure\Payments;

use App\Infrastructure\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\Gateways\AmeriaBankGateway;
use App\Infrastructure\Payments\Gateways\ArCaGateway;
use App\Infrastructure\Payments\Gateways\BankTransferGateway;
use App\Infrastructure\Payments\Gateways\CashGateway;
use App\Infrastructure\Payments\Gateways\IdramGateway;
use App\Infrastructure\Payments\Gateways\StripeGateway;
use App\Infrastructure\Payments\Gateways\TelcellGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    protected array $gateways = [];

    public function __construct()
    {
        $this->registerDefaultGateways();
    }

    protected function registerDefaultGateways(): void
    {
        $this->register(new AmeriaBankGateway);
        $this->register(new IdramGateway);
        $this->register(new TelcellGateway);
        $this->register(new ArCaGateway);
        $this->register(new StripeGateway);
        $this->register(new BankTransferGateway);
        $this->register(new CashGateway);
    }

    public function register(PaymentGatewayInterface $gateway): self
    {
        $this->gateways[$gateway->getIdentifier()] = $gateway;

        return $this;
    }

    public function gateway(string $identifier): PaymentGatewayInterface
    {
        $key = strtolower($identifier);
        if ($key === 'ameria') {
            $key = 'ameriabank';
        }

        if (! isset($this->gateways[$key])) {
            throw new InvalidArgumentException("Payment gateway '{$identifier}' is not supported.");
        }

        return $this->gateways[$key];
    }

    /**
     * @return array<string>
     */
    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }
}
