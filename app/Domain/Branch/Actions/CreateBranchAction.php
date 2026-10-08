<?php

namespace App\Domain\Branch\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Branch\Models\Branch;
use App\Infrastructure\MultiTenancy\TenantContext;

class CreateBranchAction
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected EntitlementManagerInterface $entitlementManager
    ) {}

    public function execute(array $data): Branch
    {
        // 1. Quota Check
        $this->entitlementManager->assertCan('limit.branches', 1);

        $tenant = $this->tenantContext->getTenant();

        return Branch::create([
            'tenant_id' => $tenant->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'is_main' => $data['is_main'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'settings' => $data['settings'] ?? [],
        ]);
    }
}
