<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificacionController;

/*
|--------------------------------------------------------------------------
| MODULO: NOTIFICACIONES
|--------------------------------------------------------------------------
*/

Route::prefix('api/notificaciones')->middleware(['auth', 'session.timeout'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | API - NOTIFICACIONES PARA USUARIOS AUTENTICADOS
    |--------------------------------------------------------------------------
    | 2 = estudiante
    | 5 = secretaria de carrera / secretaria académica
    | 1 = admin
    | 3,4 = coordinación si también lo ocupan
    |--------------------------------------------------------------------------
    */
    Route::middleware('roleid:1,2,3,4,5')->group(function () {

        Route::get('recientes', [NotificacionController::class, 'recientes'])
            ->name('api.notificaciones.recientes');

        Route::put('marcar-leida/{id_notificacion}', [NotificacionController::class, 'marcarLeida'])
            ->name('api.notificaciones.marcar-leida');

        Route::put('marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])
            ->name('api.notificaciones.marcar-todas-leidas');
    });
});

/*
|--------------------------------------------------------------------------
| FRONTEND - BANDEJA DE NOTIFICACIONES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout', 'roleid:1,2,3,4,5'])->group(function () {

    Route::get('/notificaciones', [NotificacionController::class, 'index'])
        ->name('notificaciones.index');

    Route::get('/notificaciones/abrir/{id_notificacion}', [NotificacionController::class, 'abrir'])
        ->name('notificaciones.abrir');
});