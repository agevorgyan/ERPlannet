<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\CodSettlement;
use App\Domain\Delivery\Models\DeliveryEvent;
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

            // 4. COD Lifecycle: Transition to 'courier_holding' with discrepancy tracking
            if ($codCollected > 0 || (float) $shipment->cod_amount > 0) {
                $settlement = CodSettlement::firstOrNew([
                    'tenant_id' => $shipment->tenant_id,
                    'delivery_shipment_id' => $shipment->id,
                ]);

                $expectedAmt = (float) ($settlement->expected_amount ?: $shipment->cod_amount);
                $diff = round($codCollected - $expectedAmt, 2);

                $settlement->order_id = $order->id;
                $settlement->delivery_driver_id = $shipment->delivery_driver_id;
                $settlement->status = 'courier_holding';
                $settlement->expected_amount = $expectedAmt;
                $settlement->collected_amount = $codCollected;
                $settlement->discrepancy_amount = $diff;
                if ($diff !== 0.0) {
                    $settlement->discrepancy_reason = "Driver collected {$codCollected} AMD (expected {$expectedAmt} AMD, diff {$diff} AMD)";
                }
                $settlement->save();

                // Payment transaction record for COD
                $order->payment_status = 'paid';
                PaymentTransaction::create([
                    'tenant_id' => $order->tenant_id,
                    'order_id' => $order->id,
                    'gateway' => 'cash',
                    'payment_method' => 'cash',
                    'transaction_id' => 'TX-COD-' . strtoupper(Str::random(10)),
                    'amount' => $codCollected > 0 ? $codCollected : $expectedAmt,
                    'currency' => $order->currency,
                    'status' => 'successful',
                    'paid_at' => now(),
                    'gateway_response' => [
                        'type' => 'cash_on_delivery',
                        'collected_by_driver' => $shipment->driver?->full_name,
                        'discrepancy' => $diff,
                    ],
                ]);

                AuditLog::create([
                    'tenant_id' => $order->tenant_id,
                    'user_id' => auth()->id(),
                    'action' => 'cod.collected_by_courier',
                    'entity_type' => CodSettlement::class,
                    'entity_id' => $settlement->id,
                    'new_values' => [
                        'expected' => $expectedAmt,
                        'collected' => $codCollected,
                        'discrepancy' => $diff,
                        'driver' => $shipment->driver?->full_name,
                    ],
                    'created_at' => now(),
                ]);
            }
            $order->save();

            // 5. Update Driver status back to available
            if ($shipment->driver) {
                $shipment->driver->status = 'available';
                $shipment->driver->save();
            }

            // 6. Record Delivery Event (State machine transition log)
            DeliveryEvent::create([
                'tenant_id' => $shipment->tenant_id,
                'delivery_shipment_id' => $shipment->id,
                'delivery_driver_id' => $shipment->delivery_driver_id,
                'event_type' => 'delivered',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'metadata' => [
                    'received_by' => $receivedByName,
                    'cod_collected' => $codCollected,
                ],
                'created_at' => now(),
            ]);

            // 7. Audit Log
            AuditLog::create([
                'tenant_id' => $shipment->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'delivery.completed',
                'entity_type' => DeliveryShipment::class,
                'entity_id' => $shipment->id,
                'new_values' => [
                    'shipment_number' => $shipment->shipment_number,
                    'received_by' => $receivedByName,
                    'cod_collected' => $codCollected,
                ],
                'created_at' => now(),
            ]);

            return $shipment->load(['proof', 'driver', 'order']);
        });
    }
}
