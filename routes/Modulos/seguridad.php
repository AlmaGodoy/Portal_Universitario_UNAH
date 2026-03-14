<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RolSeguridadController;

/*
|--------------------------------------------------------------------------
| API de Seguridad
|--------------------------------------------------------------------------
*/

Route::prefix('api/seguridad')->middleware('auth')->group(function () {

    Route::get('/', [RolSeguridadController::class, 'index'])
        ->name('seguridad.index');

    Route::get('/roles', [RolController::class, 'panelRoles'])
        ->name('seguridad.roles');

    Route::get('/usuarios', [RolSeguridadController::class, 'usuarios'])
        ->name('seguridad.usuarios');

    Route::get('/objetos', [RolSeguridadController::class, 'objetos'])
        ->name('seguridad.objetos');

    Route::get('/accesos', [RolSeguridadController::class, 'accesos'])
        ->name('seguridad.accesos');

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
