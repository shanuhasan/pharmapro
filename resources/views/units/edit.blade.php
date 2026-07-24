<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('units.update', $unit->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Unit Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ $unit->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
                        </div>
                        <div class="mb-4">
                            <label for="abbreviation" class="block text-sm font-medium text-gray-700">Abbreviation <span class="text-red-500">*</span></label>
                            <input type="text" name="abbreviation" id="abbreviation" value="{{ $unit->abbreviation }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" required>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="bg-medical-primary text-white py-2 px-4 rounded">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
