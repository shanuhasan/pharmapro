<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pharmacies') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">All Registered Pharmacies</h3>
                <a href="{{ route('super_admin.pharmacies.create') }}" class="inline-flex items-center px-4 py-2.5 bg-medical-primary text-sm font-semibold text-white">
                    <i class="fas fa-plus mr-2"></i> Create Pharmacy
                </a>
            </div>



            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires In</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pharmacies as $pharmacy)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pharmacy->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pharmacy->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pharmacy->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pharmacy->phone ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $pharmacy->created_at ? $pharmacy->created_at->format('Y-m-d') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php($days = $pharmacy->membershipDaysLeft())
                                        @if(is_null($days))
                                            -
                                        @else
                                            @if($days < 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                                            @elseif($days <= 30)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">{{ $days }} days</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $days }} days</span>
                                            @endif
                                            <span class="block text-[11px] text-slate-400 mt-0.5">until {{ $pharmacy->membershipEndsAt()->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($pharmacy->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <a href="{{ route('super_admin.pharmacies.edit', $pharmacy) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-teal-300 hover:text-teal-700" title="Edit pharmacy">
                                                <i class="fas fa-pen-to-square"></i> Edit
                                            </a>
                                            <a href="{{ route('super_admin.pharmacies.edit', $pharmacy) }}#renew" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold {{ ($pharmacy->membershipDaysLeft() ?? 99) <= 30 ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'text-teal-700 hover:bg-teal-50' }}" title="Renew membership">
                                                <i class="fas fa-rotate"></i> Renew
                                            </a>
                                            <form action="{{ route('super_admin.pharmacies.toggleStatus', $pharmacy->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold {{ $pharmacy->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-700 hover:bg-emerald-50' }}" onclick="return confirm('Are you sure you want to change the status of this pharmacy?');">
                                                    <i class="fas {{ $pharmacy->is_active ? 'fa-ban' : 'fa-circle-check' }}"></i> {{ $pharmacy->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No pharmacies found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
