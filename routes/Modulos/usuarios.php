<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Rutas WEB de Usuarios
|--------------------------------------------------------------------------
| Estas rutas cargan vistas Blade o enlaces públicos.
*/

Route::middleware('guest')->group(function () {
    Route::get('/register/{tipo}', [UsuarioController::class, 'formRegistroTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('register.tipo');

    Route::get('/verificar-correo/{token}', [UsuarioController::class, 'verificarCorreo'])
        ->name('email.verify');
});

/*
|--------------------------------------------------------------------------
| Rutas API de Usuarios
|--------------------------------------------------------------------------
| Estas rutas procesan formularios y acciones autenticadas.
*/

Route::prefix('api/usuarios')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/register', [UsuarioController::class, 'crearWeb'])
            ->name('register.store');
    });

    Route::middleware(['auth', 'session.timeout', 'roleid:3,4'])->group(function () {
        Route::post('/{id_persona}/activar', [UsuarioController::class, 'activar'])
            ->name('usuarios.activar');

        Route::post('/{id_persona}/desactivar', [UsuarioController::class, 'desactivar'])
            ->name('usuarios.desactivar');

        Route::post('/{id_usuario}/rol', [UsuarioController::class, 'asignarRol'])
            ->name('usuarios.rol');
    });
});