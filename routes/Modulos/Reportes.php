<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\TramiteControllerAct;
use App\Http\Controllers\ReporteTramiteController;

/*
|--------------------------------------------------------------------------
| MODULO: REPORTE DE TRÁMITES (API)
|--------------------------------------------------------------------------
*/

Route::prefix('api/tramites')->group(function () {
    Route::post('crear', [TramiteController::class, 'crear'])
        ->name('tramites.crear');

    Route::put('actualizar', [TramiteControllerAct::class, 'actualizar'])
        ->name('tramites.actualizar');
});

/*
|--------------------------------------------------------------------------
| API - REPORTE DE TRÁMITES
|--------------------------------------------------------------------------
| Estas rutas ahora funcionan tanto para:
| - secretario / coordinador
| - secretaría general
| porque el controller ya decide el flujo según el rol.
*/
Route::prefix('api/reporte-tramites')->group(function () {
    Route::get('reporte', [ReporteTramiteController::class, 'reporte'])
        ->name('reporte.tramites.reporte');

    Route::get('pdf', [ReporteTramiteController::class, 'exportarPdf'])
        ->name('reporte.tramites.pdf');

    Route::get('excel', [ReporteTramiteController::class, 'exportarExcel'])
        ->name('reporte.tramites.excel');
});

/*
|--------------------------------------------------------------------------
| FRONTEND - REPORTE SECRETARIO / COORDINADOR
|--------------------------------------------------------------------------
*/
Route::get('/empleado/reporte-tramites-vista', [ReporteTramiteController::class, 'vistaReporte'])
    ->name('reporte.tramites.vista');

/*
|--------------------------------------------------------------------------
| FRONTEND - REPORTE SECRETARÍA GENERAL
|--------------------------------------------------------------------------
*/
Route::get('/empleado/reporte-tramites-secretaria-general', [ReporteTramiteController::class, 'vistaReporteSecretariaGeneral'])
    ->name('reporte.tramites.secretaria_general.vista');
