<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Invoice - {{ $sale->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; }
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="max-w-4xl mx-auto mt-10 no-print flex justify-end space-x-4 mb-4">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow font-bold">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Invoice
        </button>
        <button onclick="window.close()" class="bg-gray-600 text-white px-4 py-2 rounded shadow font-bold">Close</button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-10 shadow-lg border-t-8 border-blue-600 print:border-none print:shadow-none print:p-0 print:mt-0">
        
        <div class="flex justify-between items-start mb-8 border-b-2 border-gray-200 pb-6">
            <div>
                @if(setting('pharmacy_logo'))
                    <img src="{{ asset(setting('pharmacy_logo')) }}" alt="Logo" class="h-16 mb-2 object-contain">
                @endif
                <!-- <h2 class="text-2xl font-bold text-gray-800">{{ setting('pharmacy_name', 'PharmaPro') }}</h2> -->
                <h1 class="text-4xl font-black text-blue-600 mb-2">INVOICE</h1>
                <p class="text-gray-500 font-bold">#{{ $sale->invoice_number }}</p>
                <p class="text-gray-500">Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800">{{ setting('pharmacy_name', 'PharmaPro') }}</h2>
                <p class="text-gray-600">{{ $sale->branch->name ?? '' }}</p>
                <p class="text-gray-600">{{ $sale->branch->address ?? '' }}</p>
                @if(setting('pharmacy_gst'))
                    <p class="text-gray-600 font-bold">GST No: {{ setting('pharmacy_gst') }}</p>
                @endif
            </div>
        </div>

        <div class="flex justify-between mb-8">
            <div>
                <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Billed To</p>
                @if($sale->customer)
                    <p class="font-bold text-gray-800 text-lg">{{ $sale->customer->name }}</p>
                    <p class="text-gray-600">{{ $sale->customer->phone }}</p>
                    <p class="text-gray-600">{{ $sale->customer->address }}</p>
                @else
                    <p class="font-bold text-gray-800 text-lg">{{ $sale->customer_name ?? 'Walk-in Customer' }}</p>
                    @if($sale->customer_phone)
                        <p class="text-gray-600">{{ $sale->customer_phone }}</p>
                    @endif
                    @if($sale->customer_address)
                        <p class="text-gray-600">{{ $sale->customer_address }}</p>
                    @endif
                @endif
            </div>
            <div class="text-right">
                <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Payment Details</p>
                <p class="text-gray-800"><span class="font-semibold">Method:</span> {{ ucfirst($sale->payment_method) }}</p>
                <p class="text-gray-800"><span class="font-semibold">Cashier:</span> {{ $sale->user->name ?? 'System' }}</p>
                
                @if($sale->doctor_name)
                <div class="mt-4">
                    <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Prescribing Doctor</p>
                    <p class="font-bold text-gray-800">{{ $sale->doctor_name }}</p>
                    @if($sale->doctor_address)
                        <p class="text-gray-600 text-sm">{{ $sale->doctor_address }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <table class="w-full text-left border-collapse mb-8">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-sm uppercase border-b-2 border-gray-300">
                    <th class="py-3 px-4 font-bold">Products/Items</th>
                    <th class="py-3 px-4 font-bold">HSN Code</th>
                    <th class="py-3 px-4 font-bold">Batch</th>
                    <th class="py-3 px-4 font-bold">Expiry</th>
                    <th class="py-3 px-4 text-right font-bold">Price</th>
                    <th class="py-3 px-4 text-right font-bold">Qty</th>
                    <th class="py-3 px-4 text-right font-bold">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr class="border-b border-gray-200">
                    <td class="py-3 px-4 font-medium text-gray-800">{{ $item->medicine->name }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $item->medicine->hsn_code ?? '-' }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $item->batch_number }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $item->stock && $item->stock->expiry_date ? \Carbon\Carbon::parse($item->stock->expiry_date)->format('d-m-Y') : '-' }}</td>
                    <td class="py-3 px-4 text-right text-gray-600">{{ setting('currency_symbol', '₹') }}{{ number_format($item->sale_price, 2) }}</td>
                    <td class="py-3 px-4 text-right font-bold text-gray-800">{{ $item->quantity }}</td>
                    <td class="py-3 px-4 text-right font-bold text-green-600">{{ setting('currency_symbol', '₹') }}{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end mb-8">
            <div class="w-1/2">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600 font-semibold">Subtotal</span>
                    <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600 font-semibold">Discount</span>
                    <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->discount, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-gray-600 font-semibold">Tax</span>
                    <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->tax, 2) }}</span>
                </div>
                <div class="flex justify-between py-3">
                    <span class="text-gray-800 font-black text-xl">Total</span>
                    <span class="text-green-600 font-black text-xl">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-gray-500 text-sm mt-12 border-t pt-8">
            <p class="font-bold text-gray-600 mb-1">Thank you for your business!</p>
            <!-- <p>Medicines once sold cannot be returned or exchanged without a valid receipt.</p> -->
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
