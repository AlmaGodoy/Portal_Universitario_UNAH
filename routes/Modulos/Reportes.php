<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\TramiteControllerAct;
use App\Http\Controllers\ReporteTramiteController;

/*
|--------------------------------------------------------------------------
| MÓDULO: TRÁMITES (API)
|--------------------------------------------------------------------------
*/
Route::prefix('api/tramites')->middleware(['auth', 'session.timeout', 'roleid:2'])->group(function () {
    Route::post('crear', [TramiteController::class, 'crear'])
        ->name('tramites.crear');

    Route::put('actualizar', [TramiteControllerAct::class, 'actualizar'])
        ->name('tramites.actualizar');
});

/*
|--------------------------------------------------------------------------
| API - REPORTE DE TRÁMITES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout', 'roleid:1,3,4,5'])->prefix('api/reporte-tramites')->group(function () {
    Route::get('reporte', [ReporteTramiteController::class, 'reporte'])
        ->name('reporte.tramites.reporte');

    Route::get('pdf', [ReporteTramiteController::class, 'exportarPdf'])
        ->name('reporte.tramites.pdf');

    Route::get('excel', [ReporteTramiteController::class, 'exportarExcel'])
        ->name('reporte.tramites.excel');
});

/*
|--------------------------------------------------------------------------
| FRONTEND - REPORTE COORDINADOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout', 'roleid:3,4'])->get('/empleado/reporte-tramites-vista', [ReporteTramiteController::class, 'vistaReporte'])
    ->name('reporte.tramites.vista');

/*
|--------------------------------------------------------------------------
| FRONTEND - REPORTE SECRETARÍA DE CARRERA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout', 'roleid:5'])->get('/empleado/reporte-tramites-secretaria', [ReporteTramiteController::class, 'vistaReporteSecretaria'])
    ->name('reporte.tramites.secretaria.vista');

/*
|--------------------------------------------------------------------------
| FRONTEND - REPORTE SECRETARÍA GENERAL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout', 'roleid:1'])->get('/empleado/reporte-tramites-secretaria-general', [ReporteTramiteController::class, 'vistaReporteSecretariaGeneral'])
    ->name('reporte.tramites.secretaria_general.vista');