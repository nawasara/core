<?php

namespace Nawasara\Core\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Nawasara\Core\Services\SsoService;

/**
 * RP-initiated logout: after Fortify tears down the Laravel session, redirect
 * the browser to Keycloak's end_session_endpoint so the SSO session ends too.
 *
 * The id_token (needed as id_token_hint) is captured into a static by the
 * Logout event listener BEFORE Fortify invalidates the session — by the time
 * this response runs, the session is already gone. If there's no id_token
 * (local login, or SSO without a stored token), fall back to Fortify's normal
 * redirect home.
 */
class KeycloakLogoutResponse implements LogoutResponseContract
{
    /** id_token stashed by the Logout event listener, consumed once here. */
    public static ?string $idToken = null;

    /** True when the session being torn down was an SSO login. */
    public static bool $wasSso = false;

    public function __construct(protected SsoService $sso)
    {
    }

    public function toResponse($request)
    {
        $idToken = static::$idToken;
        $wasSso = static::$wasSso;

        // Consume the one-shot state so a later local logout won't reuse it.
        static::$idToken = null;
        static::$wasSso = false;

        $home = config('fortify.home', '/');

        // Only do RP-initiated logout for SSO sessions, and only if enabled.
        if ($wasSso && config('nawasara.sso.rp_logout', true)) {
            $postLogout = url($home);
            $url = $this->sso->logoutUrl($idToken, $postLogout);
            if ($url) {
                return $request->wantsJson()
                    ? response()->json(['redirect' => $url])
                    : redirect()->away($url);
            }
        }

        return $request->wantsJson()
            ? response()->json(['redirect' => $home])
            : redirect($home);
    }
}
