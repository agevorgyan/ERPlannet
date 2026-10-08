<?php

namespace App\Domain\Billing\Models;

use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status', // trialing, active, past_due, canceled, grace_period
        'billing_cycle', // monthly, yearly
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'canceled_at',
        'grace_period_ends_at',
        'auto_renew',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'auto_renew' => 'boolean',
        'metadata' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class, 'subscription_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']) &&
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isGracePeriod(): bool
    {
        return $this->status === 'grace_period' &&
            $this->grace_period_ends_at !== null &&
            $this->grace_period_ends_at->isFuture();
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }
}
