<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        @if(auth()->user()->role === 'admin')
                        <div class="w-1/3">
                            <label for="branch_filter" class="block text-sm font-medium text-gray-700">Filter by Branch</label>
                            <select id="branch_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    
                    <table class="min-w-full divide-y divide-gray-200" id="stock-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Adjust Modal -->
    <div id="adjustModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="adjustForm">
                    @csrf
                    <input type="hidden" id="adjust_stock_id">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Adjust Stock Quantity</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">New Quantity</label>
                            <input type="number" id="adjust_qty" name="new_quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required min="0">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Reason</label>
                            <input type="text" id="adjust_reason" name="reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="e.g. Damaged, Expired, Audit Correction" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save Adjustment</button>
                        <button type="button" class="close-modal mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="transferForm">
                    @csrf
                    <input type="hidden" id="transfer_stock_id">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Transfer Stock to Branch</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Destination Branch</label>
                            <select id="transfer_dest" name="destination_branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Transfer Quantity</label>
                            <input type="number" id="transfer_qty" name="transfer_quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required min="1">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Reason / Notes</label>
                            <input type="text" id="transfer_reason" name="reason" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Transfer Stock</button>
                        <button type="button" class="close-modal mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DataTables CSS/JS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(function () {
            var table = $('#stock-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('stock.index') }}',
                    data: function (d) {
                        d.branch_id = $('#branch_filter').val();
                    }
                },
                columns: [
                    {data: 'branch', name: 'branch.name'},
                    {data: 'medicine', name: 'medicine.name'},
                    {data: 'batch_number', name: 'batch_number'},
                    {data: 'expiry_date', name: 'expiry_date'},
                    {data: 'quantity', name: 'quantity'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            $('#branch_filter').change(function(){
                table.draw();
            });

            // Adjust Modal
            $(document).on('click', '.adjust-stock', function() {
                $('#adjust_stock_id').val($(this).data('id'));
                $('#adjust_qty').val($(this).data('qty'));
                $('#adjust_reason').val('');
                $('#adjustModal').removeClass('hidden');
            });

            // Transfer Modal
            $(document).on('click', '.transfer-stock', function() {
                $('#transfer_stock_id').val($(this).data('id'));
                $('#transfer_qty').val($(this).data('qty'));
                $('#transfer_qty').attr('max', $(this).data('qty'));
                $('#transfer_dest').val('');
                $('#transfer_reason').val('');
                $('#transferModal').removeClass('hidden');
            });

            $('.close-modal').click(function() {
                $('#adjustModal').addClass('hidden');
                $('#transferModal').addClass('hidden');
            });

            // Handle Adjust Form
            $('#adjustForm').submit(function(e) {
                e.preventDefault();
                var id = $('#adjust_stock_id').val();
                $.ajax({
                    url: '/stock/' + id + '/adjust',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#adjustModal').addClass('hidden');
                        table.draw();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                });
            });

            // Handle Transfer Form
            $('#transferForm').submit(function(e) {
                e.preventDefault();
                var id = $('#transfer_stock_id').val();
                $.ajax({
                    url: '/stock/' + id + '/transfer',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#transferModal').addClass('hidden');
                        table.draw();
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
</x-admin-layout>
