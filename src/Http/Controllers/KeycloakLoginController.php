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
        $user = Socialite::driver('keycloak')->user();

        // cari user di DB atau buat baru
        $authUser = \App\Models\User::updateOrCreate(
            ['email' => $user->getEmail()],
            ['name' => $user->getName()]
        );

        Auth::login($authUser, true);

        return redirect()->route(config('nawasara-core.home_route'));
    }
}
