<?php

namespace App\Domain\Quality\Services;

use App\Domain\Quality\Models\QualityInspection;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class QualityInspectionNumberGenerator
{
    /**
     * Generate next sequential Quality Inspection number for the tenant (e.g. QA-2026-000001).
     */
    public function generate(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            $year = date('Y');
            $prefix = "QA-{$year}-";

            $latestInspection = QualityInspection::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('inspection_number', 'like', "{$prefix}%")
                ->orderByDesc('inspection_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latestInspection) {
                $lastNum = (int) substr($latestInspection->inspection_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            return $prefix.str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
