<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; }
        .invoice-box { width: 100%; max-width: 800px; margin: auto; border: 1px solid #000; }
        table { width: 100%; border-collapse: collapse; }
        
        /* Layout Table */
        .header-table td { border-bottom: 1px solid #000; padding: 8px; vertical-align: top; }
        .header-left { width: 40%; border-right: 1px solid #000; }
        .header-center { width: 25%; border-right: 1px solid #000; text-align: center; vertical-align: middle !important; }
        .header-right { width: 35%; }
        
        .title { font-size: 16px; font-weight: bold; letter-spacing: 2px; }
        .pharmacy-name { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
        
        /* Items Table */
        .items-table th, .items-table td { border: 1px solid #000; padding: 4px; text-align: center; }
        .items-table th { font-weight: bold; }
        .items-table td.text-left { text-align: left; }
        .items-table td.text-right { text-align: right; }
        .items-table .no-border-bottom td { border-bottom: none; border-top: none; }
        .items-table .bottom-border td { border-bottom: 1px solid #000; border-top: none; padding: 0; height: 0; }
        
        /* Footer/Summary section */
        .footer-table td { padding: 4px; vertical-align: top; }
        .footer-left { width: 65%; border-right: 1px solid #000; border-bottom: 1px solid #000; }
        .footer-right { width: 35%; border-bottom: 1px solid #000; padding: 0; }
        
        .summary-box { width: 100%; border-collapse: collapse; height: 100%; }
        .summary-box td { border-bottom: 1px solid #000; padding: 4px; }
        .summary-box td:first-child { border-right: 1px solid #000; }
        .summary-box tr:last-child td { border-bottom: none; }
        
        .signatory { width: 100%; padding: 4px; text-align: right; margin-top: 30px; }
        .signatory span { border-top: 1px solid #000; padding: 4px 10px; font-weight: bold; display: inline-block; margin-right: 20px; }
    </style>
</head>
<body>

    <div class="invoice-box">
        
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="pharmacy-name">{{ strtoupper(setting('pharmacy_name', 'PharmaPro')) }}</div>
                    <div>{{ setting('pharmacy_address', 'Pharmacy Address Here') }}</div>
                    <div>Phone : {{ setting('pharmacy_contact', '1234567890') }}</div>
                    @if(setting('pharmacy_gst'))
                        <div style="margin-top: 5px; font-weight: bold;">GSTIN-{{ setting('pharmacy_gst') }}</div>
                    @endif
                </td>
                <td class="header-center">
                    <div class="title">GST INVOICE</div>
                </td>
                <td class="header-right">
                    <strong>Patient Name :</strong> {{ strtoupper($sale->customer->name ?? $sale->customer_name ?? 'CASH') }}<br>
                    @if($sale->customer || $sale->customer_phone)
                    <strong>Patient Phone :</strong> {{ $sale->customer->phone ?? $sale->customer_phone }}<br>
                    @endif
                    @if($sale->customer || $sale->customer_address)
                    <strong>Patient Address :</strong> {{ $sale->customer->address ?? $sale->customer_address }}<br>
                    @endif
                    <strong>Dr Name :</strong> {{ strtoupper($sale->doctor_name ?? 'SELF') }}<br>
                    <div style="margin-top: 5px;"><strong>Invoice No. :</strong> {{ $sale->invoice_number }}</div>
                    <div><strong>Date :</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table" style="border-top: none; border-left: none; border-right: none;">
            <thead>
                <tr>
                    <th style="width: 20px;">SN</th>
                    <th class="text-left">PRODUCT NAME</th>
                    <th>PACK</th>
                    <th>HSN</th>
                    <th>BATCH</th>
                    <th>EXP</th>
                    <th>QTY</th>
                    <th class="text-right">MRP</th>
                    <th class="text-right">RATE</th>
                    <th>SGST</th>
                    <th>CGST</th>
                    <th class="text-right">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $index => $item)
                <tr class="no-border-bottom">
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left" style="font-weight: bold;">{{ strtoupper($item->medicine->name) }}</td>
                    <td>{{ $item->medicine->medicines_per_strip ?? 1 }}</td>
                    <td>{{ $item->hsn_code }}</td>
                    <td>{{ strtoupper($item->batch_number) }}</td>
                    <td>{{ $item->stock && $item->stock->expiry_date ? \Carbon\Carbon::parse($item->stock->expiry_date)->format('m/y') : '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>
                    <td>{{ setting('sgst_percentage', '0') }}</td>
                    <td>{{ setting('cgst_percentage', '0') }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
                
                <!-- Empty rows for spacing -->
                @for($i = $sale->saleItems->count(); $i < 8; $i++)
                <tr class="no-border-bottom" style="color: white;">
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
                @endfor
                
                <tr class="bottom-border"><td colspan="12"></td></tr>
            </tbody>
        </table>

        <!-- Summary Section -->
        <table class="footer-table" style="border-bottom: 1px solid #000;">
            <tr>
                <td class="footer-left">
                    <div style="margin-bottom: 15px;">
                        <span style="font-weight: bold; text-decoration: underline;">Terms & Conditions</span><br>
                        Goods once sold will not be taken back or exchanged.<br>
                        Bills not paid due date will attract 24% interest.<br>
                        All disputes subject to Jurisdiction only.<br>
                        Prescribed Sales Tax declaration will be given.<br><br>
                        @if(setting('sgst_percentage') > 0 || setting('cgst_percentage') > 0)
                            GST Details: {{ $sale->subtotal }} * {{ setting('sgst_percentage') + setting('cgst_percentage') }}% = {{ number_format($sale->tax, 2) }}
                        @endif
                    </div>
                    
                    <div>
                        <strong>Rs. {{ \Illuminate\Support\Number::spell($sale->total_amount) }} Only</strong>
                    </div>
                </td>
                
                <td class="footer-right">
                    <table class="summary-box">
                        <tr>
                            <td style="font-weight: bold;">SUB TOTAL</td>
                            <td class="text-right font-bold">{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST {{ setting('sgst_percentage', '0') }} %</td>
                            <td class="text-right">{{ number_format($sale->tax / 2, 2) }}</td>
                        </tr>
                        <tr>
                            <td>CGST {{ setting('cgst_percentage', '0') }} %</td>
                            <td class="text-right">{{ number_format($sale->tax / 2, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Roundoff</td>
                            <td class="text-right">0.00</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; font-size: 14px; padding-top: 15px;">GRAND TOTAL</td>
                            <td class="text-right" style="font-weight: bold; font-size: 14px; padding-top: 15px;">{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- Signatory Section -->
        <div class="signatory">
            <span>Authorised Signatory</span>
        </div>

    </div>

</body>
</html>
