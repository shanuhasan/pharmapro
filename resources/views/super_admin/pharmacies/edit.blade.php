<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('super_admin.pharmacies') }}" class="text-slate-400 hover:text-teal-700">Pharmacies</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">Edit {{ $pharmacy->name }}</h2>
        </div>
    </x-slot>

    @php
        $field = 'block mt-1 w-full rounded-md shadow-sm border-gray-300 sm:text-sm';
        $expiresAt = $pharmacy->membershipEndsAt();
        $days = $pharmacy->membershipDaysLeft();
        $renewFrom = ($expiresAt && $expiresAt->isFuture()) ? $expiresAt->copy()->startOfDay() : now()->startOfDay();
    @endphp

    <div class="py-6">
        <form method="POST" action="{{ route('super_admin.pharmacies.update', $pharmacy) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            @csrf
            @method('PUT')

            <div class="lg:col-span-2 space-y-6">
                <!-- Pharmacy details -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 grid place-items-center"><i class="fas fa-clinic-medical"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Pharmacy details</h3>
                            <p class="text-xs text-slate-500">Shown on invoices and used to contact the pharmacy.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_name">Pharmacy name <span class="text-rose-500">*</span></label>
                            <input type="text" name="pharmacy_name" id="pharmacy_name" class="{{ $field }}" value="{{ old('pharmacy_name', $pharmacy->name) }}" required>
                            @error('pharmacy_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_email">Pharmacy email <span class="text-rose-500">*</span></label>
                            <input type="email" name="pharmacy_email" id="pharmacy_email" class="{{ $field }}" value="{{ old('pharmacy_email', $pharmacy->email) }}" required>
                            @error('pharmacy_email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_phone">Phone</label>
                            <input type="text" name="pharmacy_phone" id="pharmacy_phone" class="{{ $field }}" value="{{ old('pharmacy_phone', $pharmacy->phone) }}">
                            @error('pharmacy_phone') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_address">Address</label>
                            <textarea name="pharmacy_address" id="pharmacy_address" rows="3" class="{{ $field }}">{{ old('pharmacy_address', $pharmacy->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Admin account -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 grid place-items-center"><i class="fas fa-user-shield"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Admin login</h3>
                            <p class="text-xs text-slate-500">The pharmacy's main admin account.</p>
                        </div>
                    </div>

                    @if($admin)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="admin_name">Admin name <span class="text-rose-500">*</span></label>
                                <input type="text" name="admin_name" id="admin_name" class="{{ $field }}" value="{{ old('admin_name', $admin->name) }}" required>
                                @error('admin_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="admin_email">Admin email (login ID) <span class="text-rose-500">*</span></label>
                                <input type="email" name="admin_email" id="admin_email" class="{{ $field }}" value="{{ old('admin_email', $admin->email) }}" required>
                                @error('admin_email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-4">
                            <p class="text-sm font-medium text-slate-700"><i class="fas fa-key text-slate-400 mr-1.5"></i> Reset password</p>
                            <p class="text-xs text-slate-500 mt-0.5">Leave blank to keep the current password.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                                <div>
                                    <label class="block font-medium text-sm text-gray-700" for="admin_password">New password</label>
                                    <input type="password" name="admin_password" id="admin_password" class="{{ $field }}" autocomplete="new-password">
                                    @error('admin_password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block font-medium text-sm text-gray-700" for="admin_password_confirmation">Confirm new password</label>
                                    <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" class="{{ $field }}" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            <i class="fas fa-triangle-exclamation mr-1.5"></i> No admin user is linked to this pharmacy.
                        </div>
                    @endif
                </div>

                <!-- Renewal history -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 grid place-items-center"><i class="fas fa-clock-rotate-left"></i></span>
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Renewal history</h3>
                                <p class="text-xs text-slate-500">Every membership renewal for this pharmacy.</p>
                            </div>
                        </div>
                        @if($renewals->isNotEmpty())
                            <div class="text-right">
                                <p class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ $renewals->count() }} {{ str('renewal')->plural($renewals->count()) }}</p>
                                @if($renewals->whereNotNull('amount')->isNotEmpty())
                                    <p class="text-sm font-semibold text-slate-800">₹{{ number_format($renewals->sum('amount'), 2) }} received</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($renewals->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
                            <i class="fas fa-clock-rotate-left text-2xl text-slate-300"></i>
                            <p class="mt-2 text-sm font-medium text-slate-600">No renewals yet</p>
                            <p class="text-xs text-slate-400">Renewals you make will be listed here.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Period</th>
                                        <th>Membership end</th>
                                        <th class="!text-right">Amount</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($renewals as $r)
                                        <tr>
                                            <td class="whitespace-nowrap">
                                                <span class="block font-medium text-slate-800">{{ $r->created_at->format('d M Y') }}</span>
                                                <span class="block text-[11px] text-slate-400">{{ $r->created_at->format('h:i A') }}</span>
                                            </td>
                                            <td class="whitespace-nowrap">
                                                <span class="inline-flex items-center rounded-full bg-teal-50 text-teal-700 px-2 py-0.5 text-xs font-semibold">{{ $r->periodLabel() }}</span>
                                                @if($r->reactivated)
                                                    <span class="block mt-1 text-[11px] font-medium text-emerald-600"><i class="fas fa-circle-check"></i> Reactivated</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap text-sm">
                                                <span class="text-slate-400 line-through">{{ $r->previous_expires_at?->format('d M Y') ?? '—' }}</span>
                                                <i class="fas fa-arrow-right text-[10px] text-slate-300 mx-1"></i>
                                                <span class="font-semibold text-slate-800">{{ $r->new_expires_at->format('d M Y') }}</span>
                                                @if($r->note)
                                                    <span class="block text-[11px] text-slate-500 mt-0.5 max-w-xs truncate" title="{{ $r->note }}"><i class="far fa-note-sticky"></i> {{ $r->note }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right tabular-nums whitespace-nowrap">{{ $r->amount !== null ? '₹' . number_format($r->amount, 2) : '—' }}</td>
                                            <td class="whitespace-nowrap text-slate-600">{{ $r->renewedBy->name ?? 'Deleted user' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar: status + membership + actions -->
            <div class="space-y-6 lg:sticky lg:top-[5.5rem]">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-slate-900 mb-4">Status</h3>
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center justify-between gap-4 cursor-pointer select-none" x-data="{ on: {{ old('is_active', $pharmacy->is_active) ? 'true' : 'false' }} }">
                        <span>
                            <span class="block text-sm font-medium text-slate-800" x-text="on ? 'Active' : 'Inactive'"></span>
                            <span class="block text-xs text-slate-500" x-text="on ? 'Users can sign in and work normally.' : 'Users are signed out and blocked.'"></span>
                        </span>
                        <input type="checkbox" name="is_active" value="1" class="sr-only" x-model="on">
                        <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors" :class="on ? 'bg-teal-600' : 'bg-slate-300'">
                            <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform" :class="on ? 'translate-x-5' : ''"></span>
                        </span>
                    </label>

                    <div class="mt-6 pt-5 border-t border-slate-100 space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Registered</span><span class="font-medium text-slate-800">{{ $pharmacy->created_at?->format('d M Y') ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Membership ends</span><span class="font-medium text-slate-800">{{ $expiresAt?->format('d M Y') ?? '—' }}</span></div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Remaining</span>
                            @if(is_null($days))
                                <span>—</span>
                            @elseif($days < 0)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                            @elseif($days <= 30)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">{{ $days }} days</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $days }} days</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Renew membership (separate form, linked via the form="" attribute) -->
                <div id="renew" class="bg-white shadow-sm sm:rounded-lg p-6 scroll-mt-24"
                     x-data="{ type: '{{ old('renew_type', 'months') }}', months: {{ (int) old('months', 12) }}, base: '{{ $renewFrom->toDateString() }}',
                               preview() { const d = new Date(this.base + 'T00:00:00'); const day = d.getDate(); d.setDate(1); d.setMonth(d.getMonth() + Number(this.months)); d.setDate(Math.min(day, new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate())); return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); } }">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 grid place-items-center"><i class="fas fa-rotate"></i></span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Renew membership</h3>
                            <p class="text-xs text-slate-500">{{ ($expiresAt && $expiresAt->isFuture()) ? 'Extends from the current end date.' : 'Membership has ended — renews from today.' }}</p>
                        </div>
                    </div>

                    <input type="hidden" name="renew_type" x-model="type" form="renew-form">

                    <div class="flex rounded-lg bg-slate-100 p-1 text-xs font-semibold mb-4">
                        <button type="button" @click="type = 'months'" class="flex-1 rounded-md py-1.5 transition" :class="type === 'months' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'">Extend by period</button>
                        <button type="button" @click="type = 'date'" class="flex-1 rounded-md py-1.5 transition" :class="type === 'date' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'">Set end date</button>
                    </div>

                    <div x-show="type === 'months'">
                        <div class="grid grid-cols-3 gap-2">
                            @foreach([1 => '1 month', 3 => '3 months', 6 => '6 months', 12 => '1 year', 24 => '2 years', 36 => '3 years'] as $m => $label)
                                <button type="button" @click="months = {{ $m }}"
                                        class="rounded-lg border px-2 py-2 text-xs font-semibold transition"
                                        :class="months === {{ $m }} ? 'border-teal-600 bg-teal-50 text-teal-700 ring-2 ring-teal-600/15' : 'border-slate-200 text-slate-600 hover:border-teal-300'">{{ $label }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="months" :value="type === 'months' ? months : ''" form="renew-form">
                        <p class="mt-3 text-xs text-slate-500">New end date: <span class="font-semibold text-slate-800" x-text="preview()"></span></p>
                    </div>

                    <div x-show="type === 'date'" x-cloak>
                        <label class="block font-medium text-sm text-gray-700" for="renew_expires_at">Membership ends on</label>
                        <input type="date" id="renew_expires_at" name="expires_at" form="renew-form" min="{{ now()->toDateString() }}"
                               value="{{ old('expires_at', $renewFrom->copy()->addYear()->toDateString()) }}" :disabled="type !== 'date'"
                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 sm:text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="block font-medium text-xs text-gray-600" for="renew_amount">Amount received <span class="text-slate-400">(optional)</span></label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                                <input type="number" step="0.01" min="0" id="renew_amount" name="amount" form="renew-form" value="{{ old('amount') }}" class="block w-full rounded-md shadow-sm border-gray-300 sm:text-sm !pl-7" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block font-medium text-xs text-gray-600" for="renew_note">Note <span class="text-slate-400">(optional)</span></label>
                            <input type="text" id="renew_note" name="note" form="renew-form" maxlength="500" value="{{ old('note') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 sm:text-sm" placeholder="e.g. UPI ref 4821">
                        </div>
                    </div>

                    @if($errors->hasAny(['renew_type', 'months', 'expires_at', 'amount', 'note']))
                        <p class="mt-2 text-xs text-rose-600">{{ $errors->first('months') ?: $errors->first('expires_at') ?: $errors->first('amount') ?: $errors->first('note') ?: $errors->first('renew_type') }}</p>
                    @endif

                    @unless($pharmacy->is_active)
                        <label class="mt-4 flex items-start gap-2 text-xs text-slate-600">
                            <input type="checkbox" name="reactivate" value="1" form="renew-form" checked class="mt-0.5 rounded border-gray-300">
                            Also reactivate this pharmacy
                        </label>
                    @endunless

                    <button type="submit" form="renew-form" class="mt-4 w-full inline-flex justify-center items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-b from-amber-500 to-amber-600 shadow-sm shadow-amber-600/30 hover:brightness-105"
                            onclick="return confirm('Renew membership for {{ addslashes($pharmacy->name) }}?');">
                        <i class="fas fa-rotate"></i> Renew membership
                    </button>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('super_admin.pharmacies') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-medical-primary text-white text-sm font-semibold">
                        <i class="fas fa-check"></i> Save changes
                    </button>
                </div>
            </div>
        </form>

        <form id="renew-form" method="POST" action="{{ route('super_admin.pharmacies.renew', $pharmacy) }}" class="hidden">
            @csrf
        </form>
    </div>
</x-admin-layout>
