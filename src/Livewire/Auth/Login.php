<?php

namespace Nawasara\Core\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Nawasara\Core\Auth\AuthMode;

/**
 * Login form. Conditional render berdasarkan AuthMode (lihat blade):
 *   - local : form username/email + password tampil, button SSO sembunyi
 *   - sso   : button SSO tampil, form sembunyi
 *   - both  : keduanya tampil
 *
 * Backend safety: kalau user `auth_type='sso'` coba lewat form, di-reject
 * dengan pesan "akun ini login pakai SSO" — sehingga config mode tidak
 * jadi satu-satunya garis pertahanan.
 */
class Login extends Component
{
    public string $identifier = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        // Mode `sso` exclusive — form login lokal di-block
        if (! AuthMode::isLocalEnabled()) {
            $this->addError('identifier', 'Login lokal sedang dinonaktifkan. Gunakan tombol SSO.');
            return;
        }

        $this->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], attributes: [
            'identifier' => 'username/email',
        ]);

        $field = filter_var($this->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($field, $this->identifier)->first();

        if (! $user || ! Hash::check($this->password, $user->password ?? '')) {
            $this->addError('identifier', 'Username/email atau password salah.');
            return;
        }

        // Auth type guard — user SSO tidak boleh login lewat password
        if (method_exists($user, 'isSso') && $user->isSso()) {
            $this->addError('identifier', 'Akun ini login lewat SSO — gunakan tombol Login with SSO.');
            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        $this->redirectIntended('/home', navigate: false);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.auth.login', [
            'authMode' => AuthMode::current(),
            'rawAuthMode' => AuthMode::raw(),
            'localEnabled' => AuthMode::isLocalEnabled(),
            'ssoEnabled' => AuthMode::isSsoEnabled(),
        ])->layout('nawasara-ui::components.layouts.guest');
    }
}
