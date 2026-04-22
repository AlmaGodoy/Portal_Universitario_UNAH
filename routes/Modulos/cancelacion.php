<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;
use App\Http\Controllers\CancelacionPaso2Controller;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/cancelacion', [DocumentoExcepcionalController::class, 'index'])
        ->name('cancelacion.index');

    Route::post('/cancelacion', [DocumentoExcepcionalController::class, 'subir'])
        ->name('cancelacion.subir');

    Route::get('/cancelacion/nueva', [DocumentoExcepcionalController::class, 'nuevaSolicitud'])
        ->name('cancelacion.nueva');

    Route::get('/cancelacion/{id_tramite}/paso2', [CancelacionPaso2Controller::class, 'index'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2');

    Route::post('/cancelacion/{id_tramite}/subir-identidad', [CancelacionPaso2Controller::class, 'subirIdentidad'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.subir-identidad');

    Route::post('/cancelacion/{id_tramite}/subir-base', [CancelacionPaso2Controller::class, 'subirDocumentoBase'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.subir-base');

    Route::post('/cancelacion/{id_tramite}/subir-riesgo', [CancelacionPaso2Controller::class, 'subirDocumentoAltoRiesgo'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.subir-riesgo');

    Route::post('/cancelacion/{id_tramite}/subir-flexible', [CancelacionPaso2Controller::class, 'subirDocumentoFlexible'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.subir-flexible');

    Route::delete('/cancelacion/{id_tramite}/documento/{id_documento}', [CancelacionPaso2Controller::class, 'eliminarDocumento'])
        ->whereNumber('id_tramite')
        ->whereNumber('id_documento')
        ->name('cancelacion.paso2.eliminar');

    Route::post('/cancelacion/{id_tramite}/validar', [CancelacionPaso2Controller::class, 'validarPaso2'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2.validar');

    Route::get('/cancelacion/{id_tramite}/paso3', [CancelacionPaso2Controller::class, 'paso3'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso3');
});