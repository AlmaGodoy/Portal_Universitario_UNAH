<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CambioCarreraController;

/*
|--------------------------------------------------------------------------
| MODULO: CAMBIO DE CARRERA (API)
|--------------------------------------------------------------------------
*/
Route::prefix('api/cambio-carrera')->middleware(['auth', 'session.timeout'])->group(function () {
    Route::middleware('roleid:2')->group(function () {
        Route::post('crear', [CambioCarreraController::class, 'crear']);
        Route::get('ver/{codigo}', [CambioCarreraController::class, 'ver']);
        Route::get('estado-actual', [CambioCarreraController::class, 'estadoActual']);
        Route::delete('eliminar/{id_tramite}', [CambioCarreraController::class, 'eliminar']);
    });

    Route::middleware('roleid:5,1')->group(function () {
        Route::put('estado/{id_tramite}', [CambioCarreraController::class, 'actualizarEstado']);
        
    });

    Route::get('calendario-vigente', [CambioCarreraController::class, 'calendarioVigente']);
    Route::get('carreras', [CambioCarreraController::class, 'carreras']);

    Route::middleware('roleid:5,1')->group(function () {
        Route::get('secretaria/listado', [CambioCarreraController::class, 'listadoSecretaria']);
        Route::get('secretaria/detalle/{id_tramite}', [CambioCarreraController::class, 'detalleSecretaria']);
        Route::post('secretaria/guardar-revision', [CambioCarreraController::class, 'guardarRevisionSecretaria']);
    });

    Route::middleware('roleid:3,4')->group(function () {
        Route::get('coordinacion/listado', [CambioCarreraController::class, 'listadoCoordinacion']);
        Route::get('coordinacion/detalle/{id_tramite}', [CambioCarreraController::class, 'detalleCoordinacion']);
        Route::put('coordinacion/dictaminar/{id_tramite}', [CambioCarreraController::class, 'dictaminarCoordinacion']);
    });

    Route::middleware('roleid:5,1')->group(function () {
        Route::get('secretaria/calendarios', [CambioCarreraController::class, 'listarCalendariosAcademicos']);
        Route::post('secretaria/calendarios', [CambioCarreraController::class, 'crearCalendarioAcademico']);
        Route::put('secretaria/calendarios/{id_calendario}', [CambioCarreraController::class, 'actualizarCalendarioAcademico']);
        Route::put('secretaria/calendarios/estado/{id_calendario}', [CambioCarreraController::class, 'cambiarEstadoCalendarioAcademico']);
        Route::delete('secretaria/calendarios/{id_calendario}', [CambioCarreraController::class, 'eliminarCalendarioAcademico']);
    });
});

/*
|--------------------------------------------------------------------------
| FRONTEND - CAMBIO DE CARRERA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::middleware('roleid:2')->group(function () {
        Route::get('/cambio-carrera', function () {
            return view('cambio_carrera');
        })->name('cambio-carrera.index');

        Route::get('/cambio-carrera/mis-tramites', function () {
            return view('cambio_carrera_tramites');
        });

        Route::get('/cambio-carrera/estado', function () {
            return view('cambio_carrera_estado');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | FRONTEND - SECRETARÍA CAMBIO DE CARRERA
    |--------------------------------------------------------------------------
    */
    Route::middleware('roleid:5,1')->group(function () {
        Route::get('/cambio-carrera/secretaria', function () {
            return view('cambio_carrera_secretaria');
        })->name('cambio-carrera.secretaria');

        Route::get('/cambio-carrera/secretaria/revisar/{id_tramite}', function ($id_tramite) {
            return view('cambio_carrera_secretaria_revision', compact('id_tramite'));
        })->name('cambio-carrera.secretaria.revisar');

        Route::get('/cambio-carrera/secretaria/calendarios', function () {
            return view('cambio_carrera_secretaria_calendario');
        })->name('cambio-carrera.secretaria.calendarios');
    });

    /*
        Vista principal de Coordinación para emitir dictamen final.
    */
    Route::middleware('roleid:3,4')->group(function () {
        Route::get('/cambio-carrera/coordinacion', function () {
            return view('cambio_carrera_coordinacion');
        })->name('cambio-carrera.coordinacion');

        Route::get('/cambio-carrera/coordinacion/dictamen/{id_tramite}', function ($id_tramite) {
            return view('cambio_carrera_coordinacion_dictamen', compact('id_tramite'));
        })->name('cambio-carrera.coordinacion.dictamen');

        Route::get('/coordinador/cambio-carrera', [CambioCarreraController::class, 'vistaCoordinador'])
            ->name('coordinador.cambio-carrera.index');
    });

    Route::middleware('roleid:1,3,4,5')->group(function () {
        Route::get('/empleado/cambio-carrera/documento/{id_tramite}', [CambioCarreraController::class, 'verDocumento'])
            ->name('cambio-carrera.documento');
    });
});