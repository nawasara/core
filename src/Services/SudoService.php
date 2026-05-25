<?php

namespace Nawasara\Core\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Nawasara\Vault\Facades\Vault;

/**
 * Sudo mode orchestration — GitHub-style step-up re-authentication.
 *
 * Re-uses the SAME Keycloak realm/client as normal SSO (driver `keycloak`,
 * Vault group `sso`), but drives a step-up: the redirect carries
 * `acr_values=sudo`, which Keycloak maps (ACR-to-LoA) to the `Sudo Step-up`
 * flow — Cookie + OTP Form, no password prompt.
 *
 * The callback does NOT trust "the redirect came back" as proof of step-up.
 * It decodes the ID token, verifies the signature against Keycloak's JWKS,
 * and asserts the `acr` claim equals `SUDO_ACR`. Without that check, a plain
 * SSO redirect would satisfy sudo mode and the whole feature would be
 * security theatre.
 *
 * Session window after a successful step-up is owned by SudoController
 * (`sudo_confirmed_at`); this service only proves the step-up happened.
 */
class SudoService
{
    /** Session key holding the redirect_uri override used for the sudo leg. */
    protected const REDIRECT_OVERRIDE_KEY = '_sudo_redirect_uri';

    /**
     * ACR value requested from Keycloak and expected back in the ID token.
     * Mapped to LoA 1 in the `nawasara-sso-client` ACR-to-LoA config.
     *
     * Read from auth-primitives config: the constant lives in the
     * primitives package so middleware / attribute / service all share
     * one definition.
     */
    public function sudoAcr(): string
    {
        return (string) config('auth-primitives.sudo.acr', 'sudo');
    }

    /**
     * Hydrate Socialite's keycloak config from Vault.
     *
     * Mirrors SsoService::hydrateSocialiteConfig but forces the sudo
     * callback as the `redirect` — Keycloak validates redirect_uri exactly,
     * so the sudo leg must use its own registered URI (/sudo/callback).
     */
    protected function hydrateSocialiteConfig(): void
    {
        $base = [
            'client_id' => (string) Vault::get('sso', 'client_id'),
            'client_secret' => (string) Vault::get('sso', 'client_secret'),
            'redirect' => route('sudo.callback'),
            'base_url' => rtrim((string) Vault::get('sso', 'base_url'), '/'),
            'realms' => (string) (Vault::get('sso', 'realm') ?: 'master'),
        ];

        Config::set('services.keycloak', $base);
    }

    /**
     * SSO must be configured (Vault group `sso` complete) — sudo mode
     * piggybacks on the same credentials.
     */
    public function isConfigured(): bool
    {
        return Vault::isConfigured('sso');
    }

    /**
     * Build the step-up redirect to Keycloak.
     *
     * `acr_values=sudo`  → triggers the Sudo Step-up flow via ACR-to-LoA.
     * `prompt=login`     → defensive: even if ACR mapping were misread,
     *                      Keycloak is asked to actively re-authenticate
     *                      rather than silently reuse the SSO session.
     */
    public function redirect()
    {
        $this->hydrateSocialiteConfig();

        return Socialite::driver('keycloak')
            ->with([
                'acr_values' => $this->sudoAcr(),
                'prompt' => 'login',
            ])
            ->redirect();
    }

    /**
     * Handle the Keycloak callback for the sudo leg.
     *
     * Returns true ONLY when the ID token is signature-valid AND its `acr`
     * claim equals SUDO_ACR — i.e. the user genuinely completed the OTP
     * step-up. Any failure (no id_token, bad signature, wrong acr) returns
     * false; the caller treats that as "sudo not granted".
     *
     * @throws \Throwable on transport errors from Socialite (bad code, etc.)
     */
    public function verifyCallback(): bool
    {
        $this->hydrateSocialiteConfig();

        $user = Socialite::driver('keycloak')->user();

        $idToken = $user->accessTokenResponseBody['id_token'] ?? null;
        if (! is_string($idToken) || $idToken === '') {
            return false;
        }

        $claims = $this->decodeIdToken($idToken);
        if ($claims === null) {
            return false;
        }

        return $this->acrClaim($claims) === $this->sudoAcr();
    }

    /**
     * Decode + verify an ID token against Keycloak's published JWKS.
     *
     * The signing keys are fetched from the realm's `certs` endpoint and
     * cached 1 hour — Keycloak rotates keys rarely and always publishes the
     * new key before signing with it, so a stale cache self-heals on the
     * next miss. Returns the claims array, or null if verification fails.
     *
     * @return array<string,mixed>|null
     */
    protected function decodeIdToken(string $idToken): ?array
    {
        try {
            $jwks = $this->jwks();
            $decoded = JWT::decode($idToken, JWK::parseKeySet($jwks));

            return (array) $decoded;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Fetch (and cache) the realm JWKS used to verify ID token signatures.
     *
     * @return array<string,mixed>
     */
    protected function jwks(): array
    {
        $base = rtrim((string) Vault::get('sso', 'base_url'), '/');
        $realm = (string) (Vault::get('sso', 'realm') ?: 'master');
        $url = "{$base}/realms/{$realm}/protocol/openid-connect/certs";

        return Cache::remember(
            'nawasara:sudo:jwks:'.md5($url),
            now()->addHour(),
            fn () => Http::timeout(5)->get($url)->throw()->json(),
        );
    }

    /**
     * Read the `acr` claim. Keycloak emits it as a scalar string; guard
     * against an array shape just in case a mapper is misconfigured.
     *
     * @param  array<string,mixed>  $claims
     */
    protected function acrClaim(array $claims): ?string
    {
        $acr = $claims['acr'] ?? null;

        if (is_array($acr)) {
            $acr = $acr[0] ?? null;
        }

        return is_string($acr) ? $acr : null;
    }

    /**
     * Stash the post-sudo destination so the callback can bounce the user
     * back to where they were headed.
     *
     * The destination originates from a query param (and ultimately, for
     * Livewire actions, the Referer header) — both attacker-influenceable.
     * Only same-origin URLs are stored; anything else is dropped so the
     * callback can never be turned into an open redirect.
     */
    public function setIntended(string $url): void
    {
        if ($this->isSafeIntended($url)) {
            Session::put(self::REDIRECT_OVERRIDE_KEY, $url);
        }
    }

    /**
     * Pull (and clear) the stashed destination. Falls back to the
     * dashboard when nothing safe was stored.
     */
    public function pullIntended(): string
    {
        $url = Session::pull(self::REDIRECT_OVERRIDE_KEY);

        return (is_string($url) && $this->isSafeIntended($url))
            ? $url
            : route('dashboard');
    }

    /**
     * A destination is safe only when it resolves to this app's own
     * origin (scheme + host + port). Relative paths are accepted; absolute
     * URLs to any other host are rejected.
     */
    protected function isSafeIntended(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        // Relative path ("/sudo/demo") — same-origin by definition.
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $target = parse_url($url);
        $appHost = parse_url((string) config('app.url'));

        if (! isset($target['host'], $appHost['host'])) {
            return false;
        }

        return strcasecmp($target['host'], $appHost['host']) === 0
            && ($target['port'] ?? null) == ($appHost['port'] ?? null);
    }
}
