<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Billing\Contracts\EntitlementManagerInterface;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\Delivery\Services\DeliveryShipmentNumberGenerator;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\TenantContext;

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
        if (!$tenant) {
            throw new \RuntimeException('Tenant context not set.');
        }

        $this->entitlements->assertCan('feature.delivery');

        $order = Order::findOrFail($orderId);
        $shipmentNumber = $this->numberGenerator->generate($tenant);

        return DeliveryShipment::create([
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
    }
}
