<?php

namespace App\Infrastructure\MultiTenancy;

use App\Domain\Tenant\Models\Tenant;

class TenantContext
{
    protected ?Tenant $currentTenant = null;

    public function setCurrentTenant(?Tenant $tenant): self
    {
        $this->currentTenant = $tenant;

        return $this;
    }

    public function getTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    public function id(): ?string
    {
        return $this->currentTenant?->id;
    }

    public function hasTenant(): bool
    {
        return $this->currentTenant !== null;
    }

    public function timezone(): string
    {
        return $this->currentTenant?->timezone ?? config('app.timezone', 'UTC');
    }

    public function currency(): string
    {
        return $this->currentTenant?->currency ?? 'AMD';
    }

    public function locale(): string
    {
        return $this->currentTenant?->default_locale ?? config('app.locale', 'hy');
    }

    public function clear(): void
    {
        $this->currentTenant = null;
    }
}
