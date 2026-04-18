<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CancelacionSecretariaController;

Route::middleware(['auth', 'session.timeout', 'roleid:5'])
    ->prefix('empleado/secretaria/cancelacion')
    ->name('cancelacion.secretaria.')
    ->controller(CancelacionSecretariaController::class)
    ->group(function () {

        Route::get('/', 'index')
            ->name('index');

        Route::get('/documento/{id_documento}', 'verDocumento')
            ->whereNumber('id_documento')
            ->name('documento');

        Route::post('/{id_tramite}/devolver', 'devolverDocumentacion')
            ->whereNumber('id_tramite')
            ->name('devolver');

        Route::post('/{id_tramite}/listo', 'marcarListoParaCoordinadora')
            ->whereNumber('id_tramite')
            ->name('listo');

        Route::get('/{id_tramite}', 'detalle')
            ->whereNumber('id_tramite')
            ->name('detalle');
    });