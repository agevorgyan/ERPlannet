<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Manufacturing\Models\RecipeItem;
use App\Domain\Sales\Models\OrderItem;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    public const TYPE_RAW_MATERIAL = 'raw_material';

    public const TYPE_INGREDIENT = 'ingredient';

    public const TYPE_SEMI_FINISHED = 'semi_finished';

    public const TYPE_FINISHED_PRODUCT = 'finished_product';

    public const TYPE_PACKAGING = 'packaging';

    public const TYPE_SERVICE = 'service';

    public const TYPE_MODIFIER = 'modifier';

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'unit_id',
        'type',
        'sku',
        'barcode',
        'name',
        'description',
        'cost_price',
        'sale_price',
        'currency',
        'shelf_life_days',
        'track_stock',
        'is_produced',
        'is_active',
        'images',
        'metadata',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'shelf_life_days' => 'integer',
        'track_stock' => 'boolean',
        'is_produced' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'metadata' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->orderBy('created_at');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * Reverse lookup: find all recipes / BOMs where this item is used as an ingredient or component.
     */
    public function recipesWhereUsed(): HasMany
    {
        return $this->hasMany(RecipeItem::class, 'product_id');
    }

    public function isRawMaterial(): bool
    {
        return $this->type === self::TYPE_RAW_MATERIAL;
    }

    public function isIngredient(): bool
    {
        return in_array($this->type, [self::TYPE_INGREDIENT, self::TYPE_RAW_MATERIAL, self::TYPE_SEMI_FINISHED]);
    }

    public function isSemiFinished(): bool
    {
        return $this->type === self::TYPE_SEMI_FINISHED;
    }

    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->name[$locale] ?? $this->name['hy'] ?? $this->name['en'] ?? '';
    }
}
