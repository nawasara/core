<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Core\Livewire\Auth\Login;
use Nawasara\Core\Livewire\Auth\SwitchRole;
use Nawasara\Core\Livewire\Role\Form;
use Nawasara\Core\Livewire\User\Index;
use Nawasara\Core\Livewire\Role\Index as RoleIndex;
use Nawasara\Core\Livewire\UserSso\Index as UserSso;
use Nawasara\Core\Http\Controllers\Auth\SsoController;

Route::middleware(['web'])->group(function () {
    Route::get('/login', Login::class)
        ->middleware(['guest'])
        ->name('login');

    Route::prefix('nawasara-core')->group(function () {
        Route::get('user-sso', UserSso::class)->name('nawasara-core.user-sso.index');
        Route::get('users', Index::class)->name('nawasara-core.user.index');
        Route::get('role/form/{id?}', Form::class)->name('nawasara-core.role.form');
        Route::get('roles', RoleIndex::class)->name('nawasara-core.role.index');
    });

    if (config('nawasara.auth_provider') === 'keycloak') {
        Route::get('/login', [\Nawasara\Core\Http\Controllers\KeycloakLoginController::class, 'redirect'])->name('login');
        Route::get('/callback', [\Nawasara\Core\Http\Controllers\KeycloakLoginController::class, 'callback']);
    }

    // SSO
    Route::get('/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
    Route::get('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');

    Route::get('switch-role', SwitchRole::class)->name('nawasara-core.switch-role');
});
