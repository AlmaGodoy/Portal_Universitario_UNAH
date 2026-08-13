<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoExcepcionalController;
use App\Http\Controllers\CancelacionPaso2Controller;


/*
|--------------------------------------------------------------------------
| MÓDULO: CANCELACIÓN EXCEPCIONAL - ESTUDIANTE
|--------------------------------------------------------------------------
|
| Seguridad:
|
| auth                  = Usuario autenticado
| session.timeout       = Sesión vigente
| cuenta.activa         = Cuenta individual activa
| roleid:2              = Solo estudiante
| rol.carrera.activo    = Rol Estudiante activo en su carrera
| acceso.modulo         = Permiso específico sobre CANCELACIONES
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:2',
    'rol.carrera.activo',
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | PANTALLA PRINCIPAL
    |--------------------------------------------------------------------------
    |
    | Permiso: VISUALIZAR
    |
    */

    Route::get(
        '/cancelacion',
        [
            DocumentoExcepcionalController::class,
            'index'
        ]
    )
    ->middleware(
        'acceso.modulo:CANCELACIONES,visualizar'
    )
    ->name('cancelacion.index');


    /*
    |--------------------------------------------------------------------------
    | CREAR / REGISTRAR SOLICITUD
    |--------------------------------------------------------------------------
    |
    | Esta operación crea información nueva.
    |
    | Permiso: GUARDAR
    |
    */

    Route::post(
        '/cancelacion',
        [
            DocumentoExcepcionalController::class,
            'subir'
        ]
    )
    ->middleware(
        'acceso.modulo:CANCELACIONES,guardar'
    )
    ->name('cancelacion.subir');


    /*
    |--------------------------------------------------------------------------
    | NUEVA SOLICITUD
    |--------------------------------------------------------------------------
    |
    | Solo muestra el formulario.
    |
    | Permiso: VISUALIZAR
    |
    */

    Route::get(
        '/cancelacion/nueva',
        [
            DocumentoExcepcionalController::class,
            'nuevaSolicitud'
        ]
    )
    ->middleware(
        'acceso.modulo:CANCELACIONES,visualizar'
    )
    ->name('cancelacion.nueva');


    /*
    |--------------------------------------------------------------------------
    | PASO 2
    |--------------------------------------------------------------------------
    |
    | Consulta los documentos y requisitos del trámite.
    |
    | Permiso: VISUALIZAR
    |
    */

    Route::get(
        '/cancelacion/{id_tramite}/paso2',
        [
            CancelacionPaso2Controller::class,
            'index'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,visualizar'
    )
    ->name('cancelacion.paso2');


    /*
    |--------------------------------------------------------------------------
    | SUBIR IDENTIDAD
    |--------------------------------------------------------------------------
    |
    | Se agrega un documento al trámite.
    |
    | Permiso: GUARDAR
    |
    */

    Route::post(
        '/cancelacion/{id_tramite}/subir-identidad',
        [
            CancelacionPaso2Controller::class,
            'subirIdentidad'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,guardar'
    )
    ->name('cancelacion.paso2.subir-identidad');


    /*
    |--------------------------------------------------------------------------
    | SUBIR DOCUMENTO BASE
    |--------------------------------------------------------------------------
    |
    | Permiso: GUARDAR
    |
    */

    Route::post(
        '/cancelacion/{id_tramite}/subir-base',
        [
            CancelacionPaso2Controller::class,
            'subirDocumentoBase'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,guardar'
    )
    ->name('cancelacion.paso2.subir-base');


    /*
    |--------------------------------------------------------------------------
    | SUBIR DOCUMENTO DE ALTO RIESGO
    |--------------------------------------------------------------------------
    |
    | Permiso: GUARDAR
    |
    */

    Route::post(
        '/cancelacion/{id_tramite}/subir-riesgo',
        [
            CancelacionPaso2Controller::class,
            'subirDocumentoAltoRiesgo'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,guardar'
    )
    ->name('cancelacion.paso2.subir-riesgo');


    /*
    |--------------------------------------------------------------------------
    | SUBIR DOCUMENTO FLEXIBLE
    |--------------------------------------------------------------------------
    |
    | Permiso: GUARDAR
    |
    */

    Route::post(
        '/cancelacion/{id_tramite}/subir-flexible',
        [
            CancelacionPaso2Controller::class,
            'subirDocumentoFlexible'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,guardar'
    )
    ->name('cancelacion.paso2.subir-flexible');


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR DOCUMENTO
    |--------------------------------------------------------------------------
    |
    | Permiso: ELIMINAR
    |
    */

    Route::delete(
        '/cancelacion/{id_tramite}/documento/{id_documento}',
        [
            CancelacionPaso2Controller::class,
            'eliminarDocumento'
        ]
    )
    ->whereNumber('id_tramite')
    ->whereNumber('id_documento')
    ->middleware(
        'acceso.modulo:CANCELACIONES,eliminar'
    )
    ->name('cancelacion.paso2.eliminar');


    /*
    |--------------------------------------------------------------------------
    | VALIDAR PASO 2
    |--------------------------------------------------------------------------
    |
    | Esta acción trabaja sobre un trámite ya existente
    | y modifica su avance/validación.
    |
    | Permiso: ACTUALIZAR
    |
    */

    Route::post(
        '/cancelacion/{id_tramite}/validar',
        [
            CancelacionPaso2Controller::class,
            'validarPaso2'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,actualizar'
    )
    ->name('cancelacion.paso2.validar');


    /*
    |--------------------------------------------------------------------------
    | PASO 3
    |--------------------------------------------------------------------------
    |
    | Muestra el resultado o continuación del trámite.
    |
    | Permiso: VISUALIZAR
    |
    */

    Route::get(
        '/cancelacion/{id_tramite}/paso3',
        [
            CancelacionPaso2Controller::class,
            'paso3'
        ]
    )
    ->whereNumber('id_tramite')
    ->middleware(
        'acceso.modulo:CANCELACIONES,visualizar'
    )
    ->name('cancelacion.paso3');

});
