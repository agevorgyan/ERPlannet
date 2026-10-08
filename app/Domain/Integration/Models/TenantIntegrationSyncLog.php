<?php

declare(strict_types=1);

namespace App\Domain\Integration\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantIntegrationSyncLog extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    public $timestamps = false; // Only uses created_at

    protected $table = 'tenant_integration_sync_logs';

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'entity_type',
        'direction',
        'status',
        'records_processed',
        'records_failed',
        'details',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'records_processed' => 'integer',
        'records_failed' => 'integer',
        'duration_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(TenantIntegration::class, 'integration_id');
    }
}
