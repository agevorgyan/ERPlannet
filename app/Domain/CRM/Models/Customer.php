<?php

namespace App\Domain\CRM\Models;

use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuids, BelongsToTenant, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'company_name',
        'tax_id',
        'email',
        'phone',
        'source',
        'tags',
        'notes',
        'total_spent',
        'orders_count',
        'last_ordered_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'total_spent' => 'decimal:2',
        'orders_count' => 'integer',
        'last_ordered_at' => 'datetime',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id')->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
