<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profit / Loss Report') }}
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
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Profit</th>
                            </tr>
                        </thead>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <link href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script>
        $(function () {
            let table = $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("reports.profit") }}',
                    data: function (d) {
                        d.branch_id = $('#filter_branch').val();
                        d.from_date = $('#filter_from').val();
                        d.to_date = $('#filter_to').val();
                    }
                },
                columns: [
                    {data: 'sale_date', name: 'sale.sale_date'},
                    {data: 'sale.invoice_number', name: 'sale.invoice_number'},
                    {data: 'medicine.name', name: 'medicine.name'},
                    {data: 'quantity', name: 'quantity'},
                    {data: 'purchase_cost', name: 'purchase_cost', render: $.fn.dataTable.render.number(',', '.', 2, '{{ setting('currency_symbol', '₹') }}')},
                    {data: 'sale_revenue', name: 'sale_revenue', render: $.fn.dataTable.render.number(',', '.', 2, '{{ setting('currency_symbol', '₹') }}')},
                    {data: 'profit', name: 'profit', render: $.fn.dataTable.render.number(',', '.', 2, '{{ setting('currency_symbol', '₹') }}')},
                ]
            });

            $('#btn_filter').click(function(){ table.draw(); });
            
            function buildExportUrl(type) {
                let url = '{{ route("reports.profit") }}?export=' + type;
                if($('#filter_branch').length) url += '&branch_id=' + $('#filter_branch').val();
                url += '&from_date=' + $('#filter_from').val() + '&to_date=' + $('#filter_to').val();
                return url;
            }

            $('#btn_export_pdf').click(function(){ window.open(buildExportUrl('pdf'), '_blank'); });
            $('#btn_export_excel').click(function(){ window.open(buildExportUrl('excel'), '_blank'); });
        });
    </script>
</x-admin-layout>
