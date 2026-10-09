<?php

namespace App\Domain\Procurement\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Procurement\Models\Supplier;
use App\Infrastructure\MultiTenancy\TenantContext;

class CreateSupplierAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected TenantContext $tenantContext
    ) {}

    public function execute(array $data): Supplier
    {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        $this->entitlements->assertCan('limit.suppliers', 1);

        return Supplier::create([
            'tenant_id' => $tenant->id,
            'company_name' => $data['company_name'],
            'legal_name' => $data['legal_name'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'website' => $data['website'] ?? null,
            'legal_address' => $data['legal_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'currency' => $data['currency'] ?? $tenant->currency ?? 'AMD',
            'payment_terms_days' => $data['payment_terms_days'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
