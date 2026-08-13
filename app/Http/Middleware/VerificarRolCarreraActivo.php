<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerificarRolCarreraActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return $next($request);
        }

        $idPersona = (int) ($usuario->id_persona ?? 0);
        $idRol = (int) ($usuario->id_rol ?? 0);

        /*
        |--------------------------------------------------------------------------
        | SOLO APLICA A ESTUDIANTE Y SECRETARÍA DE CARRERA
        |--------------------------------------------------------------------------
        |
        | 2 = Estudiante
        | 5 = Secretaría de Carrera
        |
        | Coordinador y demás roles no son bloqueados aquí.
        |
        */

        if (!in_array($idRol, [2, 5], true)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | OBTENER CARRERA DEL USUARIO
        |--------------------------------------------------------------------------
        */

        if ($idRol === 2) {

            $idCarrera = DB::table('tbl_estudiante')
                ->where('id_persona', $idPersona)
                ->value('id_carrera');

        } else {

            $idCarrera = DB::table('tbl_empleados')
                ->where('id_persona', $idPersona)
                ->value('id_carrera');
        }

        if (!$idCarrera) {

            return $this->cerrarSesion(
                $request,
                'No fue posible determinar la carrera asociada a tu usuario.',
                $idRol
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR ROL GLOBAL
        |--------------------------------------------------------------------------
        */

        $rolGlobalActivo = DB::table('tbl_rol')
            ->where('id_rol', $idRol)
            ->where('estado_activo', 1)
            ->exists();

        if (!$rolGlobalActivo) {

            return $this->cerrarSesion(
                $request,
                'Tu rol se encuentra temporalmente deshabilitado.',
                $idRol
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR ROL EN LA CARRERA
        |--------------------------------------------------------------------------
        */

        $rolCarreraActivo = DB::table('tbl_rol_carrera')
            ->where('id_carrera', (int) $idCarrera)
            ->where('id_rol', $idRol)
            ->where('estado_activo', 1)
            ->exists();

        if (!$rolCarreraActivo) {

            return $this->cerrarSesion(
                $request,
                'Tu rol se encuentra temporalmente deshabilitado para tu carrera.',
                $idRol
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ROL ACTIVO
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | CERRAR SESIÓN
    |--------------------------------------------------------------------------
    */

    private function cerrarSesion(
        Request $request,
        string $mensaje,
        int $idRol
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | CERRAR AUTENTICACIÓN
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
        | SOLICITUD API / AJAX
        |--------------------------------------------------------------------------
        */

        if (
            $request->is('api/*')
            || $request->is('*/api/*')
            || $request->expectsJson()
        ) {

            return response()->json([
                'ok' => false,
                'resultado' => 'ERROR',
                'mensaje' => $mensaje,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | ESTUDIANTE
        |--------------------------------------------------------------------------
        */

        if ($idRol === 2) {

            return redirect()
                ->route('login.tipo', [
                    'tipo' => 'estudiante'
                ])
                ->withErrors([
                    'rol' => $mensaje
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SECRETARÍA DE CARRERA
        |--------------------------------------------------------------------------
        */

        if ($idRol === 5) {

            return redirect()
                ->route('login.tipo', [
                    'tipo' => 'empleado'
                ])
                ->withErrors([
                    'rol' => $mensaje
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | RESPALDO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('portal')
            ->withErrors([
                'rol' => $mensaje
            ]);
    }
}
