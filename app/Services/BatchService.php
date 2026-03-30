<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\InventoryBatch;
use Illuminate\Support\Facades\DB;

class BatchService
{
    /**
     * Get the FEFO (First Expired First Out) ordered batches for a variant.
     *
     * @param ProductVariant $variant
     * @param int $quantity Required quantity
     * @return \Illuminate\Database\Eloquent\Collection<InventoryBatch>
     */
    public static function getFefoBatches(ProductVariant $variant, int $quantity): array
    {
        return $variant->batches()
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('expiry_date', 'asc') // FEFO: Earliest expiry first
            ->get();
    }

    /**
     * Deduct stock from batches using FEFO logic.
     *
     * @param ProductVariant $variant
     * @param int $quantity Quantity to deduct
     * @return array Array of batch allocations ['batch_id' => quantity]
     * @throws \Exception if insufficient stock
     */
    public static function deductStock(ProductVariant $variant, int $quantity): array
    {
        if ($variant->total_stock < $quantity) {
            throw new \Exception("Insufficient stock for {$variant->variant_name}. Available: {$variant->total_stock}, Requested: {$quantity}");
        }

        $batches = static::getFefoBatches($variant, $quantity);
        $allocations = [];
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deductFromThisBatch = min($batch->current_stock, $remaining);

            $allocations[$batch->id] = $deductFromThisBatch;
            $remaining -= $deductFromThisBatch;
        }

        return $allocations;
    }

    /**
     * Deduct stock within a transaction.
     */
    public static function deductStockTransaction(ProductVariant $variant, int $quantity): array
    {
        return DB::transaction(function () use ($variant, $quantity) {
            $allocations = static::deductStock($variant, $quantity);

            foreach ($allocations as $batchId => $deductQty) {
                $batch = InventoryBatch::where('id', $batchId)->lockForUpdate()->first();
                $batch->decrement('current_stock', $deductQty);
            }

            return $allocations;
        });
    }

    /**
     * Get batches that will expire within specified days.
     */
    public static function getExpiringBatches(int $days = 90)
    {
        return InventoryBatch::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date', 'asc')
            ->with(['variant.product', 'variant.unit'])
            ->get();
    }

    /**
     * Get expired batches (for reporting).
     */
    public static function getExpiredBatches()
    {
        return InventoryBatch::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->whereDate('expiry_date', '<', now())
            ->orderBy('expiry_date', 'asc')
            ->with(['variant.product', 'variant.unit'])
            ->get();
    }

    /**
     * Calculate potential loss from expiring batches.
     */
    public static function calculateExpiryLoss(int $days = 90): float
    {
        $batches = static::getExpiringBatches($days);

        $totalLoss = 0.0;
        foreach ($batches as $batch) {
            // Loss = (selling_price - purchase_price) * current_stock
            // If we can't sell at profit, it's potential lost revenue
            $loss = ($batch->selling_price - $batch->purchase_price) * $batch->current_stock;
            $totalLoss += $loss;
        }

        return $totalLoss;
    }
}
