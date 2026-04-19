<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| PORTAL
|--------------------------------------------------------------------------
| Si el usuario ya inició sesión y vuelve a /portal,
| se redirige a su dashboard en lugar de mostrar choose_portal.
|--------------------------------------------------------------------------
*/
Route::get('/portal', function () {
    if (Auth::check()) {
        $loginTipo = session('login_tipo');

        if ($loginTipo === 'estudiante') {
            return redirect()->route('panel.estudiante');
        }

        if ($loginTipo === 'empleado') {
            return redirect()->route('empleado.dashboard');
        }

        return redirect()->route('portal');
    }

    return view('auth.choose_portal');
})->name('portal');

/*
|--------------------------------------------------------------------------
| RUTAS SOLO PARA INVITADOS
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
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

        Route::post('/password/update', [PasswordResetController::class, 'updatePassword'])
            ->name('custom.password.update');
    });

    Route::middleware('auth')->group(function () {
        // Logout
        Route::post('/logout', [LoginController::class, 'logout'])
            ->name('logout');
    });
});