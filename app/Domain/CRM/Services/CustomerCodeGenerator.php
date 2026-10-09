<?php

namespace App\Domain\CRM\Services;

use App\Domain\CRM\Models\Customer;

class CustomerCodeGenerator
{
    /**
     * Generate an incremental readable customer code per tenant.
     * E.g. CUST-0001, CUST-0002 ...
     */
    public static function generate(string $tenantId, string $prefix = 'CUST'): string
    {
        $count = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->count();

        $nextNumber = $count + 1;

        do {
            $code = sprintf('%s-%04d', $prefix, $nextNumber);
            $exists = Customer::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('customer_code', $code)
                ->exists();

            if (! $exists) {
                return $code;
            }

            $nextNumber++;
        } while (true);
    }
}
