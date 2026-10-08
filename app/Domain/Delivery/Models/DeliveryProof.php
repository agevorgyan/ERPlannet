<?php

namespace App\Domain\Delivery\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProof extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'delivery_proofs';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'delivery_shipment_id',
        'received_by_name',
        'signature_url',
        'photo_url',
        'latitude',
        'longitude',
        'notes',
        'delivered_at',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(DeliveryShipment::class, 'delivery_shipment_id');
    }
}
