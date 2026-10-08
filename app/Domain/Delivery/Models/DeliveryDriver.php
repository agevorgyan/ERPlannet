<?php

namespace App\Domain\Delivery\Models;

use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryDriver extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'delivery_drivers';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'vehicle_type',
        'license_plate',
        'status', // available, on_delivery, offline
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_driver_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
