<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\DeliveryEvent;
use App\Domain\Delivery\Models\DeliveryShipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FailDeliveryAction
{
    public function execute(string $shipmentId, string $reason, ?string $userId = null): DeliveryShipment
    {
        return DB::transaction(function () use ($shipmentId, $reason, $userId) {
            $shipment = DeliveryShipment::with('driver')
                ->where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['assigned', 'in_transit'], true)) {
                throw new InvalidArgumentException("Cannot mark shipment as failed from status {$shipment->status}.");
            }

            $shipment->status = 'failed';
            $shipment->failure_reason = $reason;
            $shipment->save();

            // Free driver
            if ($shipment->driver) {
                $shipment->driver->status = 'available';
                $shipment->driver->save();
            }

            // Delivery Event
            DeliveryEvent::create([
                'tenant_id' => $shipment->tenant_id,
                'delivery_shipment_id' => $shipment->id,
                'delivery_driver_id' => $shipment->delivery_driver_id,
                'event_type' => 'failed',
                'metadata' => ['reason' => $reason],
                'created_at' => now(),
            ]);

            // Audit Log
            AuditLog::create([
                'tenant_id' => $shipment->tenant_id,
                'user_id' => $userId ?? auth()->id(),
                'action' => 'delivery.failed',
                'entity_type' => DeliveryShipment::class,
                'entity_id' => $shipment->id,
                'old_values' => ['status' => 'in_transit'],
                'new_values' => ['status' => 'failed', 'reason' => $reason],
                'created_at' => now(),
            ]);

            return $shipment;
        });
    }
}
