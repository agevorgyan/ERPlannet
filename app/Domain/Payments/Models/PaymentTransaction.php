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
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'order_id',
        'invoice_id',
        'pos_session_id',
        'gateway', // idram, telcell, ameria, cash, stripe, bank_transfer
        'payment_method', // cash, card, qr, transfer
        'transaction_id',
        'amount',
        'refunded_amount',
        'currency',
        'status', // pending, successful, failed, refunded
        'payer_details',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'payer_details' => 'array',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PaymentTransaction $payment) {
            if (empty($payment->gateway)) {
                $payment->gateway = $payment->payment_method ?: 'cash';
            }
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = 'TXN-'.strtoupper(Str::random(10));
            }
        });
    }

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
