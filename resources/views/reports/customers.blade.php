<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Top Customers Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-6 flex justify-end space-x-2">
                        <button id="btn_export_pdf" class="bg-red-500 text-white font-bold py-2 px-4 rounded shadow hover:bg-red-600"><i class="fas fa-file-pdf mr-2"></i> Export PDF</button>
                        <button id="btn_export_excel" class="bg-green-500 text-white font-bold py-2 px-4 rounded shadow hover:bg-green-600"><i class="fas fa-file-excel mr-2"></i> Export Excel</button>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200" id="report-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Purchases</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent</th>
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
            $('#report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("reports.customers") }}',
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'phone', name: 'phone'},
                    {data: 'sales_count', name: 'sales_count', searchable: false},
                    {data: 'sales_sum_total_amount', name: 'sales_sum_total_amount', searchable: false, className: 'font-bold text-blue-600'},
                ],
                order: [[3, 'desc']] // Order by spent amount
            });

            $('#btn_export_pdf').click(function(){ window.open('{{ route("reports.customers") }}?export=pdf', '_blank'); });
            $('#btn_export_excel').click(function(){ window.open('{{ route("reports.customers") }}?export=excel', '_blank'); });
        });
    </script>
</x-admin-layout>
