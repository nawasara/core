<?php

use Illuminate\Support\Facades\Route;
use Nawasara\Core\Http\Controllers\RoleController;
use Nawasara\Core\Http\Controllers\UserController;

Route::prefix('core')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('nawasara-core.users.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('nawasara-core.roles.store');

});
