<?php

use App\Http\Controllers\RolController;
use App\Http\Controllers\RolSeguridadController;

Route::middleware('auth')->group(function () {

    Route::get('/seguridad', [RolSeguridadController::class, 'index'])->name('seguridad.index');

    Route::get('/seguridad/roles', [RolController::class, 'panelRoles'])->name('seguridad.roles');

    Route::get('/seguridad/usuarios', [RolSeguridadController::class, 'usuarios'])->name('seguridad.usuarios');

    Route::get('/seguridad/objetos', [RolSeguridadController::class, 'objetos'])->name('seguridad.objetos');

    Route::get('/seguridad/accesos', [RolSeguridadController::class, 'accesos'])->name('seguridad.accesos');

    Route::post('/seguridad/rol', [RolController::class, 'storeRol'])->name('seguridad.rol.store');

    Route::put('/seguridad/rol/{id}', [RolController::class, 'updateRol'])->name('seguridad.rol.update');

    Route::post('/seguridad/asignar-permisos-objeto', [RolController::class, 'asignarPermisosObjeto'])->name('seguridad.asignar.objeto');

    Route::delete('/seguridad/asignacion/{id}', [RolController::class, 'deleteAsignacion'])->name('seguridad.asignacion.delete');

    Route::post('/seguridad/objeto', [RolSeguridadController::class, 'storeObjeto'])->name('seguridad.objeto.store');

    Route::put('/seguridad/objeto/{id}', [RolSeguridadController::class, 'updateObjeto'])->name('seguridad.objeto.update');

    Route::put('/seguridad/usuario/{id}/estado', [RolSeguridadController::class, 'updateEstadoUsuario'])->name('seguridad.usuario.estado');

    Route::post('/seguridad/acceso', [RolSeguridadController::class, 'storeAcceso'])->name('seguridad.acceso.store');

    Route::delete('/seguridad/acceso/{id}', [RolSeguridadController::class, 'deleteAcceso'])->name('seguridad.acceso.delete');

});
