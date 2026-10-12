<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('View Invoice') }} - {{ $sale->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            



            <!-- Action Buttons -->
            <div class="mb-4 flex flex-wrap justify-end gap-2">
                <button type="button" onclick="openEditModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
                    <i class="fas fa-user-edit mr-2"></i> Edit Patient
                </button>
                <a href="{{ route('sales.edit', $sale->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
                    <i class="fas fa-edit mr-2"></i> Full Edit Page
                </a>
                <a href="{{ route('invoice.print', $sale->id) }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </a>
                <a href="{{ route('invoice.pdf', $sale->id) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Download PDF
                </a>
                @if($sale->customer && $sale->customer->email)
                    <form action="{{ route('invoice.email', $sale->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="email" value="{{ $sale->customer->email }}">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow flex items-center">
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
                        <div class="flex items-center space-x-2 mb-1">
                            <p class="text-gray-500 text-sm font-bold uppercase tracking-wider">Billed To</p>
                            <button type="button" onclick="openEditModal()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center px-1.5 py-0.5 rounded hover:bg-blue-50 transition">
                                <i class="fas fa-pencil-alt mr-1"></i> Edit
                            </button>
                        </div>
                        @php
                            $dispName = $sale->customer_name ?: ($sale->customer->name ?? 'Walk-in Customer');
                            $dispPhone = $sale->customer_phone ?: ($sale->customer->phone ?? null);
                            $dispEmail = $sale->customer->email ?? null;
                            $dispAddress = $sale->customer_address ?: ($sale->customer->address ?? null);
                        @endphp
                        <p class="font-bold text-gray-800 text-lg" id="disp_patient_name">{{ $dispName }}</p>
                        <p class="text-gray-600" id="disp_patient_phone">{{ $dispPhone ?? '' }}</p>
                        @if($dispEmail)
                            <p class="text-gray-600" id="disp_patient_email">{{ $dispEmail }}</p>
                        @endif
                        <p class="text-gray-600" id="disp_patient_address">{{ $dispAddress ?? '' }}</p>
                        @if($sale->doctor_name)
                            <p class="text-gray-500 text-sm mt-2" id="disp_doctor_name"><span class="font-semibold">Doctor:</span> {{ $sale->doctor_name }}</p>
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
                            <th class="py-3 px-4 rounded-tl-lg font-bold">Products/Items</th>
                            <th class="py-3 px-4 font-bold">HSN Code</th>
                            <th class="py-3 px-4 font-bold">Batch</th>
                            <th class="py-3 px-4 font-bold">Expiry</th>
                            <th class="py-3 px-4 text-right font-bold">Price</th>
                            <th class="py-3 px-4 text-right font-bold">Qty</th>
                            <th class="py-3 px-4 text-right rounded-tr-lg font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleItems as $item)
                        <tr class="border-b border-gray-100">
                            <td class="py-4 px-4 font-medium text-gray-800">{{ $item->medicine->name }} <span class="text-xs text-gray-500 block">{{ $item->medicine->generic_name }}</span></td>
                            <td class="py-4 px-4 text-gray-600">{{ $item->hsn_code }}</td>
                            <td class="py-4 px-4 text-gray-600">{{ $item->batch_number }}</td>
                            <td class="py-4 px-4 text-gray-600">{{ $item->stock && $item->stock->expiry_date ? \Carbon\Carbon::parse($item->stock->expiry_date)->format('d-m-Y') : '-' }}</td>
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
                            <span class="text-gray-600 font-semibold">Discount ({{ $sale->subtotal > 0 ? round(($sale->discount / $sale->subtotal) * 100, 2) : 0 }}%)</span>
                            <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->discount, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600 font-semibold">SGST ({{ setting('sgst_percentage', '0') }}%)</span>
                            <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->tax / 2, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600 font-semibold">CGST ({{ setting('cgst_percentage', '0') }}%)</span>
                            <span class="text-gray-800 font-bold">{{ setting('currency_symbol', '₹') }}{{ number_format($sale->tax / 2, 2) }}</span>
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
                    <p class="font-bold text-gray-600 mb-1">{{ setting('receipt_footer_message', 'Thank you for your business!') }}</p>
                    <!-- <p>Medicines once sold cannot be returned or exchanged without a valid receipt.</p> -->
                </div>

            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editPatientForm" onsubmit="savePatientDetails(event)">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-user-edit text-blue-600 mr-2"></i> Edit Invoice Patient Details
                            </h3>
                            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500 text-xl font-bold">&times;</button>
                        </div>

                        <div id="modal_alert" class="hidden mb-4 p-3 rounded text-sm"></div>

                        <!-- Optional customer link -->
                        <div class="mb-4 bg-gray-50 p-3 rounded border border-gray-200">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Link Registered Customer (Optional)</label>
                            <input type="text" id="modal_cust_search" placeholder="Search registered customer..." class="w-full text-xs p-2 border border-gray-300 rounded">
                            <div id="modal_cust_results" class="hidden mt-1 bg-white border border-gray-200 rounded max-h-32 overflow-y-auto shadow-sm text-xs"></div>
                            <input type="hidden" id="modal_customer_id" name="customer_id" value="{{ $sale->customer_id }}">
                            <div id="modal_linked_badge" class="{{ $sale->customer ? '' : 'hidden' }} mt-2 text-xs text-blue-700 flex justify-between items-center">
                                <span>Linked: <strong id="modal_linked_name">{{ $sale->customer->name ?? '' }}</strong></span>
                                <button type="button" onclick="unlinkCustomerModal()" class="text-red-500 hover:underline">Unlink</button>
                            </div>
                        </div>

                        <!-- Patient Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Patient Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="patient_name" id="modal_patient_name" 
                                value="{{ $sale->customer_name ?: ($sale->customer->name ?? '') }}" 
                                required class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2">
                        </div>

                        <!-- Patient Phone -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Patient Phone</label>
                            <input type="text" name="patient_phone" id="modal_patient_phone" 
                                value="{{ $sale->customer_phone ?: ($sale->customer->phone ?? '') }}" 
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2">
                        </div>

                        <!-- Patient Address -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Patient Address</label>
                            <textarea name="patient_address" id="modal_patient_address" rows="2" 
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2">{{ $sale->customer_address ?: ($sale->customer->address ?? '') }}</textarea>
                        </div>

                        <!-- Doctor Name -->
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Doctor Name</label>
                            <input type="text" name="doctor_name" id="modal_doctor_name" 
                                value="{{ $sale->doctor_name ?? '' }}" 
                                placeholder="e.g. Dr. Sharma (Default: SELF)"
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" id="modal_save_btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            <i class="fas fa-save mr-1.5 mt-0.5"></i> Save Changes
                        </button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal() {
            $('#modal_alert').addClass('hidden');
            $('#editPatientModal').removeClass('hidden');
            $('#modal_patient_name').focus();
        }

        function closeEditModal() {
            $('#editPatientModal').addClass('hidden');
        }

        function unlinkCustomerModal() {
            $('#modal_customer_id').val('');
            $('#modal_linked_badge').addClass('hidden');
        }

        // Live customer search inside modal
        $('#modal_cust_search').on('input', function() {
            let q = $(this).val();
            if(q.length < 2) {
                $('#modal_cust_results').addClass('hidden');
                return;
            }
            $.get('/api/customer/search', {q: q}, function(data) {
                let html = '';
                if(data.length === 0) {
                    html = '<div class="p-2 text-gray-500">No customers found.</div>';
                } else {
                    data.forEach(c => {
                        html += `<div class="p-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 modal-cust-select" 
                                    data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone || ''}" data-address="${c.address || ''}">
                                    <strong>${c.name}</strong> <span class="text-gray-500">(${c.phone || 'No phone'})</span>
                                 </div>`;
                    });
                }
                $('#modal_cust_results').html(html).removeClass('hidden');
            });
        });

        $(document).on('click', '.modal-cust-select', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let phone = $(this).data('phone');
            let address = $(this).data('address');

            $('#modal_customer_id').val(id);
            $('#modal_patient_name').val(name);
            if(phone) $('#modal_patient_phone').val(phone);
            if(address) $('#modal_patient_address').val(address);

            $('#modal_linked_name').text(name);
            $('#modal_linked_badge').removeClass('hidden');
            $('#modal_cust_search').val('');
            $('#modal_cust_results').addClass('hidden');
        });

        function savePatientDetails(e) {
            e.preventDefault();
            let btn = $('#modal_save_btn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            let payload = {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                customer_id: $('#modal_customer_id').val(),
                patient_name: $('#modal_patient_name').val(),
                patient_phone: $('#modal_patient_phone').val(),
                patient_address: $('#modal_patient_address').val(),
                doctor_name: $('#modal_doctor_name').val()
            };

            $.ajax({
                url: '{{ route('sales.update', $sale->id) }}',
                type: 'POST',
                data: payload,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1.5"></i> Save Changes');
                    if(res.success) {
                        $('#disp_patient_name').text(payload.patient_name);
                        $('#disp_patient_phone').text(payload.patient_phone);
                        $('#disp_patient_address').text(payload.patient_address);
                        if(payload.doctor_name) {
                            if($('#disp_doctor_name').length) {
                                $('#disp_doctor_name').html('<span class="font-semibold">Doctor:</span> ' + payload.doctor_name);
                            } else {
                                $('#disp_patient_address').after('<p class="text-gray-500 text-sm mt-2" id="disp_doctor_name"><span class="font-semibold">Doctor:</span> ' + payload.doctor_name + '</p>');
                            }
                        }
                        closeEditModal();
                        alert('Patient details updated successfully!');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1.5"></i> Save Changes');
                    let msg = 'Failed to update patient details.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#modal_alert').removeClass('hidden').addClass('bg-red-100 text-red-700').text(msg);
                }
            });
        }
    </script>
</x-admin-layout>
