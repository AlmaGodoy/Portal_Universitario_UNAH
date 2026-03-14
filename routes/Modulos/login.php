<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| API de Login y Recuperación
|--------------------------------------------------------------------------
*/

Route::prefix('api/login')->middleware('guest')->group(function () {
    Route::get('{tipo}', [LoginController::class, 'showLoginFormTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('login.tipo');

    Route::post('{tipo}', [LoginController::class, 'loginTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('login.tipo.post');

    Route::get('/', function () {
        return redirect()->route('portal');
    })->name('login');
});

Route::prefix('api/logout')->middleware('auth')->group(function () {
    Route::post('/', [LoginController::class, 'logout'])
        ->name('logout');
});

Route::prefix('api/2fa')->middleware('guest')->group(function () {
    Route::get('/', [TwoFactorController::class, 'form'])
        ->name('twofa.form');

    Route::post('/', [TwoFactorController::class, 'verify'])
        ->name('twofa.verify');
});

Route::prefix('api/password')->middleware('guest')->group(function () {
    Route::get('recuperar', [PasswordResetController::class, 'showRequestForm'])
        ->name('custom.password.request');

    Route::post('recuperar', [PasswordResetController::class, 'sendResetLink'])
        ->name('custom.password.email');

    Route::get('reset/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('custom.password.reset.form');

    Route::post('reset', [PasswordResetController::class, 'updatePassword'])
        ->name('custom.password.update');
});

Route::prefix('api/panel')->middleware(['auth', 'roleid:2'])->group(function () {
    Route::get('estudiante', function () {
        return view('estudiante');
    })->name('panel.estudiante');
});

/*
|--------------------------------------------------------------------------
| Compatibilidad con ruta anterior
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'roleid:2'])->group(function () {
    Route::get('/panel-estudiante', function () {
        return redirect()->route('panel.estudiante');
    });
});
