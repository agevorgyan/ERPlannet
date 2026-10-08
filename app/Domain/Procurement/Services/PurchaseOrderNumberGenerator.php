<?php

namespace App\Domain\Procurement\Services;

use App\Domain\Procurement\Models\PurchaseOrder;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class PurchaseOrderNumberGenerator
{
    /**
     * Generate next sequential Purchase Order number for the tenant (e.g. PO-2026-000001).
     */
    public function generate(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            $year = date('Y');
            $prefix = "PO-{$year}-";

            $latestPO = PurchaseOrder::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('po_number', 'like', "{$prefix}%")
                ->orderByDesc('po_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latestPO) {
                $lastNum = (int) substr($latestPO->po_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            return $prefix . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
