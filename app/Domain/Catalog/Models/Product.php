<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Manufacturing\Models\Recipe;
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
        'net_quantity',
        'name',
        'description',
        'cost_price',
        'sale_price',
        'special_price',
        'vat_rate',
        'has_vat',
        'discount_percent',
        'allow_discount',
        'allow_price_edit',
        'transaction_type',
        'min_stock_level',
        'currency',
        'shelf_life_days',
        'shelf_life_info',
        'track_stock',
        'is_produced',
        'allow_modifiers',
        'is_ungrouped_in_order',
        'is_excise',
        'is_marked',
        'is_stop_list',
        'is_active',
        'calories',
        'nutritional_info',
        'allergens',
        'dietary_tags',
        'available_branch_ids',
        'time_availability',
        'discount_hours',
        'images',
        'metadata',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'special_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'has_vat' => 'boolean',
        'discount_percent' => 'decimal:2',
        'allow_discount' => 'boolean',
        'allow_price_edit' => 'boolean',
        'min_stock_level' => 'decimal:3',
        'shelf_life_days' => 'integer',
        'track_stock' => 'boolean',
        'is_produced' => 'boolean',
        'allow_modifiers' => 'boolean',
        'is_ungrouped_in_order' => 'boolean',
        'is_excise' => 'boolean',
        'is_marked' => 'boolean',
        'is_stop_list' => 'boolean',
        'is_active' => 'boolean',
        'calories' => 'decimal:2',
        'nutritional_info' => 'array',
        'allergens' => 'array',
        'dietary_tags' => 'array',
        'available_branch_ids' => 'array',
        'time_availability' => 'array',
        'discount_hours' => 'array',
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

    /**
     * Recipes / BOMs defined for producing this product.
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'product_id');
    }

    /**
     * Get active recipe / technical card.
     */
    public function activeRecipe(): ?Recipe
    {
        return $this->recipes()
            ->where('is_active', true)
            ->with(['items.product.unit', 'items.unit', 'yieldUnit'])
            ->first();
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
