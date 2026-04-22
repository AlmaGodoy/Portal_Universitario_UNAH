<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfiguracionController;

/*
|--------------------------------------------------------------------------
| MÓDULO: CONFIGURACIÓN
|--------------------------------------------------------------------------
| Disponible para todos los usuarios autenticados.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| FRONTEND - CONFIGURACIÓN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/configuracion', function () {
        return view('configuracion');
    })->name('configuracion.index');
});

/*
|--------------------------------------------------------------------------
| API - CONFIGURACIÓN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])
    ->prefix('api/configuracion')
    ->name('configuracion.')
    ->group(function () {

        Route::get('/perfil', [ConfiguracionController::class, 'perfil'])
            ->name('perfil');

        Route::put('/actualizar-perfil', [ConfiguracionController::class, 'actualizarPerfil'])
            ->name('actualizar-perfil');

        Route::put('/cambiar-password', [ConfiguracionController::class, 'cambiarPassword'])
            ->name('cambiar-password');
    });