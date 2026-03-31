<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Show receipt PDF for a sale.
     * Route: GET /receipt/{invoice}
     */
    public function show(Request $request, string $invoice)
    {
        $sale = Sale::with(['customer', 'soldBy', 'items.variant.product', 'items.batch'])
            ->where('invoice_no', $invoice)
            ->first();

        if (! $sale) {
            abort(404, 'Sale not found');
        }

        // Ensure user is authenticated via web guard
        if (! auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $service = new ReceiptService;

        return $service->stream($sale);
    }
}
