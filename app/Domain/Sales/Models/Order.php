<?php

namespace App\Domain\Sales\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\CRM\Models\Customer;
use App\Domain\CRM\Models\CustomerAddress;
use App\Domain\Delivery\Models\DeliveryShipment;
use App\Domain\Fiscal\Models\FiscalReceipt;
use App\Domain\IAM\Models\User;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\POS\Models\PosSession;
use App\Domain\POS\Models\PosTerminal;
use App\Domain\Printing\Models\PrintJob;
use App\Domain\Warehouse\Models\Warehouse;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'customer_id',
        'customer_address_id',
        'order_number',
        'status', // draft, new, confirmed, processing, ready, completed, cancelled
        'source', // pos, storefront, xml_import, manual, external, direct, phone, web, woocommerce
        'order_type', // standard, preliminary, b2b, catering
        'external_reference',
        'delivery_type', // delivery, pickup, dine_in
        'preparation_status', // pending, preparing, ready, fulfilled
        'delivery_status', // unassigned, assigned, in_transit, delivered, failed
        'fiscal_status', // not_fiscalized, fiscalized, failed, refunded
        'currency',
        'subtotal',
        'item_discounts_total',
        'order_discount',
        'discount',
        'promo_code',
        'promo_discount',
        'delivery_fee',
        'tax',
        'total',
        'paid_amount',
        'balance_due',
        'pos_terminal_id',
        'pos_session_id',
        'responsible_employee_id',
        'receipt_number',
        'idempotency_key',
        'payment_status', // unpaid, partially_paid, paid, refunded, partially_refunded
        'customer_notes',
        'internal_notes',
        'cancellation_reason',
        'delivery_address_snapshot',
        'customer_snapshot',
        'metadata',
        'placed_at',
        'confirmed_at',
        'scheduled_for',
        'delivered_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'item_discounts_total' => 'decimal:2',
        'order_discount' => 'decimal:2',
        'discount' => 'decimal:2',
        'promo_discount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'delivery_address_snapshot' => 'array',
        'customer_snapshot' => 'array',
        'metadata' => 'array',
        'placed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function responsibleEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_employee_id');
    }

    public function posTerminal(): BelongsTo
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function fiscalReceipt(): HasOne
    {
        return $this->hasOne(FiscalReceipt::class, 'order_id');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(DeliveryShipment::class, 'order_id');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id');
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
        return $this->hasMany(PaymentTransaction::class, 'order_id');
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(B2bDeliveryNote::class, 'order_id');
    }

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class, 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPreliminary(): bool
    {
        return $this->order_type === 'preliminary' || ($this->scheduled_for && $this->scheduled_for->isFuture());
    }

    public function canBeRescheduled(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled'], true);
    }

    /**
     * Recalculates payments, balances, and payment_status based on transactions.
     */
    public function recalculateBalances(): void
    {
        $paid = (float) $this->paymentTransactions()
            ->whereIn('status', ['successful', 'completed', 'refunded'])
            ->sum('amount');

        $refunded = (float) $this->paymentTransactions()
            ->whereIn('status', ['successful', 'completed', 'refunded'])
            ->sum('refunded_amount');

        $netPaid = max(0.00, $paid - $refunded);
        $total = (float) $this->total;
        $balance = max(0.00, round($total - $netPaid, 2));

        $status = 'unpaid';
        if ($netPaid >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($netPaid > 0) {
            $status = 'partial';
        } elseif ($refunded > 0 && $netPaid == 0) {
            $status = 'refunded';
        }

        $this->update([
            'paid_amount' => $netPaid,
            'balance_due' => $balance,
            'payment_status' => $status,
        ]);
    }

    /**
     * Take an immutable snapshot of customer details to preserve historical context.
     */
    public function snapshotCustomer(?Customer $customer = null): void
    {
        $cust = $customer ?? $this->customer;
        if (! $cust) {
            return;
        }

        $address = $this->address ?? $cust->defaultAddress;

        $snapshot = [
            'id' => $cust->id,
            'code' => $cust->customer_code ?? $cust->code,
            'name' => $cust->name ?? $cust->display_name ?? $cust->full_name,
            'type' => $cust->type, // individual, company
            'phone' => $cust->phone,
            'email' => $cust->email,
            'tax_id' => $cust->tax_id ?? $cust->company?->tax_id,
            'company_name' => $cust->company?->company_name,
            'address' => $address ? [
                'city' => $address->city,
                'street' => $address->street,
                'building' => $address->building,
                'apartment' => $address->apartment,
                'formatted' => $address->formatted_address,
            ] : null,
            'snapshot_at' => now()->toIso8601String(),
        ];

        $this->update(['customer_snapshot' => $snapshot]);
    }
}
