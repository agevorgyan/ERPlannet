<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\DeliveryEvent;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\Warehouse\Actions\RecordStockMovementAction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReturnDeliveryShipmentAction
{
    public function __construct(
        protected RecordStockMovementAction $recordStockMovement
    ) {}

    public function execute(string $shipmentId, string $reason = 'Customer refused or address invalid', ?string $userId = null): DeliveryShipment
    {
        return DB::transaction(function () use ($shipmentId, $reason, $userId) {
            $shipment = DeliveryShipment::with(['order.items', 'driver'])
                ->where('id', $shipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($shipment->status, ['failed', 'in_transit'], true)) {
                throw new InvalidArgumentException("Cannot return shipment in status {$shipment->status}.");
            }

            $shipment->status = 'returned';
            $shipment->returned_at = now();
            $shipment->failure_reason = $reason;
            $shipment->save();

            // Restock items back to branch/warehouse
            $order = $shipment->order;
            $warehouse = $order->branch?->warehouses()->first();
            $warehouseId = $warehouse?->id;

            if ($warehouseId && $order) {
                foreach ($order->items as $item) {
                    $this->recordStockMovement->execute(
                        warehouseId: $warehouseId,
                        productId: $item->product_id,
                        productVariantId: $item->variant_id,
                        type: 'adjustment_plus',
                        quantity: (float) $item->quantity,
                        unitCost: 0.0,
                        userId: $userId ?? auth()->id(),
                        referenceType: DeliveryShipment::class,
                        referenceId: $shipment->id,
                        notes: "Delivery returned restock for shipment {$shipment->shipment_number}"
                    );
                }
            }

            if ($shipment->driver) {
                $shipment->driver->status = 'available';
                $shipment->driver->save();
            }

            // Delivery Event
            DeliveryEvent::create([
                'tenant_id' => $shipment->tenant_id,
                'delivery_shipment_id' => $shipment->id,
                'delivery_driver_id' => $shipment->delivery_driver_id,
                'event_type' => 'returned',
                'metadata' => ['reason' => $reason],
                'created_at' => now(),
            ]);

            // Audit Log
            AuditLog::create([
                'tenant_id' => $shipment->tenant_id,
                'user_id' => $userId ?? auth()->id(),
                'action' => 'delivery.returned',
                'entity_type' => DeliveryShipment::class,
                'entity_id' => $shipment->id,
                'old_values' => ['status' => 'failed'],
                'new_values' => ['status' => 'returned', 'reason' => $reason],
                'created_at' => now(),
            ]);

            return $shipment;
        });
    }
}
