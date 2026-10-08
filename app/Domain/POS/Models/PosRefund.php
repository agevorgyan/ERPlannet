<?php

namespace App\Domain\POS\Models;

use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosRefund extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'pos_refunds';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'pos_session_id',
        'cashier_id',
        'refund_number',
        'amount',
        'refund_method',
        'reason',
        'status',
        'items_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'items_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
