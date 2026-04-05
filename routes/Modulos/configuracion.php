<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfiguracionController;

/*
|--------------------------------------------------------------------------
| MODULO: CONFIGURACION (API)
|--------------------------------------------------------------------------
*/

Route::prefix('api/configuracion')->group(function () {
    Route::get('perfil', [ConfiguracionController::class, 'perfil'])
        ->name('configuracion.perfil');

    Route::put('actualizar-perfil', [ConfiguracionController::class, 'actualizarPerfil'])
        ->name('configuracion.actualizar-perfil');

    Route::put('cambiar-password', [ConfiguracionController::class, 'cambiarPassword'])
        ->name('configuracion.cambiar-password');
});

/*
|--------------------------------------------------------------------------
| FRONTEND - CONFIGURACION
|--------------------------------------------------------------------------
*/

Route::get('/configuracion', function () {
    return view('configuracion');
})->name('configuracion.index');