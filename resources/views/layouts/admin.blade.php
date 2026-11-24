<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('pharmacy_name', config('app.name', 'PharmaPro')) }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <link rel="apple-touch-icon" href="{{ asset('images/logo-mark.svg') }}">

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
    <body class="app-theme font-sans antialiased" x-data="{ sidebarOpen: false }">
        <div class="flex h-screen overflow-hidden">

            <!-- Mobile sidebar backdrop -->
            <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm lg:hidden" x-cloak></div>

            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <div class="relative flex flex-col flex-1 overflow-hidden">

                <!-- Scrollable Area -->
                <div class="app-scroll flex flex-col flex-1 overflow-y-auto overflow-x-hidden">

                    <!-- Top Bar -->
                    <header class="sticky top-0 z-30 flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 h-16 bg-white/80 backdrop-blur-xl border-b border-slate-200/70">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden w-9 h-9 grid place-items-center rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none" aria-label="Toggle menu">
                                <i class="fas fa-bars"></i>
                            </button>
                            <div class="app-page-title flex-1 min-w-0">
                                @isset($header)
                                    {{ $header }}
                                @endisset
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                            <span class="hidden lg:inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500">
                                <i class="far fa-calendar text-teal-600"></i> {{ now()->format('D, d M Y') }}
                            </span>
                            @if(Route::has('pos.index') && auth()->user()->role !== 'super_admin' && !request()->routeIs('pos.*'))
                                <a href="{{ route('pos.index') }}" class="hidden sm:inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold text-white bg-gradient-to-b from-teal-500 to-teal-600 shadow-sm shadow-teal-600/30 hover:brightness-105 transition">
                                    <i class="fas fa-cash-register text-xs"></i> New Sale
                                </a>
                            @endif

                            <!-- User Dropdown -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center gap-2.5 rounded-full pl-1 pr-2.5 py-1 hover:bg-slate-100 transition focus:outline-none">
                                        <span class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-emerald-600 grid place-items-center text-white text-sm font-semibold">
                                            {{ strtoupper(mb_substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                        </span>
                                        <span class="hidden sm:block text-left leading-tight">
                                            <span class="block text-sm font-semibold text-slate-800">{{ Auth::user()->name ?? 'User' }}</span>
                                            <span class="block text-[11px] text-slate-500 capitalize">{{ str_replace('_', ' ', Auth::user()->role ?? '') }}</span>
                                        </span>
                                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        <i class="far fa-user mr-2 text-slate-400"></i>{{ __('Profile') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            <i class="fas fa-arrow-right-from-bracket mr-2 text-slate-400"></i>{{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </header>

                    <!-- Free-trial banner for pharmacy users -->
                    @php($tenant = auth()->user()->role !== 'super_admin' ? auth()->user()->pharmacy : null)
                    @if($tenant && $tenant->on_trial && !$tenant->isMembershipExpired())
                        @php($trialLeft = $tenant->membershipDaysLeft())
                        <div class="px-4 sm:px-6 lg:px-8 pt-6">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border px-4 py-3 shadow-sm {{ $trialLeft <= 3 ? 'border-amber-200 bg-amber-50/80 text-amber-900' : 'border-sky-200 bg-sky-50/80 text-sky-900' }}">
                                <span class="w-8 h-8 shrink-0 rounded-lg grid place-items-center {{ $trialLeft <= 3 ? 'bg-amber-500' : 'bg-sky-500' }} text-white"><i class="fas fa-flask text-sm"></i></span>
                                <p class="flex-1 text-sm">
                                    <span class="font-semibold">Free trial:</span>
                                    {{ $trialLeft === 0 ? 'last day today' : $trialLeft . ' ' . str('day')->plural($trialLeft) . ' left' }}
                                    <span class="opacity-75">(ends {{ $tenant->membershipEndsAt()->format('d M Y') }}).</span>
                                    Contact the administrator to activate a paid membership and keep using {{ setting('pharmacy_name', 'PharmaPro') }} without interruption.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="px-4 sm:px-6 lg:px-8 pt-6" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 6000)">
                            <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-emerald-800 shadow-sm" role="status">
                                <span class="mt-0.5 w-6 h-6 shrink-0 rounded-full bg-emerald-500 text-white grid place-items-center"><i class="fas fa-check text-[11px]"></i></span>
                                <span class="flex-1 text-sm font-medium pt-0.5">{{ session('success') }}</span>
                                <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 focus:outline-none" aria-label="Dismiss"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="px-4 sm:px-6 lg:px-8 pt-6" x-data="{ show: true }" x-show="show" x-transition>
                            <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/80 px-4 py-3 text-rose-800 shadow-sm" role="status">
                                <span class="mt-0.5 w-6 h-6 shrink-0 rounded-full bg-rose-500 text-white grid place-items-center"><i class="fas fa-exclamation text-[11px]"></i></span>
                                <span class="flex-1 text-sm font-medium pt-0.5">{{ session('error') }}</span>
                                <button @click="show = false" class="text-rose-600 hover:text-rose-900 focus:outline-none" aria-label="Dismiss"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    @endif

                    <!-- Main Content Body -->
                    <main class="app-main px-4 sm:px-6 lg:px-8 py-6 flex-grow">
                        {{ $slot }}
                    </main>

                    <!-- Footer -->
                    <footer class="px-4 sm:px-6 lg:px-8 py-5 border-t border-slate-200/70 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                        <p>&copy; {{ date('Y') }} {{ auth()->user()->role === 'super_admin' ? 'M PharmaPro' : setting('pharmacy_name', 'M PharmaPro') }}. All rights reserved.</p>
                        <p class="flex items-center gap-1.5">Powered by <x-brand-mark class="w-4 h-4" /> <span class="font-medium text-slate-600">M PharmaPro</span> · Developed by <a href="https://musheeda.com/" class="text-teal-600 hover:text-teal-700 font-medium">Musheeda</a></p>
                    </footer>

                </div> <!-- End Scrollable Area -->
            </div>
        </div>
    </body>
</html>
