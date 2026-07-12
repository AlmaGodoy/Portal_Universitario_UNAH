<?php

use App\Http\Controllers\BitacoraController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MÓDULO: BITÁCORA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'session.timeout'])
    ->prefix('bitacora')
    ->name('bitacora.')
    ->group(function () {

        Route::get('/', [BitacoraController::class, 'index'])
            ->name('index');

        Route::get(
            '/coordinador',
            [BitacoraController::class, 'coordinador']
        )
            ->name('coordinador')
            ->middleware('roleid:4');

        Route::get(
            '/secretaria-carrera',
            [BitacoraController::class, 'secretariaCarrera']
        )
            ->name('secretaria_carrera')
            ->middleware('roleid:5');

        Route::get(
            '/secretaria-general',
            [BitacoraController::class, 'secretariaGeneral']
        )
            ->name('secretaria_general')
            ->middleware('roleid:1');
    });