<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;
use App\Http\Controllers\CambioCarreraController;
use App\Http\Controllers\HistorialAcademicoController;


// Rutas de API agrupadas
Route::prefix('api')->group(function () {
    Route::post('cancelaciones/crear', [DocumentoExcepcionalController::class, 'subir']);
    Route::get('cancelaciones/todas', [DocumentoExcepcionalController::class, 'obtenerTodos']);
    Route::get('cancelaciones/detalle/{id}', [DocumentoExcepcionalController::class, 'obtenerCancelacion']);
    Route::delete('cancelaciones/eliminar/{id}', [DocumentoExcepcionalController::class, 'eliminar']);
    // CAMBIO DE CARRERA
    Route::post('cambio-carrera/crear', [CambioCarreraController::class, 'crear']);
    Route::get('cambio-carrera/detalle/{codigo}', [CambioCarreraController::class, 'detalle']);
    Route::put('cambio-carrera/estado/{id_tramite}', [CambioCarreraController::class, 'actualizarEstado']);
    Route::delete('cambio-carrera/eliminar/{id_tramite}', [CambioCarreraController::class, 'eliminar']);

    // HISTORIAL ACADÉMICO
    Route::post('historial/crear', [HistorialAcademicoController::class, 'crear']);
    Route::get('historial/ver/{id_persona}', [HistorialAcademicoController::class, 'ver']);
    Route::put('historial/actualizar/{id_persona}', [HistorialAcademicoController::class, 'actualizar']);
    Route::delete('historial/eliminar/{id_historial}', [HistorialAcademicoController::class, 'eliminar']);



});

// Rutas web
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
