<?php

use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo de respaldos
|--------------------------------------------------------------------------
|
| Disponible para:
| 1 = Secretaría Académica o Secretaría General
| 3 = Administrador
| 4 = Coordinador de Carrera
| 5 = Secretaría de Carrera
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'roleid:1,3,4,5',
])
    ->prefix('respaldos')
    ->name('backup.')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Panel principal
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [BackupController::class, 'mostrarPanel']
        )->name('index');

        /*
        |--------------------------------------------------------------------------
        | Probar conexión
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/probar',
            [BackupController::class, 'probarConexion']
        )->name('probar');

        /*
        |--------------------------------------------------------------------------
        | Generar, registrar y descargar el respaldo
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/generar',
            [BackupController::class, 'crearBackup']
        )->name('generar');
    });