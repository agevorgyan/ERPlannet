<?php

namespace App\Http\Controllers\Api\V1\Tenant\Delivery;

use App\Domain\Delivery\Actions\AssignDeliveryDriverAction;
use App\Domain\Delivery\Actions\CompleteDeliveryAction;
use App\Domain\Delivery\Actions\CreateDeliveryShipmentAction;
use App\Domain\Delivery\Actions\DispatchShipmentAction;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DeliveryShipment::with(['driver', 'order.customer', 'proof']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('delivery_driver_id')) {
            $query->where('delivery_driver_id', $request->query('delivery_driver_id'));
        }

        $shipments = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'total' => $shipments->total(),
            ],
        ]);
    }

    public function store(Request $request, CreateDeliveryShipmentAction $action): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'uuid', 'exists:orders,id'],
            'delivery_address' => ['required', 'string'],
            'recipient_name' => ['nullable', 'string', 'max:150'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'scheduled_slot_start' => ['nullable', 'date'],
            'scheduled_slot_end' => ['nullable', 'date'],
            'cod_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $shipment = $action->execute(
            orderId: $validated['order_id'],
            deliveryAddress: $validated['delivery_address'],
            recipientName: $validated['recipient_name'] ?? null,
            recipientPhone: $validated['recipient_phone'] ?? null,
            scheduledSlotStart: $validated['scheduled_slot_start'] ?? null,
            scheduledSlotEnd: $validated['scheduled_slot_end'] ?? null,
            codAmount: (float) ($validated['cod_amount'] ?? 0.0),
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Delivery shipment created.',
            'data' => $shipment->load(['order', 'driver']),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $shipment = DeliveryShipment::with(['driver', 'order.items.product', 'order.customer', 'proof'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ]);
    }

    public function assign(Request $request, string $id, AssignDeliveryDriverAction $action): JsonResponse
    {
        $validated = $request->validate([
            'delivery_driver_id' => ['required', 'uuid', 'exists:delivery_drivers,id'],
        ]);

        $shipment = $action->execute($id, $validated['delivery_driver_id']);

        return response()->json([
            'success' => true,
            'message' => 'Driver assigned to delivery shipment.',
            'data' => $shipment,
        ]);
    }

    public function dispatch(string $id, DispatchShipmentAction $action): JsonResponse
    {
        $shipment = $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Shipment dispatched for delivery.',
            'data' => $shipment,
        ]);
    }

    public function complete(Request $request, string $id, CompleteDeliveryAction $action): JsonResponse
    {
        $validated = $request->validate([
            'received_by_name' => ['required', 'string', 'max:150'],
            'signature_url' => ['nullable', 'string', 'max:500'],
            'photo_url' => ['nullable', 'string', 'max:500'],
            'cod_collected' => ['nullable', 'numeric', 'min:0'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $shipment = $action->execute(
            shipmentId: $id,
            receivedByName: $validated['received_by_name'],
            signatureUrl: $validated['signature_url'] ?? null,
            photoUrl: $validated['photo_url'] ?? null,
            codCollected: (float) ($validated['cod_collected'] ?? 0.0),
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Delivery completed with Proof of Delivery.',
            'data' => $shipment,
        ]);
    }
}
