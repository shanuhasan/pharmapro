<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Invoice - {{ $sale->invoice_number }}</title>
    <script src="{{ asset('vendor/tailwindcss/tailwindcss.js') }}"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { font-family: Arial, sans-serif; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #000;
        }
        .b-bottom { border-bottom: 1px solid #000; }
        .b-right { border-right: 1px solid #000; }
        .b-left { border-left: 1px solid #000; }
        .b-top { border-top: 1px solid #000; }
        
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-size: 12px;
        }
        .items-table th {
            font-weight: bold;
        }
        .items-table td.text-left { text-align: left; }
        .items-table td.text-right { text-align: right; }
        .no-border-bottom td { border-bottom: none; border-top: none; }
        
        .summary-box { width: 100%; border-collapse: collapse; }
        .summary-box td { border: 1px solid #000; padding: 4px 6px; font-size: 12px; }
    </style>
</head>
<body class="bg-gray-100 py-10 print:py-0">

    <div class="max-w-4xl mx-auto mb-4 no-print flex justify-end space-x-4">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow font-bold">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Invoice
        </button>
        <button onclick="window.close()" class="bg-gray-600 text-white px-4 py-2 rounded shadow font-bold">Close</button>
    </div>

    <div class="invoice-box bg-white">
        
        <!-- Header Section -->
        <div class="flex w-full b-bottom">
            <!-- Left Info -->
            <div class="w-5/12 p-2 b-right text-sm leading-tight">
                <div class="font-bold text-lg mb-1">{{ strtoupper(setting('pharmacy_name', 'PharmaPro')) }}</div>
                <div>{{ setting('pharmacy_address', 'Pharmacy Address Here') }}</div>
                <div>Phone : {{ setting('pharmacy_contact', '1234567890') }}</div>
                @if(setting('pharmacy_gst'))
                    <div class="mt-2 font-bold">GSTIN-{{ setting('pharmacy_gst') }}</div>
                @endif
            </div>
            
            <!-- Middle Info -->
            <div class="w-3/12 p-2 b-right flex items-center justify-center">
                <div class="font-bold text-xl tracking-wider text-center">GST INVOICE</div>
            </div>
            
            <!-- Right Info -->
            <div class="w-4/12 p-2 text-sm leading-tight">
                <div class="font-bold">Patient Name : {{ strtoupper($sale->customer->name ?? $sale->customer_name ?? '') }}</div>
                @if($sale->customer || $sale->customer_phone)
                <div>Patient Phone : {{ $sale->customer->phone ?? $sale->customer_phone }}</div>
                @endif
                @if($sale->customer || $sale->customer_address)
                <div>Patient Address : {{ $sale->customer->address ?? $sale->customer_address }}</div>
                @endif
                <div>Dr Name : {{ strtoupper($sale->doctor_name ?? 'SELF') }}</div>
                <div class="mt-2">Invoice No. : {{ $sale->invoice_number }}</div>
                <div>Date : {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table border-t-0 border-l-0 border-r-0">
            <thead>
                <tr>
                    <th class="w-8">SN</th>
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
                    <td class="text-left font-bold">{{ strtoupper($item->medicine->name) }}</td>
                    <td>{{ $item->medicine->medicines_per_strip ?? 1 }}</td>
                    <td>{{ $item->hsn_code }}</td>
                    <td>{{ strtoupper($item->batch_number) }}</td>
                    <td>{{ $item->stock && $item->stock->expiry_date ? \Carbon\Carbon::parse($item->stock->expiry_date)->format('m/y') : '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>
                    <td>{{ setting('sgst_percentage', '0') }}</td>
                    <td>{{ setting('cgst_percentage', '0') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
                
                <!-- Empty rows for spacing to match physical invoice feel -->
                @for($i = $sale->saleItems->count(); $i < 8; $i++)
                <tr class="no-border-bottom text-transparent">
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
                @endfor
                
                <!-- Bottom border line for the items area -->
                <tr class="h-0"><td colspan="12" class="p-0 border-b border-black"></td></tr>
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="flex w-full">
            <div class="w-8/12 p-3 text-xs leading-tight b-right flex flex-col justify-between">
                <div>
                    <span class="font-bold underline">Terms & Conditions</span><br>
                    Goods once sold will not be taken back or exchanged.<br>
                    Bills not paid due date will attract 24% interest.<br>
                    All disputes subject to Jurisdiction only.<br>
                    Prescribed Sales Tax declaration will be given.<br><br>
                    @if(setting('sgst_percentage') > 0 || setting('cgst_percentage') > 0)
                        GST Details: {{ $sale->subtotal }} * {{ setting('sgst_percentage') + setting('cgst_percentage') }}% = {{ number_format($sale->tax, 2) }}
                    @endif
                </div>
                
                <div class="mt-4">
                    <span class="font-bold">Rs. {{ \Illuminate\Support\Number::spell($sale->total_amount) }} Only</span>
                </div>
            </div>
            
            <div class="w-4/12 flex flex-col">
                <table class="summary-box border-none w-full h-full">
                    <tr>
                        <td class="font-bold border-t-0 border-l-0">SUB TOTAL</td>
                        <td class="text-right font-bold border-t-0 border-r-0">{{ number_format($sale->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border-l-0">SGST {{ setting('sgst_percentage', '0') }} %</td>
                        <td class="text-right border-r-0">{{ number_format($sale->tax / 2, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border-l-0">CGST {{ setting('cgst_percentage', '0') }} %</td>
                        <td class="text-right border-r-0">{{ number_format($sale->tax / 2, 2) }}</td>
                    </tr>
                    @if($sale->discount > 0)
                    <tr>
                        <td class="border-l-0">Discount</td>
                        <td class="text-right border-r-0">{{ number_format($sale->discount, 2) }}</td>
                    </tr>
                    @endif
                    <!-- <tr>
                        <td class="border-l-0">Roundoff</td>
                        <td class="text-right border-r-0">0.00</td>
                    </tr> -->
                    <tr class="h-full align-bottom">
                        <td class="font-bold text-lg border-b-0 border-l-0 pt-4">GRAND TOTAL</td>
                        <td class="text-right font-bold text-lg border-b-0 border-r-0 pt-4">{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Signatory Section -->
        <div class="flex w-full b-top p-2 text-sm">
            <div class="w-8/12"></div>
            <div class="w-4/12 text-center pt-8">
                <div class="font-bold border-t border-gray-400 inline-block px-4">Authorised Signatory</div>
            </div>
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
