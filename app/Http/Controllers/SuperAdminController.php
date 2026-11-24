<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pharmacy;
use App\Models\PharmacyRenewal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $pharmacies = Pharmacy::all();
        $today = now()->startOfDay();

        $stats = [
            'total' => $pharmacies->count(),
            'active' => $pharmacies->where('is_active', true)->count(),
            'inactive' => $pharmacies->where('is_active', false)->count(),
            'expired' => $pharmacies->filter->isMembershipExpired()->count(),
            'expiring' => $pharmacies->filter(function ($p) {
                $d = $p->membershipDaysLeft();
                return $d !== null && $d >= 0 && $d <= 30;
            })->count(),
            'users' => User::withoutGlobalScopes()->whereNotNull('pharmacy_id')->count(),
            'revenue_total' => (float) PharmacyRenewal::sum('amount'),
            'revenue_month' => (float) PharmacyRenewal::where('created_at', '>=', $today->copy()->startOfMonth())->sum('amount'),
            'renewals_month' => PharmacyRenewal::where('created_at', '>=', $today->copy()->startOfMonth())->count(),
        ];

        // Memberships ending within 60 days or already expired, most urgent first.
        $attention = $pharmacies
            ->map(fn ($p) => ['pharmacy' => $p, 'days' => $p->membershipDaysLeft()])
            ->filter(fn ($row) => $row['days'] !== null && $row['days'] <= 60)
            ->sortBy('days')
            ->take(8)
            ->values();

        $recentRenewals = PharmacyRenewal::with(['pharmacy', 'renewedBy'])->latest()->latest('id')->take(6)->get();
        $recentPharmacies = Pharmacy::latest()->take(5)->get();

        // Revenue for the last 6 months (oldest first) for the mini bar chart.
        $revenueByMonth = collect(range(5, 0))->map(function ($i) use ($today) {
            $from = $today->copy()->startOfMonth()->subMonths($i);
            return [
                'label' => $from->format('M'),
                'amount' => (float) PharmacyRenewal::whereBetween('created_at', [$from, $from->copy()->endOfMonth()])->sum('amount'),
            ];
        });

        return view('super_admin.dashboard', compact('stats', 'attention', 'recentRenewals', 'recentPharmacies', 'revenueByMonth'));
    }

    public function pharmacies()
    {
        $pharmacies = Pharmacy::all();
        return view('super_admin.pharmacies.index', compact('pharmacies'));
    }

    public function createPharmacy()
    {
        return view('super_admin.pharmacies.create');
    }

    public function storePharmacy(Request $request)
    {
        $data = $request->validate([
            'pharmacy_name' => 'required|string|max:255',
            'pharmacy_email' => 'required|email|unique:pharmacies,email',
            'pharmacy_phone' => 'nullable|string|max:20',
            'pharmacy_address' => 'nullable|string',

            // Initial membership
            'months' => 'required|integer|in:1,3,6,12,24,36',
            'amount' => 'nullable|numeric|min:0|max:99999999',
            'note' => 'nullable|string|max:500',

            // Initial Admin User validation
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $expiresAt = now()->startOfDay()->addMonthsNoOverflow((int) $data['months']);

        $pharmacy = DB::transaction(function () use ($request, $data, $expiresAt) {
            $pharmacy = Pharmacy::create([
                'name' => $request->pharmacy_name,
                'email' => $request->pharmacy_email,
                'phone' => $request->pharmacy_phone,
                'address' => $request->pharmacy_address,
                'is_active' => true,
                'expires_at' => $expiresAt->toDateString(),
            ]);

            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'pharmacy_id' => $pharmacy->id,
                'is_active' => true,
            ]);

            // First entry in the membership history.
            $pharmacy->renewals()->create([
                'renewed_by' => auth()->id(),
                'previous_expires_at' => null,
                'new_expires_at' => $expiresAt->toDateString(),
                'renew_type' => 'initial',
                'months' => (int) $data['months'],
                'amount' => $data['amount'] ?? null,
                'note' => $data['note'] ?? null,
                'reactivated' => false,
            ]);

            return $pharmacy;
        });

        return redirect()->route('super_admin.pharmacies')
            ->with('success', 'Pharmacy "' . $pharmacy->name . '" and its admin login were created. Membership runs until ' . $expiresAt->format('d M Y') . '.');
    }

    public function editPharmacy(Pharmacy $pharmacy)
    {
        $admin = $this->pharmacyAdmin($pharmacy);
        $renewals = $pharmacy->renewals()->with('renewedBy')->get();

        return view('super_admin.pharmacies.edit', compact('pharmacy', 'admin', 'renewals'));
    }

    public function updatePharmacy(Request $request, Pharmacy $pharmacy)
    {
        $admin = $this->pharmacyAdmin($pharmacy);

        $rules = [
            'pharmacy_name' => 'required|string|max:255',
            'pharmacy_email' => ['required', 'email', Rule::unique('pharmacies', 'email')->ignore($pharmacy->id)],
            'pharmacy_phone' => 'nullable|string|max:20',
            'pharmacy_address' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];

        if ($admin) {
            $rules += [
                'admin_name' => 'required|string|max:255',
                'admin_email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin->id)],
                'admin_password' => ['nullable', 'confirmed', Password::defaults()],
            ];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $pharmacy, $admin) {
            $pharmacy->update([
                'name' => $request->pharmacy_name,
                'email' => $request->pharmacy_email,
                'phone' => $request->pharmacy_phone,
                'address' => $request->pharmacy_address,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($admin) {
                $admin->name = $request->admin_name;
                $admin->email = $request->admin_email;
                if ($request->filled('admin_password')) {
                    $admin->password = Hash::make($request->admin_password);
                }
                $admin->save();
            }
        });

        return redirect()->route('super_admin.pharmacies')->with('success', 'Pharmacy "' . $pharmacy->name . '" updated successfully.');
    }

    /**
     * The pharmacy's primary admin account (the earliest-created admin).
     */
    private function pharmacyAdmin(Pharmacy $pharmacy): ?User
    {
        return User::withoutGlobalScopes()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();
    }

    public function renewPharmacy(Request $request, Pharmacy $pharmacy)
    {
        $data = $request->validate([
            'renew_type' => 'required|in:months,date',
            'months' => 'required_if:renew_type,months|nullable|integer|in:1,3,6,12,24,36',
            'expires_at' => 'required_if:renew_type,date|nullable|date|after_or_equal:today',
            'reactivate' => 'nullable|boolean',
            'amount' => 'nullable|numeric|min:0|max:99999999',
            'note' => 'nullable|string|max:500',
        ]);

        $previous = $pharmacy->membershipEndsAt();

        if ($data['renew_type'] === 'months') {
            // Extend from the current end date if still running, otherwise from today,
            // so early renewals never lose remaining days.
            $base = ($previous && $previous->isFuture()) ? $previous->copy()->startOfDay() : now()->startOfDay();
            $newEnd = $base->addMonthsNoOverflow((int) $data['months']);
        } else {
            $newEnd = \Illuminate\Support\Carbon::parse($data['expires_at']);
        }

        $reactivated = $request->boolean('reactivate') && !$pharmacy->is_active;

        DB::transaction(function () use ($pharmacy, $newEnd, $previous, $data, $reactivated) {
            $pharmacy->expires_at = $newEnd->toDateString();
            if ($reactivated) {
                $pharmacy->is_active = true;
            }
            $pharmacy->save();

            $pharmacy->renewals()->create([
                'renewed_by' => auth()->id(),
                'previous_expires_at' => $previous?->toDateString(),
                'new_expires_at' => $newEnd->toDateString(),
                'renew_type' => $data['renew_type'],
                'months' => $data['renew_type'] === 'months' ? (int) $data['months'] : null,
                'amount' => $data['amount'] ?? null,
                'note' => $data['note'] ?? null,
                'reactivated' => $reactivated,
            ]);
        });

        return redirect()->route('super_admin.pharmacies.edit', $pharmacy)
            ->with('success', 'Membership for "' . $pharmacy->name . '" now runs until ' . $newEnd->format('d M Y') . '.');
    }

    public function toggleStatus(Pharmacy $pharmacy)
    {
        $pharmacy->is_active = !$pharmacy->is_active;
        $pharmacy->save();

        return redirect()->back()->with('success', 'Pharmacy status updated successfully.');
    }
}
