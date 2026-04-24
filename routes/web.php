<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Core\Http\Controllers\Auth\SsoController;
use Nawasara\Core\Livewire\Auth\Login;
use Nawasara\Core\Livewire\Auth\SwitchRole;
use Nawasara\Core\Livewire\Branding\Index as BrandingIndex;
use Nawasara\Core\Livewire\Role\Form;
use Nawasara\Core\Livewire\Role\Index as RoleIndex;
use Nawasara\Core\Livewire\User\Index;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::middleware(['web'])->group(function () {
    Route::get('/login', Login::class)
        ->middleware(['guest'])
        ->name('login');

    Route::middleware(['auth'])->prefix('nawasara-core')->group(function () {
        Route::get('users', Index::class)
            ->middleware(PermissionMiddleware::using('nawasara-core.user.view'))
            ->name('nawasara-core.user.index');

        Route::get('roles', RoleIndex::class)
            ->middleware(PermissionMiddleware::using('nawasara-core.role.view'))
            ->name('nawasara-core.role.index');

        Route::get('role/form/{id?}', Form::class)
            ->middleware(PermissionMiddleware::using('nawasara-core.role.create|nawasara-core.role.edit'))
            ->name('nawasara-core.role.form');

        Route::get('branding', BrandingIndex::class)
            ->middleware(PermissionMiddleware::using('nawasara-core.branding.manage'))
            ->name('nawasara-core.branding.index');
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
