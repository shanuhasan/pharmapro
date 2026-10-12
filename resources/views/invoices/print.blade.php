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
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 1;
            pointer-events: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .watermark img {
            max-width: 500px;
            max-height: 500px;
        }
        .invoice-box {
            position: relative;
            z-index: 2;
        }
        .invoice-content {
            position: relative;
            z-index: 3;
        }
    </style>
</head>
<body class="bg-gray-100 py-10 print:py-0">

    <div class="max-w-4xl mx-auto mb-4 no-print flex justify-end space-x-3">
        <button type="button" onclick="openPrintEditModal()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded shadow font-bold">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Edit Patient
        </button>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow font-bold">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Invoice
        </button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow font-bold">Close</button>
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
                <div class="font-bold" id="print_patient_name_display">Patient Name : {{ strtoupper($sale->customer_name ?: ($sale->customer->name ?? '')) }}</div>
                @if($sale->customer_phone || ($sale->customer && $sale->customer->phone))
                <div id="print_patient_phone_display">Patient Phone : {{ $sale->customer_phone ?: $sale->customer->phone }}</div>
                @endif
                @if($sale->customer_address || ($sale->customer && $sale->customer->address))
                <div id="print_patient_address_display">Patient Address : {{ $sale->customer_address ?: $sale->customer->address }}</div>
                @endif
                <div id="print_doctor_name_display">Dr Name : {{ strtoupper($sale->doctor_name ?? 'SELF') }}</div>
                <div class="mt-2">Invoice No. : {{ $sale->invoice_number }}</div>
                <div>Date : {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="relative items-table-wrapper">
            @if(setting('pharmacy_logo'))
            <div class="watermark">
                <img src="{{ asset(setting('pharmacy_logo')) }}" alt="Background Logo">
            </div>
            @endif
            <table class="items-table border-t-0 border-l-0 border-r-0 relative z-10 bg-transparent">
            <thead>
                <tr>
                    <th class="w-8">SN</th>
                    <th class="text-left">PRODUCT NAME</th>
                    <th>PACK</th>
                    <th>HSN</th>
                    <th>BATCH</th>
                    <th>EXP</th>
                    <th>STRIP</th>
                    <th class="text-right">S.MRP</th>
                    <th>QTY</th>
                    <th class="text-right">MRP</th>                    
                    <!-- <th class="text-right">RATE</th> -->
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
                    @php
                        $medPerStrip = $item->medicine->medicines_per_strip > 0 ? $item->medicine->medicines_per_strip : 1;
                        $stripQty = $item->quantity / $medPerStrip;
                        $stripRate = $item->sale_price * $medPerStrip;
                    @endphp
                    <td>{{ $stripQty == floor($stripQty) ? (int)$stripQty : number_format($stripQty, 2) }}</td>
                    <td class="text-right">{{ number_format($stripRate, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>                    
                    <!-- <td class="text-right">{{ number_format($item->sale_price, 2) }}</td> -->
                    <td>{{ setting('sgst_percentage', '0') }}</td>
                    <td>{{ setting('cgst_percentage', '0') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
                
                <!-- Empty rows for spacing to match physical invoice feel -->
                @for($i = $sale->saleItems->count(); $i < 8; $i++)
                <tr class="no-border-bottom text-transparent">
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
                @endfor
                
                <!-- Bottom border line for the items area -->
                <tr class="h-0"><td colspan="14" class="p-0 border-b border-black"></td></tr>
            </tbody>
            </table>
        </div>

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

    <!-- Edit Patient Modal (no-print) -->
    <div id="printEditModal" class="fixed inset-0 z-50 overflow-y-auto hidden no-print" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closePrintEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form id="printEditForm" onsubmit="savePrintPatientDetails(event)">
                    <div class="bg-white px-6 pt-5 pb-4">
                        <div class="flex justify-between items-center border-b pb-3 mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Edit Invoice Patient Details</h3>
                            <button type="button" onclick="closePrintEditModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                        </div>

                        <div id="print_modal_error" class="hidden mb-3 p-2 bg-red-100 text-red-700 text-xs rounded"></div>

                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Patient Name <span class="text-red-500">*</span></label>
                            <input type="text" id="p_modal_patient_name" required 
                                value="{{ $sale->customer_name ?: ($sale->customer->name ?? '') }}" 
                                class="w-full text-sm p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Patient Phone</label>
                            <input type="text" id="p_modal_patient_phone" 
                                value="{{ $sale->customer_phone ?: ($sale->customer->phone ?? '') }}" 
                                class="w-full text-sm p-2 border border-gray-300 rounded">
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Patient Address</label>
                            <textarea id="p_modal_patient_address" rows="2" 
                                class="w-full text-sm p-2 border border-gray-300 rounded">{{ $sale->customer_address ?: ($sale->customer->address ?? '') }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Doctor Name</label>
                            <input type="text" id="p_modal_doctor_name" 
                                value="{{ $sale->doctor_name ?? '' }}" 
                                placeholder="e.g. Dr. Sharma (Default: SELF)" 
                                class="w-full text-sm p-2 border border-gray-300 rounded">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-2 border-t">
                        <button type="button" onclick="closePrintEditModal()" class="px-4 py-2 bg-white border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
                        <button type="submit" id="p_modal_save_btn" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-bold hover:bg-blue-700">Save & Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPrintEditModal() {
            document.getElementById('print_modal_error').classList.add('hidden');
            document.getElementById('printEditModal').classList.remove('hidden');
            document.getElementById('p_modal_patient_name').focus();
        }

        function closePrintEditModal() {
            document.getElementById('printEditModal').classList.add('hidden');
        }

        function savePrintPatientDetails(e) {
            e.preventDefault();
            const btn = document.getElementById('p_modal_save_btn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            const payload = {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                patient_name: document.getElementById('p_modal_patient_name').value,
                patient_phone: document.getElementById('p_modal_patient_phone').value,
                patient_address: document.getElementById('p_modal_patient_address').value,
                doctor_name: document.getElementById('p_modal_doctor_name').value
            };

            fetch('{{ route('sales.update', $sale->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Save & Update';
                if(data.success) {
                    window.location.reload();
                } else {
                    document.getElementById('print_modal_error').textContent = data.message || 'Failed to update';
                    document.getElementById('print_modal_error').classList.remove('hidden');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'Save & Update';
                document.getElementById('print_modal_error').textContent = 'Network or server error.';
                document.getElementById('print_modal_error').classList.remove('hidden');
            });
        }

        window.onload = function() {
            // Only auto-print if modal is not open
            if(window.location.search.indexOf('no_autoprint') === -1) {
                // Auto print
                window.print();
            }
        }
    </script>
</body>
</html>
