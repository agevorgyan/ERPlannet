<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Delivery\Models\DeliveryShipment;
use Illuminate\Support\Facades\DB;

class DispatchShipmentAction
{
    public function execute(string $shipmentId): DeliveryShipment
    {
        return DB::transaction(function () use ($shipmentId) {
            $shipment = DeliveryShipment::where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['pending', 'assigned'], true)) {
                throw new \InvalidArgumentException("Cannot dispatch shipment in status {$shipment->status}.");
            }

            $shipment->status = 'in_transit';
            $shipment->dispatched_at = now();
            $shipment->save();

            // Update associated order status
            $order = $shipment->order;
            $order->status = 'delivery';
            $order->save();

            return $shipment;
        });
    }
}
