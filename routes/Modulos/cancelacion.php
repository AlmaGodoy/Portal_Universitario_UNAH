<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;
use App\Http\Controllers\CancelacionPaso2Controller;

/*
|--------------------------------------------------------------------------
| MÓDULO: CANCELACIÓN EXCEPCIONAL
|--------------------------------------------------------------------------
| Paso 1: formulario inicial
| Paso 2: carga de documentos
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'session.timeout', 'roleid:2'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | FRONTEND
    |--------------------------------------------------------------------------
    */

    // Paso 1 - Vista principal
    Route::get('/cancelacion', [DocumentoExcepcionalController::class, 'index'])
        ->name('cancelacion.index');

    // Paso 1 - Procesar formulario y crear trámite
    Route::post('/cancelacion/subir', [DocumentoExcepcionalController::class, 'subir'])
        ->name('cancelacion.subir');

    // Mis solicitudes
    Route::get('/cancelacion/mis-solicitudes', function () {
        return view('cancelacion_tramites');
    })->name('cancelacion.mis-tramites');

    // Estado general
    Route::get('/cancelacion/estado', function () {
        return view('cancelacion_estado');
    })->name('cancelacion.estado');

    // Paso 2 - Vista para adjuntar documentación
    Route::get('/cancelacion/paso2/{id_tramite}', [CancelacionPaso2Controller::class, 'index'])
        ->whereNumber('id_tramite')
        ->name('cancelacion.paso2');


    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */

    Route::prefix('api/cancelacion')->group(function () {

        // Consultas generales del trámite
        Route::get('mis-solicitudes', [DocumentoExcepcionalController::class, 'misSolicitudes'])
            ->name('cancelacion.api.mis-solicitudes');

        Route::get('detalle/{id_tramite}', [DocumentoExcepcionalController::class, 'detalle'])
            ->whereNumber('id_tramite')
            ->name('cancelacion.detalle');

        Route::get('documento/{id_documento}', [DocumentoExcepcionalController::class, 'verDocumento'])
            ->whereNumber('id_documento')
            ->name('cancelacion.documento');
    });

    /*
    |--------------------------------------------------------------------------
    | API PASO 2
    |--------------------------------------------------------------------------
    */

    Route::prefix('api/cancelacion-excepcional/paso2')->group(function () {

        Route::post('/{id_tramite}/subir-documento-base', [CancelacionPaso2Controller::class, 'subirDocumentoBase'])
            ->whereNumber('id_tramite')
            ->name('cancelacion.paso2.subir-base');

        Route::post('/{id_tramite}/subir-documento-riesgo', [CancelacionPaso2Controller::class, 'subirDocumentoRiesgo'])
            ->whereNumber('id_tramite')
            ->name('cancelacion.paso2.subir-riesgo');

        Route::post('/{id_tramite}/subir-documento-flexible', [CancelacionPaso2Controller::class, 'subirDocumentoFlexible'])
            ->whereNumber('id_tramite')
            ->name('cancelacion.paso2.subir-flexible');

        Route::post('/{id_tramite}/validar', [CancelacionPaso2Controller::class, 'validarPaso2'])
            ->whereNumber('id_tramite')
            ->name('cancelacion.paso2.validar');

        Route::delete('/documento/{id_documento}', [CancelacionPaso2Controller::class, 'eliminarDocumento'])
            ->whereNumber('id_documento')
            ->name('cancelacion.paso2.eliminar-documento');
    });
});