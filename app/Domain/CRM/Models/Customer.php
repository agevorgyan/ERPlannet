<?php

namespace App\Domain\CRM\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\IAM\Models\User;
use App\Domain\Sales\Models\Order;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    public const TYPE_INDIVIDUAL = 'individual';

    public const TYPE_COMPANY = 'company';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_ARCHIVED = 'archived';

    public const TIER_BASIC = 'basic';

    public const TIER_BRONZE = 'bronze';

    public const TIER_SILVER = 'silver';

    public const TIER_GOLD = 'gold';

    public const TIER_VIP = 'vip';

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'type',
        'status',
        'primary_branch_id',
        'last_order_branch_id',
        'created_by_user_id',
        'assigned_manager_id',
        'acquisition_source_id',
        'last_order_source_id',
        'first_name',
        'last_name',
        'company_name',
        'display_name',
        'tax_id',
        'email',
        'primary_email',
        'phone',
        'primary_phone',
        'source',
        'tags',
        'notes',
        'total_spent',
        'orders_count',
        'canceled_orders_count',
        'returned_orders_count',
        'average_order_value',
        'lifetime_value',
        'first_ordered_at',
        'last_ordered_at',
        'average_order_frequency_days',
        'customer_score',
        'loyalty_tier',
        'custom_discount_percent',
        'marketing_sms_consent',
        'marketing_email_consent',
        'marketing_calls_consent',
        'consent_recorded_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'lifetime_value' => 'decimal:2',
        'average_order_frequency_days' => 'decimal:2',
        'custom_discount_percent' => 'decimal:2',
        'orders_count' => 'integer',
        'canceled_orders_count' => 'integer',
        'returned_orders_count' => 'integer',
        'customer_score' => 'integer',
        'first_ordered_at' => 'datetime',
        'last_ordered_at' => 'datetime',
        'consent_recorded_at' => 'datetime',
        'marketing_sms_consent' => 'boolean',
        'marketing_email_consent' => 'boolean',
        'marketing_calls_consent' => 'boolean',
    ];

    public function individual(): HasOne
    {
        return $this->hasOne(CustomerIndividual::class, 'customer_id');
    }

    public function company(): HasOne
    {
        return $this->hasOne(CustomerCompany::class, 'customer_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class, 'customer_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id')->where('is_default', true);
    }

    public function lastUsedAddress(): HasOne
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id')->where('is_last_used', true);
    }

    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(CustomerLoyaltyAccount::class, 'customer_id');
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(CustomerLoyaltyTransaction::class, 'customer_id')->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class, 'customer_id')->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class, 'customer_id')->latest();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function primaryBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    public function acquisitionSource(): BelongsTo
    {
        return $this->belongsTo(CustomerSource::class, 'acquisition_source_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    public function isCompany(): bool
    {
        return $this->type === self::TYPE_COMPANY;
    }

    public function isIndividual(): bool
    {
        return $this->type === self::TYPE_INDIVIDUAL || empty($this->type);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->isCompany() && ! empty($this->company_name)) {
            return $this->company_name;
        }

        $name = trim("{$this->first_name} {$this->last_name}");

        return $name ?: ($this->company_name ?: 'Հաճախորդ');
    }

    public function getDisplayNameAttribute(): string
    {
        if (! empty($this->attributes['display_name'])) {
            return $this->attributes['display_name'];
        }

        return $this->full_name;
    }

    public function getEffectiveDiscountPercent(): float
    {
        if ((float) $this->custom_discount_percent > 0) {
            return (float) $this->custom_discount_percent;
        }

        if ($this->loyaltyAccount && (float) $this->loyaltyAccount->active_discount_percent > 0) {
            return (float) $this->loyaltyAccount->active_discount_percent;
        }

        $tierDiscounts = [
            self::TIER_BASIC => 0.0,
            self::TIER_BRONZE => 3.0,
            self::TIER_SILVER => 5.0,
            self::TIER_GOLD => 8.0,
            self::TIER_VIP => 12.0,
        ];

        return $tierDiscounts[$this->loyalty_tier] ?? 0.0;
    }
}
