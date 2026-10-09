<?php

declare(strict_types=1);

namespace App\Domain\Integration\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class TenantWebhookSubscription extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'tenant_webhook_subscriptions';

    protected $fillable = [
        'tenant_id',
        'name',
        'target_url',
        'secret_encrypted',
        'events',
        'is_active',
        'headers',
        'retry_count_max',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
        'retry_count_max' => 'integer',
    ];

    public function getSecret(): string
    {
        if (empty($this->secret_encrypted)) {
            return '';
        }

        try {
            return Crypt::decryptString($this->secret_encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public function setSecret(string $secret): self
    {
        $this->secret_encrypted = Crypt::encryptString($secret);

        return $this;
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(TenantWebhookDelivery::class, 'subscription_id');
    }
}
