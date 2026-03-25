<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BitacoraController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo de Bitácora
|--------------------------------------------------------------------------
*/

Route::prefix('api/bitacora')->group(function () {
    Route::get('bitacora/index', [BitacoraController::class, 'index']);
    Route::get('bitacora/ver/{fi}/{ff}', [BitacoraController::class, 'ver']);
    Route::get('bitacora/ingresar/{id_usuario}/{id_objeto}/{accion}/{fecha}/{desc}', [BitacoraController::class, 'ingresar']);
});

Route::get('/bitacora', function () {
    return view('bitacora');
})->name('bitacora.index');
