<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Rutas WEB de Login y Recuperación
|--------------------------------------------------------------------------
| Estas rutas cargan vistas Blade.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login/{tipo}', [LoginController::class, 'showLoginFormTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('login.tipo');

    Route::get('/login', function () {
        return redirect()->route('portal');
    })->name('login');

    Route::get('/2fa', [TwoFactorController::class, 'form'])
        ->name('twofa.form');

    Route::get('/password/recuperar', [PasswordResetController::class, 'showRequestForm'])
        ->name('custom.password.request');

    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('custom.password.reset.form');
});

/*
|--------------------------------------------------------------------------
| Rutas API de Login y Recuperación
|--------------------------------------------------------------------------
| Estas rutas procesan formularios.
*/

Route::prefix('api')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/login/{tipo}', [LoginController::class, 'loginTipo'])
            ->whereIn('tipo', ['estudiante', 'empleado'])
            ->name('login.tipo.post');

        Route::post('/2fa', [TwoFactorController::class, 'verify'])
            ->name('twofa.verify');

        Route::post('/password/recuperar', [PasswordResetController::class, 'sendResetLink'])
            ->name('custom.password.email');

        Route::post('/password/reset', [PasswordResetController::class, 'updatePassword'])
            ->name('custom.password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Panel estudiante
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
