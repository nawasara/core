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

        // Sesi impersonasi admin DITOLAK — diperiksa sebelum kedua jalur login
        // (user existing maupun auto-provision), supaya tidak ada satu pun
        // celah yang terlewat.
        //
        // ⚠️ **Ini jaring pengaman, BUKAN penjaga utama.**
        //
        // Nawasara mengenali orang dari klaim `sub`, dan token impersonasi
        // membawa `sub` MILIK KORBAN — jadi tanpa penanda tambahan ia tidak
        // dapat dibedakan dari login yang sah. Penanda itu tidak selalu
        // dikirim Keycloak; pada banyak pemasangan ia baru ada setelah admin
        // menambahkan protocol mapper. Karena itu:
        //
        //   Yang benar-benar menutup celah = TIDAK memberi role
        //   `impersonation` kepada siapa pun di Keycloak.
        //   Lihat docs/panduan/cabut-akses-master-keycloak.md
        //
        // Alasannya nyata: satu akun admin master yang dipakai bersama dapat
        // menyamar menjadi pemegang role `developer` — kuasa penuh atas
        // Nawasara — tanpa perlu tahu sandinya, dan log Keycloak hanya
        // mencatat "admin" sehingga pelakunya tak dapat dibedakan.
        // Ditemukan 24 Agustus 2026.
        if (! empty($userData['impersonator'])) {
            Log::warning('[sso] login impersonasi ditolak', [
                'korban' => $username ?? $email,
                'pelaku' => $userData['impersonator'],
                'ip' => request()->ip(),
            ]);

            return redirect()->route('login')->withErrors([
                'sso' => 'Sesi impersonasi tidak diizinkan masuk ke Nawasara. '
                    .'Silakan masuk memakai akun Anda sendiri.',
            ]);
        }

        // Match by keycloak_id (sub) dulu — stabil terhadap rename — lalu
        // username, lalu email. Urutan ini dipusatkan di provisioner supaya
        // sama persis dengan jalur provisioning lain (registry, form user).
        $provisioner = $this->provisioner();
        $user = $provisioner
            ? $provisioner->findLocal($userData['id'] ?? null, $username, $email)
            : $this->findLocalFallback($username, $email);

        if ($user) {
            // Reject hijack — user lokal tidak boleh login lewat SSO
            if (method_exists($user, 'isLocal') && $user->isLocal()) {
                return redirect()->route('login')
                    ->withErrors(['sso' => 'Akun ini terdaftar sebagai akun lokal. Login pakai password.']);
            }

            if ($provisioner) {
                // Menyegarkan nama/email sekaligus mengunci tautan keycloak_id
                // untuk baris lama yang dulu ketemu lewat string match.
                $provisioner->fromClaims($userData);
                $user->refresh();
            } else {
                $user->update(array_filter([
                    'name' => $userData['name'] ?? null,
                    'email' => $email,
                ]));
            }

            $this->syncEmailLinks($user, $userData['kominfo_emails'] ?? []);

            // NO remember-me for SSO: a remember cookie silently re-authenticates
            // the user after their session expires WITHOUT going through this
            // callback, so no sso.* tokens are stored and EnsureKeycloakSession
            // can never tie the session to Keycloak's lifecycle. An SSO session
            // must live and die with Keycloak — when it expires, the user
            // re-authenticates through the realm (and is bounced if logged out).
            Auth::login($user);
            $this->storeSsoTokens($userData);
            return redirect()->intended('/home');
        }

        // User belum exist — cek auto-provision
        if (! AuthMode::autoProvision()) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Akun Anda belum terdaftar di Nawasara. Hubungi administrator.']);
        }

        // Auto-provision: create user dengan role default
        try {
            $user = $provisioner
                ? $provisioner->fromClaims($userData)
                : $this->createFallback($username, $email, $userData);
        } catch (\Throwable $e) {
            Log::error('[sso] auto-provision failed: '.$e->getMessage(), [
                'username' => $username,
                'email' => $email,
            ]);
            return redirect()->route('login')
                ->withErrors(['sso' => 'Gagal membuat akun otomatis: '.$e->getMessage()]);
        }

        $this->syncEmailLinks($user, $userData['kominfo_emails'] ?? []);

        // No remember-me for SSO — see the note on the other Auth::login above.
        Auth::login($user);
        $this->storeSsoTokens($userData);
        return redirect()->intended('/home');
    }

    /**
     * KeycloakUserProvisioner kalau package nawasara/keycloak terpasang.
     *
     * Core sengaja tidak hard-depend ke package keycloak — login SSO harus
     * tetap jalan di instalasi yang hanya memasang core. Kalau package-nya ada
     * (kasus normal), provisioner dipakai supaya aturan penautan dan pemberian
     * role default identik dengan jalur provisioning lain.
     */
    protected function provisioner(): ?object
    {
        $class = '\Nawasara\Keycloak\Support\KeycloakUserProvisioner';

        return class_exists($class) ? app($class) : null;
    }

    /** Pencocokan username→email untuk instalasi tanpa package keycloak. */
    protected function findLocalFallback(?string $username, ?string $email): ?User
    {
        $user = $username ? User::where('username', $username)->first() : null;

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        return $user;
    }

    /**
     * Auto-provision untuk instalasi tanpa package keycloak.
     *
     * @param  array<string,mixed>  $userData
     */
    protected function createFallback(?string $username, ?string $email, array $userData): User
    {
        return DB::transaction(function () use ($username, $email, $userData) {
            $user = User::create([
                'name' => $userData['name'] ?? $username ?? 'SSO User',
                'username' => $username ?? Str::before($email, '@'),
                'email' => $email ?? ($username.'@sso.local'),
                'password' => bcrypt(Str::random(40)), // unused — login selalu lewat SSO
                'auth_type' => 'sso',
                'keycloak_id' => $userData['id'] ?? null,
            ]);

            $defaultRole = AuthMode::defaultSsoRole();
            if (Role::where('name', $defaultRole)->exists()) {
                $user->assignRole($defaultRole);
            }

            return $user;
        });
    }

    /**
     * Persist the Keycloak tokens into the session so the app can keep the
     * Laravel session tied to the Keycloak session:
     *   - refresh_token → periodic liveness check (EnsureKeycloakSession)
     *   - id_token      → id_token_hint for RP-initiated logout
     * Also stamps the initial check time so the middleware doesn't refresh on
     * the very first request.
     *
     * @param  array<string,mixed>  $userData
     */
    protected function storeSsoTokens(array $userData): void
    {
        session([
            'sso.refresh_token' => $userData['refresh_token'] ?? null,
            'sso.id_token' => $userData['id_token'] ?? null,
            'sso.checked_at' => now()->timestamp,
        ]);
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
