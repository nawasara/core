<?php

namespace Nawasara\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Nawasara\Core\Services\SsoService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the Laravel session tied to the Keycloak SSO session for SSO logins.
 *
 * Laravel's session is independent of Keycloak's once Auth::login() runs, so a
 * user logged out on Keycloak would otherwise stay logged in to Nawasara until
 * the 120-min idle timeout. This middleware periodically (every
 * `nawasara.sso.check_interval` seconds, default 300) exchanges the stored
 * refresh_token at the realm token endpoint:
 *   - success   → the SSO session is alive; slide it forward, update tokens.
 *   - rejected  → the SSO session ended/was revoked → log the user out.
 *   - transport → Keycloak unreachable → leave the session alone (don't punish
 *                 the user for a network blip); retry next interval.
 *
 * Local (non-SSO) logins have no sso.refresh_token and pass through untouched.
 */
class EnsureKeycloakSession
{
    public function __construct(protected SsoService $sso)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Only relevant for an authenticated SSO session that stored a refresh
        // token. Local logins and guests are untouched.
        $refreshToken = session('sso.refresh_token');
        if (! Auth::check() || ! $refreshToken) {
            return $next($request);
        }

        $interval = (int) config('nawasara.sso.check_interval', 300);
        $checkedAt = (int) session('sso.checked_at', 0);

        if (now()->timestamp - $checkedAt < $interval) {
            return $next($request); // checked recently — no realm round-trip
        }

        $result = $this->sso->refreshToken($refreshToken);

        // Transport error → Keycloak unreachable. Don't log out on a blip;
        // just defer the next check a little so we're not hammering it.
        if (is_array($result) && ($result['error'] ?? null) === 'transport') {
            session(['sso.checked_at' => now()->timestamp - $interval + 30]);
            return $next($request);
        }

        // null → refresh rejected (invalid_grant): the SSO session is gone.
        if ($result === null) {
            Log::info('[sso] refresh rejected — logging out stale session', [
                'user_id' => Auth::id(),
            ]);
            return $this->logout($request);
        }

        // Success — SSO session still alive. Persist rotated tokens + timestamp.
        session([
            'sso.refresh_token' => $result['refresh_token'] ?? $refreshToken,
            'sso.id_token' => $result['id_token'] ?? session('sso.id_token'),
            'sso.checked_at' => now()->timestamp,
        ]);

        return $next($request);
    }

    /**
     * Tear down the Laravel session and bounce to login. For a normal request
     * we redirect; for JSON/XHR we return 401 so the SPA layer can react.
     */
    protected function logout(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sesi SSO telah berakhir. Silakan login kembali.'], 401);
        }

        return redirect()->route('login')
            ->withErrors(['sso' => 'Sesi SSO Anda telah berakhir. Silakan login kembali.']);
    }
}
