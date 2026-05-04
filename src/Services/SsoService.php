<?php

namespace Nawasara\Core\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Nawasara\Vault\Facades\Vault;

/**
 * SSO orchestration — baca credential dari Vault group `sso` lalu delegate
 * ke Laravel Socialite. Driver dynamic (config-driven) supaya bisa swap
 * dari keycloak ke google/saml tanpa edit code.
 *
 * Pattern konsisten dengan service lain: credential di Vault, audit log
 * lewat Vault::get(), bisa rotate tanpa redeploy.
 */
class SsoService
{
    /**
     * Hydrate Socialite config dari Vault sebelum panggil driver.
     * Wajib di-call sebelum Socialite::driver(...).
     */
    protected function hydrateSocialiteConfig(): void
    {
        $driver = $this->driver();

        $base = [
            'client_id' => (string) Vault::get('sso', 'client_id'),
            'client_secret' => (string) Vault::get('sso', 'client_secret'),
            'redirect' => (string) Vault::get('sso', 'redirect_uri'),
        ];

        if ($driver === 'keycloak') {
            $base['base_url'] = rtrim((string) Vault::get('sso', 'base_url'), '/');
            $base['realms'] = (string) (Vault::get('sso', 'realm') ?: 'master');
        }

        Config::set("services.{$driver}", $base);
    }

    public function driver(): string
    {
        return (string) (Vault::get('sso', 'driver') ?: 'keycloak');
    }

    public function isConfigured(): bool
    {
        return Vault::isConfigured('sso');
    }

    public function redirect()
    {
        $this->hydrateSocialiteConfig();
        return Socialite::driver($this->driver())->redirect();
    }

    /**
     * Resolve user dari provider callback. Return normalised array
     * supaya controller tidak bergantung ke shape spesifik provider.
     *
     * @return array{email:?string, name:?string, id:?string, username:?string}
     */
    public function callback(): array
    {
        $this->hydrateSocialiteConfig();
        $user = Socialite::driver($this->driver())->user();

        // username preference order:
        //   1. nickname (provider-agnostic accessor)
        //   2. preferred_username (Keycloak/OIDC standard claim)
        //   3. email local-part (last resort)
        $username = $user->getNickname()
            ?? ($user->user['preferred_username'] ?? null)
            ?? ($user->getEmail() ? strtolower(strstr($user->getEmail(), '@', true)) : null);

        return [
            'email' => $user->getEmail(),
            'name' => $user->getName() ?? $username,
            'id' => (string) $user->getId(),
            'username' => $username,
        ];
    }

    /**
     * Dipanggil dari Vault credential list "Test Connection" button.
     * Probe OIDC discovery endpoint — kalau respond, credential URL valid.
     * Tidak benar-benar test login (butuh redirect interaktif).
     *
     * Return shape mengikuti Vault convention: {success, message}.
     */
    public function testConnection(?string $instance = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Field SSO belum lengkap di Vault.'];
        }

        $driver = $this->driver();
        $base = rtrim((string) Vault::get('sso', 'base_url'), '/');
        $realm = (string) (Vault::get('sso', 'realm') ?: 'master');

        try {
            $url = match ($driver) {
                'keycloak' => "{$base}/realms/{$realm}/.well-known/openid-configuration",
                default => null,
            };

            if (! $url) {
                return ['success' => false, 'message' => "Driver `{$driver}` belum punya test discovery endpoint."];
            }

            $response = Http::timeout(5)->get($url);

            if (! $response->successful()) {
                return ['success' => false, 'message' => "Discovery endpoint HTTP {$response->status()} — cek base_url & realm."];
            }

            $issuer = $response->json('issuer');
            return [
                'success' => true,
                'message' => 'OIDC discovery OK. Issuer: '.($issuer ?? 'unknown'),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }
}
