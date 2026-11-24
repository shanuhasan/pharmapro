<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Super Admin Dashboard') }}</h2>
    </x-slot>

    @php
        $rupee = fn ($v) => '₹' . number_format($v, ($v == floor($v)) ? 0 : 2);
        $maxRev = max(1, $revenueByMonth->max('amount'));
    @endphp

    <div class="py-6 space-y-6">

        <!-- Welcome strip -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-teal-900 px-6 py-6 sm:px-8 text-white shadow-lg">
            <div class="absolute -top-20 -right-10 w-72 h-72 rounded-full bg-teal-400/15 blur-3xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[.16em] text-teal-200/80">{{ now()->format('l, d F Y') }}</p>
                    <h3 class="mt-1 text-2xl font-semibold">Welcome back, {{ strtok(auth()->user()->name, ' ') }}</h3>
                    <p class="mt-1 text-sm text-slate-300">
                        @if($stats['expired'] + $stats['expiring'] > 0)
                            {{ $stats['expiring'] }} {{ str('membership')->plural($stats['expiring']) }} ending within 30 days · {{ $stats['expired'] }} expired.
                        @else
                            All memberships are in good standing.
                        @endif
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('super_admin.pharmacies') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold hover:bg-white/10 transition">
                        <i class="fas fa-list"></i> All pharmacies
                    </a>
                    <a href="{{ route('super_admin.pharmacies.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-b from-teal-400 to-teal-600 px-4 py-2.5 text-sm font-semibold shadow-lg shadow-teal-900/40 hover:brightness-105 transition">
                        <i class="fas fa-plus"></i> New pharmacy
                    </a>
                </div>
            </div>
        </div>

        <!-- KPI tiles -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach([
                ['Pharmacies', $stats['total'], $stats['active'] . ' active · ' . $stats['inactive'] . ' inactive', 'fa-clinic-medical', 'bg-teal-50 text-teal-600'],
                ['Expiring in 30 days', $stats['expiring'], $stats['expired'] . ' already expired', 'fa-hourglass-half', 'bg-amber-50 text-amber-600'],
                ['Revenue this month', $rupee($stats['revenue_month']), $stats['renewals_month'] . ' ' . str('renewal')->plural($stats['renewals_month']) . ' this month', 'fa-indian-rupee-sign', 'bg-emerald-50 text-emerald-600'],
                ['Pharmacy users', $stats['users'], 'Across all pharmacies', 'fa-users', 'bg-violet-50 text-violet-600'],
            ] as [$label, $value, $sub, $icon, $tone])
                <div class="bg-white shadow-sm sm:rounded-lg rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <span class="w-9 h-9 rounded-xl grid place-items-center {{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                    </div>
                    <p class="mt-2 text-2xl sm:text-3xl font-bold text-slate-900 tabular-nums">{{ $value }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $sub }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

            <!-- Needs attention -->
            <div class="xl:col-span-2 bg-white shadow-sm sm:rounded-lg rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Memberships needing attention</h3>
                        <p class="text-xs text-slate-500">Expired or ending within the next 60 days.</p>
                    </div>
                    <a href="{{ route('super_admin.pharmacies') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">View all <i class="fas fa-arrow-right text-[10px]"></i></a>
                </div>

                @if($attention->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-200 py-10 text-center">
                        <span class="mx-auto w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 grid place-items-center"><i class="fas fa-check"></i></span>
                        <p class="mt-3 text-sm font-medium text-slate-700">Nothing to renew right now</p>
                        <p class="text-xs text-slate-400">No membership ends in the next 60 days.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr><th>Pharmacy</th><th>Ends on</th><th>Remaining</th><th>Status</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($attention as $row)
                                    @php($p = $row['pharmacy'])
                                    <tr>
                                        <td>
                                            <span class="block font-medium text-slate-800">{{ $p->name }}</span>
                                            <span class="block text-[11px] text-slate-400">{{ $p->email }}</span>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $p->membershipEndsAt()->format('d M Y') }}</td>
                                        <td class="whitespace-nowrap">
                                            @if($row['days'] < 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Expired {{ abs($row['days']) }}d ago</span>
                                            @elseif($row['days'] <= 30)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">{{ $row['days'] }} days</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $row['days'] }} days</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-600">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('super_admin.pharmacies.edit', $p) }}#renew" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                                <i class="fas fa-rotate"></i> Renew
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Revenue -->
            <div class="bg-white shadow-sm sm:rounded-lg rounded-2xl p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Membership revenue</h3>
                        <p class="text-xs text-slate-500">Last 6 months, from recorded amounts.</p>
                    </div>
                </div>
                <p class="mt-4 text-3xl font-bold text-slate-900 tabular-nums">{{ $rupee($stats['revenue_total']) }}</p>
                <p class="text-xs text-slate-500">All-time total</p>

                <div class="mt-6 flex items-end gap-3 h-36" role="img" aria-label="Revenue by month">
                    @foreach($revenueByMonth as $i => $m)
                        @php($h = $m['amount'] > 0 ? max(6, round($m['amount'] / $maxRev * 100)) : 2)
                        <div class="flex-1 h-full flex flex-col items-center justify-end gap-2 group">
                            <span class="text-[10px] font-semibold text-slate-500 opacity-0 group-hover:opacity-100 transition tabular-nums whitespace-nowrap">{{ $rupee($m['amount']) }}</span>
                            <div class="w-full rounded-t-md {{ $loop->last ? 'bg-gradient-to-t from-teal-600 to-teal-400' : 'bg-teal-100 group-hover:bg-teal-200' }} transition" style="height: {{ $h }}%"></div>
                            <span class="text-[11px] {{ $loop->last ? 'font-semibold text-slate-800' : 'text-slate-400' }}">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
            <!-- Recent renewals -->
            <div class="bg-white shadow-sm sm:rounded-lg rounded-2xl p-6">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Recent renewals</h3>
                @forelse($recentRenewals as $r)
                    <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                        <span class="w-9 h-9 rounded-xl grid place-items-center shrink-0 {{ $r->renew_type === 'initial' ? 'bg-sky-50 text-sky-600' : 'bg-amber-50 text-amber-600' }}">
                            <i class="fas {{ $r->renew_type === 'initial' ? 'fa-plus' : 'fa-rotate' }} text-sm"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 truncate">
                                @if($r->pharmacy)
                                    <a href="{{ route('super_admin.pharmacies.edit', $r->pharmacy) }}" class="hover:text-teal-700">{{ $r->pharmacy->name }}</a>
                                @else
                                    Deleted pharmacy
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">{{ $r->renew_type === 'initial' ? 'Registered' : 'Renewed' }} · {{ $r->periodLabel() }} · until {{ $r->new_expires_at->format('d M Y') }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-slate-800 tabular-nums">{{ $r->amount !== null ? $rupee($r->amount) : '—' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $r->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400">No renewals recorded yet.</p>
                @endforelse
            </div>

            <!-- Newest pharmacies -->
            <div class="bg-white shadow-sm sm:rounded-lg rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-slate-900">Newest pharmacies</h3>
                    <a href="{{ route('super_admin.pharmacies.create') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800"><i class="fas fa-plus text-[10px]"></i> Add</a>
                </div>
                @forelse($recentPharmacies as $p)
                    <a href="{{ route('super_admin.pharmacies.edit', $p) }}" class="flex items-center gap-3 py-3 -mx-2 px-2 rounded-lg hover:bg-slate-50 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                        <span class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-400 to-emerald-600 text-white grid place-items-center text-sm font-semibold shrink-0">{{ strtoupper(mb_substr($p->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $p->name }}</p>
                            <p class="text-xs text-slate-500 truncate">Joined {{ $p->created_at?->format('d M Y') }}</p>
                        </div>
                        @if($p->is_active)
                            <span class="w-2 h-2 rounded-full bg-emerald-500" title="Active"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-300" title="Inactive"></span>
                        @endif
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400">No pharmacies yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
