<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Supplier Details') }} - {{ $supplier->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 grid grid-cols-2 gap-4">
                    <div><p class="text-gray-500 text-sm">Company:</p> <p class="font-bold">{{ $supplier->company ?? 'N/A' }}</p></div>
                    <div><p class="text-gray-500 text-sm">Contact:</p> <p class="font-bold">{{ $supplier->name }}</p></div>
                    <div><p class="text-gray-500 text-sm">Phone:</p> <p class="font-bold">{{ $supplier->phone ?? 'N/A' }}</p></div>
                    <div><p class="text-gray-500 text-sm">Email:</p> <p class="font-bold">{{ $supplier->email ?? 'N/A' }}</p></div>
                    <div><p class="text-gray-500 text-sm">Address:</p> <p class="font-bold">{{ $supplier->address ?? 'N/A' }}</p></div>
                    <div><p class="text-gray-500 text-sm">NTN Number:</p> <p class="font-bold">{{ $supplier->ntn_number ?? 'N/A' }}</p></div>
                </div>
            </div>

            <h3 class="text-lg font-bold mb-4">Purchase History</h3>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($supplier->purchases as $purchase)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->purchase_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->invoice_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $purchase->branch->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-green-600 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($purchase->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><a href="{{ route('purchases.show', $purchase->id) }}" class="text-indigo-600 hover:text-indigo-900">View Invoice</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No purchases found for this supplier.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
