<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        $pharmacyId = auth()->check() ? auth()->user()->pharmacy_id : 'global';
        $cacheKey = 'app_settings_' . $pharmacyId;

        // Cache settings forever, update cache when a setting changes
        $settings = Cache::rememberForever($cacheKey, function () {
            try {
                // If it's a super admin (pharmacyId is null), we probably just get empty settings or global ones
                return Setting::pluck('value', 'key')->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });

        return $settings[$key] ?? $default;
    }
}
