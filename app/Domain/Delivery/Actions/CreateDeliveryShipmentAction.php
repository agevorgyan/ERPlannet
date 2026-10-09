<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Delivery\Models\CodSettlement;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\Delivery\Services\DeliveryShipmentNumberGenerator;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CreateDeliveryShipmentAction
{
    public function __construct(
        protected EntitlementManagerInterface $entitlements,
        protected DeliveryShipmentNumberGenerator $numberGenerator,
        protected TenantContext $tenantContext
    ) {}

    public function execute(
        string $orderId,
        string $deliveryAddress,
        ?string $recipientName = null,
        ?string $recipientPhone = null,
        ?string $scheduledSlotStart = null,
        ?string $scheduledSlotEnd = null,
        float $codAmount = 0.0,
        ?string $notes = null
    ): DeliveryShipment {
        $tenant = $this->tenantContext->getTenant();
        if (! $tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        $this->entitlements->assertCan('feature.delivery');

        return DB::transaction(function () use (
            $tenant,
            $orderId,
            $deliveryAddress,
            $recipientName,
            $recipientPhone,
            $scheduledSlotStart,
            $scheduledSlotEnd,
            $codAmount,
            $notes
        ) {
            $order = Order::findOrFail($orderId);
            $shipmentNumber = $this->numberGenerator->generate($tenant);

            $shipment = DeliveryShipment::create([
                'tenant_id' => $tenant->id,
                'order_id' => $order->id,
                'shipment_number' => $shipmentNumber,
                'status' => 'pending',
                'delivery_address' => $deliveryAddress,
                'recipient_name' => $recipientName ?? $order->customer?->full_name,
                'recipient_phone' => $recipientPhone ?? $order->customer?->phone,
                'scheduled_slot_start' => $scheduledSlotStart,
                'scheduled_slot_end' => $scheduledSlotEnd,
                'cod_amount' => $codAmount,
                'notes' => $notes,
            ]);

            // Initialize COD settlement tracking if COD amount is specified
            if ($codAmount > 0) {
                CodSettlement::create([
                    'tenant_id' => $tenant->id,
                    'delivery_shipment_id' => $shipment->id,
                    'order_id' => $order->id,
                    'delivery_driver_id' => null,
                    'status' => 'expected',
                    'expected_amount' => $codAmount,
                    'collected_amount' => 0.00,
                ]);
            }

            AuditLog::create([
                'tenant_id' => $tenant->id,
                'user_id' => auth()->id(),
                'action' => 'delivery.shipment_created',
                'entity_type' => DeliveryShipment::class,
                'entity_id' => $shipment->id,
                'new_values' => [
                    'shipment_number' => $shipmentNumber,
                    'order_id' => $order->id,
                    'cod_amount' => $codAmount,
                ],
                'created_at' => now(),
            ]);

            return $shipment;
        });
    }
}
