<?php

declare(strict_types=1);

namespace App\Domain\Integration\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantWebhookDelivery extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'tenant_webhook_deliveries';

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'event_name',
        'payload',
        'status',
        'attempts',
        'response_status_code',
        'response_body',
        'last_attempt_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'response_status_code' => 'integer',
        'last_attempt_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantWebhookSubscription::class, 'subscription_id');
    }
}
