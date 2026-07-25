<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
}
