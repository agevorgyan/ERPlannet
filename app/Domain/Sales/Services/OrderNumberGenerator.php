<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;

class OrderNumberGenerator
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Generate unique sequential order number for current tenant: ORD-2026-000001
     */
    public function generate(): string
    {
        $tenantId = $this->tenantContext->id();
        $year = date('Y');
        $prefix = "ORD-{$year}-";

        // Query orders for this tenant in current year
        $orders = Order::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('order_number', 'like', "{$prefix}%")
            ->pluck('order_number');

        $maxNumber = 0;
        foreach ($orders as $num) {
            if (preg_match('/^ORD-\d{4}-(\d+)$/', $num, $matches)) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        $nextNumber = $maxNumber + 1;

        return $prefix . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
