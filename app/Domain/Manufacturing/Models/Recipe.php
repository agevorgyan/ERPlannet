<?php

namespace App\Domain\Manufacturing\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Catalog\Models\Unit;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'recipes';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'code',
        'name',
        'version',
        'yield_quantity',
        'yield_unit_id',
        'scrap_percentage',
        'labor_cost',
        'overhead_cost',
        'instructions',
        'is_active',
    ];

    protected $casts = [
        'yield_quantity' => 'float',
        'scrap_percentage' => 'float',
        'labor_cost' => 'float',
        'overhead_cost' => 'float',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function yieldUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class, 'recipe_id')->orderBy('sort_order', 'asc');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'recipe_id');
    }

    /**
     * Calculate cost breakdown of recipe / BOM based on current component costs.
     *
     * @return array{raw_material_cost: float, scrap_cost: float, labor_cost: float, overhead_cost: float, total_cost: float, unit_cost: float, yield_quantity: float, items: array}
     */
    public function calculateCostBreakdown(): array
    {
        $rawMaterialCost = 0.0;
        $itemsBreakdown = [];

        foreach ($this->items as $item) {
            $component = $item->product;
            $unitCost = (float) ($item->cost_per_unit ?: ($component?->cost_price ?? 0));
            $qty = (float) $item->quantity;
            $wastePct = (float) ($item->waste_percentage ?? 0);
            $grossQty = $item->gross_quantity ? (float) $item->gross_quantity : ($qty * (1 + ($wastePct / 100)));
            $itemTotal = round($grossQty * $unitCost, 2);

            $rawMaterialCost += $itemTotal;
            $itemsBreakdown[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $component?->getLocalizedName() ?? 'Component',
                'sku' => $component?->sku,
                'type' => $component?->type,
                'net_quantity' => $qty,
                'gross_quantity' => round($grossQty, 4),
                'waste_percentage' => $wastePct,
                'unit_cost' => $unitCost,
                'total_cost' => $itemTotal,
                'unit_name' => $item->unit?->name['hy'] ?? $item->unit?->code ?? '',
            ];
        }

        $scrapPct = (float) ($this->scrap_percentage ?? 0);
        $scrapCost = round($rawMaterialCost * ($scrapPct / 100), 2);
        $labor = (float) ($this->labor_cost ?? 0);
        $overhead = (float) ($this->overhead_cost ?? 0);
        $totalCost = $rawMaterialCost + $scrapCost + $labor + $overhead;
        $yieldQty = max(0.0001, (float) ($this->yield_quantity ?: 1));
        $unitCost = round($totalCost / $yieldQty, 2);

        return [
            'raw_material_cost' => round($rawMaterialCost, 2),
            'scrap_cost' => $scrapCost,
            'labor_cost' => $labor,
            'overhead_cost' => $overhead,
            'total_cost' => round($totalCost, 2),
            'unit_cost' => $unitCost,
            'yield_quantity' => $yieldQty,
            'items' => $itemsBreakdown,
        ];
    }
}
