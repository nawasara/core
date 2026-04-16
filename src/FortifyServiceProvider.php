<?php

namespace Nawasara\Core;

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = config('nawasara.auth');

        // Login dengan username atau email
        self::loginByUsernameOrEmail();

        // Tampilan form login
        // Fortify::loginView(fn () => view($config['views']['login']));
        // Tampilan form register
        Fortify::registerView(fn () => view($config['views']['register']));

        // Reset password
        Fortify::requestPasswordResetLinkView(fn () => view($config['views']['forgot-password']));
        Fortify::resetPasswordView(fn ($request) => view($config['views']['reset-password'], ['request' => $request]));

        // Verifikasi email
        Fortify::verifyEmailView(fn () => view($config['views']['verify-email']));


    }

    public function loginByUsernameOrEmail()
    {
        Fortify::authenticateUsing(function (Request $request) {
            $field = 'email';
            if (! self::isValidEmail($request->email)) {
                $field = 'username';
            }

            $user = User::where($field, $request->email)->first();
            if ($user &&
                Hash::check($request->password, $user->password)) {
                return $user;
            }
        });
    }

    public function isValidEmail($email)
    {
        // Regex pattern untuk email
        $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

        // Menggunakan preg_match untuk mencocokkan pattern
        return preg_match($pattern, $email) ? true : false;
    }
}
