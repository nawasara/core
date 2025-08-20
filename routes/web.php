<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Core\Http\Controllers\UserController;
use Nawasara\Core\Http\Controllers\RoleController;

Route::prefix('core')->group(function () {
    Route::get('/test', function () {
        return view('nawasara-core::demo', [
            'title' => 'Demo Core Layout'
        ]);
    });
    Route::get('/users', [UserController::class, 'index'])->name('nawasara-core.users.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('nawasara-core.roles.store');

});

Route::middleware(['web'])->group(function () {
    if (config('nawasara-core.use_default_home')) {
        Route::get('/', function () {
            return redirect()->route(config('nawasara-core.home_route'));
        });
    }

    Route::get('/dashboard', function () {
        return view('nawasara-core::dashboard');
    })->name('nawasara-core.dashboard');
});
