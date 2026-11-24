<!-- Sidebar -->
<div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="app-sidebar fixed inset-y-0 left-0 z-30 w-64 flex flex-col overflow-y-auto transition duration-300 transform lg:translate-x-0 lg:static lg:inset-auto">
    <!-- Sidebar Header -->
    <div class="flex items-center h-16 px-5 shrink-0 border-b border-white/5">
        <a href="{{ route('dashboard') }}" class="text-white text-base font-semibold tracking-tight flex items-center min-w-0 truncate">
            @if(auth()->user()->role === 'super_admin')
                <x-brand-mark class="w-8 h-8 mr-2.5" />
                PharmaPro
            @else
                @if(setting('pharmacy_logo'))
                    <img src="{{ asset(setting('pharmacy_logo')) }}" alt="Logo" class="h-8 mr-3 object-contain bg-white rounded p-1">
                @else
                    <x-brand-mark class="w-8 h-8 mr-2.5" />
                @endif
                {{ setting('pharmacy_name', 'PharmaPro') }}
            @endif
        </a>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-5 px-3 pb-6 flex-1">
        <p class="px-4 text-[11px] font-semibold text-slate-500 uppercase tracking-[.14em] mb-2">Main Menu</p>
        
        @php
            $dashboardRoute = auth()->user()->role === 'super_admin' ? route('super_admin.dashboard') : route('dashboard');
            $isDashboardRoute = auth()->user()->role === 'super_admin' ? request()->routeIs('super_admin.dashboard') : request()->routeIs('dashboard');
        @endphp
        <a href="{{ $dashboardRoute }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ $isDashboardRoute ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-tachometer-alt mr-3 text-base w-5 text-center {{ $isDashboardRoute ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Dashboard
        </a>

        @if(auth()->user()->role === 'super_admin')
        <a href="{{ route('super_admin.pharmacies') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super_admin.pharmacies*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-clinic-medical mr-3 text-base w-5 text-center {{ request()->routeIs('super_admin.pharmacies*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Pharmacies
        </a>
        @else

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('branches.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('branches.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-code-branch mr-3 text-base w-5 text-center {{ request()->routeIs('branches.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Branches
        </a>
        @endif

        <div x-data="{ expanded: {{ request()->is('medicines*') || request()->is('medicine-categories*') || request()->is('units*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-white/5 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-pills mr-3 text-base w-5 text-center text-slate-500 group-hover:text-teal-300"></i>
                    Medicines
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down text-[10px] text-slate-500' : 'fa-chevron-right text-[10px] text-slate-500'"></i>
            </button>
            <div x-show="expanded" class="ml-6 pl-5 pr-2 py-1.5 space-y-0.5 border-l border-white/10" x-cloak>
                <a href="{{ route('medicine-categories.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('medicine-categories.*') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Categories</a>
                <a href="{{ route('units.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('units.*') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Units</a>
                <a href="{{ route('medicines.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('medicines.*') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">List</a>
            </div>
        </div>

        <a href="{{ route('suppliers.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('suppliers.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-truck mr-3 text-base w-5 text-center {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Suppliers
        </a>

        <div x-data="{ expanded: {{ request()->is('purchases*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-white/5 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-shopping-cart mr-3 text-base w-5 text-center text-slate-500 group-hover:text-teal-300"></i>
                    Purchases
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down text-[10px] text-slate-500' : 'fa-chevron-right text-[10px] text-slate-500'"></i>
            </button>
            <div x-show="expanded" class="ml-6 pl-5 pr-2 py-1.5 space-y-0.5 border-l border-white/10" x-cloak>
                <a href="{{ route('purchases.create') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('purchases.create') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">New Purchase</a>
                <a href="{{ route('purchases.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('purchases.index') || request()->routeIs('purchases.show') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">List</a>
            </div>
        </div>

        <div x-data="{ expanded: {{ request()->is('stock*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-white/5 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-boxes mr-3 text-base w-5 text-center text-slate-500 group-hover:text-teal-300"></i>
                    Stock
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down text-[10px] text-slate-500' : 'fa-chevron-right text-[10px] text-slate-500'"></i>
            </button>
            <div x-show="expanded" class="ml-6 pl-5 pr-2 py-1.5 space-y-0.5 border-l border-white/10" x-cloak>
                <a href="{{ route('stock.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('stock.index') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">View Stock</a>
            </div>
        </div>

        <a href="{{ route('pos.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('pos.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-desktop mr-3 text-base w-5 text-center {{ request()->routeIs('pos.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Sales / POS
        </a>

        <a href="{{ route('customers.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('customers.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-users mr-3 text-base w-5 text-center {{ request()->routeIs('customers.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Customers
        </a>

        <a href="{{ route('sales.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('sales.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-file-invoice mr-3 text-base w-5 text-center {{ request()->routeIs('sales.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Invoices
        </a>

        <p class="px-4 mt-7 text-[11px] font-semibold text-slate-500 uppercase tracking-[.14em] mb-2">System</p>

        <div x-data="{ expanded: {{ request()->routeIs('returns.*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-white/5 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-undo mr-3 text-base w-5 text-center text-slate-500 group-hover:text-teal-300"></i>
                    Returns
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down text-[10px] text-slate-500' : 'fa-chevron-right text-[10px] text-slate-500'"></i>
            </button>
            <div x-show="expanded" class="ml-6 pl-5 pr-2 py-1.5 space-y-0.5 border-l border-white/10" x-cloak>
                <a href="{{ route('returns.sale.create') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('returns.sale.*') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Customer Refunds</a>
                <a href="{{ route('returns.purchase.create') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('returns.purchase.*') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Supplier Returns</a>
            </div>
        </div>

        <a href="{{ route('expenses.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('expenses.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-money-bill-wave mr-3 text-base w-5 text-center {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Expenses
        </a>

        <div x-data="{ expanded: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-white/5 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar mr-3 text-base w-5 text-center text-slate-500 group-hover:text-teal-300"></i>
                    Reports
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down text-[10px] text-slate-500' : 'fa-chevron-right text-[10px] text-slate-500'"></i>
            </button>
            <div x-show="expanded" class="ml-6 pl-5 pr-2 py-1.5 space-y-0.5 border-l border-white/10" x-cloak>
                <a href="{{ route('reports.sales') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.sales') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Sales Report</a>
                <a href="{{ route('reports.purchases') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.purchases') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Purchase Report</a>
                <a href="{{ route('reports.stock') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.stock') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Stock Report</a>
                <a href="{{ route('reports.expiry') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.expiry') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Expiry Report</a>
                <a href="{{ route('reports.customers') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.customers') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Customer Report</a>
                <a href="{{ route('reports.profit') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.profit') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Profit / Loss</a>
                <a href="{{ route('reports.medicine_sales') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.medicine_sales') ? 'nav-sub-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">Medicine Sales</a>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('settings.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('settings.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-cogs mr-3 text-base w-5 text-center {{ request()->routeIs('settings.*') ? 'text-slate-300' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            Settings
        </a>
        <a href="{{ route('users.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('users.*') ? 'nav-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-users mr-3 text-base w-5 text-center {{ request()->routeIs('users.*') ? 'text-slate-300' : 'text-slate-500 group-hover:text-teal-300' }}"></i>
            User Management
        </a>
        @endif
        @endif
    </nav>

    <!-- Signed-in user -->
    <div class="shrink-0 m-3 mt-0 rounded-xl border border-white/5 bg-white/[.03] p-3 flex items-center gap-3">
        <span class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-400 to-emerald-600 grid place-items-center text-white text-sm font-semibold shrink-0">
            {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
            <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->branch->name ?? ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-8 h-8 grid place-items-center rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition" title="Log out">
                <i class="fas fa-arrow-right-from-bracket text-sm"></i>
            </button>
        </form>
    </div>
</div>
