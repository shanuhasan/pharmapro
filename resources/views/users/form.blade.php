<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->exists ? 'Edit User' : 'Add New User' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST">
                        @csrf
                        @if($user->exists)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Name -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Role -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <select name="role" id="role_select" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="cashier" {{ old('role', $user->role) === 'cashier' ? 'selected' : '' }}>Cashier</option>
                                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Branch Manager</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                                @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Branch -->
                            <div class="mb-4" id="branch_container">
                                <label class="block text-sm font-medium text-gray-700">Assign to Branch</label>
                                <select name="branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Branch...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Password {{ $user->exists ? '(Leave blank to keep current)' : '' }}</label>
                                <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ !$user->exists ? 'required' : '' }}>
                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ !$user->exists ? 'required' : '' }}>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('users.index') }}" class="bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded shadow hover:bg-gray-400 mr-3">Cancel</a>
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded shadow hover:bg-blue-700">
                                {{ $user->exists ? 'Update User' : 'Create User' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role_select');
            const branchContainer = document.getElementById('branch_container');
            const branchSelect = document.getElementById('branch_id');

            function toggleBranch() {
                if (roleSelect.value === 'admin') {
                    branchContainer.style.display = 'none';
                    branchSelect.value = '';
                    branchSelect.removeAttribute('required');
                } else {
                    branchContainer.style.display = 'block';
                    branchSelect.setAttribute('required', 'required');
                }
            }

            roleSelect.addEventListener('change', toggleBranch);
            toggleBranch(); // Initial run
        });
    </script>
</x-admin-layout>
