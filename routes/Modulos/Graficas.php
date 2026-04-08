<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GraficasController;

/*
|--------------------------------------------------------------------------
| FRONTEND - VISTAS WEB
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------
    | VISTA SECRETARIA DE CARRERA
    |--------------------------------------------------------------
    */
    Route::get('/empleado/graficas/secretaria-carrera', [GraficasController::class, 'vistaSecretariaCarrera'])
        ->name('graficas.secretaria_carrera');

    /*
    |--------------------------------------------------------------
    | VISTA SECRETARIA ACADÉMICA
    |--------------------------------------------------------------
    */
    Route::get('/empleado/graficas/secretaria-academica', [GraficasController::class, 'vistaSecretariaAcademica'])
        ->name('graficas.secretaria_academica');
});

/*
|--------------------------------------------------------------------------
| API - DATOS JSON PARA GRÁFICAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api/graficas')->group(function () {

    /*
    |--------------------------------------------------------------
    | DATOS - SECRETARIA DE CARRERA
    |--------------------------------------------------------------
    */
    Route::get('/secretaria-carrera', [GraficasController::class, 'datosSecretariaCarrera'])
        ->name('api.graficas.secretaria_carrera');

    /*
    |--------------------------------------------------------------
    | DATOS - SECRETARIA ACADÉMICA
    |--------------------------------------------------------------
    */
    Route::get('/secretaria-academica', [GraficasController::class, 'datosSecretariaAcademica'])
        ->name('api.graficas.secretaria_academica');
});
