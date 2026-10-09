<?php

namespace App\Domain\Delivery\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryEvent extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'delivery_events';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'delivery_shipment_id',
        'delivery_driver_id',
        'event_type',
        'latitude',
        'longitude',
        'speed',
        'battery_level',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'decimal:2',
        'battery_level' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(DeliveryShipment::class, 'delivery_shipment_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'delivery_driver_id');
    }
}
