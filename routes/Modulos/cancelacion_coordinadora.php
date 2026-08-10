<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CancelacionCoordinadoraController;


/*
|--------------------------------------------------------------------------
| MÓDULO: CANCELACIÓN EXCEPCIONAL - COORDINACIÓN
|--------------------------------------------------------------------------
|
| Seguridad:
|
| auth                  = Usuario autenticado
| session.timeout       = Sesión vigente
| cuenta.activa         = Cuenta individual activa
| roleid:3,4            = Administrador / Coordinador
| rol.carrera.activo    = Validación general de rol por carrera
| acceso.modulo         = Permiso sobre CANCELACIONES
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:3,4',
    'rol.carrera.activo',
])
    ->prefix('empleado/coordinadora/cancelacion')
    ->name('cancelacion.coordinadora.')
    ->controller(CancelacionCoordinadoraController::class)
    ->group(function () {


        /*
        |--------------------------------------------------------------------------
        | LISTADO DE SOLICITUDES
        |--------------------------------------------------------------------------
        |
        | Permite consultar las solicitudes que llegaron
        | a la etapa de Coordinación.
        |
        | Permiso: VISUALIZAR
        |
        */

        Route::get(
            '/',
            'index'
        )
        ->middleware(
            'acceso.modulo:CANCELACIONES,visualizar'
        )
        ->name('index');


        /*
        |--------------------------------------------------------------------------
        | VER DOCUMENTO
        |--------------------------------------------------------------------------
        |
        | Permite consultar los documentos asociados
        | a una solicitud.
        |
        | Permiso: VISUALIZAR
        |
        */

        Route::get(
            '/documento/{id_documento}',
            'verDocumento'
        )
        ->whereNumber('id_documento')
        ->middleware(
            'acceso.modulo:CANCELACIONES,visualizar'
        )
        ->name('documento');


        /*
        |--------------------------------------------------------------------------
        | APROBAR SOLICITUD
        |--------------------------------------------------------------------------
        |
        | La solicitud ya existe.
        | Esta acción modifica su estado y emite el dictamen.
        |
        | Permiso: ACTUALIZAR
        |
        */

        Route::post(
            '/{id_tramite}/aprobar',
            'aprobar'
        )
        ->whereNumber('id_tramite')
        ->middleware(
            'acceso.modulo:CANCELACIONES,actualizar'
        )
        ->name('aprobar');


        /*
        |--------------------------------------------------------------------------
        | RECHAZAR SOLICITUD
        |--------------------------------------------------------------------------
        |
        | La solicitud ya existe.
        | Esta acción modifica su estado y registra el rechazo.
        |
        | Permiso: ACTUALIZAR
        |
        */

        Route::post(
            '/{id_tramite}/rechazar',
            'rechazar'
        )
        ->whereNumber('id_tramite')
        ->middleware(
            'acceso.modulo:CANCELACIONES,actualizar'
        )
        ->name('rechazar');


        /*
        |--------------------------------------------------------------------------
        | DETALLE DEL TRÁMITE
        |--------------------------------------------------------------------------
        |
        | Permiso: VISUALIZAR
        |
        */

        Route::get(
            '/{id_tramite}',
            'detalle'
        )
        ->whereNumber('id_tramite')
        ->middleware(
            'acceso.modulo:CANCELACIONES,visualizar'
        )
        ->name('detalle');

    });
