<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Quick Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Today Sales -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-b-4 border-medical-primary">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-medical-primary">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-semibold uppercase">Today's Sales</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ setting('currency_symbol', '₹') }}{{ number_format($todaySalesAmount, 2) }}</h3>
                            <p class="text-xs text-gray-400 mt-1">{{ $todaySalesCount }} Orders</p>
                        </div>
                    </div>
                </div>

                <!-- Today Purchases -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-b-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-truck-loading fa-2x"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-semibold uppercase">Today's Purchases</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ setting('currency_symbol', '₹') }}{{ number_format($todayPurchasesAmount, 2) }}</h3>
                            <p class="text-xs text-gray-400 mt-1">Stock inbound</p>
                        </div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-b-4 border-red-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-semibold uppercase">Low Stock Alerts</p>
                            <h3 class="text-2xl font-bold text-red-600">{{ $lowStockCount }}</h3>
                            <p class="text-xs text-gray-400 mt-1">Items below {{$lowqty}} qty</p>
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-b-4 border-orange-500">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-semibold uppercase">Expiring Soon</p>
                            <h3 class="text-2xl font-bold text-orange-600">{{ $expiringSoonCount }}</h3>
                            <p class="text-xs text-gray-400 mt-1">Within next {{$days}} days</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Row -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Monthly Revenue Overview</h3>
                <div class="relative h-96 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js -->
    <script src="{{ asset('vendor/chartjs/chart.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            const labels = @json($chartLabels);
            const dataPoints = @json($chartData);

            const currency = @json(setting('currency_symbol', '₹'));
            const fmt = v => currency + Number(v).toLocaleString('en-IN', { maximumFractionDigits: 2 });
            const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight || 320);
            gradient.addColorStop(0, 'rgba(20, 184, 166, 0.28)');
            gradient.addColorStop(1, 'rgba(20, 184, 166, 0)');

            Chart.defaults.font.family = "Figtree, ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.color = '#64748b';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: dataPoints,
                        borderColor: '#0d9488',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#0d9488',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: items => 'Day ' + items[0].label,
                                label: c => 'Revenue: ' + fmt(c.parsed.y)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: '#eef2f6' },
                            ticks: { callback: v => fmt(v), maxTicksLimit: 6 }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false },
                            title: { display: true, text: 'Day of Month' }
                        }
                    }
                }
            });
        });
    </script>
</x-admin-layout>
