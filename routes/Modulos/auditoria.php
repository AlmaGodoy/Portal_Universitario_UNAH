<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

/*
|--------------------------------------------------------------------------
| MÓDULO: AUDITORÍA
|--------------------------------------------------------------------------
| Rutas protegidas por autenticación y timeout
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
    Route::get('/auditoria/administrativa',
        [AuditoriaController::class, 'administrativa']
    )
        ->name('auditoria.administrativa')
        ->middleware('roleid:5');

    // Coordinador
    Route::get('/auditoria/coordinador',
        [AuditoriaController::class, 'coordinador']
    )
        ->name('auditoria.coordinador')
        ->middleware('roleid:4');

    // Secretaría General
    Route::get('/auditoria/general',
        [AuditoriaController::class, 'general']
    )
        ->name('auditoria.general')
        ->middleware('roleid:1');

    /*
    |--------------------------------------------------------------------------
    | API - AUDITORÍA
    |--------------------------------------------------------------------------
    */

    Route::prefix('api/auditoria')
        ->name('api.auditoria.')
        ->group(function () {

            // Listado general
            Route::get('/listar',
                [AuditoriaController::class, 'listar']
            )->name('listar');

            // Detalle por ID
            Route::get('/detalle/{id}',
                [AuditoriaController::class, 'detalle']
            )
                ->whereNumber('id')
                ->name('detalle');

            // Filtros
            Route::get('/filtrar',
                [AuditoriaController::class, 'filtrar']
            )->name('filtrar');

            // API por rol

            Route::get('/administrativa',
                [AuditoriaController::class, 'listarAdministrativa']
            )
                ->name('administrativa')
                ->middleware('roleid:5');

            Route::get('/coordinador',
                [AuditoriaController::class, 'listarCoordinador']
            )
                ->name('coordinador')
                ->middleware('roleid:4');

            Route::get('/general',
                [AuditoriaController::class, 'listarGeneral']
            )
                ->name('general')
                ->middleware('roleid:1');
        });

});
