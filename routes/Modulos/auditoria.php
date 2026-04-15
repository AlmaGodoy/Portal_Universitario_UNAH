<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

Route::middleware(['auth'])->group(function () {

    // Ruta general
    Route::get('/auditoria',
        [AuditoriaController::class, 'redirectAuditoria']
    )->name('auditoria');

    // Secretaria Administrativa
    Route::get('/auditoria/administrativa',
        [AuditoriaController::class, 'administrativa']
    )
    ->name('auditoria.administrativa')
    ->middleware('roleid:1');

    // Coordinador
    Route::get('/auditoria/coordinador',
        [AuditoriaController::class, 'coordinador']
    )
    ->name('auditoria.coordinador')
    ->middleware('roleid:4');

    // Secretaria General
    Route::get('/auditoria/general',
        [AuditoriaController::class, 'general']
    )
    ->name('auditoria.general')
    ->middleware('roleid:5');

Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria');
});
