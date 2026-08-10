<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CancelacionSecretariaController;


/*
|--------------------------------------------------------------------------
| MÓDULO: CANCELACIÓN EXCEPCIONAL - SECRETARÍA DE CARRERA
|--------------------------------------------------------------------------
|
| Seguridad:
|
| auth                  = Usuario autenticado
| session.timeout       = Sesión vigente
| cuenta.activa         = Cuenta individual activa
| roleid:5              = Solo Secretaría de Carrera
| rol.carrera.activo    = Rol Secretario activo en su carrera
| acceso.modulo         = Permiso sobre CANCELACIONES
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:5',
    'rol.carrera.activo',
])
    ->prefix('empleado/secretaria/cancelacion')
    ->name('cancelacion.secretaria.')
    ->controller(CancelacionSecretariaController::class)
    ->group(function () {


        /*
        |--------------------------------------------------------------------------
        | LISTADO DE SOLICITUDES
        |--------------------------------------------------------------------------
        |
        | Secretaría consulta las solicitudes de cancelación
        | correspondientes a su carrera.
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
        | Permite consultar un documento asociado al trámite.
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
        | DEVOLVER DOCUMENTACIÓN
        |--------------------------------------------------------------------------
        |
        | Esta acción modifica el estado/revisión de un trámite
        | existente.
        |
        | Permiso: ACTUALIZAR
        |
        */

        Route::post(
            '/{id_tramite}/devolver',
            'devolverDocumentacion'
        )
        ->whereNumber('id_tramite')
        ->middleware(
            'acceso.modulo:CANCELACIONES,actualizar'
        )
        ->name('devolver');


        /*
        |--------------------------------------------------------------------------
        | MARCAR LISTO PARA COORDINACIÓN
        |--------------------------------------------------------------------------
        |
        | Secretaría cambia el estado del trámite para enviarlo
        | a la siguiente etapa.
        |
        | Permiso: ACTUALIZAR
        |
        */

        Route::post(
            '/{id_tramite}/listo',
            'marcarListoParaCoordinadora'
        )
        ->whereNumber('id_tramite')
        ->middleware(
            'acceso.modulo:CANCELACIONES,actualizar'
        )
        ->name('listo');


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
