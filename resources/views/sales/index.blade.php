<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoices List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            


            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">All Invoices</h3>
                        <a href="{{ route('pos.index') }}" class="bg-medical-primary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-plus mr-2"></i> New Sale (POS)
                        </a>
                    </div>
                    
                    <table class="min-w-full divide-y divide-gray-200" id="sales-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="indexEditPatientModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeIndexEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="indexEditPatientForm" onsubmit="saveIndexPatientDetails(event)">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="idx_modal_sale_id">
                    <input type="hidden" id="idx_modal_customer_id">
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-user-edit text-blue-600 mr-2"></i> Edit Patient Details (<span id="idx_modal_invoice_no" class="text-blue-600"></span>)
                            </h3>
                            <button type="button" onclick="closeIndexEditModal()" class="text-gray-400 hover:text-gray-500 text-xl font-bold">&times;</button>
                        </div>

                        <div id="idx_modal_alert" class="hidden mb-4 p-3 rounded text-sm"></div>

                        <!-- Optional customer link -->
                        <div class="mb-4 bg-gray-50 p-3 rounded border border-gray-200">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Link Registered Customer (Optional)</label>
                            <input type="text" id="idx_modal_cust_search" placeholder="Type name or phone to search..." class="w-full text-xs p-2 border border-gray-300 rounded">
                            <div id="idx_modal_cust_results" class="hidden mt-1 bg-white border border-gray-200 rounded max-h-32 overflow-y-auto shadow-sm text-xs"></div>
                            <div id="idx_modal_linked_badge" class="hidden mt-2 text-xs text-blue-700 flex justify-between items-center">
                                <span>Linked Customer: <strong id="idx_modal_linked_name"></strong></span>
                                <button type="button" onclick="unlinkIndexCustomer()" class="text-red-500 hover:underline">Unlink</button>
                            </div>
                        </div>

                        <!-- Patient Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Patient Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="patient_name" id="idx_modal_patient_name" required 
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" placeholder="Patient Name">
                        </div>

                        <!-- Patient Phone -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Patient Phone</label>
                            <input type="text" name="patient_phone" id="idx_modal_patient_phone" 
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" placeholder="Phone Number">
                        </div>

                        <!-- Patient Address -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Patient Address</label>
                            <textarea name="patient_address" id="idx_modal_patient_address" rows="2" 
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" placeholder="Address"></textarea>
                        </div>

                        <!-- Doctor Name -->
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Doctor Name</label>
                            <input type="text" name="doctor_name" id="idx_modal_doctor_name" 
                                class="w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" placeholder="e.g. Dr. Sharma">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" id="idx_modal_save_btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            <i class="fas fa-save mr-1.5 mt-0.5"></i> Save Changes
                        </button>
                        <button type="button" onclick="closeIndexEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DataTables CSS/JS -->
    <link href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script>
        let salesTable;
        $(function () {
            salesTable = $('#sales-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('sales.index') }}',
                columns: [
                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'sale_date', name: 'sale_date'},
                    {data: 'branch', name: 'branch.name'},
                    {data: 'customer', name: 'customer.name'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            // Handle Edit Patient Click from DataTable
            $(document).on('click', '.edit-patient-btn', function() {
                let saleId = $(this).data('id');
                let invoice = $(this).data('invoice');
                let name = $(this).data('name');
                let phone = $(this).data('phone');
                let address = $(this).data('address');
                let doctor = $(this).data('doctor');
                let custId = $(this).data('customer-id');

                $('#idx_modal_sale_id').val(saleId);
                $('#idx_modal_invoice_no').text(invoice);
                $('#idx_modal_patient_name').val(name);
                $('#idx_modal_patient_phone').val(phone);
                $('#idx_modal_patient_address').val(address);
                $('#idx_modal_doctor_name').val(doctor);
                $('#idx_modal_customer_id').val(custId || '');

                if(custId && name) {
                    $('#idx_modal_linked_name').text(name);
                    $('#idx_modal_linked_badge').removeClass('hidden');
                } else {
                    $('#idx_modal_linked_badge').addClass('hidden');
                }

                $('#idx_modal_alert').addClass('hidden');
                $('#idx_modal_cust_search').val('');
                $('#idx_modal_cust_results').addClass('hidden');

                $('#indexEditPatientModal').removeClass('hidden');
                $('#idx_modal_patient_name').focus();
            });

            // Customer live search in modal
            $('#idx_modal_cust_search').on('input', function() {
                let q = $(this).val();
                if(q.length < 2) {
                    $('#idx_modal_cust_results').addClass('hidden');
                    return;
                }
                $.get('/api/customer/search', {q: q}, function(data) {
                    let html = '';
                    if(data.length === 0) {
                        html = '<div class="p-2 text-gray-500">No customers found.</div>';
                    } else {
                        data.forEach(c => {
                            html += `<div class="p-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 idx-cust-select" 
                                        data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone || ''}" data-address="${c.address || ''}">
                                        <strong>${c.name}</strong> <span class="text-gray-500">(${c.phone || 'No phone'})</span>
                                     </div>`;
                        });
                    }
                    $('#idx_modal_cust_results').html(html).removeClass('hidden');
                });
            });

            $(document).on('click', '.idx-cust-select', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let phone = $(this).data('phone');
                let address = $(this).data('address');

                $('#idx_modal_customer_id').val(id);
                $('#idx_modal_patient_name').val(name);
                if(phone) $('#idx_modal_patient_phone').val(phone);
                if(address) $('#idx_modal_patient_address').val(address);

                $('#idx_modal_linked_name').text(name);
                $('#idx_modal_linked_badge').removeClass('hidden');
                $('#idx_modal_cust_search').val('');
                $('#idx_modal_cust_results').addClass('hidden');
            });
        });

        function closeIndexEditModal() {
            $('#indexEditPatientModal').addClass('hidden');
        }

        function unlinkIndexCustomer() {
            $('#idx_modal_customer_id').val('');
            $('#idx_modal_linked_badge').addClass('hidden');
        }

        function saveIndexPatientDetails(e) {
            e.preventDefault();
            let saleId = $('#idx_modal_sale_id').val();
            let btn = $('#idx_modal_save_btn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            let payload = {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                customer_id: $('#idx_modal_customer_id').val(),
                patient_name: $('#idx_modal_patient_name').val(),
                patient_phone: $('#idx_modal_patient_phone').val(),
                patient_address: $('#idx_modal_patient_address').val(),
                doctor_name: $('#idx_modal_doctor_name').val()
            };

            $.ajax({
                url: '/sales/' + saleId,
                type: 'POST',
                data: payload,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1.5"></i> Save Changes');
                    if(res.success) {
                        closeIndexEditModal();
                        salesTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1.5"></i> Save Changes');
                    let msg = 'Failed to update patient details.';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#idx_modal_alert').removeClass('hidden').addClass('bg-red-100 text-red-700').text(msg);
                }
            });
        }
    </script>
</x-admin-layout>
