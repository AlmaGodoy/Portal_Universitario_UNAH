<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResolucionCancelacionController;

/*
|--------------------------------------------------------------------------
| MÓDULO: RESOLUCIÓN DE CANCELACIÓN EXCEPCIONAL
|--------------------------------------------------------------------------
| Este archivo es autocargado por web.php desde routes/Modulos.
| No requiere modificar web.php.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| VISTA WEB DEL MÓDULO
|--------------------------------------------------------------------------
| Se mantiene bajo /empleado/... para seguir la convención del módulo
| que usa el coordinador al entrar desde el portal.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/empleado/resolucion-cancelacion-vista', [ResolucionCancelacionController::class, 'vista'])
        ->name('resolucion.cancelacion.vista');
});

/*
|--------------------------------------------------------------------------
| API - RESOLUCIÓN DE CANCELACIÓN
|--------------------------------------------------------------------------
| Todas las operaciones funcionales del módulo se consumen por API.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->prefix('api/resolucion-cancelacion')->group(function () {

    // Listado de solicitudes
    Route::get('listado', [ResolucionCancelacionController::class, 'listar'])
        ->name('api.resolucion.cancelacion.listado');

    // Detalle de una solicitud
    Route::get('detalle/{id_tramite}', [ResolucionCancelacionController::class, 'detalle'])
        ->whereNumber('id_tramite')
        ->name('api.resolucion.cancelacion.detalle');

    // Guardar resolución
    Route::post('resolver/{id_tramite}', [ResolucionCancelacionController::class, 'resolver'])
        ->whereNumber('id_tramite')
        ->name('api.resolucion.cancelacion.resolver');

    // Ver documento adjunto
    Route::get('documento/{id_documento}', [ResolucionCancelacionController::class, 'documento'])
        ->whereNumber('id_documento')
        ->name('api.resolucion.cancelacion.documento');
});
