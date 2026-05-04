<?php

namespace Nawasara\Core\Auth;

use Nawasara\Core\Models\Setting;

/**
 * Single source of truth untuk authentication mode.
 *
 * Mode disimpan di Setting table (key `auth.mode`) — admin bisa toggle
 * lewat UI tanpa redeploy. Default `local` kalau Setting belum ada.
 *
 * Auto-fallback ke `local` kalau mode `sso` aktif tapi Vault SSO
 * credential belum configured — biar admin tidak terkunci dari sistem
 * gara-gara salah toggle.
 */
final class AuthMode
{
    public const LOCAL = 'local';
    public const SSO = 'sso';
    public const BOTH = 'both';

    public const ALL_MODES = [self::LOCAL, self::SSO, self::BOTH];

    /**
     * Effective mode setelah safety fallback.
     *
     * Kalau setting bilang `sso` atau `both` tapi Vault credential
     * belum lengkap, downgrade ke `local` supaya admin tetap bisa login.
     */
    public static function current(): string
    {
        $configured = self::raw();

        if ($configured === self::LOCAL) {
            return self::LOCAL;
        }

        // Mode butuh SSO — pastikan Vault sudah configured
        if (! self::ssoConfigured()) {
            // Mode `both` degraded jadi `local` (SSO disabled, form tetap jalan)
            // Mode `sso` degraded jadi `local` (admin tetap bisa login lewat form)
            return self::LOCAL;
        }

        return $configured;
    }

    /**
     * Mode mentah dari Setting tanpa fallback. Untuk Setting UI yang
     * mau tampilkan apa yang admin set vs apa yang effective.
     */
    public static function raw(): string
    {
        $value = (string) Setting::get('auth.mode', self::LOCAL);
        return in_array($value, self::ALL_MODES, true) ? $value : self::LOCAL;
    }

    public static function isLocalEnabled(): bool
    {
        $mode = self::current();
        return $mode === self::LOCAL || $mode === self::BOTH;
    }

    public static function isSsoEnabled(): bool
    {
        $mode = self::current();
        return $mode === self::SSO || $mode === self::BOTH;
    }

    public static function isExclusiveSso(): bool
    {
        return self::current() === self::SSO;
    }

    public static function autoProvision(): bool
    {
        return (bool) Setting::get('auth.sso.auto_provision', true);
    }

    public static function defaultSsoRole(): string
    {
        return (string) Setting::get('auth.sso.default_role', 'guest');
    }

    /**
     * Apakah Vault SSO group ter-configure (semua field required terisi).
     * Helper kecil supaya tidak hard-depend ke Vault facade kalau dipanggil
     * dari context tanpa container (e.g. early boot).
     */
    public static function ssoConfigured(): bool
    {
        if (! class_exists(\Nawasara\Vault\Facades\Vault::class)) {
            return false;
        }

        try {
            return \Nawasara\Vault\Facades\Vault::isConfigured('sso');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
