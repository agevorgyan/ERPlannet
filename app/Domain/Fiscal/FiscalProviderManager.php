<?php

namespace App\Domain\Fiscal;

use App\Domain\Fiscal\Contracts\FiscalProviderInterface;
use App\Domain\Fiscal\Providers\MockArmeniaFiscalProvider;
use InvalidArgumentException;

class FiscalProviderManager
{
    /** @var array<string, FiscalProviderInterface> */
    protected array $providers = [];

    public function __construct()
    {
        $this->register(new MockArmeniaFiscalProvider());
    }

    public function register(FiscalProviderInterface $provider): void
    {
        $this->providers[$provider->getIdentifier()] = $provider;
    }

    public function provider(?string $identifier = null): FiscalProviderInterface
    {
        $id = $identifier ?? config('services.fiscal.default', 'mock_armenia_src');

        if (!isset($this->providers[$id])) {
            throw new InvalidArgumentException("Fiscal provider [{$id}] is not registered.");
        }

        return $this->providers[$id];
    }
}
