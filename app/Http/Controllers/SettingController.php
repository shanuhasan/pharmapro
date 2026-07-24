<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        // Only admins can access settings
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return view('settings');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->except('_token');

        // Handle File Upload for Logo
        if ($request->hasFile('pharmacy_logo')) {
            $path = $request->file('pharmacy_logo')->store('logo', 'public');
            // Store the path relative to storage
            $data['pharmacy_logo'] = 'storage/' . $path;
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Clear cache so changes reflect instantly
        Cache::forget('app_settings');

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
