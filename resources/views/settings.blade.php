<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Global Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- General Settings -->
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <h3 class="font-bold text-lg text-gray-700 mb-4 border-b pb-2"><i class="fas fa-store mr-2"></i>Pharmacy Details</h3>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Pharmacy Name</label>
                                    <input type="text" name="pharmacy_name" value="{{ setting('pharmacy_name', 'PharmaPro') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                                    <input type="text" name="pharmacy_contact" value="{{ setting('pharmacy_contact', '+1234567890') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">GST No.</label>
                                    <input type="text" name="pharmacy_gst" value="{{ setting('pharmacy_gst') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea name="pharmacy_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ setting('pharmacy_address', '123 Health Ave') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                                    @if(setting('pharmacy_logo'))
                                        <div class="mb-2">
                                            <img src="{{ asset(setting('pharmacy_logo')) }}" alt="Logo" class="h-16 object-contain">
                                        </div>
                                    @endif
                                    <input type="file" name="pharmacy_logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>

                            <!-- Invoicing & System Settings -->
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <h3 class="font-bold text-lg text-gray-700 mb-4 border-b pb-2"><i class="fas fa-cogs mr-2"></i>System & Invoicing</h3>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                                    <input type="text" name="currency_symbol" value="{{ setting('currency_symbol', '₹') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Invoice Prefix</label>
                                    <input type="text" name="invoice_prefix" value="{{ setting('invoice_prefix', 'INV-') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Tax Percentage (%)</label>
                                    <input type="number" step="0.01" name="tax_percentage" value="{{ setting('tax_percentage', '0') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Low Stock Threshold</label>
                                    <input type="number" name="low_stock_threshold" value="{{ setting('low_stock_threshold', '10') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Receipt Footer Message</label>
                                    <textarea name="receipt_footer_message" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3">{{ setting('receipt_footer_message', 'Thank you for your business!') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded shadow hover:bg-blue-700">
                                Save Settings
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
