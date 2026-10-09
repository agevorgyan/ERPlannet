<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Sales\Models\OrderItem;
use App\Infrastructure\MultiTenancy\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'product_variants';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku',
        'barcode',
        'name',
        'cost_price',
        'sale_price',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    public function getEffectivePrice(): float
    {
        return (float) ($this->sale_price ?? $this->product->sale_price);
    }
}
