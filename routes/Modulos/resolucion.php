<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResolucionCancelacionController;

/*
|--------------------------------------------------------------------------
| MÓDULO: RESOLUCIÓN DE CANCELACIÓN DE CLASES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VISTA WEB
    |--------------------------------------------------------------------------
    */
    Route::get('/empleado/resolucion-cancelacion-vista', [ResolucionCancelacionController::class, 'vista'])
        ->name('resolucion.cancelacion.vista');

    /*
    |--------------------------------------------------------------------------
    | DOCUMENTOS ADJUNTOS
    |--------------------------------------------------------------------------
    */
    Route::get('/empleado/resolucion-cancelacion/documento/{idDocumento}', [ResolucionCancelacionController::class, 'documento'])
        ->whereNumber('idDocumento')
        ->name('resolucion.cancelacion.documento');

    /*
    |--------------------------------------------------------------------------
    | API DEL MÓDULO
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/resolucion-cancelacion')->group(function () {

        Route::get('/listar', [ResolucionCancelacionController::class, 'listar'])
            ->name('resolucion.cancelacion.listar');

        Route::get('/detalle/{idTramite}', [ResolucionCancelacionController::class, 'detalle'])
            ->whereNumber('idTramite')
            ->name('resolucion.cancelacion.detalle');

        Route::post('/resolver/{idTramite}', [ResolucionCancelacionController::class, 'resolver'])
            ->whereNumber('idTramite')
            ->name('resolucion.cancelacion.resolver');
    });
});