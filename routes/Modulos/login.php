<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Rutas WEB de Login - Vistas Blade (solo GET, sin procesar datos)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Portal de selección de tipo de usuario
    Route::get('/portal', function () {
        return view('auth.choose_portal');
    })->name('choose.portal');

    // Vista de login por tipo (estudiante o empleado)
    Route::get('/login/{tipo}', [LoginController::class, 'showLoginFormTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('login.tipo');

    // Vista 2FA
    Route::get('/2fa', [TwoFactorController::class, 'form'])
        ->name('twofa.form');

    // Vista recuperar contraseña
    Route::get('/password/recuperar', [PasswordResetController::class, 'showRequestForm'])
        ->name('custom.password.request');

    // Vista reset contraseña
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('custom.password.reset.form');

});

/*
|--------------------------------------------------------------------------
| Rutas API de Login - Procesamiento de datos
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {

    Route::middleware('guest')->group(function () {

        // Procesar login por tipo
        Route::post('/login/{tipo}', [LoginController::class, 'loginTipo'])
            ->whereIn('tipo', ['estudiante', 'empleado'])
            ->name('login.tipo.post');

        // Verificar código 2FA
        Route::post('/2fa', [TwoFactorController::class, 'verify'])
            ->name('twofa.verify');

        // Enviar link de recuperación
        Route::post('/password/recuperar', [PasswordResetController::class, 'sendResetLink'])
            ->name('custom.password.email');

        // Actualizar contraseña
        Route::post('/password/reset', [PasswordResetController::class, 'updatePassword'])
            ->name('custom.password.update');

    });

    // Logout solo para gente autenticada
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Panel Estudiante
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'roleid:2'])->group(function () {

    Route::get('/panel/estudiante', function () {
        return view('estudiante');
    })->name('panel.estudiante');

    Route::get('/panel-estudiante', function () {
        return redirect()->route('panel.estudiante');
    });

});
