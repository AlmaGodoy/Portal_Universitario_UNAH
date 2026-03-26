<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\TramiteControllerAct;
use App\Http\Controllers\ReporteTramiteController;

/*
|--------------------------------------------------------------------------
| Rutas de Reporte de Trámites
|--------------------------------------------------------------------------
*/

// ── API de trámites ──────────────────────────────────────────────────────
Route::prefix('api/tramites')->group(function () {
    Route::post('crear', [TramiteController::class, 'crear'])
        ->name('tramites.crear');

    Route::put('actualizar', [TramiteControllerAct::class, 'actualizar'])
        ->name('tramites.actualizar');
});

// ── API de reportes de trámites ──────────────────────────────────────────
Route::prefix('api/reporte-tramites')->group(function () {
    Route::get('reporte', [ReporteTramiteController::class, 'reporte'])
        ->name('reporte.tramites.reporte');

    Route::get('pdf', [ReporteTramiteController::class, 'exportarPdf'])
        ->name('reporte.tramites.pdf');

    Route::get('excel', [ReporteTramiteController::class, 'exportarExcel'])
        ->name('reporte.tramites.excel');
});

// ── Vista del módulo de reportes ─────────────────────────────────────────
Route::get('/reporte-tramites-vista', [ReporteTramiteController::class, 'vistaReporte'])
    ->name('reporte.tramites.vista');
