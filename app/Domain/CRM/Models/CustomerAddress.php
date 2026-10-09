<?php

namespace App\Domain\CRM\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'title',
        'city',
        'address_line_1',
        'address_line_2',
        'floor',
        'apartment',
        'entry_code',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = [$this->city, $this->address_line_1];
        if ($this->address_line_2) {
            $parts[] = $this->address_line_2;
        }
        if ($this->floor) {
            $parts[] = "Floor {$this->floor}";
        }
        if ($this->apartment) {
            $parts[] = "Apt {$this->apartment}";
        }

        return implode(', ', $parts);
    }
}
