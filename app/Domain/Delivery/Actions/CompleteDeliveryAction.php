<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Delivery\Models\DeliveryProof;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompleteDeliveryAction
{
    public function execute(
        string $shipmentId,
        string $receivedByName,
        ?string $signatureUrl = null,
        ?string $photoUrl = null,
        float $codCollected = 0.0,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null
    ): DeliveryShipment {
        return DB::transaction(function () use (
            $shipmentId,
            $receivedByName,
            $signatureUrl,
            $photoUrl,
            $codCollected,
            $latitude,
            $longitude,
            $notes
        ) {
            $shipment = DeliveryShipment::with(['order', 'driver'])
                ->where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['assigned', 'in_transit'], true)) {
                throw new \InvalidArgumentException("Cannot complete shipment from status {$shipment->status}.");
            }

            // 1. Create Proof of Delivery
            DeliveryProof::create([
                'tenant_id' => $shipment->tenant_id,
                'delivery_shipment_id' => $shipment->id,
                'received_by_name' => $receivedByName,
                'signature_url' => $signatureUrl,
                'photo_url' => $photoUrl,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'notes' => $notes,
                'delivered_at' => now(),
                'created_at' => now(),
            ]);

            // 2. Update Shipment
            $shipment->status = 'delivered';
            $shipment->delivered_at = now();
            $shipment->cod_collected = $codCollected;
            $shipment->save();

            // 3. Update Order
            $order = $shipment->order;
            $order->status = 'delivered';
            $order->delivered_at = now();

            // If COD was collected, record payment transaction and mark paid
            if ($codCollected > 0) {
                $order->payment_status = 'paid';
                PaymentTransaction::create([
                    'tenant_id' => $order->tenant_id,
                    'order_id' => $order->id,
                    'gateway' => 'cash',
                    'payment_method' => 'cash',
                    'transaction_id' => 'TX-COD-' . strtoupper(Str::random(10)),
                    'amount' => $codCollected,
                    'currency' => $order->currency,
                    'status' => 'successful',
                    'paid_at' => now(),
                    'gateway_response' => [
                        'type' => 'cash_on_delivery',
                        'collected_by_driver' => $shipment->driver?->full_name,
                    ],
                ]);
            }
            $order->save();

            // 4. Update Driver status back to available
            if ($shipment->driver) {
                $shipment->driver->status = 'available';
                $shipment->driver->save();
            }

            return $shipment->load(['proof', 'driver', 'order']);
        });
    }
}
