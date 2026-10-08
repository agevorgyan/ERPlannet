<?php

namespace App\Domain\Delivery\Actions;

use App\Domain\Audit\Models\AuditLog;
use App\Domain\Delivery\Models\DeliveryDriver;
use App\Domain\Delivery\Models\DeliveryDriverShift;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StartDriverShiftAction
{
    public function execute(
        string $driverId,
        string $vehicleType = 'car',
        ?string $licensePlate = null,
        ?float $startingOdometer = null,
        ?string $notes = null
    ): DeliveryDriverShift {
        return DB::transaction(function () use ($driverId, $vehicleType, $licensePlate, $startingOdometer, $notes) {
            $driver = DeliveryDriver::lockForUpdate()->findOrFail($driverId);

            $activeShift = DeliveryDriverShift::where('delivery_driver_id', $driverId)
                ->where('status', 'active')
                ->first();

            if ($activeShift) {
                throw new InvalidArgumentException("Driver already has an active shift.");
            }

            $shift = DeliveryDriverShift::create([
                'tenant_id' => $driver->tenant_id,
                'delivery_driver_id' => $driver->id,
                'shift_start' => now(),
                'status' => 'active',
                'vehicle_type' => $vehicleType,
                'vehicle_license_plate' => $licensePlate ?: $driver->license_plate,
                'starting_odometer' => $startingOdometer,
                'notes' => $notes,
            ]);

            $driver->status = 'available';
            $driver->save();

            AuditLog::create([
                'tenant_id' => $driver->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'delivery.driver_shift_started',
                'entity_type' => DeliveryDriverShift::class,
                'entity_id' => $shift->id,
                'new_values' => ['driver_id' => $driver->id, 'vehicle_type' => $vehicleType],
                'created_at' => now(),
            ]);

            return $shift;
        });
    }

    public function endShift(
        string $shiftId,
        ?float $endingOdometer = null,
        ?string $notes = null
    ): DeliveryDriverShift {
        return DB::transaction(function () use ($shiftId, $endingOdometer, $notes) {
            $shift = DeliveryDriverShift::lockForUpdate()->findOrFail($shiftId);

            if ($shift->status !== 'active') {
                throw new InvalidArgumentException("Shift is already completed.");
            }

            $shift->status = 'completed';
            $shift->shift_end = now();
            $shift->ending_odometer = $endingOdometer;
            if ($notes) {
                $shift->notes = ($shift->notes ? $shift->notes . "\n" : '') . $notes;
            }
            $shift->save();

            $driver = $shift->driver;
            if ($driver) {
                $driver->status = 'offline';
                $driver->save();
            }

            AuditLog::create([
                'tenant_id' => $shift->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'delivery.driver_shift_ended',
                'entity_type' => DeliveryDriverShift::class,
                'entity_id' => $shift->id,
                'new_values' => ['shift_id' => $shift->id, 'ending_odometer' => $endingOdometer],
                'created_at' => now(),
            ]);

            return $shift;
        });
    }
}
