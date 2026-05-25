<?php

use Nawasara\Core\Models\Setting;

// Note: sudo_active() and sudo_remaining_seconds() live in
// nawasara/auth-primitives — see packages/nawasara-auth-primitives/
// src/Helpers/functions.php. Core depends on auth-primitives, so they
// are still available app-wide; the move just keeps the primitives
// reusable by domain packages that don't depend on core.

if (! function_exists('brand')) {
    /**
     * Get a branding setting value.
     *
     * Common keys:
     *   - app_name   (string, default: "Nawasara")
     *   - app_subtitle (string, default: null)
     *   - logo       (file URL, default: null)
     *   - logo_dark  (file URL, default: null)
     *   - favicon    (file URL, default: null)
     *
     * Returns default if setting doesn't exist or table not yet migrated.
     */
    function brand(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get("branding.{$key}", $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('setting')) {
    /**
     * Get any app setting.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
