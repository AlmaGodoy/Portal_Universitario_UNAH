<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;
use App\Http\Controllers\CancelacionPaso2Controller;
use App\Http\Controllers\ResolucionCancelacionController;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/cancelacion-excepcional', [DocumentoExcepcionalController::class, 'index'])
        ->name('cancelacion.index');

    Route::post('/cancelacion-excepcional/subir', [DocumentoExcepcionalController::class, 'subir'])
        ->name('cancelacion.subir');

    Route::get('/cancelacion-excepcional/paso2/{id_tramite}', [CancelacionPaso2Controller::class, 'index'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2');

    Route::get('/cancelacion-excepcional/paso3/{id_tramite}', [CancelacionPaso2Controller::class, 'paso3'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso3');

    Route::get('/coordinador/cancelacion', [ResolucionCancelacionController::class, 'vista'])
        ->name('coordinador.cancelacion.index');
});

Route::middleware(['auth', 'session.timeout'])->prefix('api/cancelacion-excepcional/paso2')->group(function () {

    Route::post('{id_tramite}/documentos/base', [CancelacionPaso2Controller::class, 'subirDocumentoBase'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.base.upload');

    Route::post('{id_tramite}/documentos/riesgo', [CancelacionPaso2Controller::class, 'subirDocumentoAltoRiesgo'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.riesgo.upload');

    Route::post('{id_tramite}/documentos/flex', [CancelacionPaso2Controller::class, 'subirDocumentoFlexible'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.flex.upload');

    Route::delete('{id_tramite}/documentos/{id_documento}', [CancelacionPaso2Controller::class, 'eliminarDocumento'])
        ->whereNumber('id_tramite')
        ->whereNumber('id_documento')
        ->name('cancelacion.paso2.eliminar');

    Route::post('{id_tramite}/validar', [CancelacionPaso2Controller::class, 'validarPaso2'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.validar');
});
