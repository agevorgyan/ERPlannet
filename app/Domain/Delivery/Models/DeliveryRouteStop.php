<?php

namespace App\Domain\Delivery\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRouteStop extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'delivery_route_stops';

    protected $fillable = [
        'tenant_id',
        'delivery_route_id',
        'delivery_shipment_id',
        'stop_sequence',
        'status',
        'estimated_arrival_at',
        'actual_arrival_at',
    ];

    protected $casts = [
        'stop_sequence' => 'integer',
        'estimated_arrival_at' => 'datetime',
        'actual_arrival_at' => 'datetime',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(DeliveryShipment::class, 'delivery_shipment_id');
    }
}
