<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquivalenciaController;


/*
|--------------------------------------------------------------------------
| MÓDULO: EQUIVALENCIAS
|--------------------------------------------------------------------------
|
| Seguridad:
|
| auth                  = Usuario autenticado
| session.timeout       = Sesión vigente
| cuenta.activa         = Cuenta individual activa
| rol.carrera.activo    = Rol activo dentro de la carrera
| acceso.modulo         = Permiso específico sobre EQUIVALENCIAS
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'rol.carrera.activo',
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | FRONTEND - ESTUDIANTE
    |--------------------------------------------------------------------------
    |
    | Rol 2 = Estudiante
    |
    */

    Route::middleware([
        'roleid:2',
    ])->group(function () {

        Route::get(
            '/equivalencias',
            [
                EquivalenciaController::class,
                'indexAlumno'
            ]
        )
        ->middleware(
            'acceso.modulo:EQUIVALENCIAS,visualizar'
        )
        ->name('equivalencias.alumno');

    });


    /*
    |--------------------------------------------------------------------------
    | FRONTEND - SECRETARÍA
    |--------------------------------------------------------------------------
    |
    | Rol 5 = Secretario
    |
    */

    Route::middleware([
        'roleid:5',
    ])->group(function () {

        Route::get(
            '/equivalencias/revision',
            [
                EquivalenciaController::class,
                'indexRevisor'
            ]
        )
        ->middleware(
            'acceso.modulo:EQUIVALENCIAS,visualizar'
        )
        ->name('equivalencias.revisor');

    });


    /*
    |--------------------------------------------------------------------------
    | API / OPERACIONES DE EQUIVALENCIAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('equivalencias/api')
        ->name('api.equivalencias.')
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | SOLO ESTUDIANTE
            |--------------------------------------------------------------------------
            |
            | Rol 2
            |
            */

            Route::middleware([
                'roleid:2',
            ])->group(function () {


                /*
                |--------------------------------------------------------------------------
                | MIS SOLICITUDES
                |--------------------------------------------------------------------------
                |
                | Consulta las solicitudes realizadas por el estudiante.
                |
                | Permiso: VISUALIZAR
                |
                */

                Route::get(
                    '/mis-solicitudes',
                    [
                        EquivalenciaController::class,
                        'misSolicitudes'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('mis');


                /*
                |--------------------------------------------------------------------------
                | CREAR SOLICITUD
                |--------------------------------------------------------------------------
                |
                | Crea una nueva solicitud de equivalencia.
                |
                | Permiso: GUARDAR
                |
                */

                Route::post(
                    '/solicitud',
                    [
                        EquivalenciaController::class,
                        'crearSolicitud'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,guardar'
                )
                ->name('crear');


                /*
                |--------------------------------------------------------------------------
                | ASIGNATURAS DEL PLAN VIEJO
                |--------------------------------------------------------------------------
                |
                | Solo consulta información para construir
                | la solicitud.
                |
                | Permiso: VISUALIZAR
                |
                */

                Route::get(
                    '/plan-viejo/{versionPlanViejo}/asignaturas',
                    [
                        EquivalenciaController::class,
                        'obtenerAsignaturasPlanViejo'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('planViejo.asignaturas');


                /*
                |--------------------------------------------------------------------------
                | GUARDAR DETALLE DE SOLICITUD
                |--------------------------------------------------------------------------
                |
                | Se agregan los detalles/asignaturas asociados
                | a la solicitud.
                |
                | Permiso: GUARDAR
                |
                */

                Route::post(
                    '/solicitud/detalle',
                    [
                        EquivalenciaController::class,
                        'guardarDetalleSolicitud'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,guardar'
                )
                ->name('detalle.guardar');

            });


            /*
            |--------------------------------------------------------------------------
            | LECTURA - ESTUDIANTE Y SECRETARÍA
            |--------------------------------------------------------------------------
            |
            | Rol 2 = Estudiante
            | Rol 5 = Secretario
            |
            */

            Route::middleware([
                'roleid:2,5',
            ])->group(function () {


                /*
                |--------------------------------------------------------------------------
                | CABECERA DE SOLICITUD
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/solicitud/{idSolicitud}/cabecera',
                    [
                        EquivalenciaController::class,
                        'verCabeceraSolicitud'
                    ]
                )
                ->whereNumber('idSolicitud')
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('solicitud.cabecera');


                /*
                |--------------------------------------------------------------------------
                | DETALLE DE SOLICITUD
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/solicitud/{idSolicitud}/detalle',
                    [
                        EquivalenciaController::class,
                        'verDetalleSolicitud'
                    ]
                )
                ->whereNumber('idSolicitud')
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('solicitud.detalle');


                /*
                |--------------------------------------------------------------------------
                | EQUIVALENCIAS PRELIMINARES
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/solicitud/{idSolicitud}/preliminares',
                    [
                        EquivalenciaController::class,
                        'verEquivalenciasPreliminares'
                    ]
                )
                ->whereNumber('idSolicitud')
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('solicitud.preliminares');


                /*
                |--------------------------------------------------------------------------
                | DOCUMENTO DE LA SOLICITUD
                |--------------------------------------------------------------------------
                |
                | En este caso forma parte del flujo de Equivalencias,
                | por eso se controla con EQUIVALENCIAS / VISUALIZAR.
                |
                */

                Route::get(
                    '/solicitud/{idSolicitud}/documento',
                    [
                        EquivalenciaController::class,
                        'descargarDocumento'
                    ]
                )
                ->whereNumber('idSolicitud')
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('solicitud.documento');

            });


            /*
            |--------------------------------------------------------------------------
            | SOLO SECRETARÍA
            |--------------------------------------------------------------------------
            |
            | Rol 5
            |
            */

            Route::middleware([
                'roleid:5',
            ])->group(function () {


                /*
                |--------------------------------------------------------------------------
                | SOLICITUDES PENDIENTES
                |--------------------------------------------------------------------------
                |
                | Permiso: VISUALIZAR
                |
                */

                Route::get(
                    '/pendientes',
                    [
                        EquivalenciaController::class,
                        'solicitudesPendientes'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,visualizar'
                )
                ->name('pendientes');


                /*
                |--------------------------------------------------------------------------
                | VALIDAR DETALLE DE SOLICITUD
                |--------------------------------------------------------------------------
                |
                | Modifica la revisión de una solicitud existente.
                |
                | Permiso: ACTUALIZAR
                |
                */

                Route::post(
                    '/solicitud/detalle/validar',
                    [
                        EquivalenciaController::class,
                        'validarDetalleSolicitud'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,actualizar'
                )
                ->name('detalle.validar');


                /*
                |--------------------------------------------------------------------------
                | VALIDAR SOLICITUD
                |--------------------------------------------------------------------------
                |
                | Realiza la validación final de una solicitud existente.
                |
                | Permiso: ACTUALIZAR
                |
                */

                Route::post(
                    '/solicitud/validar',
                    [
                        EquivalenciaController::class,
                        'validarSolicitud'
                    ]
                )
                ->middleware(
                    'acceso.modulo:EQUIVALENCIAS,actualizar'
                )
                ->name('validar');

            });

        });

});
