<?php

namespace App\Domain\Delivery\Models;

use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryShipment extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'delivery_shipments';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'delivery_driver_id',
        'shipment_number',
        'status', // pending, assigned, picked_up, in_transit, delivered, failed
        'delivery_address',
        'recipient_name',
        'recipient_phone',
        'scheduled_slot_start',
        'scheduled_slot_end',
        'cod_amount',
        'cod_collected',
        'dispatched_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'cod_collected' => 'decimal:2',
        'scheduled_slot_start' => 'datetime',
        'scheduled_slot_end' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = ['tracking_number'];

    public function getTrackingNumberAttribute(): ?string
    {
        return $this->shipment_number;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'delivery_driver_id');
    }

    public function proof(): HasOne
    {
        return $this->hasOne(DeliveryProof::class, 'delivery_shipment_id');
    }
}
