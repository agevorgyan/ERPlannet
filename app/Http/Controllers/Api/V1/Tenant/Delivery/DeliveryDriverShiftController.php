<?php

namespace App\Http\Controllers\Api\V1\Tenant\Delivery;

use App\Domain\Delivery\Actions\StartDriverShiftAction;
use App\Domain\Delivery\Models\DeliveryDriverShift;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryDriverShiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shifts = DeliveryDriverShift::with('driver')->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $shifts->items(),
            'meta' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    public function start(Request $request, string $driverId, StartDriverShiftAction $action): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_type' => ['nullable', 'string', 'in:car,motorcycle,van,bicycle'],
            'license_plate' => ['nullable', 'string', 'max:50'],
            'starting_odometer' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $shift = $action->execute(
            driverId: $driverId,
            vehicleType: $validated['vehicle_type'] ?? 'car',
            licensePlate: $validated['license_plate'] ?? null,
            startingOdometer: isset($validated['starting_odometer']) ? (float) $validated['starting_odometer'] : null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Driver shift started.',
            'data' => $shift->load('driver'),
        ], 201);
    }

    public function end(Request $request, string $shiftId, StartDriverShiftAction $action): JsonResponse
    {
        $validated = $request->validate([
            'ending_odometer' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $shift = $action->endShift(
            shiftId: $shiftId,
            endingOdometer: isset($validated['ending_odometer']) ? (float) $validated['ending_odometer'] : null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Driver shift completed.',
            'data' => $shift->load('driver'),
        ]);
    }
}
