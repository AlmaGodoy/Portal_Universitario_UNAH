<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

/*
|--------------------------------------------------------------------------
| MÓDULO DE AUDITORÍA
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'session.timeout',
])->prefix('auditoria')->name('auditoria.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | REDIRECCIÓN SEGÚN EL ROL
    |--------------------------------------------------------------------------
    */

    Route::get('/', [AuditoriaController::class, 'index'])
        ->name('index');

    /*
    |--------------------------------------------------------------------------
    | SECRETARÍA ACADÉMICA / GENERAL — ROL 1
    |--------------------------------------------------------------------------
    | Acceso global a todas las carreras.
    */

    Route::get(
        '/administrativa',
        [AuditoriaController::class, 'administrativa']
    )
        ->middleware('roleid:1')
        ->name('administrativa');

    /*
    |--------------------------------------------------------------------------
    | COORDINADOR DE CARRERA — ROL 4
    |--------------------------------------------------------------------------
    | Acceso limitado a su carrera.
    */

    Route::get(
        '/coordinador',
        [AuditoriaController::class, 'coordinador']
    )
        ->middleware('roleid:4')
        ->name('coordinador');

    /*
    |--------------------------------------------------------------------------
    | SECRETARÍA DE CARRERA — ROL 5
    |--------------------------------------------------------------------------
    | Acceso limitado a su carrera.
    */

    Route::get(
        '/secretaria-carrera',
        [AuditoriaController::class, 'secretariaCarrera']
    )
        ->middleware('roleid:5')
        ->name('secretaria_carrera');

    /*
    |--------------------------------------------------------------------------
    | COMPATIBILIDAD CON LA RUTA ANTERIOR
    |--------------------------------------------------------------------------
    | Permite que /auditoria/general continúe funcionando.
    */

    Route::get(
        '/general',
        [AuditoriaController::class, 'general']
    )
        ->middleware('roleid:5')
        ->name('general');
});