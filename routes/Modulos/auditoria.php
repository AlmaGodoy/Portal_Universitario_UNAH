<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

/*
|--------------------------------------------------------------------------
| MÓDULO DE AUDITORÍA
|--------------------------------------------------------------------------
| Se deja una ruta principal llamada "auditoria" porque algunos layouts
| todavía usan route('auditoria').
|
| También se mantienen las rutas con prefijo:
| auditoria.index
| auditoria.administrativa
| auditoria.coordinador
| auditoria.secretaria_carrera
| auditoria.secretaria
| auditoria.general
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'session.timeout',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | RUTA PRINCIPAL / ALIAS
    |--------------------------------------------------------------------------
    | Esta ruta corrige el error:
    | Route [auditoria] not defined.
    */

    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->name('auditoria');


    /*
    |--------------------------------------------------------------------------
    | RUTAS INTERNAS DEL MÓDULO DE AUDITORÍA
    |--------------------------------------------------------------------------
    */

    Route::prefix('auditoria')->name('auditoria.')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | REDIRECCIÓN SEGÚN EL ROL
        |--------------------------------------------------------------------------
        | Se usa /auditoria/inicio para conservar el nombre auditoria.index
        | sin duplicar la ruta /auditoria.
        */

        Route::get('/inicio', [AuditoriaController::class, 'index'])
            ->name('index');


        /*
        |--------------------------------------------------------------------------
        | SECRETARÍA ACADÉMICA / GENERAL — ROL 1
        |--------------------------------------------------------------------------
        | Acceso global a todas las carreras.
        */

        Route::get('/administrativa', [AuditoriaController::class, 'administrativa'])
            ->middleware('roleid:1')
            ->name('administrativa');


        /*
        |--------------------------------------------------------------------------
        | COORDINADOR DE CARRERA — ROL 4
        |--------------------------------------------------------------------------
        | Acceso limitado a su carrera.
        */

        Route::get('/coordinador', [AuditoriaController::class, 'coordinador'])
            ->middleware('roleid:4')
            ->name('coordinador');


        /*
        |--------------------------------------------------------------------------
        | SECRETARÍA DE CARRERA — ROL 5
        |--------------------------------------------------------------------------
        | Acceso limitado a su carrera.
        */

        Route::get('/secretaria-carrera', [AuditoriaController::class, 'secretariaCarrera'])
            ->middleware('roleid:5')
            ->name('secretaria_carrera');


        /*
        |--------------------------------------------------------------------------
        | ALIAS PARA SECRETARÍA DE CARRERA
        |--------------------------------------------------------------------------
        | Se deja esta ruta por compatibilidad con layouts que usan:
        | route('auditoria.secretaria')
        */

        Route::get('/secretaria', [AuditoriaController::class, 'secretariaCarrera'])
            ->middleware('roleid:5')
            ->name('secretaria');



        Route::get('/general', [AuditoriaController::class, 'general'])
            ->middleware('roleid:5')
            ->name('general');
    });
});