<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryShipment;
use Illuminate\Support\Facades\DB;

class AssignDeliveryDriverAction
{
    public function execute(string $shipmentId, string $driverId): DeliveryShipment
    {
        return DB::transaction(function () use ($shipmentId, $driverId) {
            $shipment = DeliveryShipment::where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            $driver = DeliveryDriver::where('id', $driverId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$driver->is_active) {
                throw new \InvalidArgumentException("Driver {$driver->full_name} is inactive.");
            }

            $shipment->delivery_driver_id = $driver->id;
            $shipment->status = 'assigned';
            $shipment->save();

            $driver->status = 'on_delivery';
            $driver->save();

            return $shipment->load('driver');
        });
    }
}
