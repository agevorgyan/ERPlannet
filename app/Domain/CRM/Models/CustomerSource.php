<?php

namespace App\Domain\CRM\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class CustomerSource extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'customer_sources';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'acquisition_source_id');
    }

    public function getLocalizedName(?string $locale = null): string
    {
        if (is_string($this->name)) {
            return $this->name;
        }

        $locale = $locale ?: app()->getLocale();

        return $this->name[$locale] ?? $this->name['hy'] ?? $this->name['en'] ?? (is_array($this->name) ? (Arr::first($this->name) ?? '') : '');
    }
}
