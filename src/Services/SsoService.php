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
     * `kominfo_emails` adalah list mailbox @ponorogo.go.id dari custom
     * Keycloak claim (defaults `kominfo_email`). Multi-value diserialize
     * sebagai delimited string oleh Keycloak — kita split di sini.
     *
     * @return array{email:?string, name:?string, id:?string, username:?string, kominfo_emails:array<int,string>}
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
            'kominfo_emails' => $this->extractKominfoEmails($user->user ?? []),
            // Tokens for keeping the Laravel session tied to the Keycloak session:
            //   refresh_token → periodic liveness check (middleware)
            //   id_token      → id_token_hint for RP-initiated logout
            'refresh_token' => $user->refreshToken ?? null,
            'access_token' => $user->token ?? null,
            'id_token' => $user->accessTokenResponseBody['id_token'] ?? null,
        ];
    }

    /**
     * Exchange a refresh_token for a new token set at the realm token endpoint.
     * Used by the session-liveness middleware: a successful refresh proves the
     * Keycloak SSO session is still alive (and slides it forward); a failure
     * means the SSO session was logged out or revoked.
     *
     * Returns:
     *   - array with new tokens        → session still alive
     *   - null                         → refresh rejected (invalid_grant) → log out
     *   - ['error' => 'transport']     → Keycloak unreachable → DON'T log out (blip)
     *
     * @return array<string,mixed>|null
     */
    public function refreshToken(string $refreshToken): ?array
    {
        $driver = $this->driver();
        if ($driver !== 'keycloak') {
            return null; // only keycloak supported for now
        }

        $base = rtrim((string) Vault::get('sso', 'base_url'), '/');
        $realm = (string) (Vault::get('sso', 'realm') ?: 'master');
        $url = "{$base}/realms/{$realm}/protocol/openid-connect/token";

        try {
            $response = Http::asForm()->timeout(8)->post($url, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => (string) Vault::get('sso', 'client_id'),
                'client_secret' => (string) Vault::get('sso', 'client_secret'),
            ]);
        } catch (\Throwable $e) {
            // Transport error (Keycloak unreachable) — return a distinct signal
            // so the caller can decide NOT to log the user out on a blip.
            return ['error' => 'transport'];
        }

        if (! $response->successful()) {
            // 400 invalid_grant = session ended/revoked → caller logs out.
            return null;
        }

        $body = $response->json();

        return [
            'access_token' => (string) ($body['access_token'] ?? ''),
            'refresh_token' => (string) ($body['refresh_token'] ?? $refreshToken),
            'id_token' => $body['id_token'] ?? null,
        ];
    }

    /**
     * Build the Keycloak RP-initiated logout URL (end_session_endpoint) so
     * logging out of Nawasara also ends the Keycloak SSO session.
     */
    public function logoutUrl(?string $idToken, string $postLogoutRedirect): ?string
    {
        if ($this->driver() !== 'keycloak') {
            return null;
        }

        $base = rtrim((string) Vault::get('sso', 'base_url'), '/');
        $realm = (string) (Vault::get('sso', 'realm') ?: 'master');
        $endpoint = "{$base}/realms/{$realm}/protocol/openid-connect/logout";

        $params = ['post_logout_redirect_uri' => $postLogoutRedirect];
        if ($idToken) {
            $params['id_token_hint'] = $idToken;
        } else {
            // Without an id_token_hint Keycloak requires client_id to honour
            // the redirect.
            $params['client_id'] = (string) Vault::get('sso', 'client_id');
        }

        return $endpoint.'?'.http_build_query($params);
    }

    /**
     * Extract list mailbox @ponorogo.go.id dari custom Keycloak claim.
     *
     * Keycloak attribute biasanya single-value, tapi kalau admin set
     * sebagai multi-value, mapper akan serialize jadi string dengan
     * delimiter (default koma). Defensive split — kalau tidak ada delimiter,
     * tetap return 1-element array.
     *
     * @return array<int, string>  list email lowercased + trimmed, deduped
     */
    protected function extractKominfoEmails(array $userPayload): array
    {
        $claim = (string) config('nawasara.webmail.sso_claim', 'kominfo_email');
        $delimiter = (string) config('nawasara.webmail.claim_delimiter', ',');

        $raw = $userPayload[$claim] ?? null;
        if ($raw === null || $raw === '') {
            return [];
        }

        // Keycloak bisa kasih array (multi-value attribute) atau string
        $values = is_array($raw) ? $raw : explode($delimiter, (string) $raw);

        return collect($values)
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->filter(fn ($v) => $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
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
