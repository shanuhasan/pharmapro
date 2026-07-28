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

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Revenue ($)',
                        data: dataPoints,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#0d6efd',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return '$' + value;
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Day of Month'
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-admin-layout>
