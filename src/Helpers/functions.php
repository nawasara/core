<?php

use Nawasara\Core\Models\Setting;

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
