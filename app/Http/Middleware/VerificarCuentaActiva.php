<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarCuentaActiva
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | USUARIO AUTENTICADO
        |--------------------------------------------------------------------------
        */

        $usuario = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | SI NO HAY USUARIO
        |--------------------------------------------------------------------------
        |
        | Normalmente el middleware "auth" actuará antes que este,
        | pero se mantiene esta validación como protección adicional.
        |
        */

        if (!$usuario) {

            if (
                $request->is('api/*')
                || $request->expectsJson()
            ) {

                return response()->json([
                    'ok' => false,
                    'resultado' => 'ERROR',
                    'mensaje' => 'Debes iniciar sesión para continuar.'
                ], 401);
            }


            return redirect()
                ->route('portal')
                ->withErrors([
                    'cuenta' =>
                        'Debes iniciar sesión para continuar.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR ESTADO DE LA CUENTA
        |--------------------------------------------------------------------------
        */

        if ((int) ($usuario->estado_cuenta ?? 0) !== 1) {

            /*
            |--------------------------------------------------------------------------
            | CERRAR SESIÓN
            |--------------------------------------------------------------------------
            */

            Auth::logout();


            /*
            |--------------------------------------------------------------------------
            | INVALIDAR SESIÓN
            |--------------------------------------------------------------------------
            */

            $request->session()->invalidate();


            /*
            |--------------------------------------------------------------------------
            | REGENERAR TOKEN CSRF
            |--------------------------------------------------------------------------
            */

            $request->session()->regenerateToken();


            /*
            |--------------------------------------------------------------------------
            | SI ES UNA SOLICITUD API
            |--------------------------------------------------------------------------
            */

            if (
                $request->is('api/*')
                || $request->expectsJson()
            ) {

                return response()->json([
                    'ok' => false,
                    'resultado' => 'ERROR',
                    'mensaje' =>
                        'Tu cuenta se encuentra desactivada. Comunícate con el administrador correspondiente.'
                ], 403);
            }


            /*
            |--------------------------------------------------------------------------
            | SI ES UNA SOLICITUD WEB
            |--------------------------------------------------------------------------
            |
            | Se utiliza "portal" porque en PumaGestión no existe
            | una ruta nombrada simplemente como "login".
            |
            */

            return redirect()
                ->route('portal')
                ->withErrors([
                    'cuenta' =>
                        'Tu cuenta se encuentra desactivada. No puedes acceder al sistema.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | CUENTA ACTIVA
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}
