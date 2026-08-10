<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RolSeguridadController;


/*
|--------------------------------------------------------------------------
| MÓDULO DE SEGURIDAD
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:4'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | PANEL PRINCIPAL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/seguridad',
        [
            RolSeguridadController::class,
            'index'
        ]
    )->name('seguridad.index');


    /*
    |--------------------------------------------------------------------------
    | ROLES DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/seguridad/roles',
        [
            RolController::class,
            'panelRoles'
        ]
    )->name('seguridad.roles');


    /*
    | Activar / desactivar rol dentro de la carrera
    */

    Route::put(
        '/seguridad/roles/{idRol}/estado',
        [
            RolController::class,
            'updateEstadoRolCarrera'
        ]
    )->name('seguridad.rol.estado');


    /*
    |--------------------------------------------------------------------------
    | USUARIOS DE MI CARRERA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/seguridad/usuarios',
        [
            RolSeguridadController::class,
            'usuarios'
        ]
    )->name('seguridad.usuarios');


    /*
    | Activar / desactivar cuenta
    */

    Route::put(
        '/seguridad/usuarios/{id}/estado',
        [
            RolSeguridadController::class,
            'updateEstadoUsuario'
        ]
    )->name('seguridad.usuario.estado');


    /*
    |--------------------------------------------------------------------------
    | MÓDULOS DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/seguridad/modulos',
        [
            RolSeguridadController::class,
            'objetos'
        ]
    )->name('seguridad.objetos');


    /*
    | Cambiar disponibilidad del módulo
    | para Estudiante o Secretaría de la carrera
    */

    Route::put(
        '/seguridad/modulos/{idObjeto}/estado-rol',
        [
            RolSeguridadController::class,
            'updateEstadoModuloRol'
        ]
    )->name('seguridad.modulo.rol.estado');


    /*
    |--------------------------------------------------------------------------
    | PERMISOS POR ROL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/seguridad/permisos',
        [
            RolSeguridadController::class,
            'accesos'
        ]
    )->name('seguridad.accesos');


    /*
    |--------------------------------------------------------------------------
    | ASIGNAR UNO O VARIOS PERMISOS
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/seguridad/permisos',
        [
            RolSeguridadController::class,
            'storeAcceso'
        ]
    )->name('seguridad.acceso.store');


    /*
    |--------------------------------------------------------------------------
    | EDITAR PERMISO
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/seguridad/permisos/{id}',
        [
            RolSeguridadController::class,
            'updateAcceso'
        ]
    )->name('seguridad.acceso.update');


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR PERMISO
    |--------------------------------------------------------------------------
    |
    | estado = 1 → Activar
    | estado = 0 → Desactivar
    |
    */

    Route::put(
        '/seguridad/permisos/{id}/estado',
        [
            RolSeguridadController::class,
            'updateEstadoAcceso'
        ]
    )->name('seguridad.acceso.estado');


    /*
    |--------------------------------------------------------------------------
    | DESACTIVAR PERMISO - COMPATIBILIDAD
    |--------------------------------------------------------------------------
    |
    | Se mantiene esta ruta porque ya existía en el sistema.
    | La nueva vista utilizará principalmente seguridad.acceso.estado.
    |
    */

    Route::delete(
        '/seguridad/permisos/{id}',
        [
            RolSeguridadController::class,
            'deleteAcceso'
        ]
    )->name('seguridad.acceso.delete');

});
