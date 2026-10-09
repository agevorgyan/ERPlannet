<?php

namespace App\Domain\Catalog\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'units';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'precision',
    ];

    protected $casts = [
        'name' => 'array',
        'precision' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->name[$locale] ?? $this->name['hy'] ?? $this->code;
    }
}
