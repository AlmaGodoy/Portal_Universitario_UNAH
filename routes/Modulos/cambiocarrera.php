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
    Route::get('secretaria/listado', [CambioCarreraController::class, 'listadoSecretaria']);
    Route::get('secretaria/detalle/{id_tramite}', [CambioCarreraController::class, 'detalleSecretaria']);
    Route::post('secretaria/guardar-revision', [CambioCarreraController::class, 'guardarRevisionSecretaria']);
        Route::get('coordinacion/listado', [CambioCarreraController::class, 'listadoCoordinacion']);
    Route::put('coordinacion/dictaminar/{id_tramite}', [CambioCarreraController::class, 'dictaminarCoordinacion']);
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

/*
|--------------------------------------------------------------------------
| FRONTEND - SECRETARÍA CAMBIO DE CARRERA
|--------------------------------------------------------------------------
*/
Route::get('/cambio-carrera/secretaria', function () {
    return view('cambio_carrera_secretaria');
})->name('cambio-carrera.secretaria');

Route::get('/cambio-carrera/secretaria/revisar/{id_tramite}', function ($id_tramite) {
    return view('cambio_carrera_secretaria_revision', compact('id_tramite'));
})->name('cambio-carrera.secretaria.revisar');

/*
    Vista principal de Coordinación para emitir dictamen final.
*/
Route::get('/cambio-carrera/coordinacion', function () {
    return view('cambio_carrera_coordinacion');
})->name('cambio-carrera.coordinacion');


Route::get('/cambio-carrera/coordinacion/dictamen/{id_tramite}', function ($id_tramite) {
    return view('cambio_carrera_coordinacion_dictamen', compact('id_tramite'));
})->name('cambio-carrera.coordinacion.dictamen');




/*
    Vista principal de Coordinación para emitir dictamen final.
*/
Route::get('/cambio-carrera/coordinacion', function () {
    return view('cambio_carrera_coordinacion');
})->name('cambio-carrera.coordinacion');


Route::get('/cambio-carrera/coordinacion/dictamen/{id_tramite}', function ($id_tramite) {
    return view('cambio_carrera_coordinacion_dictamen', compact('id_tramite'));
})->name('cambio-carrera.coordinacion.dictamen');






