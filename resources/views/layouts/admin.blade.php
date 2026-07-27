<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('pharmacy_name', config('app.name', 'PharmaPro')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- FontAwesome -->
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

        <!-- jQuery and Select2 -->
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
        <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('select:not(.hidden):not(.no-select2)').select2({
                    width: '100%'
                });
            });
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            
            /* Select2 Tailwind CSS styling */
            .select2-container .select2-selection--single {
                height: 38px !important;
                border: 1px solid #d1d5db !important;
                border-radius: 0.375rem !important;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
                padding-top: 0.35rem;
                padding-left: 0.5rem;
                font-size: 0.875rem !important;
                background-color: #fff;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
                right: 5px !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #111827 !important;
                line-height: 22px !important;
                padding-left: 0 !important;
            }
            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #6366f1 !important;
                box-shadow: 0 0 0 1px #6366f1 !important;
                outline: 0 !important;
            }
            .select2-dropdown {
                border-color: #d1d5db !important;
                border-radius: 0.375rem !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
                margin-top: 2px;
                z-index: 9999;
            }
            .select2-search__field {
                border-radius: 0.375rem !important;
                border: 1px solid #d1d5db !important;
                padding: 0.375rem 0.75rem !important;
                font-size: 0.875rem !important;
            }
            .select2-search__field:focus {
                border-color: #6366f1 !important;
                box-shadow: 0 0 0 1px #6366f1 !important;
                outline: 0 !important;
            }
            .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
                background-color: #6366f1 !important;
                color: white !important;
            }
            .select2-results__option {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.875rem !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
        <div class="flex h-screen overflow-hidden">
            
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
                
                <!-- Top Header -->
                <header class="sticky top-0 z-30 flex items-center justify-between px-6 py-4 bg-white shadow-md">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- User Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>{{ Auth::user()->name ?? 'User' }}</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Page Header -->
                @isset($header)
                    <div class="bg-white border-b border-gray-200 px-6 py-4">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="px-6 pt-6">
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="px-6 pt-6">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Main Content Body -->
                <main class="p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
