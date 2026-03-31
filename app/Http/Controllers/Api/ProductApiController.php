<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    /**
     * Get product variant details by SKU.
     * Returns variant info with available batches (FEFO order).
     */
    public function show(string $sku): JsonResponse
    {
        $variant = ProductVariant::with(['product', 'unit', 'batches' => function ($query) {
            $query->where('is_active', true)
                ->where('current_stock', '>', 0)
                ->orderBy('expiry_date', 'asc'); // FEFO
        }])->where('sku', $sku)->first();

        if (! $variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Format batches
        $batches = $variant->batches->map(function ($batch) {
            return [
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'purchase_price' => (float) $batch->purchase_price,
                'selling_price' => (float) $batch->selling_price,
                'current_stock' => (int) $batch->current_stock,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => $variant->product->name,
                'generic_name' => $variant->product->generic_name,
                'variant_name' => $variant->variant_name,
                'unit' => $variant->unit?->name,
                'total_stock' => $variant->total_stock,
                'batches' => $batches,
                'suggested_batch' => $batches->first(), // earliest expiry
            ],
        ]);
    }
}
