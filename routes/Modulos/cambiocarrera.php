<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CambioCarreraController;

/*
|--------------------------------------------------------------------------
| MODULO: CAMBIO DE CARRERA (API)
|--------------------------------------------------------------------------
*/

Route::prefix('api/cambio-carrera')->group(function () {
    Route::post('crear', [CambioCarreraController::class, 'crear']);
    Route::get('ver/{codigo}', [CambioCarreraController::class, 'ver']);
    Route::put('estado/{id_tramite}', [CambioCarreraController::class, 'actualizarEstado']);
    Route::delete('eliminar/{id_tramite}', [CambioCarreraController::class, 'eliminar']);

    // catálogos
    Route::get('calendario-vigente', [CambioCarreraController::class, 'calendarioVigente']);
    Route::get('carreras', [CambioCarreraController::class, 'carreras']);
});


// ============================
// FRONTEND - CAMBIO DE CARRERA
// ============================
Route::get('/cambio-carrera', function () {
    return view('cambio_carrera');
    })->name('cambio-carrera.index');


Route::get('/cambio-carrera/mis-tramites', function () {
    return view('cambio_carrera_tramites');
});

Route::get('/cambio-carrera/estado', function () {
    return view('cambio_carrera_estado');
});
