<?php

namespace App\Domain\Delivery\Models;

use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodSettlement extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'cod_settlements';

    protected $fillable = [
        'tenant_id',
        'delivery_shipment_id',
        'order_id',
        'delivery_driver_id',
        'cashier_id',
        'status', // expected, collected, courier_holding, submitted, verified, settled
        'expected_amount',
        'collected_amount',
        'submitted_amount',
        'verified_amount',
        'discrepancy_amount',
        'discrepancy_reason',
        'settled_at',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'submitted_amount' => 'decimal:2',
        'verified_amount' => 'decimal:2',
        'discrepancy_amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(DeliveryShipment::class, 'delivery_shipment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'delivery_driver_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
