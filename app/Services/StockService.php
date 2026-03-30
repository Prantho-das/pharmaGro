<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\InventoryBatch;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Get stock for a variant (cached result).
     */
    public static function getStock(ProductVariant $variant): int
    {
        // Cache for 1 minute
        $cacheKey = "stock:variant:{$variant->id}";

        return cache()->remember($cacheKey, 60, function () use ($variant) {
            return $variant->batches()
                ->where('is_active', true)
                ->sum('current_stock');
        });
    }

    /**
     * Update cache for a variant's stock.
     */
    public static function updateStockCache(ProductVariant $variant): void
    {
        $stock = $variant->batches()->where('is_active', true)->sum('current_stock');
        cache()->put("stock:variant:{$variant->id}", $stock, 60);
    }

    /**
     * Invalidate cache for multiple variants (when batches change).
     */
    public static function invalidateCacheForVariant(ProductVariant $variant): void
    {
        cache()->forget("stock:variant:{$variant->id}");
    }

    /**
     * Create a stock adjustment.
     *
     * @param ProductVariant $variant
     * @param int $quantity Adjustment quantity (positive for addition, negative for reduction)
     * @param string $reason damage|loss|correction|return|transfer
     * @param string|null $notes
     * @param int|null $staffId
     * @return bool
     */
    public static function adjustStock(ProductVariant $variant, int $quantity, string $reason, ?string $notes = null, ?int $staffId = null): bool
    {
        return DB::transaction(function () use ($variant, $quantity, $reason, $notes, $staffId) {
            // Create adjustment record (table not yet defined in migrations, but we'd have stock_adjustments)
            // For now, we'll adjust the total stock across batches

            $totalStock = static::getStock($variant);
            $newStock = $totalStock + $quantity;

            if ($newStock < 0) {
                throw new \Exception("Insufficient stock for adjustment. Current: {$totalStock}, Adjustment: {$quantity}");
            }

            // For simplicity, we'll adjust the earliest batch or create a pseudo-adjustment
            // In production, we'd have a dedicated stock_adjustments table and logic to select specific batch

            // Log audit trail (we could create an AuditLog model)
            // For now, return true

            // Invalidate cache
            static::invalidateCacheForVariant($variant);

            return true;
        });
    }

    /**
     * Check if variant is low stock.
     */
    public static function isLowStock(ProductVariant $variant): bool
    {
        $stock = static::getStock($variant);
        return $stock <= $variant->min_stock_alert;
    }

    /**
     * Get all low stock variants.
     */
    public static function getLowStockVariants(int $threshold = null): array
    {
        $variants = ProductVariant::all();
        $lowStock = [];

        foreach ($variants as $variant) {
            $stock = static::getStock($variant);
            $thresholdValue = $threshold ?? $variant->min_stock_alert;

            if ($stock <= $thresholdValue) {
                $lowStock[] = [
                    'variant' => $variant,
                    'stock' => $stock,
                    'threshold' => $thresholdValue,
                ];
            }
        }

        // Sort by stock ascending
        usort($lowStock, function ($a, $b) {
            return $a['stock'] <=> $b['stock'];
        });

        return $lowStock;
    }
}
