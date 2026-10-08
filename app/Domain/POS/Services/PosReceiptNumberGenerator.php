<?php

namespace App\Domain\POS\Services;

use App\Domain\Sales\Models\Order;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class PosReceiptNumberGenerator
{
    public function generate(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            $year = date('Y');
            $prefix = "REC-{$year}-";

            $latest = Order::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('receipt_number', 'like', "{$prefix}%")
                ->orderByDesc('receipt_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latest && $latest->receipt_number) {
                $lastNum = (int) substr($latest->receipt_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            return $prefix . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
