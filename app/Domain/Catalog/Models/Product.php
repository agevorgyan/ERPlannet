<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Manufacturing\Models\RecipeItem;
use App\Domain\Procurement\Models\Supplier;
use App\Domain\Procurement\Models\SupplierProduct;
use App\Domain\Sales\Models\OrderItem;
use App\Domain\Warehouse\Models\StockLevel;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'subcategory_id',
        'unit_id',
        'type',
        'sku',
        'barcode',
        'hs_code',
        'packaging',
        'name',
        'description',
        'cost_price',
        'sale_price',
        'vat_rate',
        'discount_percent',
        'transaction_type',
        'min_stock_level',
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
        'vat_rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'min_stock_level' => 'decimal:3',
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

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_products', 'product_id', 'supplier_id')
            ->using(SupplierProduct::class)
            ->withPivot(['supply_price', 'lead_time_days'])
            ->withTimestamps();
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class, 'product_id');
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
        return $this->hasMany(RecipeItem::class, 'product_id')->with('recipe.product');
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
