<?php

namespace App\Domain\Billing\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'gateway', // ameriabank, idram, stripe, bank_transfer, cash
        'transaction_id',
        'amount',
        'currency',
        'status', // pending, successful, failed, refunded
        'gateway_response',
        'payment_method_details',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'payment_method_details' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }
}
