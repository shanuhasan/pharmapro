<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('View Purchase') }} #{{ $purchase->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Invoice Number</p>
                            <p class="font-semibold">{{ $purchase->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="font-semibold">{{ $purchase->purchase_date }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Branch</p>
                            <p class="font-semibold">{{ $purchase->branch->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Supplier</p>
                            <p class="font-semibold">{{ $purchase->supplier->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold mb-4">Items Included</h3>
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchase->purchaseItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->medicine->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->batch_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $item->expiry_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">{{ setting('currency_symbol', '₹') }}{{ number_format($item->purchase_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium">{{ setting('currency_symbol', '₹') }}{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="px-6 py-4 text-right font-bold text-gray-900">Grand Total</th>
                                <th class="px-6 py-4 text-right font-bold text-gray-900">{{ setting('currency_symbol', '₹') }}{{ number_format($purchase->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('purchases.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Back to List</a>
                        <a href="{{ route('purchases.edit', $purchase->id) }}" class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700"><i class="fas fa-edit mr-2"></i> Edit Purchase</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
