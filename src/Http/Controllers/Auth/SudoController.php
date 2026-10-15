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
 * confirms the step-up via the signed ID token, done by the same Keycloak
 * account (`sub` === users.keycloak_id). A bare SSO redirect that skipped
 * OTP, or a step-up by another account, is rejected. See SudoService for why.
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

        // Step-up hanya bermakna bila akun ini terhubung ke identitas
        // Keycloak. User lokal tanpa keycloak_id tidak punya `sub` untuk
        // dicocokkan, dan dulu dapat "membuktikan diri" memakai akun Keycloak
        // siapa pun. Audit keamanan 15 Oktober 2026, temuan T6.
        $user = Auth::user();
        if (! $this->keycloakSubject($user)) {
            return $this->rejectLocalUser($this->sudo->pullIntended());
        }

        return $this->sudo->redirect($user->username ?: $user->email);
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

        $expectedSubject = $this->keycloakSubject(Auth::user());
        if (! $expectedSubject) {
            return $this->rejectLocalUser($intended);
        }

        // Outcomes are flashed as a toast, not just withErrors(): the app layout
        // renders the toaster on every page but does not render an $errors bag,
        // so a bare withErrors() left the user staring at an unchanged page with
        // no idea why nothing happened.
        try {
            $outcome = $this->sudo->verifyCallback($expectedSubject);
        } catch (\Throwable $e) {
            Log::warning('[sudo] callback failed: '.$e->getMessage());

            return redirect()->to($intended)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Verifikasi sudo gagal: '.$e->getMessage(),
                    'options' => [],
                ])
                ->withErrors(['sudo' => 'Verifikasi sudo gagal: '.$e->getMessage()]);
        }

        if ($outcome === SudoService::OUTCOME_SUBJECT_MISMATCH) {
            // Step-up diselesaikan oleh akun Keycloak yang BUKAN pemilik
            // sesi ini. Bisa salah ketik akun, bisa juga sesi yang dibajak
            // (temuan T6). Jendela sudo tidak dibuka, dan kejadiannya
            // dicatat supaya dapat ditelusuri.
            Log::warning('[sudo] step-up subject mismatch: Keycloak account differs from session user', [
                'user_id' => Auth::id(),
            ]);

            return redirect()->to($intended)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Konfirmasi sudo ditolak: masuklah memakai akun Keycloak Anda sendiri, bukan akun lain.',
                    'options' => ['duration' => 9000],
                ])
                ->withErrors(['sudo' => 'Konfirmasi sudo harus memakai akun Anda sendiri.']);
        }

        if ($outcome !== SudoService::OUTCOME_OK) {
            // Step-up did not happen (no acr=sudo in the ID token). Reject —
            // do NOT open the window on an un-stepped-up session.
            Log::warning('[sudo] step-up not satisfied — acr claim missing or wrong', [
                'user_id' => Auth::id(),
            ]);

            return redirect()->to($intended)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Konfirmasi sudo belum lengkap — verifikasi tambahan (OTP) tidak terpenuhi. Hubungi admin bila berulang.',
                    'options' => ['duration' => 9000],
                ])
                ->withErrors(['sudo' => 'Konfirmasi sudo tidak lengkap. Coba lagi.']);
        }

        Sudo::confirm((int) Auth::id());

        // Tell the user the window is open AND that the action must be repeated:
        // the step-up aborts the original action, it does not replay it.
        return redirect()->to($intended)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Mode sudo aktif '.Sudo::windowMinutes().' menit — silakan ulangi aksi tadi.',
                'options' => ['duration' => 8000],
            ])
            ->with('status', 'Mode sudo aktif selama '.Sudo::windowMinutes().' menit.');
    }

    /**
     * `keycloak_id` user yang sedang login, atau null bila akun lokal.
     */
    protected function keycloakSubject(mixed $user): ?string
    {
        $sub = $user?->keycloak_id ?? null;

        return is_string($sub) && $sub !== '' ? $sub : null;
    }

    /**
     * Tolak step-up untuk akun yang tidak terhubung ke Keycloak.
     */
    protected function rejectLocalUser(string $intended): RedirectResponse
    {
        Log::warning('[sudo] step-up refused: account has no keycloak_id', [
            'user_id' => Auth::id(),
        ]);

        return redirect()->to($intended)
            ->with('toast', [
                'type' => 'error',
                'message' => 'Mode sudo hanya tersedia untuk akun yang terhubung ke SSO Keycloak. Akun lokal tidak dapat menjalankan aksi kritis.',
                'options' => ['duration' => 9000],
            ])
            ->withErrors(['sudo' => 'Akun ini belum terhubung ke SSO, jadi tidak dapat masuk mode sudo.']);
    }
}
