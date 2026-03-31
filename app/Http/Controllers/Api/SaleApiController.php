<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryBatch;
use App\Models\PointsTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SaleApiController extends Controller
{
    /**
     * Create a new sale from mobile API.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.batch_id' => 'required|integer|exists:inventory_batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'payment_method' => 'required|string|in:cash,bkash,nagad,card',
            'paid_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        $customerId = $validated['customer_id'] ?? null;
        $paymentMethod = $validated['payment_method'];
        $paidAmount = (float) $validated['paid_amount'];
        $discountAmount = (float) ($validated['discount_amount'] ?? 0);

        // Calculate totals
        $subTotal = 0;

        // Validate batches and compute subtotal
        foreach ($items as $item) {
            $batch = InventoryBatch::find($item['batch_id']);
            if (! $batch) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid batch ID: {$item['batch_id']}",
                ], 422);
            }

            if ($batch->current_stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for batch {$batch->batch_no}",
                ], 422);
            }

            $subTotal += $batch->selling_price * $item['quantity'];
        }

        // Get tax rate from settings (as percentage)
        $taxRate = (float) Setting::get('tax_rate', 0);
        $taxAmount = $subTotal * ($taxRate / 100);

        $grandTotal = $subTotal + $taxAmount - $discountAmount;
        $dueAmount = max(0, $grandTotal - $paidAmount);

        // Generate invoice number
        $invoiceNo = Sale::generateInvoiceNumber();

        try {
            DB::transaction(function () use ($invoiceNo, $customerId, $paymentMethod, $paidAmount, $discountAmount, $taxAmount, $grandTotal, $dueAmount, $items) {
                $sale = Sale::create([
                    'invoice_no' => $invoiceNo,
                    'customer_id' => $customerId,
                    'sub_total' => $subTotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paidAmount,
                    'due_amount' => $dueAmount,
                    'payment_method' => $paymentMethod,
                    'sold_by_id' => auth()->id(),
                ]);

                foreach ($items as $item) {
                    $batch = InventoryBatch::find($item['batch_id']);
                    $quantity = $item['quantity'];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'variant_id' => $item['variant_id'],
                        'batch_id' => $item['batch_id'],
                        'quantity' => $quantity,
                        'unit_price' => $batch->selling_price,
                        'total_price' => $batch->selling_price * $quantity,
                    ]);

                    $batch->decrement('current_stock', $quantity);
                }

                // Award loyalty points if customer and grandTotal > 0
                if ($customerId && $grandTotal > 0) {
                    $pointsRate = (float) Setting::get('points_rate', 0.01);
                    $pointsEarned = (int) floor($grandTotal * $pointsRate);

                    if ($pointsEarned > 0) {
                        $customer = Customer::find($customerId);
                        if ($customer) {
                            $newBalance = $customer->points + $pointsEarned;
                            $customer->increment('points', $pointsEarned);

                            PointsTransaction::create([
                                'customer_id' => $customer->id,
                                'sale_id' => $sale->id,
                                'points' => $pointsEarned,
                                'type' => 'earned',
                                'balance_after' => $newBalance,
                                'notes' => "Earned from sale {$invoiceNo}",
                            ]);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_no' => $invoiceNo,
                'grand_total' => $grandTotal,
                'due_amount' => $dueAmount,
            ],
        ], 201);
    }

    /**
     * Get sale details by invoice number.
     */
    public function show(string $invoiceNo): JsonResponse
    {
        $sale = Sale::with(['customer', 'soldBy', 'items.variant.product', 'items.batch'])
            ->where('invoice_no', $invoiceNo)
            ->first();

        if (! $sale) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        $items = $sale->items->map(function ($item) {
            return [
                'variant_name' => $item->variant->product->name.' - '.$item->variant->variant_name,
                'sku' => $item->variant->sku,
                'batch_no' => $item->batch->batch_no,
                'expiry_date' => $item->batch->expiry_date->format('Y-m-d'),
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_no' => $sale->invoice_no,
                'customer' => $sale->customer?->name,
                'customer_phone' => $sale->customer?->phone,
                'date' => $sale->created_at->format('Y-m-d H:i:s'),
                'sold_by' => $sale->soldBy?->name,
                'payment_method' => $sale->payment_method,
                'sub_total' => (float) $sale->sub_total,
                'tax_amount' => (float) $sale->tax_amount,
                'discount_amount' => (float) $sale->discount_amount,
                'grand_total' => (float) $sale->grand_total,
                'paid_amount' => (float) $sale->paid_amount,
                'due_amount' => (float) $sale->due_amount,
                'items' => $items,
            ],
        ]);
    }

    /**
     * Generate and return PDF receipt for a sale.
     * Route: GET /api/sales/{invoiceNo}/receipt
     */
    public function receipt(string $invoiceNo): Response
    {
        $sale = Sale::with(['customer', 'soldBy', 'items.variant.product', 'items.batch'])
            ->where('invoice_no', $invoiceNo)
            ->first();

        if (! $sale) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        $service = new ReceiptService;

        return $service->stream($sale);
    }
}
