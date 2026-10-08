<?php

namespace App\Domain\Payments\Models;

use App\Domain\Billing\Models\Invoice;
use App\Domain\POS\Models\PosSession;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'invoice_id',
        'pos_session_id',
        'gateway', // idram, telcell, ameria, cash, stripe, bank_transfer
        'payment_method', // cash, card, qr, transfer
        'transaction_id',
        'amount',
        'currency',
        'status', // pending, successful, failed, refunded
        'payer_details',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payer_details' => 'array',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }
}
