<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BitacoraController;

Route::middleware(['auth'])->group(function () {

    // 🔵 Coordinador
    Route::get('/bitacora/coordinador', [BitacoraController::class, 'coordinador'])
        ->name('bitacora.coordinador')
        ->middleware('role:2');

    // 🟡 Secretaria Académica
    Route::get('/bitacora/secretaria-academica', [BitacoraController::class, 'secretariaAcademica'])
        ->name('bitacora.secretaria')
        ->middleware('role:4');

    // 🔴 Secretaria General
    Route::get('/bitacora/secretaria-general', [BitacoraController::class, 'secretariaGeneral'])
        ->name('bitacora.general')
        ->middleware('role:1');

});
