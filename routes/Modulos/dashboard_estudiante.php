<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| DASHBOARD DEL ESTUDIANTE
|--------------------------------------------------------------------------
|
| Seguridad aplicada:
|
| auth                = Usuario autenticado
| session.timeout     = Sesión vigente
| cuenta.activa       = Cuenta individual activa
| roleid:2            = Debe ser estudiante
| rol.carrera.activo  = Rol Estudiante activo en su carrera
|
*/

Route::middleware([
    'auth',
    'session.timeout',
    'cuenta.activa',
    'roleid:2',
    'rol.carrera.activo',
])->get('/dashboard', function () {

    $user = Auth::user();

    $displayName = 'Alumno';


    /*
    |--------------------------------------------------------------------------
    | NOMBRE DEL ESTUDIANTE
    |--------------------------------------------------------------------------
    */

    if ($user) {

        if (
            isset($user->persona)
            && !empty($user->persona->nombre_persona)
        ) {

            $displayName =
                trim($user->persona->nombre_persona);

        } elseif (!empty($user->nombre_persona)) {

            $displayName =
                trim($user->nombre_persona);

        } elseif (!empty($user->name)) {

            $displayName =
                trim($user->name);

        } elseif (!empty($user->id_persona)) {

            $persona = DB::table('tbl_persona')
                ->where(
                    'id_persona',
                    $user->id_persona
                )
                ->first();


            if (
                $persona
                && !empty($persona->nombre_persona)
            ) {

                $displayName =
                    trim($persona->nombre_persona);
            }

        } elseif (!empty($user->email)) {

            $displayName =
                trim($user->email);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | INICIALES
    |--------------------------------------------------------------------------
    */

    $parts = preg_split(
        '/\s+/',
        trim($displayName)
    );


    $initials = '';


    foreach (
        array_slice($parts, 0, 2)
        as $part
    ) {

        if (!empty($part)) {

            $initials .= strtoupper(
                mb_substr(
                    $part,
                    0,
                    1
                )
            );
        }
    }


    if ($initials === '') {
        $initials = 'A';
    }


    return view(
        'dashboard',
        compact(
            'displayName',
            'initials'
        )
    );

})->name('dashboard');


/*
|--------------------------------------------------------------------------
| MANTENER SESIÓN ACTIVA
|--------------------------------------------------------------------------
|
| Se mantiene únicamente con auth porque esta ruta sirve para renovar
| el contador de sesión.
|
*/

Route::middleware('auth')
    ->post(
        '/session/keep-alive',
        function (Request $request) {

            $request->session()->put(
                'last_activity_time',
                time()
            );


            return response()->json([
                'ok' => true,
                'message' =>
                    'Sesión renovada correctamente.'
            ]);
        }
    )
    ->name('session.keepalive');


/*
|--------------------------------------------------------------------------
| CERRAR SESIÓN POR INACTIVIDAD
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->post(
        '/session/logout-inactive',
        function (Request $request) {

            $loginTipo =
                session('login_tipo');


            Auth::logout();


            $request
                ->session()
                ->invalidate();


            $request
                ->session()
                ->regenerateToken();


            $redirectUrl =
                '/portal';


            if ($loginTipo === 'estudiante') {

                $redirectUrl =
                    route(
                        'login.tipo',
                        [
                            'tipo' =>
                                'estudiante'
                        ]
                    );

            } elseif ($loginTipo === 'empleado') {

                $redirectUrl =
                    route(
                        'login.tipo',
                        [
                            'tipo' =>
                                'empleado'
                        ]
                    );
            }


            return response()->json([
                'ok' => true,
                'redirect' => $redirectUrl,
                'message' =>
                    'La sesión expiró por inactividad.'
            ]);
        }
    )
    ->name('session.logout.inactive');
