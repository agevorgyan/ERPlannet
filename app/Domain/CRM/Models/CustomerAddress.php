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
        'country',
        'province',
        'city',
        'postal_code',
        'street',
        'building',
        'entrance',
        'floor',
        'apartment',
        'door_code',
        'address_line_1',
        'address_line_2',
        'entry_code',
        'latitude',
        'longitude',
        'delivery_instructions',
        'is_default',
        'is_last_used',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_default' => 'boolean',
        'is_last_used' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = [];

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->street) {
            $streetPart = $this->street;
            if ($this->building) {
                $streetPart .= " {$this->building}";
            }
            $parts[] = $streetPart;
        } elseif ($this->address_line_1) {
            $parts[] = $this->address_line_1;
        }

        if ($this->address_line_2) {
            $parts[] = $this->address_line_2;
        }

        if ($this->entrance) {
            $parts[] = "մուտք {$this->entrance}";
        }

        if ($this->floor) {
            $parts[] = "հարկ {$this->floor}";
        }

        if ($this->apartment) {
            $parts[] = "բն. {$this->apartment}";
        }

        return implode(', ', $parts) ?: ($this->address_line_1 ?: 'Հասցե');
    }

    public function toSnapshot(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'country' => $this->country,
            'province' => $this->province,
            'city' => $this->city,
            'street' => $this->street,
            'building' => $this->building,
            'entrance' => $this->entrance,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'door_code' => $this->door_code,
            'delivery_instructions' => $this->delivery_instructions,
            'formatted_address' => $this->formatted_address,
            'snapshot_at' => now()->toIso8601String(),
        ];
    }
}
