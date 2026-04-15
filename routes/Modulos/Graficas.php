<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GraficasController;

/*
|--------------------------------------------------------------------------
| FRONTEND - VISTAS WEB
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/empleado/graficas/secretaria-carrera', [GraficasController::class, 'vistaSecretariaCarrera'])
        ->name('graficas.secretaria_carrera');

    Route::get('/empleado/graficas/secretaria-academica', [GraficasController::class, 'vistaSecretariaAcademica'])
        ->name('graficas.secretaria_academica');
});

/*
|--------------------------------------------------------------------------
| API - DATOS JSON PARA GRÁFICAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->prefix('api/graficas')->group(function () {

    Route::get('/secretaria-carrera', [GraficasController::class, 'datosSecretariaCarrera'])
        ->name('api.graficas.secretaria_carrera');

    Route::get('/secretaria-academica', [GraficasController::class, 'datosSecretariaAcademica'])
        ->name('api.graficas.secretaria_academica');
});
