<?php

namespace Nawasara\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Nawasara\Core\Services\SsoService;

class SsoController extends Controller
{
    protected $sso;

    public function __construct(SsoService $sso)
    {
        $this->sso = $sso;
    }

    public function redirect()
    {
        return $this->sso->redirect();
    }

    public function callback()
    {
        $userData = $this->sso->callback();

        $user = \App\Models\User::where('username', $userData['username'])
            ->where('auth_type', 'sso')
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Akun belum terdaftar. Hubungi administrator.']);
        }

        $user->update([
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        Auth::login($user, true);

        return redirect()->intended('/home');
    }
}
