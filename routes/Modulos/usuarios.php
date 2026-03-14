<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| API de Usuarios
|--------------------------------------------------------------------------
*/

// Registro web por tipo
Route::prefix('api/usuarios')->middleware('guest')->group(function () {

    Route::get('/register/{tipo}', [UsuarioController::class, 'formRegistroTipo'])
        ->whereIn('tipo', ['estudiante', 'empleado'])
        ->name('register.tipo');

    Route::post('/register', [UsuarioController::class, 'crearWeb'])
        ->name('register.store');

    Route::get('/verificar-correo/{token}', [UsuarioController::class, 'verificarCorreo'])
        ->name('email.verify');
});

// Gestión de usuarios autenticados
Route::prefix('api/usuarios')->middleware('auth')->group(function () {

    Route::post('/{id_persona}/activar', [UsuarioController::class, 'activar'])
        ->name('usuarios.activar');

    Route::post('/{id_persona}/desactivar', [UsuarioController::class, 'desactivar'])
        ->name('usuarios.desactivar');

    Route::post('/{id_usuario}/rol', [UsuarioController::class, 'asignarRol'])
        ->name('usuarios.rol');

});
