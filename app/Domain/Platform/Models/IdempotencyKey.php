<?php

namespace App\Domain\Platform\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'idempotency_keys';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'idempotency_key',
        'resource_type',
        'resource_id',
        'response_payload',
        'status_code',
        'created_at',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'created_at' => 'datetime',
    ];
}
