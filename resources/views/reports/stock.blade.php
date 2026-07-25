<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Current Stock Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border flex flex-col md:flex-row gap-4 items-end">
                        @if(auth()->user()->role === 'admin')
                        <div class="w-full md:w-1/3">
                            <label class="block text-sm font-medium text-gray-700">Branch</label>
                            <select id="filter_branch" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="w-full md:w-1/3 flex space-x-2">
                            <button id="btn_filter" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded shadow hover:bg-blue-700">Filter</button>
                            <button id="btn_export_pdf" class="bg-red-500 text-white font-bold py-2 px-3 rounded shadow hover:bg-red-600" title="Export PDF"><i class="fas fa-file-pdf"></i></button>
                            <button id="btn_export_excel" class="bg-green-500 text-white font-bold py-2 px-3 rounded shadow hover:bg-green-600" title="Export Excel"><i class="fas fa-file-excel"></i></button>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200" id="report-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
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
                    url: '{{ route("reports.stock") }}',
                    data: function (d) {
                        d.branch_id = $('#filter_branch').val();
                    }
                },
                columns: [
                    {data: 'medicine.name', name: 'medicine.name'},
                    {data: 'medicine.category.name', name: 'medicine.category.name', defaultContent: '-'},
                    {data: 'branch.name', name: 'branch.name'},
                    {data: 'batch_number', name: 'batch_number'},
                    {data: 'quantity', name: 'quantity', className: 'font-bold'},
                    {data: 'expiry_date', name: 'expiry_date'},
                ]
            });

            $('#btn_filter').click(function(){ table.draw(); });
            
            function buildExportUrl(type) {
                let url = '{{ route("reports.stock") }}?export=' + type;
                if($('#filter_branch').length) url += '&branch_id=' + $('#filter_branch').val();
                return url;
            }

            $('#btn_export_pdf').click(function(){ window.open(buildExportUrl('pdf'), '_blank'); });
            $('#btn_export_excel').click(function(){ window.open(buildExportUrl('excel'), '_blank'); });
        });
    </script>
</x-admin-layout>
