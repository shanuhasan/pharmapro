<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Pharmacy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('super_admin.pharmacies.store') }}">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pharmacy Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Pharmacy Details</h3>
                                
                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="pharmacy_name">Pharmacy Name *</label>
                                    <input type="text" name="pharmacy_name" id="pharmacy_name" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" value="{{ old('pharmacy_name') }}" required>
                                    @error('pharmacy_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="pharmacy_email">Pharmacy Email *</label>
                                    <input type="email" name="pharmacy_email" id="pharmacy_email" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" value="{{ old('pharmacy_email') }}" required>
                                    @error('pharmacy_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="pharmacy_phone">Pharmacy Phone</label>
                                    <input type="text" name="pharmacy_phone" id="pharmacy_phone" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" value="{{ old('pharmacy_phone') }}">
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="pharmacy_address">Address</label>
                                    <textarea name="pharmacy_address" id="pharmacy_address" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('pharmacy_address') }}</textarea>
                                </div>
                            </div>

                            <!-- Initial Admin User Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Initial Admin User</h3>
                                
                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="admin_name">Admin Name *</label>
                                    <input type="text" name="admin_name" id="admin_name" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" value="{{ old('admin_name') }}" required>
                                    @error('admin_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="admin_email">Admin Email (Login ID) *</label>
                                    <input type="email" name="admin_email" id="admin_email" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" value="{{ old('admin_email') }}" required>
                                    @error('admin_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="admin_password">Password *</label>
                                    <input type="password" name="admin_password" id="admin_password" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required autocomplete="new-password">
                                    @error('admin_password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium text-sm text-gray-700" for="admin_password_confirmation">Confirm Password *</label>
                                    <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('super_admin.pharmacies') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 mr-3">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Create Pharmacy & User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
