<?php

namespace Nawasara\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nawasara\Core\Auth\AuthMode;
use Nawasara\Core\Models\UserEmailLink;
use Nawasara\Core\Services\SsoService;
use Spatie\Permission\Models\Role;

/**
 * SSO redirect + callback handler. Pakai Socialite via SsoService;
 * pre-conditions (mode aktif + Vault configured) dicek di sini supaya
 * controller route tetap callable bahkan saat config setengah jadi.
 *
 * Auto-provision flow:
 *   - User exist + auth_type='sso'  → update profile + login
 *   - User exist + auth_type='local'→ reject (cegah hijack via SSO)
 *   - User tidak exist + auto_provision aktif → create + assign default role + login
 *   - User tidak exist + auto_provision off → reject
 */
class SsoController extends Controller
{
    public function __construct(protected SsoService $sso)
    {
    }

    public function redirect(): mixed
    {
        if (! AuthMode::isSsoEnabled()) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Login SSO sedang dinonaktifkan oleh administrator.']);
        }

        if (! $this->sso->isConfigured()) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'SSO belum dikonfigurasi (Vault group `sso` belum lengkap).']);
        }

        return $this->sso->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! AuthMode::isSsoEnabled() || ! $this->sso->isConfigured()) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'SSO tidak aktif. Hubungi administrator.']);
        }

        try {
            $userData = $this->sso->callback();
        } catch (\Throwable $e) {
            Log::warning('[sso] callback failed: '.$e->getMessage());
            return redirect()->route('login')
                ->withErrors(['sso' => 'SSO gagal: '.$e->getMessage()]);
        }

        $username = $userData['username'] ?? null;
        $email = $userData['email'] ?? null;

        if (! $username && ! $email) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'IdP tidak mengirim username/email.']);
        }

        // Match by username dulu (preferred_username Keycloak — stable),
        // fallback ke email kalau username belum pernah ada di DB
        $user = $username ? User::where('username', $username)->first() : null;
        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // Reject hijack — user lokal tidak boleh login lewat SSO
            if (method_exists($user, 'isLocal') && $user->isLocal()) {
                return redirect()->route('login')
                    ->withErrors(['sso' => 'Akun ini terdaftar sebagai akun lokal. Login pakai password.']);
            }

            $user->update(array_filter([
                'name' => $userData['name'] ?? null,
                'email' => $email,
            ]));


            $this->syncEmailLinks($user, $userData['kominfo_emails'] ?? []);

            Auth::login($user, true);
            return redirect()->intended('/home');
        }

        // User belum exist — cek auto-provision
        if (! AuthMode::autoProvision()) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Akun Anda belum terdaftar di Nawasara. Hubungi administrator.']);
        }

        // Auto-provision: create user dengan role default
        try {
            $user = DB::transaction(function () use ($username, $email, $userData) {
                $user = User::create([
                    'name' => $userData['name'] ?? $username ?? 'SSO User',
                    'username' => $username ?? Str::before($email, '@'),
                    'email' => $email ?? ($username.'@sso.local'),
                    'password' => bcrypt(Str::random(40)), // unused, just satisfy NOT NULL
                    'auth_type' => 'sso',
                ]);

                $defaultRole = AuthMode::defaultSsoRole();
                if (Role::where('name', $defaultRole)->exists()) {
                    $user->assignRole($defaultRole);
                }

                return $user;
            });
        } catch (\Throwable $e) {
            Log::error('[sso] auto-provision failed: '.$e->getMessage(), [
                'username' => $username,
                'email' => $email,
            ]);
            return redirect()->route('login')
                ->withErrors(['sso' => 'Gagal membuat akun otomatis: '.$e->getMessage()]);
        }

        $this->syncEmailLinks($user, $userData['kominfo_emails'] ?? []);

        Auth::login($user, true);
        return redirect()->intended('/home');
    }

    /**
     * Upsert UserEmailLink rows untuk user dari claim Keycloak `kominfo_email`.
     *
     * Manual link (admin override) tidak ke-touch — claim hanya update/create
     * row dengan source=`sso_attribute`. Kalau attribute Keycloak diubah dari
     * 2 mailbox jadi 1, row sso_attribute lama yang tidak lagi di-claim akan
     * di-prune (manual override tetap aman).
     */
    protected function syncEmailLinks(User $user, array $kominfoEmails): void
    {
        try {
            DB::transaction(function () use ($user, $kominfoEmails) {
                // Upsert claim emails
                foreach ($kominfoEmails as $mailbox) {
                    UserEmailLink::updateOrCreate(
                        ['user_id' => $user->id, 'email_account' => $mailbox],
                        ['source' => UserEmailLink::SOURCE_SSO_ATTRIBUTE, 'linked_at' => now()],
                    );
                }

                // Prune sso_attribute rows yang tidak lagi ada di claim
                if (! empty($kominfoEmails)) {
                    UserEmailLink::query()
                        ->where('user_id', $user->id)
                        ->where('source', UserEmailLink::SOURCE_SSO_ATTRIBUTE)
                        ->whereNotIn('email_account', $kominfoEmails)
                        ->delete();
                }
            });
        } catch (\Throwable $e) {
            // Jangan fail login kalau mapping sync gagal — log saja, admin bisa
            // troubleshoot kemudian. Webmail launch akan tampil "belum terhubung"
            // sampai mapping berhasil.
            Log::warning('[sso] sync email links failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'kominfo_emails' => $kominfoEmails,
            ]);
        }
    }
}
