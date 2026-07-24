<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; }
        .header { width: 100%; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
        .header table { width: 100%; }
        .title { font-size: 30px; font-weight: bold; color: #007bff; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .items-table th { background: #f8f9fa; font-weight: bold; }
        .items-table .right { text-align: right; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 8px; text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; color: #333; }
        .header p { margin: 2px 0; font-size: 14px; color: #666; }
        .invoice-details { margin-bottom: 20px; display: table; width: 100%; }
        .totals-table .total-row { font-size: 18px; font-weight: bold; color: #28a745; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="header">
        @if(setting('pharmacy_logo'))
            <img src="{{ public_path(setting('pharmacy_logo')) }}" class="logo" alt="Logo">
        @endif
        <h1>{{ setting('pharmacy_name', 'PharmaPro System') }}</h1>
        <p>{{ setting('pharmacy_address', '123 Health Ave, Medical District') }}</p>
        <p>Contact: {{ setting('pharmacy_contact', '+1 234 567 8900') }}</p>
    </div>

    <div class="invoice-box">
        <div class="header" style="border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px;">
            <table>
                <tr>
                    <td>
                        <span class="title" style="font-size: 30px; font-weight: bold; color: #007bff;">INVOICE</span><br>
                        #{{ $sale->invoice_number }}<br>
                        Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
                    </td>
                    <td style="text-align: right;">
                        <strong>{{ setting('pharmacy_name', config('app.name', 'PharmaPro')) }}</strong><br>
                        {{ $sale->branch->name ?? 'Main Branch' }}<br>
                        {{ $sale->branch->address ?? '' }}<br>
                        Cashier: {{ $sale->user->name ?? 'System' }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-table">
            <tr>
                <td width="50%">
                    <strong>Billed To:</strong><br>
                    @if($sale->customer)
                        {{ $sale->customer->name }}<br>
                        {{ $sale->customer->phone }}<br>
                        {{ $sale->customer->address }}
                    @else
                        <strong>{{ $sale->customer_name ?? 'Walk-in Customer' }}</strong><br>
                        @if($sale->customer_phone)
                            {{ $sale->customer_phone }}<br>
                        @endif
                    @endif
                </td>
                <td width="50%" style="text-align: right;">
                    <strong>Payment Method:</strong> {{ ucfirst($sale->payment_method) }}<br>
                    <strong>Status:</strong> {{ ucfirst($sale->status) }}
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Products/Items</th>
                    <th>HSN Code</th>
                    <th>Batch</th>
                    <th>Expiry</th>
                    <th class="right">Price</th>
                    <th class="right">Qty</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $item->medicine->name }} <br><small style="color:#777">{{ $item->medicine->generic_name }}</small></td>
                    <td>{{ $item->medicine->hsn_code ?? '-' }}</td>
                    <td>{{ $item->batch_number }}</td>
                    <td>{{ $item->stock && $item->stock->expiry_date ? \Carbon\Carbon::parse($item->stock->expiry_date)->format('d-m-Y') : '-' }}</td>
                    <td class="right">{{ setting('currency_symbol', '₹') }}{{ number_format($item->sale_price, 2) }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ setting('currency_symbol', '₹') }}{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 100%;">
            <tr>
                <td width="50%"></td>
                <td width="50%">
                    <table class="totals-table">
                        <tr>
                            <td>Subtotal:</td>
                            <td>{{ setting('currency_symbol', '₹') }}{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Discount ({{ $sale->subtotal > 0 ? round(($sale->discount / $sale->subtotal) * 100, 2) : 0 }}%):</td>
                            <td>{{ setting('currency_symbol', '₹') }}{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST ({{ setting('sgst_percentage', '0') }}%):</td>
                            <td>{{ setting('currency_symbol', '₹') }}{{ number_format($sale->tax / 2, 2) }}</td>
                        </tr>
                        <tr>
                            <td>CGST ({{ setting('cgst_percentage', '0') }}%):</td>
                            <td>{{ setting('currency_symbol', '₹') }}{{ number_format($sale->tax / 2, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total:</td>
                            <td>{{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>{{ setting('receipt_footer_message', 'Thank you for your purchase! Return policy: 7 days with original receipt.') }}</p>
            <p>Generated by {{ setting('pharmacy_name', 'PharmaPro System') }}</p>
        </div>
    </div>
</body>
</html>
