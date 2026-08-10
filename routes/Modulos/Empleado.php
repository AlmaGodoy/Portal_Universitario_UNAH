<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpleadoController;


/*
|--------------------------------------------------------------------------
| FRONTEND - VISTAS WEB
|--------------------------------------------------------------------------
|
| Seguridad:
|
| auth                = Usuario autenticado
| session.timeout     = Sesión vigente
| cuenta.activa       = Cuenta individual activa
| roleid              = Rol de empleado permitido
| rol.carrera.activo  = Comprueba Secretaría de Carrera
|
| Para roles 1, 3 y 4, rol.carrera.activo simplemente permite continuar.
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:1,3,4,5',
    'rol.carrera.activo',
])->group(function () {


    Route::get(
        '/empleado/dashboard',
        [
            EmpleadoController::class,
            'index'
        ]
    )->name('empleado.dashboard');

});


/*
|--------------------------------------------------------------------------
| API - DATOS JSON
|--------------------------------------------------------------------------
|
| También se protegen los endpoints utilizados desde el dashboard.
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:1,3,4,5',
    'rol.carrera.activo',
])
->prefix('api/empleados')
->group(function () {


    /*
    |--------------------------------------------------------------------------
    | ESTADÍSTICAS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'estadisticas',
        [
            EmpleadoController::class,
            'getEstadisticas'
        ]
    )->name(
        'api.empleados.estadisticas'
    );


    /*
    |--------------------------------------------------------------------------
    | LISTADO POR UNIDAD
    |--------------------------------------------------------------------------
    */

    Route::get(
        'listado-por-unidad',
        [
            EmpleadoController::class,
            'listarPorUnidad'
        ]
    )->name(
        'api.empleados.unidades'
    );


    /*
    |--------------------------------------------------------------------------
    | NOTIFICACIONES
    |--------------------------------------------------------------------------
    */

    Route::get(
        'notificaciones',
        [
            EmpleadoController::class,
            'getNotificaciones'
        ]
    )->name(
        'api.empleados.notificaciones'
    );

});
