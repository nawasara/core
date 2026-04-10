<?php

namespace Nawasara\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class KeycloakLoginController
{
    public function redirect()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    public function callback(Request $request)
    {
        $keycloakUser = Socialite::driver('keycloak')->user();

        $username = $keycloakUser->getNickname()
            ?? $keycloakUser->user['preferred_username']
            ?? null;

        $user = \App\Models\User::where('username', $username)
            ->where('auth_type', 'sso')
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Akun belum terdaftar. Hubungi administrator.']);
        }

        $user->update([
            'name' => $keycloakUser->getName(),
            'email' => $keycloakUser->getEmail(),
        ]);

        Auth::login($user, true);

        return redirect()->intended('/home');
    }
}
