<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_id',
        'variant_name',
        'sku',
        'min_stock_alert'
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit for this variant.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get all inventory batches for this variant.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class, 'variant_id');
    }

    /**
     * Get total current stock across all active batches.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->batches()
            ->where('is_active', true)
            ->sum('current_stock');
    }
}
