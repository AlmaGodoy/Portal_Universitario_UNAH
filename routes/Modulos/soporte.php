<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoporteController;

/*
|--------------------------------------------------------------------------
| MÓDULO DE SOPORTE
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'session.timeout',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SOPORTE GENERAL
    |--------------------------------------------------------------------------
    | Si un estudiante entra a /soporte, carga la vista del estudiante.
    | Si entra un empleado, coordinador o secretaría, redirige a soporte/secretaria.
    */
    Route::get('/soporte', function () {
        $user = auth()->user();
        $rol = (int) ($user->id_rol ?? 0);

        return match ($rol) {
            2 => app(SoporteController::class)->vista(),
            1, 4, 5 => redirect()->route('soporte.secretaria'),
            default => abort(403, 'NO AUTORIZADO.'),
        };
    })->name('soporte.vista');


    /*
    |--------------------------------------------------------------------------
    | VISTA SOPORTE EMPLEADOS
    |--------------------------------------------------------------------------
    */
    Route::get('/soporte/secretaria', [SoporteController::class, 'vistaSecretaria'])
        ->middleware('roleid:1,4,5')
        ->name('soporte.secretaria');


    /*
    |--------------------------------------------------------------------------
    | API SOPORTE ESTUDIANTE
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/soporte')
        ->middleware('roleid:2')
        ->group(function () {

            Route::get('/catalogos', [SoporteController::class, 'catalogos'])
                ->name('api.soporte.catalogos');

            Route::post('/crear', [SoporteController::class, 'crear'])
                ->name('api.soporte.crear');

            Route::get('/mis-solicitudes', [SoporteController::class, 'misSolicitudes'])
                ->name('api.soporte.mis_solicitudes');

            Route::get('/ver/{idSoporte}', [SoporteController::class, 'verMiSolicitud'])
                ->whereNumber('idSoporte')
                ->name('api.soporte.ver');
        });


    /*
    |--------------------------------------------------------------------------
    | API SOPORTE EMPLEADOS / SECRETARÍA / COORDINADOR
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/soporte/secretaria')
        ->middleware('roleid:1,4,5')
        ->group(function () {

            Route::get('/bandeja', [SoporteController::class, 'bandejaSecretaria'])
                ->name('api.soporte.secretaria.bandeja');

            Route::get('/ver/{idSoporte}', [SoporteController::class, 'verParaSecretaria'])
                ->whereNumber('idSoporte')
                ->name('api.soporte.secretaria.ver');

            Route::patch('/tomar/{idSoporte}', [SoporteController::class, 'tomarCaso'])
                ->whereNumber('idSoporte')
                ->name('api.soporte.secretaria.tomar');

            Route::patch('/resolver/{idSoporte}', [SoporteController::class, 'resolver'])
                ->whereNumber('idSoporte')
                ->name('api.soporte.secretaria.resolver');
        });
});