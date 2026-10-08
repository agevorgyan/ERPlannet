<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Delivery\Models\DeliveryEvent;

class RecordGpsPingAction
{
    public function execute(
        string $driverId,
        ?string $shipmentId,
        float $latitude,
        float $longitude,
        ?float $speed = null,
        ?int $batteryLevel = null,
        array $metadata = []
    ): DeliveryEvent {
        return DeliveryEvent::create([
            'tenant_id' => auth()->user()?->tenant_id,
            'delivery_driver_id' => $driverId,
            'delivery_shipment_id' => $shipmentId,
            'event_type' => 'gps_ping',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'battery_level' => $batteryLevel,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
