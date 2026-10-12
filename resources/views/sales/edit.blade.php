<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Invoice Patient Details') }} - {{ $sale->invoice_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('sales.show', $sale->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded shadow-sm text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Invoice
                </a>
                <a href="{{ route('sales.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-2 px-4 rounded shadow-sm text-sm">
                    All Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Invoice Info Overview Card -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6 flex flex-wrap justify-between items-center shadow-sm">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-100 px-2 py-1 rounded">Invoice #{{ $sale->invoice_number }}</span>
                    <h3 class="text-lg font-bold text-gray-800 mt-2">
                        Current Patient: 
                        <span class="text-indigo-700">{{ $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in Customer') }}</span>
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Date: <span class="font-medium">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</span> | 
                        Branch: <span class="font-medium">{{ $sale->branch->name ?? 'N/A' }}</span>
                    </p>
                </div>
                <div class="text-right mt-3 sm:mt-0">
                    <p class="text-xs uppercase text-gray-500 font-semibold">Total Amount</p>
                    <p class="text-2xl font-black text-green-600">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->total_amount, 2) }}</p>
                    <a href="{{ route('invoice.print', $sale->id) }}" target="_blank" class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 mt-1 font-semibold">
                        <i class="fas fa-print mr-1"></i> Print Current Invoice
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <form action="{{ route('sales.update', $sale->id) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-gray-200 pb-5 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-user-injured text-medical-primary mr-2"></i> Patient Information
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Update the patient name, phone number, and address displayed on this invoice and printouts.
                        </p>
                    </div>

                    <!-- Search Registered Customer (Optional Link) -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-400 mr-1"></i> Link Existing Customer (Optional)
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Search and select an existing customer to auto-fill details, or type custom patient details below.</p>
                        
                        <div class="relative">
                            <input type="text" id="customer_search_input" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5" placeholder="Type customer name or phone to search...">
                            <div id="customer_search_results" class="absolute z-20 w-full bg-white border border-gray-200 mt-1 rounded-md shadow-lg hidden max-h-48 overflow-y-auto"></div>
                        </div>

                        <input type="hidden" name="customer_id" id="edit_customer_id" value="{{ $sale->customer_id }}">

                        <div id="linked_customer_badge" class="{{ $sale->customer ? '' : 'hidden' }} mt-2 flex items-center justify-between bg-blue-50 border border-blue-200 text-blue-800 text-xs px-3 py-2 rounded">
                            <span>
                                Linked Account: <strong id="linked_customer_name">{{ $sale->customer->name ?? '' }}</strong> 
                                (<span id="linked_customer_phone">{{ $sale->customer->phone ?? 'No phone' }}</span>)
                            </span>
                            <button type="button" id="btn_clear_linked_customer" class="text-red-500 hover:text-red-700 font-bold ml-2">
                                <i class="fas fa-times-circle"></i> Unlink (Manual Patient)
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Patient Name -->
                        <div>
                            <label for="patient_name" class="block text-sm font-bold text-gray-700 mb-1">
                                Patient Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="patient_name" id="patient_name" 
                                value="{{ old('patient_name', $sale->customer_name ?: ($sale->customer->name ?? '')) }}" 
                                required
                                placeholder="Enter patient name (e.g. John Doe)"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5 @error('patient_name') border-red-500 @enderror">
                            @error('patient_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Patient Phone -->
                        <div>
                            <label for="patient_phone" class="block text-sm font-semibold text-gray-700 mb-1">
                                Patient Phone
                            </label>
                            <input type="text" name="patient_phone" id="patient_phone" 
                                value="{{ old('patient_phone', $sale->customer_phone ?: ($sale->customer->phone ?? '')) }}" 
                                placeholder="Enter patient phone number"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                        </div>
                    </div>

                    <!-- Patient Address -->
                    <div class="mb-6">
                        <label for="patient_address" class="block text-sm font-semibold text-gray-700 mb-1">
                            Patient Address
                        </label>
                        <textarea name="patient_address" id="patient_address" rows="2" 
                            placeholder="Enter patient full address (optional)"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">{{ old('patient_address', $sale->customer_address ?: ($sale->customer->address ?? '')) }}</textarea>
                    </div>

                    <div class="border-t border-gray-200 pt-5 pb-2 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fas fa-user-md text-medical-primary mr-2"></i> Doctor Information (Optional)
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Doctor Name -->
                        <div>
                            <label for="doctor_name" class="block text-sm font-semibold text-gray-700 mb-1">
                                Doctor Name
                            </label>
                            <input type="text" name="doctor_name" id="doctor_name" 
                                value="{{ old('doctor_name', $sale->doctor_name ?? '') }}" 
                                placeholder="e.g. Dr. Sharma (Default: SELF)"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                        </div>

                        <!-- Doctor Address -->
                        <div>
                            <label for="doctor_address" class="block text-sm font-semibold text-gray-700 mb-1">
                                Doctor Address / Clinic
                            </label>
                            <input type="text" name="doctor_address" id="doctor_address" 
                                value="{{ old('doctor_address', $sale->doctor_address ?? '') }}" 
                                placeholder="Doctor Clinic or Hospital (optional)"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 border-t border-gray-200 pt-5">
                        <a href="{{ route('sales.show', $sale->id) }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2.5 px-5 rounded-md shadow-sm text-sm">
                            Cancel
                        </a>
                        <button type="submit" class="bg-medical-primary hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-md shadow-md text-sm flex items-center">
                            <i class="fas fa-save mr-2"></i> Update Patient Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Customer live search
            $('#customer_search_input').on('input', function() {
                let query = $(this).val();
                if(query.length < 2) {
                    $('#customer_search_results').addClass('hidden');
                    return;
                }

                $.get('/api/customer/search', {q: query}, function(data) {
                    let html = '';
                    if(data.length === 0) {
                        html = '<div class="p-3 text-gray-500 text-sm">No customers found.</div>';
                    } else {
                        data.forEach(c => {
                            html += `<div class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 select-cust-item" 
                                        data-id="${c.id}" 
                                        data-name="${c.name}" 
                                        data-phone="${c.phone || ''}" 
                                        data-address="${c.address || ''}">
                                        <p class="font-bold text-gray-800 text-sm">${c.name} <span class="text-xs font-normal text-gray-500">(${c.phone || 'No Phone'})</span></p>
                                        ${c.address ? `<p class="text-xs text-gray-400 truncate">${c.address}</p>` : ''}
                                     </div>`;
                        });
                    }
                    $('#customer_search_results').html(html).removeClass('hidden');
                });
            });

            // Select customer from search results
            $(document).on('click', '.select-cust-item', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let phone = $(this).data('phone');
                let address = $(this).data('address');

                $('#edit_customer_id').val(id);
                $('#patient_name').val(name);
                if(phone) $('#patient_phone').val(phone);
                if(address) $('#patient_address').val(address);

                $('#linked_customer_name').text(name);
                $('#linked_customer_phone').text(phone || 'No phone');
                $('#linked_customer_badge').removeClass('hidden');

                $('#customer_search_input').val('');
                $('#customer_search_results').addClass('hidden');
            });

            // Unlink customer
            $('#btn_clear_linked_customer').click(function() {
                $('#edit_customer_id').val('');
                $('#linked_customer_badge').addClass('hidden');
            });

            // Click outside hide search dropdown
            $(document).click(function(e) {
                if(!$(e.target).closest('#customer_search_input, #customer_search_results').length) {
                    $('#customer_search_results').addClass('hidden');
                }
            });
        });
    </script>
</x-admin-layout>
