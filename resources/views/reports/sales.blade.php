<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sales Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Filters -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border flex flex-col md:flex-row gap-4 items-end">
                        @if(auth()->user()->role === 'admin')
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700">Branch</label>
                            <select id="filter_branch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" id="filter_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" id="filter_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="w-full md:w-1/4 flex space-x-2">
                            <button id="btn_filter" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded shadow hover:bg-blue-700">Filter</button>
                            <button id="btn_export_pdf" class="bg-red-500 text-white font-bold py-2 px-3 rounded shadow hover:bg-red-600" title="Export PDF"><i class="fas fa-file-pdf"></i></button>
                            <button id="btn_export_excel" class="bg-green-500 text-white font-bold py-2 px-3 rounded shadow hover:bg-green-600" title="Export Excel"><i class="fas fa-file-excel"></i></button>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200" id="report-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div id="emailModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="emailForm" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Email Invoice</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Customer Email</label>
                            <input type="email" id="email_input" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Send Email</button>
                        <button type="button" class="close-email-modal mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(function () {
            let table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("reports.sales") }}',
                    data: function (d) {
                        d.branch_id = $('#filter_branch').val();
                        d.from_date = $('#filter_from').val();
                        d.to_date = $('#filter_to').val();
                    }
                },
                columns: [
                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'sale_date', name: 'sale_date'},
                    {data: 'branch.name', name: 'branch.name'},
                    {data: 'customer.name', name: 'customer.name', defaultContent: 'Walk-in'},
                    {data: 'total_amount', name: 'total_amount', className: 'font-bold text-green-600'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });

            $('#btn_filter').click(function(){
                table.draw();
            });
            
            function buildExportUrl(type) {
                let url = '{{ route("reports.sales") }}?export=' + type;
                if($('#filter_branch').length) url += '&branch_id=' + $('#filter_branch').val();
                url += '&from_date=' + $('#filter_from').val() + '&to_date=' + $('#filter_to').val();
                return url;
            }

            $('#btn_export_pdf').click(function(){ window.open(buildExportUrl('pdf'), '_blank'); });
            $('#btn_export_excel').click(function(){ window.open(buildExportUrl('excel'), '_blank'); });

            // Email Modal logic
            $(document).on('click', '.email-invoice', function() {
                let id = $(this).data('id');
                $('#emailForm').attr('action', '/invoice/' + id + '/email');
                $('#email_input').val('');
                $('#emailModal').removeClass('hidden');
            });

            $('.close-email-modal').click(function() {
                $('#emailModal').addClass('hidden');
            });
        });
    </script>
</x-admin-layout>
