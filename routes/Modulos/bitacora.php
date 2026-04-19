<?php

namespace App\Models\Modulos;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BitacoraController;

/*
|--------------------------------------------------------------------------
| MÓDULO: BITÁCORA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/bitacora', [BitacoraController::class, 'index'])
        ->name('bitacora.index');

    Route::get('/bitacora/coordinador', [BitacoraController::class, 'coordinador'])
        ->name('bitacora.coordinador')
        ->middleware('roleid:4');

    Route::get('/bitacora/secretaria-academica', [BitacoraController::class, 'secretariaAcademica'])
        ->name('bitacora.secretaria_academica')
        ->middleware('roleid:5');

    Route::get('/bitacora/secretaria-general', [BitacoraController::class, 'secretariaGeneral'])
        ->name('bitacora.secretaria_general')
        ->middleware('roleid:1');
});
