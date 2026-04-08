<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::middleware('guest')->group(function () {
    Route::get('/portal', function () {
        return view('auth.choose_portal');
    })->name('login');

    Route::get('/login/{tipo}', [LoginController::class, 'showLoginFormTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('login.tipo');
});

Route::prefix('api')->group(function () {

    Route::middleware('guest')->group(function () {
        // Login
        Route::post('/login/{tipo}', [LoginController::class, 'loginTipo'])
            ->whereIn('tipo', ['estudiante', 'empleado'])
            ->name('login.tipo.post');

        /*
        |--------------------------------------------------------------------------
        | 2FA
        |--------------------------------------------------------------------------
        */
        Route::get('/2fa', [TwoFactorController::class, 'form'])
            ->name('twofa.form');

        Route::post('/2fa', [TwoFactorController::class, 'verify'])
            ->name('twofa.verify');

        /*
        |--------------------------------------------------------------------------
        | Recuperación de contraseña
        |--------------------------------------------------------------------------
        */
        Route::get('/password/request', [PasswordResetController::class, 'showRequestForm'])
            ->name('custom.password.request');

        Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])
            ->name('custom.password.email');

        Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])
            ->name('custom.password.reset.form');

        Route::post('/password/update', [PasswordResetController::class, 'resetPassword'])
            ->name('custom.password.update');
    });

    Route::middleware('auth')->group(function () {
        // Logout
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('logout');
    });
});
