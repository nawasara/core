<?php

namespace Nawasara\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Nawasara\Core\Auth\Sudo;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level sudo gate. Apply as the `sudo` middleware alias to any route
 * that performs a critical/destructive action.
 *
 *   Route::get('db/drop/{name}', ...)->middleware(['auth', 'sudo']);
 *
 * If the user is inside a valid sudo window, the request passes straight
 * through. Otherwise it is bounced to /sudo/redirect with the current URL
 * as `intended`, so the user lands back here after the OTP step-up.
 *
 * This middleware assumes `auth` ran first — sudo is a step-UP on an
 * existing session. A guest hitting a sudo route is sent to login by
 * `auth`; this class only ever sees authenticated users.
 */
class EnsureSudo
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) Auth::id();

        if ($userId > 0 && Sudo::isActive($userId)) {
            return $next($request);
        }

        // Not in a sudo window — start a step-up, returning here afterwards.
        $intended = $request->fullUrl();

        return redirect()->route('sudo.redirect', ['intended' => $intended]);
    }
}
