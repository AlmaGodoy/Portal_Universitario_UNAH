<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CambioCarreraController;


/*
|--------------------------------------------------------------------------
| MÓDULO: CAMBIO DE CARRERA - API
|--------------------------------------------------------------------------
*/

Route::prefix('api/cambio-carrera')
    ->middleware([
        'auth',
        'session.timeout',
        'cuenta.activa',
    ])
    ->group(function () {


        /*
        |--------------------------------------------------------------------------
        | ESTUDIANTE
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'roleid:2',
            'rol.carrera.activo',
        ])->group(function () {


            /*
            | CREAR SOLICITUD
            | Permiso: GUARDAR
            */

            Route::post(
                'crear',
                [
                    CambioCarreraController::class,
                    'crear'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,guardar'
            );


            /*
            | VER SOLICITUD
            | Permiso: VISUALIZAR
            */

            Route::get(
                'ver/{codigo}',
                [
                    CambioCarreraController::class,
                    'ver'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            /*
            | CONSULTAR ESTADO
            | Permiso: VISUALIZAR
            */

            Route::get(
                'estado-actual',
                [
                    CambioCarreraController::class,
                    'estadoActual'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            /*
            | ELIMINAR SOLICITUD
            | Permiso: ELIMINAR
            */

            Route::delete(
                'eliminar/{id_tramite}',
                [
                    CambioCarreraController::class,
                    'eliminar'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,eliminar'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | SECRETARÍA DE CARRERA / SECRETARÍA GENERAL
        |--------------------------------------------------------------------------
        |
        | 5 = Secretario
        | 1 = Secretaría General
        |
        */

        Route::middleware([
            'roleid:5,1',
            'rol.carrera.activo',
        ])->group(function () {


            /*
            | CAMBIAR ESTADO
            | Permiso: ACTUALIZAR
            */

            Route::put(
                'estado/{id_tramite}',
                [
                    CambioCarreraController::class,
                    'actualizarEstado'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,actualizar'
            );


            /*
            | LISTADO DE SOLICITUDES
            | Permiso: VISUALIZAR
            */

            Route::get(
                'secretaria/listado',
                [
                    CambioCarreraController::class,
                    'listadoSecretaria'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            /*
            | DETALLE
            | Permiso: VISUALIZAR
            */

            Route::get(
                'secretaria/detalle/{id_tramite}',
                [
                    CambioCarreraController::class,
                    'detalleSecretaria'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            /*
            | GUARDAR REVISIÓN
            |
            | Aunque técnicamente es POST, modifica un trámite
            | existente, por eso corresponde ACTUALIZAR.
            */

            Route::post(
                'secretaria/guardar-revision',
                [
                    CambioCarreraController::class,
                    'guardarRevisionSecretaria'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,actualizar'
            );


            /*
            |--------------------------------------------------------------------------
            | CALENDARIOS ACADÉMICOS
            |--------------------------------------------------------------------------
            */


            /*
            | LISTAR
            */

            Route::get(
                'secretaria/calendarios',
                [
                    CambioCarreraController::class,
                    'listarCalendariosAcademicos'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            /*
            | CREAR
            */

            Route::post(
                'secretaria/calendarios',
                [
                    CambioCarreraController::class,
                    'crearCalendarioAcademico'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,guardar'
            );


            /*
            | ACTUALIZAR
            */

            Route::put(
                'secretaria/calendarios/{id_calendario}',
                [
                    CambioCarreraController::class,
                    'actualizarCalendarioAcademico'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,actualizar'
            );


            /*
            | CAMBIAR ESTADO
            */

            Route::put(
                'secretaria/calendarios/estado/{id_calendario}',
                [
                    CambioCarreraController::class,
                    'cambiarEstadoCalendarioAcademico'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,actualizar'
            );


            /*
            | ELIMINAR
            */

            Route::delete(
                'secretaria/calendarios/{id_calendario}',
                [
                    CambioCarreraController::class,
                    'eliminarCalendarioAcademico'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,eliminar'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | COORDINACIÓN
        |--------------------------------------------------------------------------
        |
        | 3 = Administrador
        | 4 = Coordinador
        |
        */

        Route::middleware([
            'roleid:3,4',
            'rol.carrera.activo',
        ])->group(function () {


            Route::get(
                'coordinacion/listado',
                [
                    CambioCarreraController::class,
                    'listadoCoordinacion'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            Route::get(
                'coordinacion/detalle/{id_tramite}',
                [
                    CambioCarreraController::class,
                    'detalleCoordinacion'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,visualizar'
            );


            Route::put(
                'coordinacion/dictaminar/{id_tramite}',
                [
                    CambioCarreraController::class,
                    'dictaminarCoordinacion'
                ]
            )->middleware(
                'acceso.modulo:CAMBIO_CARRERA,actualizar'
            );

        });


        /*
        |--------------------------------------------------------------------------
        | CONSULTAS GENERALES DEL MÓDULO
        |--------------------------------------------------------------------------
        */

        Route::get(
            'calendario-vigente',
            [
                CambioCarreraController::class,
                'calendarioVigente'
            ]
        )->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        );


        Route::get(
            'calendario-info',
            [
                CambioCarreraController::class,
                'calendarioInfo'
            ]
        )->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        );


        Route::get(
            'carreras',
            [
                CambioCarreraController::class,
                'carreras'
            ]
        )->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        );

    });


/*
|--------------------------------------------------------------------------
| FRONTEND - CAMBIO DE CARRERA
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | ESTUDIANTE
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'roleid:2',
        'rol.carrera.activo',
    ])->group(function () {


        Route::get(
            '/cambio-carrera',
            function () {
                return view('cambio_carrera');
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.index');


        Route::get(
            '/cambio-carrera/mis-tramites',
            function () {
                return view('cambio_carrera_tramites');
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        );


        Route::get(
            '/cambio-carrera/estado',
            function () {
                return view('cambio_carrera_estado');
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        );

    });


    /*
    |--------------------------------------------------------------------------
    | SECRETARÍA
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'roleid:5,1',
        'rol.carrera.activo',
    ])->group(function () {


        Route::get(
            '/cambio-carrera/secretaria',
            function () {
                return view('cambio_carrera_secretaria');
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.secretaria');


        Route::get(
            '/cambio-carrera/secretaria/revisar/{id_tramite}',
            function ($id_tramite) {

                return view(
                    'cambio_carrera_secretaria_revision',
                    compact('id_tramite')
                );
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.secretaria.revisar');


        Route::get(
            '/cambio-carrera/secretaria/calendarios',
            function () {
                return view(
                    'cambio_carrera_secretaria_calendario'
                );
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.secretaria.calendarios');

    });


    /*
    |--------------------------------------------------------------------------
    | COORDINACIÓN
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'roleid:3,4',
        'rol.carrera.activo',
    ])->group(function () {


        Route::get(
            '/cambio-carrera/coordinacion',
            function () {
                return view(
                    'cambio_carrera_coordinacion'
                );
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.coordinacion');


        Route::get(
            '/cambio-carrera/coordinacion/dictamen/{id_tramite}',
            function ($id_tramite) {

                return view(
                    'cambio_carrera_coordinacion_dictamen',
                    compact('id_tramite')
                );
            }
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.coordinacion.dictamen');


        Route::get(
            '/coordinador/cambio-carrera',
            [
                CambioCarreraController::class,
                'vistaCoordinador'
            ]
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('coordinador.cambio-carrera.index');

    });


    /*
    |--------------------------------------------------------------------------
    | DOCUMENTO DEL TRÁMITE
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'roleid:1,3,4,5',
        'rol.carrera.activo',
    ])->group(function () {


        Route::get(
            '/empleado/cambio-carrera/documento/{id_tramite}',
            [
                CambioCarreraController::class,
                'verDocumento'
            ]
        )
        ->middleware(
            'acceso.modulo:CAMBIO_CARRERA,visualizar'
        )
        ->name('cambio-carrera.documento');

    });

});
