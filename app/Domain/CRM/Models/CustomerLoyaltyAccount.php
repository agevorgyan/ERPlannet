<?php

namespace App\Domain\CRM\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLoyaltyAccount extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'customer_loyalty_accounts';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'card_number',
        'barcode',
        'qr_code_token',
        'current_tier',
        'points_balance',
        'lifetime_points_earned',
        'lifetime_points_spent',
        'active_discount_percent',
        'expires_at',
        'is_frozen',
    ];

    protected $casts = [
        'points_balance' => 'decimal:2',
        'lifetime_points_earned' => 'decimal:2',
        'lifetime_points_spent' => 'decimal:2',
        'active_discount_percent' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_frozen' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CustomerLoyaltyTransaction::class, 'loyalty_account_id')->latest();
    }
}
