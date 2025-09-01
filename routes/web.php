<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Core\Http\Controllers\UserController;
use Nawasara\Core\Http\Controllers\RoleController;
Route::middleware(['web'])->group(function () {

    Route::prefix('nawasara-core')->group(function () {
        Route::get('/test-csrf', function () {
            echo csrf_token();
        });

        Route::get('/components/table', function () {
            return view('nawasara-core::pages.examples.table', [
                'title' => 'Table Component Example'
            ]);
        });

        Route::get('/components/base', function () {
            return view('nawasara-core::pages.examples.base', [
                'title' => 'base Component Example'
            ]);
        });
        Route::get('/users', [UserController::class, 'index'])->name('nawasara-core.users.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('nawasara-core.roles.store');

    });

    if (config('nawasara-core.auth_provider') === 'jetstream') {
        // Gunakan login Jetstream bawaan
        Auth::routes(); // atau biarkan Jetstream handle
    }

    if (config('nawasara-core.auth_provider') === 'keycloak') {
        Route::get('/login', [\Nawasara\Core\Http\Controllers\KeycloakLoginController::class, 'redirect'])->name('login');
        Route::get('/callback', [\Nawasara\Core\Http\Controllers\KeycloakLoginController::class, 'callback']);
    }
    
    if (config('nawasara-core.use_default_home')) {
        Route::get('/', function () {
            return redirect()->route(config('nawasara-core.home_route'));
        });
    }

    Route::get('/dashboard', function () {
        return view('nawasara-core::dashboard');
    })->name('nawasara-core.dashboard');
});
