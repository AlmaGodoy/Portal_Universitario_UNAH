<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BitacoraController;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/bitacora/coordinador', [BitacoraController::class, 'coordinador'])
        ->name('bitacora.coordinador')
        ->middleware('role:2');

    Route::get('/bitacora/secretaria-academica', [BitacoraController::class, 'secretariaAcademica'])
        ->name('bitacora.secretaria')
        ->middleware('role:4');

    Route::get('/bitacora/secretaria-general', [BitacoraController::class, 'secretariaGeneral'])
        ->name('bitacora.general')
        ->middleware('role:1');
});
