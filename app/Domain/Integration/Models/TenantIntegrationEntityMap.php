<?php

declare(strict_types=1);

namespace App\Domain\Integration\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantIntegrationEntityMap extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'tenant_integration_entity_maps';

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'entity_type',
        'internal_id',
        'external_id',
        'checksum',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(TenantIntegration::class, 'integration_id');
    }
}
