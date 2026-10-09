<?php

namespace App\Domain\POS\Services;

use App\Domain\POS\Models\PosSession;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class PosSessionNumberGenerator
{
    public function generate(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            $year = date('Y');
            $prefix = "SES-{$year}-";

            $latest = PosSession::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('session_number', 'like', "{$prefix}%")
                ->orderByDesc('session_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latest) {
                $lastNum = (int) substr($latest->session_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            return $prefix.str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
