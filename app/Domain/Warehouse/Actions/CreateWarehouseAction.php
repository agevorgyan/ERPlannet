<?php

namespace App\Domain\Warehouse\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Billing\Exceptions\FeatureNotAvailableException;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\TenantContext;

class CreateWarehouseAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected TenantContext $tenantContext
    ) {}

    public function execute(array $data): Warehouse
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        // 1. Feature check: if adding more than 1 warehouse, require multi-warehouse feature
        $existingCount = Warehouse::whereNull('deleted_at')->count();
        if ($existingCount >= 1 && ! $this->entitlements->can('feature.inventory_multi_warehouse')) {
            throw new FeatureNotAvailableException(
                'feature.inventory_multi_warehouse',
                'Multi-Warehouse management is not available on your current plan. Please upgrade to Growth or Enterprise.'
            );
        }

        // 2. Quota check
        $this->entitlements->assertCan('limit.warehouses', 1);

        $isDefault = $data['is_default'] ?? ($existingCount === 0);

        if ($isDefault) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        return Warehouse::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $data['branch_id'] ?? null,
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'type' => $data['type'] ?? 'standard',
            'address' => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_default' => $isDefault,
            'settings' => $data['settings'] ?? [],
        ]);
    }
}
