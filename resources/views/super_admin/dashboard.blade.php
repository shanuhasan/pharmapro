<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Pharmacies -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-b-4 border-indigo-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-500">
                            <i class="fas fa-hospital fa-2x"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-semibold uppercase">Total Pharmacies</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $pharmaciesCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Manage System</h3>
                <a href="{{ route('super_admin.pharmacies') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    View All Pharmacies
                </a>
            </div>
            
        </div>
    </div>
</x-admin-layout>
