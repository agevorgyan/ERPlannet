<?php

namespace App\Infrastructure\MultiTenancy\Traits;

use App\Domain\Tenant\Models\Tenant;
use App\Infrastructure\MultiTenancy\Scopes\TenantScope;
use App\Infrastructure\MultiTenancy\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $context = app(TenantContext::class);
            if (empty($model->tenant_id) && $context->hasTenant()) {
                $model->tenant_id = $context->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
