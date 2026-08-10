<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLES DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    public function panelRoles(Request $request)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        /*
        |--------------------------------------------------------------------------
        | CARRERA DEL COORDINADOR
        |--------------------------------------------------------------------------
        */

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        $buscar = trim(
            (string) $request->get('buscar', '')
        );

        $estado = $request->get(
            'estado_activo',
            ''
        );


        /*
        |--------------------------------------------------------------------------
        | CONSULTAR ROLES
        |--------------------------------------------------------------------------
        |
        | tbl_rol:
        | Define el rol global del sistema.
        |
        | tbl_rol_carrera:
        | Define si ese rol está activo en la carrera actual.
        |
        */

        $query = DB::table('tbl_rol as r')

            ->leftJoin(
                'tbl_rol_carrera as rc',
                function ($join) use ($idCarreraActual) {

                    $join->on(
                        'rc.id_rol',
                        '=',
                        'r.id_rol'
                    );

                    $join->where(
                        'rc.id_carrera',
                        '=',
                        $idCarreraActual
                    );
                }
            )

            ->select(
                'r.id_rol',
                'r.nombre_rol',
                'r.descripcion',
                'r.estado_activo as estado_global',
                'rc.id_rol_carrera',
                DB::raw(
                    'COALESCE(rc.estado_activo, 1) AS estado_carrera'
                )
            )

            ->whereIn(
                'r.id_rol',
                [2, 4, 5]
            )

            ->where(
                'r.estado_activo',
                1
            );


        /*
        |--------------------------------------------------------------------------
        | BUSCAR
        |--------------------------------------------------------------------------
        */

        if ($buscar !== '') {

            $query->where(
                function ($q) use ($buscar) {

                    $q->where(
                        'r.nombre_rol',
                        'LIKE',
                        '%' . $buscar . '%'
                    );

                    $q->orWhere(
                        'r.descripcion',
                        'LIKE',
                        '%' . $buscar . '%'
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRO DE ESTADO EN LA CARRERA
        |--------------------------------------------------------------------------
        */

        if (
            $estado !== ''
            && in_array(
                (string) $estado,
                ['0', '1'],
                true
            )
        ) {

            $query->whereRaw(
                'COALESCE(rc.estado_activo, 1) = ?',
                [
                    (int) $estado
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | RESULTADOS
        |--------------------------------------------------------------------------
        */

        $roles = $query
            ->orderBy('r.id_rol')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */

        return view(
            'rol_seguridad_roles',
            [
                'roles' => $roles,

                'idCarreraActual' =>
                    $idCarreraActual,

                'esCoordinador' =>
                    true,

                'esSecretariaGeneral' =>
                    false,

                'filtros' => [
                    'buscar' => $buscar,
                    'estado_activo' => $estado,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR ROL EN LA CARRERA
    |--------------------------------------------------------------------------
    */

    public function updateEstadoRolCarrera(
        Request $request,
        $idRol
    ) {

        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        /*
        |--------------------------------------------------------------------------
        | EL COORDINADOR NO PUEDE SER DESACTIVADO
        |--------------------------------------------------------------------------
        */

        if ((int) $idRol === 4) {

            return redirect()
                ->route('seguridad.roles')
                ->withErrors([
                    'rol' => 'El rol Coordinador está protegido y no puede ser desactivado desde este módulo.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SOLO ESTUDIANTE Y SECRETARIO
        |--------------------------------------------------------------------------
        */

        if (!in_array(
            (int) $idRol,
            [2, 5],
            true
        )) {

            return redirect()
                ->route('seguridad.roles')
                ->withErrors([
                    'rol' => 'El rol seleccionado no puede ser administrado desde la carrera.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'estado_activo' =>
                'required|in:0,1',
        ], [
            'estado_activo.required' =>
                'El estado del rol es obligatorio.',

            'estado_activo.in' =>
                'El estado seleccionado no es válido.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CARRERA ACTUAL
        |--------------------------------------------------------------------------
        */

        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.roles')
                ->withErrors([
                    'rol' => 'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PROCEDIMIENTO
        |--------------------------------------------------------------------------
        */

        $res = DB::select(
            'CALL UPD_ESTADO_ROL_CARRERA_SEGURIDAD(?, ?, ?, ?)',
            [
                $idCarreraActual,

                (int) $idRol,

                (int) $request->estado_activo,

                Auth::id()
            ]
        );


        $row = $res[0] ?? null;


        $resultado =
            $row->resultado
            ?? 'ERROR';


        $mensaje =
            $row->mensaje
            ?? 'No se pudo actualizar el estado del rol.';


        if ($resultado !== 'OK') {

            return redirect()
                ->route('seguridad.roles')
                ->withErrors([
                    'rol' => $mensaje
                ]);
        }


        return redirect()
            ->route('seguridad.roles')
            ->with(
                'status',
                $mensaje
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ROL ACTUAL
    |--------------------------------------------------------------------------
    */

    private function rolActual(): string
    {
        return strtolower(
            trim(
                (string) session(
                    'rol_texto',
                    'sin_rol'
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ES COORDINADOR
    |--------------------------------------------------------------------------
    */

    private function esCoordinador(): bool
    {
        return $this->rolActual()
            === 'coordinador';
    }


    /*
    |--------------------------------------------------------------------------
    | PERSONA AUTENTICADA
    |--------------------------------------------------------------------------
    */

    private function obtenerIdPersonaAutenticada(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }


        return isset($user->id_persona)

            ? (int) $user->id_persona

            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | CARRERA DEL COORDINADOR
    |--------------------------------------------------------------------------
    */

    private function obtenerIdCarreraEmpleadoActual(): ?int
    {
        $personaId =
            $this->obtenerIdPersonaAutenticada();


        if (!$personaId) {
            return null;
        }


        $res = DB::select(
            'CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)',
            [
                $personaId
            ]
        );


        $row = $res[0] ?? null;


        if (
            !$row
            || ($row->resultado ?? 'ERROR') !== 'OK'
            || empty($row->id_carrera)
        ) {

            return null;
        }


        return (int) $row->id_carrera;
    }


    /*
    |--------------------------------------------------------------------------
    | SIN PERMISO
    |--------------------------------------------------------------------------
    */

    private function redirigirSinPermiso()
    {
        $ruta = session('login_tipo')
            === 'estudiante'

            ? route('dashboard')

            : route('empleado.dashboard');


        return redirect($ruta)
            ->withErrors([
                'seguridad' =>
                    'No tienes permiso para acceder al módulo de Seguridad.'
            ]);
    }
}
