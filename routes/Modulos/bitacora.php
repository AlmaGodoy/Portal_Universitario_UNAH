<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BitacoraController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo de Bitácora
|--------------------------------------------------------------------------
*/

Route::prefix('api/bitacora')->group(function () {
    Route::get('/ver/{fecha_inicial}/{fecha_final}', [BitacoraController::class, 'ver']);
    Route::get('/ingresar/{id_usuario}/{id_objeto}/{p_accion}/{fecha_accion}/{p_descripcion}', [BitacoraController::class, 'ingresar']);
});

Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');

