<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('super_admin.pharmacies') }}" class="text-slate-400 hover:text-teal-700">Pharmacies</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Pharmacy</h2>
        </div>
    </x-slot>

    @php($field = 'block mt-1 w-full rounded-md shadow-sm border-gray-300 sm:text-sm')

    <div class="py-6">
        @if($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/80 px-4 py-3 text-rose-800" role="alert">
                <span class="mt-0.5 w-6 h-6 shrink-0 rounded-full bg-rose-500 text-white grid place-items-center"><i class="fas fa-exclamation text-[11px]"></i></span>
                <p class="text-sm font-medium pt-0.5">Please fix the highlighted fields below.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('super_admin.pharmacies.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start"
              x-data="{ plan: @js(old('plan', 'paid')), months: {{ (int) old('months', 12) }}, trialDays: {{ (int) old('trial_days', 14) }}, pharmacy: @js(old('pharmacy_name', '')), adminEmail: @js(old('admin_email', '')), showPw: false,
                        endDate() { if (this.plan === 'trial') { const t = new Date(); t.setHours(0,0,0,0); t.setDate(t.getDate() + Number(this.trialDays || 0)); return t.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); } const d = new Date(); d.setHours(0,0,0,0); const day = d.getDate(); d.setDate(1); d.setMonth(d.getMonth() + Number(this.months)); d.setDate(Math.min(day, new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate())); return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); } }">
            @csrf

            <div class="lg:col-span-2 space-y-6">
                <!-- Step 1: Pharmacy -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 grid place-items-center font-bold text-sm">1</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Pharmacy details</h3>
                            <p class="text-xs text-slate-500">The business being registered.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_name">Pharmacy name <span class="text-rose-500">*</span></label>
                            <input type="text" name="pharmacy_name" id="pharmacy_name" x-model="pharmacy" class="{{ $field }}" value="{{ old('pharmacy_name') }}" required autofocus placeholder="e.g. City Care Pharmacy">
                            @error('pharmacy_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_email">Pharmacy email <span class="text-rose-500">*</span></label>
                            <input type="email" name="pharmacy_email" id="pharmacy_email" class="{{ $field }}" value="{{ old('pharmacy_email') }}" required placeholder="contact@pharmacy.com">
                            @error('pharmacy_email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_phone">Phone</label>
                            <input type="text" name="pharmacy_phone" id="pharmacy_phone" class="{{ $field }}" value="{{ old('pharmacy_phone') }}" placeholder="Optional">
                            @error('pharmacy_phone') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-medium text-sm text-gray-700" for="pharmacy_address">Address</label>
                            <textarea name="pharmacy_address" id="pharmacy_address" rows="3" class="{{ $field }}" placeholder="Optional">{{ old('pharmacy_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Admin login -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 grid place-items-center font-bold text-sm">2</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Admin login</h3>
                            <p class="text-xs text-slate-500">The first user. They can add branches and staff afterwards.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="admin_name">Admin name <span class="text-rose-500">*</span></label>
                            <input type="text" name="admin_name" id="admin_name" class="{{ $field }}" value="{{ old('admin_name') }}" required>
                            @error('admin_name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="admin_email">Admin email (login ID) <span class="text-rose-500">*</span></label>
                            <input type="email" name="admin_email" id="admin_email" x-model="adminEmail" class="{{ $field }}" value="{{ old('admin_email') }}" required>
                            @error('admin_email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="admin_password">Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'" name="admin_password" id="admin_password" class="{{ $field }} !pr-10" required autocomplete="new-password">
                                <button type="button" @click="showPw = !showPw" class="absolute right-2 top-1/2 -translate-y-1/2 mt-0.5 w-7 h-7 grid place-items-center text-slate-400 hover:text-slate-600" :aria-label="showPw ? 'Hide password' : 'Show password'">
                                    <i class="far" :class="showPw ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            @error('admin_password') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="admin_password_confirmation">Confirm password <span class="text-rose-500">*</span></label>
                            <input :type="showPw ? 'text' : 'password'" name="admin_password_confirmation" id="admin_password_confirmation" class="{{ $field }}" required autocomplete="new-password">
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400"><i class="fas fa-circle-info mr-1"></i> Share these credentials with the pharmacy owner securely. They can change the password from their profile.</p>
                </div>
            </div>

            <!-- Right column: membership + summary -->
            <div class="space-y-6 lg:sticky lg:top-[5.5rem]">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 grid place-items-center font-bold text-sm">3</span>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Membership</h3>
                            <p class="text-xs text-slate-500">Paid period or free trial. Starts today.</p>
                        </div>
                    </div>

                    <input type="hidden" name="plan" :value="plan">
                    <div class="flex rounded-lg bg-slate-100 p-1 text-xs font-semibold mb-4">
                        <button type="button" @click="plan = 'paid'" class="flex-1 rounded-md py-1.5 transition" :class="plan === 'paid' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'"><i class="fas fa-indian-rupee-sign text-[10px]"></i> Paid</button>
                        <button type="button" @click="plan = 'trial'" class="flex-1 rounded-md py-1.5 transition" :class="plan === 'trial' ? 'bg-white shadow-sm text-sky-700' : 'text-slate-500'"><i class="fas fa-flask text-[10px]"></i> Free trial</button>
                    </div>

                    <div x-show="plan === 'trial'" x-cloak>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([7, 14, 30] as $d)
                                <button type="button" @click="trialDays = {{ $d }}"
                                        class="rounded-lg border px-2 py-2 text-xs font-semibold transition"
                                        :class="Number(trialDays) === {{ $d }} ? 'border-sky-600 bg-sky-50 text-sky-700 ring-2 ring-sky-600/15' : 'border-slate-200 text-slate-600 hover:border-sky-300'">{{ $d }} days</button>
                            @endforeach
                            <input type="number" min="1" max="90" x-model="trialDays" aria-label="Custom trial days" class="rounded-lg border-slate-200 text-xs font-semibold text-center px-1 py-2" placeholder="Days">
                        </div>
                        <input type="hidden" name="trial_days" :value="plan === 'trial' ? trialDays : ''">
                        @error('trial_days') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="mt-3 text-[11px] text-slate-500">Full access during the trial. Convert to paid any time with <strong>Renew</strong>.</p>
                    </div>

                    <div x-show="plan === 'paid'">
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([1 => '1 month', 3 => '3 months', 6 => '6 months', 12 => '1 year', 24 => '2 years', 36 => '3 years'] as $m => $label)
                            <button type="button" @click="months = {{ $m }}"
                                    class="rounded-lg border px-2 py-2 text-xs font-semibold transition"
                                    :class="months === {{ $m }} ? 'border-teal-600 bg-teal-50 text-teal-700 ring-2 ring-teal-600/15' : 'border-slate-200 text-slate-600 hover:border-teal-300'">{{ $label }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="months" :value="plan === 'paid' ? months : ''">
                    @error('months') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="block font-medium text-xs text-gray-600" for="amount">Amount received</label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">₹</span>
                                <input type="number" step="0.01" min="0" id="amount" name="amount" :disabled="plan === 'trial'" value="{{ old('amount') }}" class="block w-full rounded-md shadow-sm border-gray-300 sm:text-sm !pl-7" placeholder="Optional">
                            </div>
                            @error('amount') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-xs text-gray-600" for="note">Note</label>
                            <input type="text" id="note" name="note" maxlength="500" value="{{ old('note') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 sm:text-sm" placeholder="Optional">
                            @error('note') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Live summary -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-teal-900 p-6 text-white shadow-lg">
                    <div class="absolute -top-16 -right-16 w-44 h-44 rounded-full bg-teal-400/20 blur-2xl"></div>
                    <p class="relative text-[11px] font-semibold uppercase tracking-[.16em] text-teal-200/80">Summary</p>
                    <p class="relative mt-2 text-lg font-semibold truncate" x-text="pharmacy || 'New pharmacy'"></p>
                    <dl class="relative mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-slate-400">Admin login</dt><dd class="truncate text-slate-200" x-text="adminEmail || '—'"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Plan</dt><dd class="text-slate-200" x-text="plan === 'trial' ? trialDays + '-day free trial' : 'Paid membership'"></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Starts</dt><dd class="text-slate-200">{{ now()->format('d M Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Ends</dt><dd class="font-semibold text-amber-300" x-text="endDate()"></dd></div>
                    </dl>
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('super_admin.pharmacies') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="flex-[1.6] inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-medical-primary text-white text-sm font-semibold">
                        <i class="fas fa-plus"></i> <span x-text="plan === 'trial' ? 'Create with trial' : 'Create pharmacy'">Create pharmacy</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
