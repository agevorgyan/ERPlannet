<?php

namespace App\Domain\Delivery\Services;

use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\DB;

class DeliveryShipmentNumberGenerator
{
    public function generate(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            $year = date('Y');
            $prefix = "DLV-{$year}-";

            $latest = DeliveryShipment::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('shipment_number', 'like', "{$prefix}%")
                ->orderByDesc('shipment_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($latest) {
                $lastNum = (int) substr($latest->shipment_number, strlen($prefix));
                $nextSequence = $lastNum + 1;
            }

            return $prefix . str_pad((string) $nextSequence, 6, '0', STR_PAD_LEFT);
        });
    }
}
