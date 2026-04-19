<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria');
});
/*
|--------------------------------------------------------------------------
| MÓDULO: AUDITORÍA
|--------------------------------------------------------------------------
| Rutas web + API
*/

Route::middleware(['auth', 'session.timeout'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VISTA GENERAL / REDIRECCIÓN SEGÚN ROL
    |--------------------------------------------------------------------------
    */
    Route::get('/auditoria', [AuditoriaController::class, 'redirectAuditoria'])
        ->name('auditoria');

    /*
    |--------------------------------------------------------------------------
    | VISTAS POR ROL
    |--------------------------------------------------------------------------
    */

    // Secretaría Administrativa
    Route::get('/auditoria/administrativa', [AuditoriaController::class, 'administrativa'])
        ->name('auditoria.administrativa')
        ->middleware('roleid:1');

    // Coordinador
    Route::get('/auditoria/coordinador', [AuditoriaController::class, 'coordinador'])
        ->name('auditoria.coordinador')
        ->middleware('roleid:4');

    // Secretaría General
    Route::get('/auditoria/general', [AuditoriaController::class, 'general'])
        ->name('auditoria.general')
        ->middleware('roleid:5');

    /*
    |--------------------------------------------------------------------------
    | API - AUDITORÍA
    |--------------------------------------------------------------------------
    | Ajusta los nombres de métodos si en tu controlador los tienes distintos.
    */
    Route::prefix('api/auditoria')->name('api.auditoria.')->group(function () {

        // Listado general
        Route::get('/listar', [AuditoriaController::class, 'listar'])
            ->name('listar');

        // Detalle por ID
        Route::get('/detalle/{id}', [AuditoriaController::class, 'detalle'])
            ->whereNumber('id')
            ->name('detalle');

        // Filtros
        Route::get('/filtrar', [AuditoriaController::class, 'filtrar'])
            ->name('filtrar');

        // API por rol
        Route::get('/administrativa', [AuditoriaController::class, 'listarAdministrativa'])
            ->name('administrativa')
            ->middleware('roleid:1');

        Route::get('/coordinador', [AuditoriaController::class, 'listarCoordinador'])
            ->name('coordinador')
            ->middleware('roleid:4');

        Route::get('/general', [AuditoriaController::class, 'listarGeneral'])
            ->name('general')
            ->middleware('roleid:5');
    });
});
