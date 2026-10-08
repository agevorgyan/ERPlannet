<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\DeliveryEvent;
use App\Domain\Delivery\Models\DeliveryShipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DispatchShipmentAction
{
    public function execute(string $shipmentId): DeliveryShipment
    {
        return DB::transaction(function () use ($shipmentId) {
            $shipment = DeliveryShipment::where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['pending', 'assigned'], true)) {
                throw new InvalidArgumentException("Cannot dispatch shipment in status {$shipment->status}.");
            }

            $shipment->status = 'in_transit';
            $shipment->dispatched_at = now();
            $shipment->save();

            // Update associated order status
            $order = $shipment->order;
            if ($order) {
                $order->status = 'delivery';
                $order->save();
            }

            // Record Delivery Event
            DeliveryEvent::create([
                'tenant_id' => $shipment->tenant_id,
                'delivery_shipment_id' => $shipment->id,
                'delivery_driver_id' => $shipment->delivery_driver_id,
                'event_type' => 'dispatched',
                'metadata' => [
                    'dispatched_at' => now()->toIso8601String(),
                ],
                'created_at' => now(),
            ]);

            // Audit Log
            AuditLog::create([
                'tenant_id' => $shipment->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'delivery.dispatched',
                'entity_type' => DeliveryShipment::class,
                'entity_id' => $shipment->id,
                'old_values' => ['status' => 'assigned'],
                'new_values' => ['status' => 'in_transit'],
                'created_at' => now(),
            ]);

            return $shipment;
        });
    }
}
