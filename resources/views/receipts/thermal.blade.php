<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: "DejaVu Sans";
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            src: url("data:font/ttf;base64,{{ base64_encode(file_get_contents(public_path('fonts/DejaVuSans.ttf'))) }}") format('truetype');
        }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            width: 80mm;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            margin: 5px 0;
        }
        .header p {
            margin: 2px 0;
            font-size: 8pt;
        }
        .info {
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .info div {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        th, td {
            text-align: left;
            padding: 4px 2px;
        }
        th {
            border-bottom: 1px solid #000;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .grand-total {
            font-weight: bold;
            font-size: 11pt;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 8pt;
        }
        .footer p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company['name'] }}</h1>
        <p>{{ $company['address'] }}</p>
        <p>Phone: {{ $company['phone'] }}</p>
        <p>Email: {{ $company['email'] }}</p>
    </div>

    <div class="info">
        <div><strong>Invoice:</strong> {{ $sale->invoice_no }}</div>
        <div><strong>Date:</strong> {{ $sale->created_at->format('d M Y, h:i A') }}</div>
        <div><strong>Cashier:</strong> {{ $sale->soldBy?->name ?? 'N/A' }}</div>
        @if($sale->customer)
            <div><strong>Customer:</strong> {{ $sale->customer->name }}</div>
            @if($sale->customer->phone)
                <div><strong>Phone:</strong> {{ $sale->customer->phone }}</div>
            @endif
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>
                        {{ $item->variant->product->name }}
                        <br><small>{{ $item->variant->variant_name }}</small>
                        <br><small>Batch: {{ $item->batch->batch_no }} (Exp: {{ $item->batch->expiry_date->format('d/m/Y') }})</small>
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>
            <span>Sub Total:</span>
            <span>৳{{ number_format($sale->sub_total, 2) }}</span>
        </div>
        @if($sale->tax_amount > 0)
        <div>
            <span>Tax ({{ $taxRate }}%):</span>
            <span>৳{{ number_format($sale->tax_amount, 2) }}</span>
        </div>
        @endif
        @if($sale->discount_amount > 0)
        <div>
            <span>Discount:</span>
            <span>-৳{{ number_format($sale->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="grand-total">
            <span>Grand Total:</span>
            <span>৳{{ number_format($sale->grand_total, 2) }}</span>
        </div>
        <div>
            <span>Paid:</span>
            <span>৳{{ number_format($sale->paid_amount, 2) }}</span>
        </div>
        @if($sale->due_amount > 0)
        <div>
            <span>Due:</span>
            <span style="color: red;">৳{{ number_format($sale->due_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Thank you for your purchase!</p>
        <p>{{ config('app.name') }} - {{ now()->format('d M Y, h:i A') }}</p>
    </div>
</body>
</html>
