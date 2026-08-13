<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoController;


/*
|--------------------------------------------------------------------------
| MÓDULO: DOCUMENTOS
|--------------------------------------------------------------------------
|
| Permisos:
|
| GET     = VISUALIZAR
| POST    = GUARDAR
| PUT     = ACTUALIZAR
| DELETE  = ELIMINAR
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'rol.carrera.activo',
])
->prefix('api/documentos')
->group(function () {


    /*
    |--------------------------------------------------------------------------
    | CREAR DOCUMENTO
    |--------------------------------------------------------------------------
    |
    | Requiere permiso GUARDAR sobre DOCUMENTOS.
    |
    */

    Route::post(
        'crear',
        [
            DocumentoController::class,
            'crear'
        ]
    )->middleware(
        'acceso.modulo:DOCUMENTOS,guardar'
    );


    /*
    |--------------------------------------------------------------------------
    | VER DOCUMENTOS DEL TRÁMITE
    |--------------------------------------------------------------------------
    |
    | Requiere permiso VISUALIZAR sobre DOCUMENTOS.
    |
    */

    Route::get(
        'ver/{id_tramite}',
        [
            DocumentoController::class,
            'ver'
        ]
    )->middleware(
        'acceso.modulo:DOCUMENTOS,visualizar'
    );


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR DOCUMENTO
    |--------------------------------------------------------------------------
    |
    | Requiere permiso ACTUALIZAR sobre DOCUMENTOS.
    |
    */

    Route::put(
        'actualizar/{id_documento}',
        [
            DocumentoController::class,
            'actualizar'
        ]
    )->middleware(
        'acceso.modulo:DOCUMENTOS,actualizar'
    );


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR DOCUMENTO
    |--------------------------------------------------------------------------
    |
    | Requiere permiso ELIMINAR sobre DOCUMENTOS.
    |
    */

    Route::delete(
        'eliminar/{id_documento}',
        [
            DocumentoController::class,
            'eliminar'
        ]
    )->middleware(
        'acceso.modulo:DOCUMENTOS,eliminar'
    );

});
