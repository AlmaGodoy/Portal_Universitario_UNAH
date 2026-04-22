<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/auditoria', [AuditoriaController::class, 'redirectAuditoria'])
        ->name('auditoria');

    Route::get('/auditoria/administrativa', [AuditoriaController::class, 'administrativa'])
        ->name('auditoria.administrativa')
        ->middleware('roleid:1');

    Route::get('/auditoria/coordinador', [AuditoriaController::class, 'coordinador'])
        ->name('auditoria.coordinador')
        ->middleware('roleid:4');

    Route::get('/auditoria/general', [AuditoriaController::class, 'general'])
        ->name('auditoria.general')
        ->middleware('roleid:5');

    Route::prefix('api/auditoria')->name('api.auditoria.')->group(function () {

        Route::get('/listar', [AuditoriaController::class, 'listar'])
            ->name('listar');

        Route::get('/detalle/{id}', [AuditoriaController::class, 'detalle'])
            ->whereNumber('id')
            ->name('detalle');

        Route::get('/filtrar', [AuditoriaController::class, 'filtrar'])
            ->name('filtrar');

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
