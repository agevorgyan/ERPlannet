<?php

namespace App\Domain\Catalog\Models;

use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'categories';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'slug',
        'name',
        'description',
        'sort_order',
        'is_active',
        'image_url',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->name[$locale] ?? $this->name['hy'] ?? $this->name['en'] ?? '';
    }
}
