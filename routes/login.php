<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\UsuarioController;

// Login por tipo
Route::get('/login/{tipo}', [LoginController::class, 'showLoginFormTipo'])
    ->whereIn('tipo', ['estudiante', 'empleado'])
    ->middleware('guest')
    ->name('login.tipo');

Route::post('/login/{tipo}', [LoginController::class, 'loginTipo'])
    ->whereIn('tipo', ['estudiante', 'empleado'])
    ->middleware('guest')
    ->name('login.tipo.post');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// 2FA
Route::get('/2fa', [TwoFactorController::class, 'form'])
    ->middleware('guest')
    ->name('twofa.form');

Route::post('/2fa', [TwoFactorController::class, 'verify'])
    ->middleware('guest')
    ->name('twofa.verify');

// Recuperar contraseña
Route::middleware('guest')->group(function () {

    Route::get('/recuperar-password', [PasswordResetController::class, 'showRequestForm'])
        ->name('custom.password.request');

    Route::post('/recuperar-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('custom.password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('custom.password.reset.form');

    Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])
        ->name('custom.password.update');

});
