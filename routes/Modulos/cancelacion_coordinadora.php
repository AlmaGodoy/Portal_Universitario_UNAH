<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CancelacionCoordinadoraController;

Route::middleware(['auth'])
    ->prefix('empleado/coordinadora/cancelacion')
    ->name('cancelacion.coordinadora.')
    ->controller(CancelacionCoordinadoraController::class)
    ->group(function () {

        Route::get('/', 'index')
            ->name('index');

        Route::get('/documento/{id_documento}', 'verDocumento')
            ->whereNumber('id_documento')
            ->name('documento');

        Route::post('/{id_tramite}/aprobar', 'aprobar')
            ->whereNumber('id_tramite')
            ->name('aprobar');

        Route::post('/{id_tramite}/rechazar', 'rechazar')
            ->whereNumber('id_tramite')
            ->name('rechazar');

        Route::get('/{id_tramite}', 'detalle')
            ->whereNumber('id_tramite')
            ->name('detalle');
    });