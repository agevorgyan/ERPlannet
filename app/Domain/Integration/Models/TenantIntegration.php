<?php

declare(strict_types=1);

namespace App\Domain\Integration\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class TenantIntegration extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'tenant_integrations';

    protected $fillable = [
        'tenant_id',
        'provider',
        'name',
        'status',
        'credentials_encrypted',
        'settings',
        'last_sync_at',
        'last_error',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Get decrypted credentials array.
     *
     * @return array<string, mixed>
     */
    public function getCredentials(): array
    {
        if (empty($this->credentials_encrypted)) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($this->credentials_encrypted);
            return json_decode($decrypted, true) ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Set and encrypt credentials.
     *
     * @param array<string, mixed> $credentials
     */
    public function setCredentials(array $credentials): self
    {
        $this->credentials_encrypted = Crypt::encryptString(json_encode($credentials, JSON_THROW_ON_ERROR));
        return $this;
    }

    public function entityMaps(): HasMany
    {
        return $this->hasMany(TenantIntegrationEntityMap::class, 'integration_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(TenantIntegrationSyncLog::class, 'integration_id');
    }
}
