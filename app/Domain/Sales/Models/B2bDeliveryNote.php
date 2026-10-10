<?php

namespace App\Domain\Sales\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bDeliveryNote extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'b2b_delivery_notes';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'order_id',
        'document_number',
        'document_date',
        'supplier_name',
        'supplier_tax_id',
        'supplier_address',
        'customer_name',
        'customer_tax_id',
        'customer_address',
        'total_amount',
        'tax_amount',
        'items_snapshot',
        'delivered_by_name',
        'received_by_name',
        'notes',
        'cancellation_reason',
        'reprint_count',
        'status', // issued, cancelled, amended
        'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'items_snapshot' => 'array',
        'reprint_count' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markReprinted(): void
    {
        $this->increment('reprint_count');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getRecipientLegalNameAttribute(): ?string
    {
        return $this->customer_name;
    }

    public function getRecipientTaxIdAttribute(): ?string
    {
        return $this->customer_tax_id;
    }

    public function getDeliveryAddressAttribute(): ?string
    {
        return $this->customer_address;
    }

    public function getDeliveredByAttribute(): ?string
    {
        return $this->delivered_by_name;
    }

    public function getReceivedByAttribute(): ?string
    {
        return $this->received_by_name;
    }

    public function getItemsAttribute(): array
    {
        return $this->items_snapshot ?? [];
    }

    public function getSupplierSnapshotAttribute(): array
    {
        return [
            'name' => $this->supplier_name,
            'tax_id' => $this->supplier_tax_id,
            'address' => $this->supplier_address,
        ];
    }
}
