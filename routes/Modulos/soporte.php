<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoporteController;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/soporte', [SoporteController::class, 'vista'])
        ->name('soporte.vista');

    Route::prefix('api/soporte')->group(function () {

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

    Route::prefix('api/soporte/secretaria')
        ->middleware('rol:secretario')
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
