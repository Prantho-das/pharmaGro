<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBatch extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryBatchFactory> */
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'batch_no',
        'expiry_date',
        'purchase_price',
        'selling_price',
        'current_stock',
        'is_active'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'current_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the variant that owns the batch.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Check if batch is expiring within given days (default 90).
     */
    public function isExpiringSoon(int $days = 90): bool
    {
        return $this->expiry_date->lte(now()->addDays($days));
    }

    /**
     * Check if batch is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }
}
