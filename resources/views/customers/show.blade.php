<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Profile') }} - {{ $customer->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Profile Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2">Profile Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-gray-500 text-sm">Name:</p> <p class="font-bold">{{ $customer->name }}</p></div>
                            <div><p class="text-gray-500 text-sm">Phone:</p> <p class="font-bold">{{ $customer->phone ?? 'N/A' }}</p></div>
                            <div><p class="text-gray-500 text-sm">Email:</p> <p class="font-bold">{{ $customer->email ?? 'N/A' }}</p></div>
                            <div><p class="text-gray-500 text-sm">DOB:</p> <p class="font-bold">{{ $customer->dob ?? 'N/A' }}</p></div>
                            <div class="col-span-2"><p class="text-gray-500 text-sm">Address:</p> <p class="font-bold">{{ $customer->address ?? 'N/A' }}</p></div>
                            <div class="col-span-2"><p class="text-gray-500 text-sm text-red-500">Medical Notes/Allergies:</p> <p class="font-bold">{{ $customer->notes ?? 'None' }}</p></div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 flex flex-col justify-center h-full space-y-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500">Total Spent Amount</p>
                            <p class="text-3xl font-bold text-green-600">{{ setting('currency_symbol', '₹') }}{{ number_format($totalSpent, 2) }}</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500">Last Visit Date</p>
                            <p class="text-xl font-bold text-blue-600">{{ $lastVisit !== 'Never' ? \Carbon\Carbon::parse($lastVisit)->format('M d, Y') : 'Never' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-bold mb-4">Purchase History (Invoices)</h3>
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
                            @forelse($customer->sales as $sale)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->sale_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->invoice_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->branch->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-green-600 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="text-indigo-600 hover:text-indigo-900">View Invoice</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No purchase history found for this customer.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
