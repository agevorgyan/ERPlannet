<?php

namespace App\Domain\Delivery\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryDriverShift extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'delivery_driver_shifts';

    protected $fillable = [
        'tenant_id',
        'delivery_driver_id',
        'shift_start',
        'shift_end',
        'status',
        'vehicle_type',
        'vehicle_license_plate',
        'starting_odometer',
        'ending_odometer',
        'notes',
    ];

    protected $casts = [
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
        'starting_odometer' => 'decimal:2',
        'ending_odometer' => 'decimal:2',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'delivery_driver_id');
    }
}
