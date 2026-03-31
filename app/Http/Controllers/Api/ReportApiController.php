<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    /**
     * Get summary report: total sales, net profit.
     * Net profit = sum of (selling_price - purchase_price) * quantity across all completed sales.
     */
    public function summary(): JsonResponse
    {
        // Total sales count and gross amount
        $totalSales = Sale::count();
        $grossRevenue = Sale::sum('grand_total');
        $totalPaid = Sale::sum('paid_amount');
        $totalDue = Sale::sum('due_amount');

        // Net profit calculation: join sale_items with batches to get cost
        $profitQuery = SaleItem::selectRaw('SUM((unit_price - (SELECT purchase_price FROM inventory_batches WHERE id = batch_id)) * quantity) as net_profit')
            ->from('sale_items')
            ->join('inventory_batches', 'sale_items.batch_id', '=', 'inventory_batches.id')
            ->whereNotNull('batch_id');

        $netProfit = $profitQuery->value('net_profit') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_sales' => (int) $totalSales,
                'gross_revenue' => (float) $grossRevenue,
                'total_paid' => (float) $totalPaid,
                'total_due' => (float) $totalDue,
                'net_profit' => (float) $netProfit,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get expiring products within the next X days (default 60, max 365).
     * Query parameter: days (default 60)
     */
    public function expiring(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 60);
        $days = min($days, 365);
        $cutoffDate = Carbon::now()->addDays($days);

        // Find batches expiring within $days that have stock > 0
        $batches = InventoryBatch::with(['variant.product', 'unit'])
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '<=', $cutoffDate)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $items = $batches->map(function ($batch) {
            return [
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_to_expire' => now()->diffInDays($batch->expiry_date),
                'product_name' => $batch->variant->product->name,
                'generic_name' => $batch->variant->product->generic_name,
                'variant_name' => $batch->variant->variant_name,
                'sku' => $batch->variant->sku,
                'current_stock' => (int) $batch->current_stock,
                'selling_price' => (float) $batch->selling_price,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'days' => $days,
                'count' => $items->count(),
                'items' => $items,
            ],
        ]);
    }
}
