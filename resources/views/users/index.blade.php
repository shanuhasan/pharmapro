<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Team members</h3>
                            <p class="text-xs text-slate-500">People who can sign in to this pharmacy, their role and branch.</p>
                        </div>
                        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-b from-teal-500 to-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/30 hover:brightness-105 transition">
                            <i class="fas fa-user-plus text-xs"></i> Add New User
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200" id="users-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
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
            $('#users-table').DataTable({
                processing: true,
                language: { search: '', searchPlaceholder: 'Search name or email…', emptyTable: 'No users yet' },
                serverSide: true,
                ajax: '{{ route('users.index') }}',
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email', className: 'text-slate-500'},
                    {data: 'role', name: 'role'},
                    {data: 'branch', name: 'branch.name'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        });
    </script>
</x-admin-layout>
