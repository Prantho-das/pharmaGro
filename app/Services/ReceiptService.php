<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptService
{
    /**
     * Generate a PDF receipt for a sale.
     */
    public function generatePdf(Sale $sale): Pdf
    {
        $data = [
            'sale' => $sale->load(['customer', 'soldBy', 'items.variant.product', 'items.batch']),
            'company' => [
                'name' => config('app.name', 'PharmaGro'),
                'address' => 'Your Address Here',
                'phone' => 'Your Phone',
                'email' => 'your@email.com',
            ],
            'taxRate' => (float) Setting::get('tax_rate', 0),
        ];

        $pdf = Pdf::loadView('receipts.thermal', $data);
        $pdf->setPaper([0, 0, 80 * 2.8346, 1000], 'portrait'); // 80mm width approx

        return $pdf;
    }

    /**
     * Stream the PDF (for browser display).
     */
    public function stream(Sale $sale)
    {
        $pdf = $this->generatePdf($sale);

        return $pdf->stream("receipt-{$sale->invoice_no}.pdf");
    }

    /**
     * Download the PDF.
     */
    public function download(Sale $sale)
    {
        $pdf = $this->generatePdf($sale);

        return $pdf->download("receipt-{$sale->invoice_no}.pdf");
    }
}
