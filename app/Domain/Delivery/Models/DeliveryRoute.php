<?php

namespace App\Domain\Delivery\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryRoute extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'delivery_routes';

    protected $fillable = [
        'tenant_id',
        'delivery_driver_id',
        'route_number',
        'date',
        'status',
        'total_stops',
        'completed_stops',
    ];

    protected $casts = [
        'date' => 'date',
        'total_stops' => 'integer',
        'completed_stops' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'delivery_driver_id');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(DeliveryRouteStop::class, 'delivery_route_id')->orderBy('stop_sequence');
    }
}
