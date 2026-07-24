<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('View Invoice') }} - {{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mb-4 flex justify-end space-x-2">
                <a href="{{ route('invoice.print', $sale->id) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
                <a href="{{ route('invoice.pdf', $sale->id) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
                    <i class="fas fa-file-pdf mr-2"></i> Download PDF
                </a>
                @if($sale->customer && $sale->customer->email)
                    <form action="{{ route('invoice.email', $sale->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="email" value="{{ $sale->customer->email }}">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                            <i class="fas fa-envelope mr-2"></i> Email Customer
                        </button>
                    </form>
                @endif
            </div>

            <!-- Invoice Preview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border-t-8 border-medical-primary">
                
                <div class="flex justify-between items-start mb-8 border-b pb-6">
                    <div>
                        <h1 class="text-3xl font-black text-medical-primary mb-2">INVOICE</h1>
                        <p class="text-gray-500 font-bold">#{{ $sale->invoice_number }}</p>
                        <p class="text-gray-500">Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        @if(setting('pharmacy_logo'))
                            <img src="{{ asset(setting('pharmacy_logo')) }}" alt="Logo" class="h-12 ml-auto mb-2 object-contain">
                        @endif
                        <h2 class="text-xl font-bold text-gray-800">{{ setting('pharmacy_name', 'PharmaPro') }}</h2>
                        <p class="text-gray-600">{{ $sale->branch->name ?? 'Main Branch' }}</p>
                        <p class="text-gray-600">{{ $sale->branch->address ?? '' }}</p>
                    </div>
                </div>

                <div class="flex justify-between mb-8">
                    <div>
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Billed To</p>
                        @if($sale->customer)
                            <p class="font-bold text-gray-800 text-lg">{{ $sale->customer->name }}</p>
                            <p class="text-gray-600">{{ $sale->customer->phone }}</p>
                            <p class="text-gray-600">{{ $sale->customer->email }}</p>
                            <p class="text-gray-600">{{ $sale->customer->address }}</p>
                        @else
                            <p class="font-bold text-gray-800 text-lg">{{ $sale->customer_name ?? 'Walk-in Customer' }}</p>
                            @if($sale->customer_phone)
                                <p class="text-gray-600">{{ $sale->customer_phone }}</p>
                            @endif
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">Payment Details</p>
                        <p class="text-gray-800"><span class="font-semibold">Method:</span> {{ ucfirst($sale->payment_method) }}</p>
                        <p class="text-gray-800"><span class="font-semibold">Status:</span> {{ ucfirst($sale->status) }}</p>
                        <p class="text-gray-800"><span class="font-semibold">Cashier:</span> {{ $sale->user->name ?? 'System' }}</p>
                    </div>
                </div>

                <table class="w-full text-left border-collapse mb-8">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm uppercase">
                            <th class="py-3 px-4 rounded-tl-lg font-bold">Item</th>
                            <th class="py-3 px-4 font-bold">Batch</th>
                            <th class="py-3 px-4 text-right font-bold">Price</th>
                            <th class="py-3 px-4 text-right font-bold">Qty</th>
                            <th class="py-3 px-4 text-right rounded-tr-lg font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleItems as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-4 px-4 font-medium text-gray-800">{{ $item->medicine->name }} <span class="text-xs text-gray-500 block">{{ $item->medicine->generic_name }}</span></td>
                            <td class="py-4 px-4 text-gray-600">{{ $item->batch_number }}</td>
                            <td class="py-4 px-4 text-right text-gray-600">{{ setting('currency_symbol', '₹') }}{{ number_format($item->sale_price, 2) }}</td>
                            <td class="py-4 px-4 text-right font-bold text-gray-800">{{ $item->quantity }}</td>
                            <td class="py-4 px-4 text-right font-bold text-green-600">{{ setting('currency_symbol', '₹') }}{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end mb-8">
                    <div class="w-1/2 md:w-1/3">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600 font-semibold">Subtotal</span>
                            <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600 font-semibold">Discount</span>
                            <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->discount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600 font-semibold">Tax</span>
                            <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-gray-800 font-black text-xl">Total</span>
                            <span class="text-green-600 font-black text-xl">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-sm">
                            <span class="text-gray-500">Paid Amount</span>
                            <span class="text-gray-600">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 text-sm">
                            <span class="text-gray-500">Change Due</span>
                            <span class="text-gray-600">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->change_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-center text-gray-500 text-sm mt-12 border-t pt-8">
                    <p class="font-bold text-gray-600 mb-1">Thank you for your business!</p>
                    <!-- <p>Medicines once sold cannot be returned or exchanged without a valid receipt.</p> -->
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>
