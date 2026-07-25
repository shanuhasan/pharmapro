<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700">Supplier</label>
                            <select id="filter_supplier" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
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
                    url: '{{ route("reports.purchases") }}',
                    data: function (d) {
                        d.supplier_id = $('#filter_supplier').val();
                        d.from_date = $('#filter_from').val();
                        d.to_date = $('#filter_to').val();
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'purchase_date', name: 'purchase_date'},
                    {data: 'branch.name', name: 'branch.name'},
                    {data: 'supplier.name', name: 'supplier.name', defaultContent: 'Unknown'},
                    {data: 'total_amount', name: 'total_amount', className: 'font-bold text-blue-600'},
                ]
            });

            $('#btn_filter').click(function(){ table.draw(); });
            
            function buildExportUrl(type) {
                let url = '{{ route("reports.purchases") }}?export=' + type;
                url += '&supplier_id=' + $('#filter_supplier').val();
                url += '&from_date=' + $('#filter_from').val() + '&to_date=' + $('#filter_to').val();
                return url;
            }

            $('#btn_export_pdf').click(function(){ window.open(buildExportUrl('pdf'), '_blank'); });
            $('#btn_export_excel').click(function(){ window.open(buildExportUrl('excel'), '_blank'); });
        });
    </script>
</x-admin-layout>
