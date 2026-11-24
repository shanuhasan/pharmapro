<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $pharmaciesCount = Pharmacy::count();
        return view('super_admin.dashboard', compact('pharmaciesCount'));
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
        $request->validate([
            'pharmacy_name' => 'required|string|max:255',
            'pharmacy_email' => 'required|email|unique:pharmacies,email',
            'pharmacy_phone' => 'nullable|string|max:20',
            'pharmacy_address' => 'nullable|string',
            
            // Initial Admin User validation
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Create Pharmacy
        $pharmacy = Pharmacy::create([
            'name' => $request->pharmacy_name,
            'email' => $request->pharmacy_email,
            'phone' => $request->pharmacy_phone,
            'address' => $request->pharmacy_address,
            'expires_at' => now()->addYear()->toDateString(),
        ]);

        // Create Admin User for Pharmacy
        User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role' => 'admin',
            'pharmacy_id' => $pharmacy->id,
            'is_active' => true,
        ]);

        return redirect()->route('super_admin.pharmacies')->with('success', 'Pharmacy and Admin User created successfully.');
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
