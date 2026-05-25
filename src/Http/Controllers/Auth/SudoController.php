<?php

namespace Nawasara\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Nawasara\AuthPrimitives\Auth\Sudo;
use Nawasara\Core\Services\SudoService;

/**
 * Sudo step-up handler — GitHub-style re-authentication.
 *
 *   /sudo/redirect  → stash where the user was headed, bounce to Keycloak
 *                     with acr_values=sudo (Sudo Step-up flow → OTP only).
 *   /sudo/callback  → verify the ID token's `acr` claim really is `sudo`,
 *                     open the 15-minute sudo window, return to intended.
 *
 * Both routes require an authenticated user — sudo mode is a step-UP on top
 * of an existing session, never a login path. The `auth` middleware on the
 * route group enforces that.
 *
 * Crucially, the callback grants sudo ONLY when SudoService::verifyCallback
 * confirms the step-up via the signed ID token. A bare SSO redirect that
 * skipped OTP returns false and is rejected — see SudoService for why.
 */
class SudoController extends Controller
{
    public function __construct(protected SudoService $sudo)
    {
    }

    /**
     * Begin a step-up. `intended` is the URL to return to afterwards;
     * callers (middleware, Livewire) pass the page that needs sudo.
     */
    public function redirect(Request $request): mixed
    {
        if (! $this->sudo->isConfigured()) {
            return redirect()->route('dashboard')
                ->withErrors(['sudo' => 'Sudo mode tidak tersedia — SSO belum dikonfigurasi.']);
        }

        $intended = $request->query('intended');
        if (is_string($intended) && $intended !== '') {
            $this->sudo->setIntended($intended);
        }

        return $this->sudo->redirect();
    }

    /**
     * Keycloak callback for the sudo leg. Open the window only on a
     * verified step-up.
     */
    public function callback(): RedirectResponse
    {
        if (! $this->sudo->isConfigured()) {
            return redirect()->route('dashboard')
                ->withErrors(['sudo' => 'Sudo mode tidak tersedia — SSO belum dikonfigurasi.']);
        }

        $intended = $this->sudo->pullIntended();

        try {
            $verified = $this->sudo->verifyCallback();
        } catch (\Throwable $e) {
            Log::warning('[sudo] callback failed: '.$e->getMessage());

            return redirect()->to($intended)
                ->withErrors(['sudo' => 'Verifikasi sudo gagal: '.$e->getMessage()]);
        }

        if (! $verified) {
            // Step-up did not happen (no acr=sudo in the ID token). Reject —
            // do NOT open the window on an un-stepped-up session.
            Log::warning('[sudo] step-up not satisfied — acr claim missing or wrong', [
                'user_id' => Auth::id(),
            ]);

            return redirect()->to($intended)
                ->withErrors(['sudo' => 'Konfirmasi sudo tidak lengkap. Coba lagi.']);
        }

        Sudo::confirm((int) Auth::id());

        return redirect()->to($intended)
            ->with('status', 'Mode sudo aktif selama '.Sudo::windowMinutes().' menit.');
    }
}
