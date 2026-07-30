<!-- Sidebar -->
<div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="fixed inset-y-0 left-0 z-30 w-64 bg-medical-dark overflow-y-auto transition duration-300 transform lg:translate-x-0 lg:static lg:inset-auto">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-center h-16 bg-medical-darker shadow-md">
        <a href="{{ route('dashboard') }}" class="text-white text-xl font-bold uppercase tracking-wider flex items-center">
            @if(auth()->user()->role === 'super_admin')
                <i class="fas fa-plus-square text-medical-primary mr-2"></i>
                PharmaPro
            @else
                @if(setting('pharmacy_logo'))
                    <img src="{{ asset(setting('pharmacy_logo')) }}" alt="Logo" class="h-8 mr-3 object-contain bg-white rounded p-1">
                @else
                    <i class="fas fa-plus-square text-medical-primary mr-2"></i>
                @endif
                {{ setting('pharmacy_name', 'PharmaPro') }}
            @endif
        </a>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-5 px-2">
        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Main Menu</p>
        
        @php
            $dashboardRoute = auth()->user()->role === 'super_admin' ? route('super_admin.dashboard') : route('dashboard');
            $isDashboardRoute = auth()->user()->role === 'super_admin' ? request()->routeIs('super_admin.dashboard') : request()->routeIs('dashboard');
        @endphp
        <a href="{{ $dashboardRoute }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ $isDashboardRoute ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-tachometer-alt mr-3 text-lg {{ $isDashboardRoute ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Dashboard
        </a>

        @if(auth()->user()->role === 'super_admin')
        <a href="{{ route('super_admin.pharmacies') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('super_admin.pharmacies*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-clinic-medical mr-3 text-lg {{ request()->routeIs('super_admin.pharmacies*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Pharmacies
        </a>
        @else

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('branches.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('branches.*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-code-branch mr-3 text-lg {{ request()->routeIs('branches.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Branches
        </a>
        @endif

        <div x-data="{ expanded: {{ request()->is('medicines*') || request()->is('medicine-categories*') || request()->is('units*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-pills mr-3 text-lg text-gray-400 group-hover:text-gray-300"></i>
                    Medicines
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <div x-show="expanded" class="pl-11 pr-2 py-2 space-y-1" x-cloak>
                <a href="{{ route('medicine-categories.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('medicine-categories.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Categories</a>
                <a href="{{ route('units.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('units.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Units</a>
                <a href="{{ route('medicines.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('medicines.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">List</a>
            </div>
        </div>

        <a href="{{ route('suppliers.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('suppliers.*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-truck mr-3 text-lg {{ request()->routeIs('suppliers.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Suppliers
        </a>

        <div x-data="{ expanded: {{ request()->is('purchases*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-shopping-cart mr-3 text-lg text-gray-400 group-hover:text-gray-300"></i>
                    Purchases
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <div x-show="expanded" class="pl-11 pr-2 py-2 space-y-1" x-cloak>
                <a href="{{ route('purchases.create') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('purchases.create') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">New Purchase</a>
                <a href="{{ route('purchases.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('purchases.index') || request()->routeIs('purchases.show') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">List</a>
            </div>
        </div>

        <div x-data="{ expanded: {{ request()->is('stock*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-boxes mr-3 text-lg text-gray-400 group-hover:text-gray-300"></i>
                    Stock
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <div x-show="expanded" class="pl-11 pr-2 py-2 space-y-1" x-cloak>
                <a href="{{ route('stock.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('stock.index') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">View Stock</a>
            </div>
        </div>

        <a href="{{ route('pos.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('pos.*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-desktop mr-3 text-lg {{ request()->routeIs('pos.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Sales / POS
        </a>

        <a href="{{ route('customers.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('customers.*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-users mr-3 text-lg {{ request()->routeIs('customers.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Customers
        </a>

        <a href="{{ route('sales.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('sales.*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-file-invoice mr-3 text-lg {{ request()->routeIs('sales.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Invoices
        </a>

        <p class="px-4 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">System</p>

        <div x-data="{ expanded: {{ request()->routeIs('returns.*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-undo mr-3 text-lg text-gray-400 group-hover:text-gray-300"></i>
                    Returns
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <div x-show="expanded" class="pl-11 pr-2 py-2 space-y-1" x-cloak>
                <a href="{{ route('returns.sale.create') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('returns.sale.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Customer Refunds</a>
                <a href="{{ route('returns.purchase.create') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('returns.purchase.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Supplier Returns</a>
            </div>
        </div>

        <a href="{{ route('expenses.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('expenses.*') ? 'bg-medical-primary text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-money-bill-wave mr-3 text-lg {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Expenses
        </a>

        <div x-data="{ expanded: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
            <button @click="expanded = !expanded" class="w-full mt-1 group flex items-center justify-between px-4 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar mr-3 text-lg text-gray-400 group-hover:text-gray-300"></i>
                    Reports
                </div>
                <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <div x-show="expanded" class="pl-11 pr-2 py-2 space-y-1" x-cloak>
                <a href="{{ route('reports.sales') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.sales') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Sales Report</a>
                <a href="{{ route('reports.purchases') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.purchases') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Purchase Report</a>
                <a href="{{ route('reports.stock') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.stock') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Stock Report</a>
                <a href="{{ route('reports.expiry') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.expiry') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Expiry Report</a>
                <a href="{{ route('reports.customers') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.customers') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Customer Report</a>
                <a href="{{ route('reports.profit') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.profit') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Profit / Loss</a>
                <a href="{{ route('reports.medicine_sales') }}" class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('reports.medicine_sales') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">Medicine Sales</a>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
        <a href="{{ route('settings.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-cogs mr-3 text-lg {{ request()->routeIs('settings.*') ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            Settings
        </a>
        <a href="{{ route('users.index') }}" class="mt-1 group flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <i class="fas fa-users mr-3 text-lg {{ request()->routeIs('users.*') ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300' }}"></i>
            User Management
        </a>
        @endif
        @endif
    </nav>
</div>
