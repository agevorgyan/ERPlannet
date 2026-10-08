<?php

namespace App\Domain\Manufacturing\Services;

use App\Domain\Manufacturing\Models\ProductionOrder;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ProductionOrderNumberGenerator
{
    /**
     * Generate next sequential Production Order number for the tenant (e.g. PRD-2026-000001).
     */
    public function generate(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            $year = date('Y');
            $prefix = "PRD-{$year}-";

            $latestOrder = ProductionOrder::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('order_number', 'like', "{$prefix}%")
                ->orderByDesc('order_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latestOrder) {
                $lastNum = (int) substr($latestOrder->order_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            return $prefix . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
