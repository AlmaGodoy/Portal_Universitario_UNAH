<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificacionController;

/*
|--------------------------------------------------------------------------
| MODULO: NOTIFICACIONES (API)
|--------------------------------------------------------------------------
*/
Route::prefix('api/notificaciones')->middleware(['auth', 'session.timeout'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DEBUG TEMPORAL
    |---------------------------------------------------------------------------
    */
    Route::get('debug-usuario', function () {
        $user = auth()->user();

        return response()->json([
            'usuario_logueado' => $user,
            'id_usuario' => $user->id_usuario ?? null,
            'id' => $user->id ?? null,
            'id_persona' => $user->id_persona ?? null,
            'id_login' => $user->id_login ?? null,
            'id_usuario_login' => $user->id_usuario_login ?? null,
            'correo' => $user->email ?? $user->correo ?? $user->correo_institucional ?? null,
        ]);
    })->name('api.notificaciones.debug-usuario');

    /*
    |--------------------------------------------------------------------------
    | ESTUDIANTE
    |--------------------------------------------------------------------------
    | roleid:2 = estudiante
    |--------------------------------------------------------------------------
    */
    Route::middleware('roleid:2')->group(function () {

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
| FRONTEND - NOTIFICACIONES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::middleware('roleid:2')->group(function () {

        Route::get('/notificaciones', [NotificacionController::class, 'index'])
            ->name('notificaciones.index');

        Route::get('/notificaciones/abrir/{id_notificacion}', [NotificacionController::class, 'abrir'])
            ->name('notificaciones.abrir');
    });
});