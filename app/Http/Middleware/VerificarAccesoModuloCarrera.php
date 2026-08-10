<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerificarAccesoModuloCarrera
{
    public function handle(
        Request $request,
        Closure $next,
        string $nombreObjeto,
        string $nombrePermiso
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | USUARIO AUTENTICADO
        |--------------------------------------------------------------------------
        */

        $usuario = Auth::user();

        if (!$usuario) {

            return $this->bloquear(
                $request,
                'Debes iniciar sesión para continuar.',
                0,
                401
            );
        }


        $idPersona = (int) ($usuario->id_persona ?? 0);
        $idRol = (int) ($usuario->id_rol ?? 0);


        if (!$idPersona || !$idRol) {

            return $this->bloquear(
                $request,
                'No fue posible identificar correctamente tu usuario.',
                $idRol
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ROLES INSTITUCIONALES NO ADMINISTRADOS POR CARRERA
        |--------------------------------------------------------------------------
        |
        | 1 = Secretaría General
        | 3 = Administrador
        |
        | Estos roles no dependen de los permisos configurados
        | por un Coordinador de Carrera.
        |
        */

        if (in_array($idRol, [1, 3], true)) {

            return $next($request);
        }


        /*
        |--------------------------------------------------------------------------
        | ROLES ADMINISTRADOS
        |--------------------------------------------------------------------------
        |
        | 2 = Estudiante
        | 4 = Coordinador
        | 5 = Secretario
        |
        */

        if (!in_array($idRol, [2, 4, 5], true)) {

            return $this->bloquear(
                $request,
                'Tu rol no tiene acceso a este módulo.',
                $idRol
            );
        }


        /*
        |--------------------------------------------------------------------------
        | OBTENER CARRERA
        |--------------------------------------------------------------------------
        */

        if ($idRol === 2) {

            /*
            | Estudiante
            */

            $idCarrera = DB::table('tbl_estudiante')
                ->where(
                    'id_persona',
                    $idPersona
                )
                ->value('id_carrera');

        } else {

            /*
            | Coordinador / Secretario
            */

            $idCarrera = DB::table('tbl_empleados')
                ->where(
                    'id_persona',
                    $idPersona
                )
                ->value('id_carrera');
        }


        if (!$idCarrera) {

            return $this->bloquear(
                $request,
                'No fue posible determinar la carrera asociada a tu usuario.',
                $idRol
            );
        }


        $idCarrera = (int) $idCarrera;


        /*
        |--------------------------------------------------------------------------
        | BUSCAR MÓDULO
        |--------------------------------------------------------------------------
        */

        $nombreObjetoNormalizado = strtoupper(
            trim($nombreObjeto)
        );


        $objeto = DB::table('tbl_objeto')
            ->whereRaw(
                'UPPER(TRIM(nombre_objeto)) = ?',
                [$nombreObjetoNormalizado]
            )
            ->where(
                'estado_activo',
                1
            )
            ->first();


        if (!$objeto) {

            return $this->bloquear(
                $request,
                'El módulo solicitado no se encuentra disponible.',
                $idRol
            );
        }


        $idObjeto = (int) $objeto->id_objeto;


        /*
        |--------------------------------------------------------------------------
        | MANTENIMIENTO POR CARRERA
        |--------------------------------------------------------------------------
        |
        | Solamente afecta:
        |
        | 2 = Estudiante
        | 5 = Secretario
        |
        | El Coordinador NO queda bloqueado por mantenimiento.
        |
        */

        if (in_array($idRol, [2, 5], true)) {

            $objetoCarrera = DB::table(
                'tbl_objeto_carrera'
            )
                ->where(
                    'id_objeto',
                    $idObjeto
                )
                ->where(
                    'id_carrera',
                    $idCarrera
                )
                ->where(
                    'estado_activo',
                    1
                )
                ->first();


            if (!$objetoCarrera) {

                return $this->bloquear(
                    $request,
                    'El módulo '
                    . str_replace('_', ' ', $nombreObjetoNormalizado)
                    . ' no se encuentra disponible para tu carrera.',
                    $idRol
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ESTUDIANTE EN MANTENIMIENTO
            |--------------------------------------------------------------------------
            */

            if (
                $idRol === 2
                && (int) ($objetoCarrera->estado_estudiante ?? 0) !== 1
            ) {

                return $this->bloquear(
                    $request,
                    'El módulo '
                    . str_replace('_', ' ', $nombreObjetoNormalizado)
                    . ' se encuentra temporalmente en mantenimiento para los estudiantes de tu carrera.',
                    $idRol
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SECRETARÍA EN MANTENIMIENTO
            |--------------------------------------------------------------------------
            */

            if (
                $idRol === 5
                && (int) ($objetoCarrera->estado_secretario ?? 0) !== 1
            ) {

                return $this->bloquear(
                    $request,
                    'El módulo '
                    . str_replace('_', ' ', $nombreObjetoNormalizado)
                    . ' se encuentra temporalmente en mantenimiento para Secretaría de tu carrera.',
                    $idRol
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR PERMISO
        |--------------------------------------------------------------------------
        */

        $nombrePermisoNormalizado = strtolower(
            trim($nombrePermiso)
        );


        $permiso = DB::table('tbl_permiso')
            ->whereRaw(
                'LOWER(TRIM(nombre_permiso)) = ?',
                [$nombrePermisoNormalizado]
            )
            ->where(
                'estado_activo',
                1
            )
            ->first();


        if (!$permiso) {

            return $this->bloquear(
                $request,
                'El permiso solicitado no se encuentra configurado.',
                $idRol
            );
        }


        $idPermiso = (int) $permiso->id_permiso;


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR PERMISO
        |--------------------------------------------------------------------------
        |
        | Se verifica exactamente:
        |
        | CARRERA
        | + ROL
        | + PERMISO
        | + MÓDULO
        |
        */

        $tienePermiso = DB::table(
            'tbl_rol_permiso_carrera_v2'
        )
            ->where(
                'id_carrera',
                $idCarrera
            )
            ->where(
                'id_rol',
                $idRol
            )
            ->where(
                'id_permiso',
                $idPermiso
            )
            ->where(
                'id_objeto',
                $idObjeto
            )
            ->where(
                'estado_activo',
                1
            )
            ->exists();


        if (!$tienePermiso) {

            return $this->bloquear(
                $request,
                'No tienes permiso para realizar esta acción en el módulo '
                . str_replace('_', ' ', $nombreObjetoNormalizado)
                . '.',
                $idRol
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACCESO AUTORIZADO
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }


    /*
    |--------------------------------------------------------------------------
    | BLOQUEAR ACCESO
    |--------------------------------------------------------------------------
    |
    | API  -> devuelve JSON
    | WEB  -> redirige al panel
    |
    */

    private function bloquear(
        Request $request,
        string $mensaje,
        int $idRol,
        int $codigo = 403
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | SOLICITUD API / AJAX
        |--------------------------------------------------------------------------
        */

        if (
            $request->is('api/*')
            || $request->expectsJson()
        ) {

            return response()->json([
                'ok' => false,
                'resultado' => 'ERROR',
                'mensaje' => $mensaje
            ], $codigo);
        }


        /*
        |--------------------------------------------------------------------------
        | ESTUDIANTE
        |--------------------------------------------------------------------------
        */

        if ($idRol === 2) {

            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'acceso' => $mensaje
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EMPLEADO
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('empleado.dashboard')
            ->withErrors([
                'acceso' => $mensaje
            ]);
    }
}
