<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryEvent;
use App\Domain\Delivery\Models\DeliveryShipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssignDeliveryDriverAction
{
    public function execute(string $shipmentId, string $driverId): DeliveryShipment
    {
        return DB::transaction(function () use ($shipmentId, $driverId) {
            $shipment = DeliveryShipment::where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['pending', 'failed'], true)) {
                throw new InvalidArgumentException("Cannot assign driver to shipment in status {$shipment->status}.");
            }

            $driver = DeliveryDriver::where('id', $driverId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$driver->is_active) {
                throw new InvalidArgumentException("Driver {$driver->full_name} is inactive.");
            }

            $oldDriverId = $shipment->delivery_driver_id;
            $shipment->delivery_driver_id = $driver->id;
            $shipment->status = 'assigned';
            $shipment->save();

            $driver->status = 'on_delivery';
            $driver->save();

            // Record Delivery Event
            DeliveryEvent::create([
                'tenant_id' => $shipment->tenant_id,
                'delivery_shipment_id' => $shipment->id,
                'delivery_driver_id' => $driver->id,
                'event_type' => 'assigned',
                'metadata' => [
                    'driver_name' => $driver->full_name,
                    'vehicle_type' => $driver->vehicle_type,
                    'license_plate' => $driver->license_plate,
                ],
                'created_at' => now(),
            ]);

            // Audit Log
            AuditLog::create([
                'tenant_id' => $shipment->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'delivery.driver_assigned',
                'entity_type' => DeliveryShipment::class,
                'entity_id' => $shipment->id,
                'old_values' => ['driver_id' => $oldDriverId, 'status' => 'pending'],
                'new_values' => ['driver_id' => $driver->id, 'status' => 'assigned'],
                'created_at' => now(),
            ]);

            return $shipment->load('driver');
        });
    }
}
