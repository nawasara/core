<?php

use Illuminate\Support\Facades\Auth;
use Nawasara\Core\Auth\Sudo;
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

if (! function_exists('sudo_active')) {
    /**
     * Whether the current session holds a valid sudo window — i.e. the
     * user recently completed the Keycloak OTP step-up.
     *
     * Use in Blade to toggle UI affordances:
     *
     *   @if (sudo_active())
     *       <button wire:click="dropDatabase">Hapus</button>
     *   @endif
     *
     * Gating the actual action still belongs to the `sudo` middleware or
     * the #[RequiresSudo] attribute — this is only for display.
     */
    function sudo_active(): bool
    {
        $userId = (int) Auth::id();

        return $userId > 0 && Sudo::isActive($userId);
    }
}

if (! function_exists('sudo_remaining_seconds')) {
    /**
     * Seconds left in the current sudo window, or 0 when not active.
     * Handy for a countdown badge.
     */
    function sudo_remaining_seconds(): int
    {
        $userId = (int) Auth::id();

        return $userId > 0 ? Sudo::remainingSeconds($userId) : 0;
    }
}
