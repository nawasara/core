<?php

namespace Nawasara\Core\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Nawasara\Core\Auth\Services\SsoService;

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

        // cari user atau buat baru
        $user = \App\Models\User::firstOrCreate(
            ['email' => $userData['email']],
            ['name' => $userData['name']]
        );

        // assign role
        if (method_exists($user, 'assignRole')) {
            $user->assignRole(config('nawasara.auth.default_role', 'user'));
        }
        
        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
