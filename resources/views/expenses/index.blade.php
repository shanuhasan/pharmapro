<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expenses Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                    <p class="text-sm text-gray-500 uppercase tracking-wide font-bold">This Month's Expenses</p>
                    <p class="text-3xl font-black text-red-600 mt-2">{{ setting('currency_symbol', '₹') }}{{ number_format($totalExpenses, 2) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-500 uppercase tracking-wide font-bold">This Month's Revenue</p>
                    <p class="text-3xl font-black text-blue-600 mt-2">{{ setting('currency_symbol', '₹') }}{{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 {{ $profit >= 0 ? 'border-green-500' : 'border-red-500' }}">
                    <p class="text-sm text-gray-500 uppercase tracking-wide font-bold">Net Profit (Revenue - Expense)</p>
                    <p class="text-3xl font-black {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2">{{ setting('currency_symbol', '₹') }}{{ number_format($profit, 2) }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold">Expense Records</h3>
                        <a href="{{ route('expenses.create') }}" class="bg-medical-primary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                            <i class="fas fa-plus mr-2"></i> Record Expense
                        </a>
                    </div>
                    
                    @if(auth()->user()->role === 'admin')
                    <div class="mb-4 flex space-x-4">
                        <form method="GET" action="{{ route('expenses.index') }}" class="flex space-x-2 w-full md:w-1/3">
                            <select name="branch_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-gray-600 text-white px-3 py-2 rounded text-sm">Filter</button>
                        </form>
                    </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200" id="expenses-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables CSS/JS -->
    <link href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script>
        $(function () {
            $('#expenses-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('expenses.index', ['branch_id' => request('branch_id')]) !!}',
                columns: [
                    {data: 'expense_date', name: 'expense_date'},
                    {data: 'branch.name', name: 'branch.name'},
                    {data: 'category', name: 'category'},
                    {data: 'amount', name: 'amount', className: 'text-red-600 font-bold'},
                    {data: 'description', name: 'description', orderable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
</x-admin-layout>
