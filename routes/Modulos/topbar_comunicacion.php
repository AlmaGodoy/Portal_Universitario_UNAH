<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopbarComunicacionController;

Route::middleware(['auth', 'session.timeout'])->prefix('api/topbar')->group(function () {

    Route::get('/resumen', [TopbarComunicacionController::class, 'resumen'])
        ->name('topbar.resumen');

    Route::post('/notificaciones/marcar-todas', [TopbarComunicacionController::class, 'marcarTodasNotificacionesLeidas'])
        ->name('topbar.notificaciones.marcar_todas');

    Route::post('/notificaciones/marcar/{id}', [TopbarComunicacionController::class, 'marcarNotificacionLeida'])
        ->name('topbar.notificaciones.marcar');

    Route::post('/mensajes/marcar/{id}', [TopbarComunicacionController::class, 'marcarMensajeLeido'])
        ->name('topbar.mensajes.marcar');

    Route::post('/notificaciones/crear', [TopbarComunicacionController::class, 'crearNotificacion'])
        ->name('topbar.notificaciones.crear');

    Route::post('/mensajes/crear', [TopbarComunicacionController::class, 'crearMensaje'])
        ->name('topbar.mensajes.crear');
});

