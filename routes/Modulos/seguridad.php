<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RolSeguridadController;

/*
|--------------------------------------------------------------------------
| Rutas WEB de Seguridad
|--------------------------------------------------------------------------
| Estas rutas cargan vistas Blade
*/

Route::middleware('auth')->group(function () {

    Route::get('/seguridad', [RolSeguridadController::class, 'index'])
        ->name('seguridad.index');

    Route::get('/seguridad/roles', [RolController::class, 'panelRoles'])
        ->name('seguridad.roles');

    Route::get('/seguridad/usuarios', [RolSeguridadController::class, 'usuarios'])
        ->name('seguridad.usuarios');

    Route::get('/seguridad/objetos', [RolSeguridadController::class, 'objetos'])
        ->name('seguridad.objetos');

    Route::get('/seguridad/accesos', [RolSeguridadController::class, 'accesos'])
        ->name('seguridad.accesos');
});

/*
|--------------------------------------------------------------------------
| Rutas API de Seguridad
|--------------------------------------------------------------------------
| Estas rutas procesan formularios y acciones
*/

Route::prefix('api/seguridad')->middleware('auth')->group(function () {

    Route::post('/rol', [RolController::class, 'storeRol'])
        ->name('seguridad.rol.store');

    Route::put('/rol/{id}', [RolController::class, 'updateRol'])
        ->name('seguridad.rol.update');

    Route::post('/asignar-permisos-objeto', [RolController::class, 'asignarPermisosObjeto'])
        ->name('seguridad.asignar.objeto');

    Route::delete('/asignacion/{id}', [RolController::class, 'deleteAsignacion'])
        ->name('seguridad.asignacion.delete');

    Route::post('/objeto', [RolSeguridadController::class, 'storeObjeto'])
        ->name('seguridad.objeto.store');

    Route::put('/objeto/{id}', [RolSeguridadController::class, 'updateObjeto'])
        ->name('seguridad.objeto.update');

    Route::put('/usuario/{id}/estado', [RolSeguridadController::class, 'updateEstadoUsuario'])
        ->name('seguridad.usuario.estado');

    Route::post('/acceso', [RolSeguridadController::class, 'storeAcceso'])
        ->name('seguridad.acceso.store');

    Route::delete('/acceso/{id}', [RolSeguridadController::class, 'deleteAcceso'])
        ->name('seguridad.acceso.delete');
});
