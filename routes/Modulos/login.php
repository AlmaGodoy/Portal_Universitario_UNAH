<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| PORTAL
|--------------------------------------------------------------------------
*/

Route::get('/portal', function () {
    if (Auth::check()) {
        $loginTipo = session('login_tipo');

        if ($loginTipo === 'estudiante') {
            if (Route::has('panel.estudiante')) {
                return redirect()
                    ->route('panel.estudiante');
            }

            return redirect('/estudiantes');
        }

        if ($loginTipo === 'empleado') {
            return redirect()
                ->route('empleado.dashboard');
        }

        return redirect('/portal');
    }

    return view('auth.choose_portal');
})->name('portal');

/*
|--------------------------------------------------------------------------
| RUTAS SOLO PARA INVITADOS
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get(
        '/login/{tipo}',
        [LoginController::class, 'showLoginFormTipo']
    )
        ->whereIn(
            'tipo',
            [
                'estudiante',
                'empleado',
            ]
        )
        ->name('login.tipo');

    /*
    |--------------------------------------------------------------------------
    | CONFIRMACIÓN DE SESIÓN DUPLICADA
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/login/confirmar-nueva-sesion',
        [
            LoginController::class,
            'confirmarNuevaSesion',
        ]
    )->name(
        'login.confirmar-nueva-sesion'
    );

    Route::post(
        '/login/cancelar-nueva-sesion',
        [
            LoginController::class,
            'cancelarNuevaSesion',
        ]
    )->name(
        'login.cancelar-nueva-sesion'
    );
});

/*
|--------------------------------------------------------------------------
| RUTAS API
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    Route::middleware('guest')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/login/{tipo}',
            [
                LoginController::class,
                'loginTipo',
            ]
        )
            ->whereIn(
                'tipo',
                [
                    'estudiante',
                    'empleado',
                ]
            )
            ->name('login.tipo.post');

        /*
        |--------------------------------------------------------------------------
        | 2FA
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/2fa',
            [
                TwoFactorController::class,
                'form',
            ]
        )->name('twofa.form');

        Route::post(
            '/2fa',
            [
                TwoFactorController::class,
                'verify',
            ]
        )->name('twofa.verify');

        /*
        |--------------------------------------------------------------------------
        | RECUPERACIÓN DE CONTRASEÑA
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/password/request',
            [
                PasswordResetController::class,
                'showRequestForm',
            ]
        )->name(
            'custom.password.request'
        );

        Route::post(
            '/password/email',
            [
                PasswordResetController::class,
                'sendResetLink',
            ]
        )->name(
            'custom.password.email'
        );

        Route::get(
            '/password/reset/{token}',
            [
                PasswordResetController::class,
                'showResetForm',
            ]
        )->name(
            'custom.password.reset.form'
        );

        Route::post(
            '/password/update',
            [
                PasswordResetController::class,
                'updatePassword',
            ]
        )->name(
            'custom.password.update'
        );
    });

    Route::middleware('auth')->group(function () {
        Route::post(
            '/logout',
            [
                LoginController::class,
                'logout',
            ]
        )->name('logout');
    });
});
