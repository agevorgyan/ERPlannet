<?php

namespace App\Domain\Sales\Models;

use App\Domain\Billing\Models\Payment;
use App\Domain\Branch\Models\Branch;
use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerAddress;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuids, BelongsToTenant, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'customer_id',
        'customer_address_id',
        'order_number',
        'status', // new, confirmed, processing, packed, delivery, delivered, cancelled
        'source', // direct, phone, web, pos, woocommerce
        'delivery_type', // delivery, pickup, dine_in
        'currency',
        'subtotal',
        'discount',
        'delivery_fee',
        'tax',
        'total',
        'pos_terminal_id',
        'pos_session_id',
        'receipt_number',
        'idempotency_key',
        'payment_status', // unpaid, partially_paid, paid, refunded, partially_refunded
        'customer_notes',
        'internal_notes',
        'placed_at',
        'scheduled_for',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'placed_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function posTerminal(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\POS\Models\PosTerminal::class, 'pos_terminal_id');
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\POS\Models\PosSession::class, 'pos_session_id');
    }

    public function fiscalReceipt(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domain\Fiscal\Models\FiscalReceipt::class, 'order_id');
    }

    public function shipment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domain\Delivery\Models\DeliveryShipment::class, 'order_id');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(\App\Domain\Payments\Models\PaymentTransaction::class, 'order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')->orderBy('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
