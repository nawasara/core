<?php

namespace Nawasara\Core\Services;

use Laravel\Socialite\Facades\Socialite;

class SsoService
{
    public function redirect()
    {
        return Socialite::driver(config('nawasara.auth.sso.driver'))->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver(config('nawasara.auth.sso.driver'))->user();

        return [
            'email' => $user->getEmail(),
            'name'  => $user->getName(),
            'id'    => $user->getId(),
        ];
    }
}
