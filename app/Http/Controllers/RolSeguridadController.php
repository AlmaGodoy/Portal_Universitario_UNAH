<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RolSeguridadController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PANEL PRINCIPAL DE SEGURIDAD
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $modulos = [
            [
                'titulo' => 'Roles del Sistema',
                'descripcion' => 'Consulta y administración del estado de los roles correspondientes a tu carrera.',
                'ruta' => route('seguridad.roles'),
                'icono' => 'fas fa-user-tag'
            ],
            [
                'titulo' => 'Usuarios de mi Carrera',
                'descripcion' => 'Consulta y administración del estado de las cuentas correspondientes a tu carrera.',
                'ruta' => route('seguridad.usuarios'),
                'icono' => 'fas fa-users'
            ],
            [
                'titulo' => 'Módulos del Sistema',
                'descripcion' => 'Administración de la disponibilidad de los módulos para estudiantes y Secretaría de tu carrera.',
                'ruta' => route('seguridad.objetos'),
                'icono' => 'fas fa-cubes'
            ],
            [
                'titulo' => 'Permisos por Rol',
                'descripcion' => 'Asignación de permisos a los roles sobre los módulos disponibles.',
                'ruta' => route('seguridad.accesos'),
                'icono' => 'fas fa-user-shield'
            ],
        ];

        return view('rol_seguridad_index', [
            'modulos' => $modulos,
            'rolActual' => $this->rolActual(),
            'esCoordinador' => true,
            'esSecretariaGeneral' => false,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | USUARIOS DE MI CARRERA
    |--------------------------------------------------------------------------
    */

    public function usuarios(Request $request)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'usuario' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }


        $carreras = DB::table('tbl_carrera')
            ->select(
                'id_carrera',
                'nombre_carrera'
            )
            ->orderBy('nombre_carrera')
            ->get();


        $roles = DB::table('tbl_rol')
            ->select(
                'id_rol',
                'nombre_rol',
                'descripcion',
                'estado_activo'
            )
            ->whereIn('id_rol', [2, 4, 5])
            ->where('estado_activo', 1)
            ->orderBy('id_rol')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */

        $filtroBusqueda = trim(
            (string) $request->get('buscar', '')
        );

        $filtroTipo = trim(
            (string) $request->get('tipo_usuario', '')
        );

        $filtroEstado = $request->get(
            'estado_cuenta',
            ''
        );

        $filtroRol = trim(
            (string) $request->get('id_rol', '')
        );


        /*
        |--------------------------------------------------------------------------
        | CONSULTAR USUARIOS
        |--------------------------------------------------------------------------
        */

        $usuariosRes = DB::select(
            'CALL SEL_USUARIOS_SEGURIDAD_FILTRO(?, ?, ?, ?, ?, ?)',
            [
                'COORDINADOR',

                $idCarreraActual,

                $filtroBusqueda !== ''
                    ? $filtroBusqueda
                    : null,

                $filtroTipo !== ''
                    ? $filtroTipo
                    : null,

                $filtroRol !== ''
                    ? $filtroRol
                    : null,

                (
                    $filtroEstado !== ''
                    && in_array(
                        (string) $filtroEstado,
                        ['0', '1'],
                        true
                    )
                )
                    ? (int) $filtroEstado
                    : null
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | NO MOSTRAR AL COORDINADOR AUTENTICADO
        |--------------------------------------------------------------------------
        */

        $idUsuarioActual = (int) Auth::id();

        $usuariosCollection = collect($usuariosRes)
            ->reject(function ($usuario) use ($idUsuarioActual) {

                return (int) ($usuario->id_usuario ?? 0)
                    === $idUsuarioActual;

            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | PAGINACIÓN
        |--------------------------------------------------------------------------
        */

        $perPage = 10;

        $currentPage = (int) $request->get('page', 1);

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $pagedData = $usuariosCollection
            ->forPage(
                $currentPage,
                $perPage
            )
            ->values();

        $usuariosPaginator = new LengthAwarePaginator(
            $pagedData,
            $usuariosCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );


        return view('rol_seguridad_usuarios', [
            'usuarios' => $usuariosPaginator,
            'roles' => $roles,
            'carreras' => $carreras,

            'rolActual' => $this->rolActual(),

            'esSecretariaGeneral' => false,
            'esCoordinador' => true,

            'idCarreraActual' => $idCarreraActual,

            'filtros' => [
                'buscar' => $filtroBusqueda,
                'tipo_usuario' => $filtroTipo,
                'id_rol' => $filtroRol,
                'estado_cuenta' => $filtroEstado,
                'id_carrera' => $idCarreraActual,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR USUARIO
    |--------------------------------------------------------------------------
    */

    public function updateEstadoUsuario(Request $request, $id)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        if ((int) $id === (int) Auth::id()) {

            return redirect()
                ->route('seguridad.usuarios')
                ->withErrors([
                    'usuario' => 'No puedes activar o desactivar tu propia cuenta desde el módulo de Seguridad.'
                ]);
        }


        $request->validate([
            'estado_cuenta' => 'required|in:0,1',
        ], [
            'estado_cuenta.required' =>
                'El estado de la cuenta es obligatorio.',

            'estado_cuenta.in' =>
                'El estado de la cuenta no es válido.',
        ]);


        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.usuarios')
                ->withErrors([
                    'usuario' => 'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR MISMA CARRERA
        |--------------------------------------------------------------------------
        */

        $usuarioCarrera = DB::table('tbl_usuario as u')

            ->join(
                'tbl_persona as p',
                'p.id_persona',
                '=',
                'u.id_persona'
            )

            ->leftJoin(
                'tbl_empleados as e',
                'e.id_persona',
                '=',
                'p.id_persona'
            )

            ->leftJoin(
                'tbl_estudiante as est',
                'est.id_persona',
                '=',
                'p.id_persona'
            )

            ->where(
                'u.id_usuario',
                (int) $id
            )

            ->selectRaw(
                'COALESCE(e.id_carrera, est.id_carrera) AS id_carrera'
            )

            ->first();


        if (
            !$usuarioCarrera
            || empty($usuarioCarrera->id_carrera)
            || (int) $usuarioCarrera->id_carrera
                !== (int) $idCarreraActual
        ) {

            return redirect()
                ->route('seguridad.usuarios')
                ->withErrors([
                    'usuario' => 'No tienes permiso para modificar usuarios fuera de tu carrera.'
                ]);
        }


        $res = DB::select(
            'CALL UPD_ESTADO_USUARIO_SEGURIDAD(?, ?, ?)',
            [
                (int) $id,
                (int) $request->estado_cuenta,
                Auth::id()
            ]
        );


        $row = $res[0] ?? null;

        $resultado = $row->resultado ?? 'ERROR';

        $mensaje = $row->mensaje
            ?? 'No se pudo actualizar el estado del usuario.';


        if ($resultado !== 'OK') {

            return back()
                ->withErrors([
                    'usuario' => $mensaje
                ])
                ->withInput();
        }


        return redirect()
            ->route('seguridad.usuarios')
            ->with(
                'status',
                $mensaje
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MÓDULOS DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    public function objetos()
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'modulo' =>
                        'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }


        $objetos = DB::table('tbl_objeto as o')

            ->leftJoin(
                'tbl_objeto_carrera as oc',
                function ($join) use ($idCarreraActual) {

                    $join->on(
                        'oc.id_objeto',
                        '=',
                        'o.id_objeto'
                    );

                    $join->where(
                        'oc.id_carrera',
                        '=',
                        $idCarreraActual
                    );
                }
            )

            ->select(
                'o.id_objeto',
                'o.nombre_objeto',
                'o.tipo_objeto',
                'o.estado_activo',

                'oc.id_objeto_carrera',

                DB::raw(
                    'COALESCE(oc.estado_estudiante, 1) AS estado_estudiante'
                ),

                DB::raw(
                    'COALESCE(oc.estado_secretario, 1) AS estado_secretario'
                )
            )

            ->where(
                'o.estado_activo',
                1
            )

            ->orderBy(
                'o.nombre_objeto'
            )

            ->get();


        return view(
            'rol_seguridad_objetos',
            [
                'objetos' => $objetos,

                'idCarreraActual' =>
                    $idCarreraActual,

                'esCoordinador' =>
                    true,

                'esSecretariaGeneral' =>
                    false,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIAR DISPONIBILIDAD DEL MÓDULO
    |--------------------------------------------------------------------------
    */

    public function updateEstadoModuloRol(
        Request $request,
        $idObjeto
    ) {

        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        $request->validate([
            'tipo_usuario' =>
                'required|in:ESTUDIANTE,SECRETARIO',

            'estado' =>
                'required|in:0,1',
        ], [
            'tipo_usuario.required' =>
                'Debes indicar el tipo de usuario.',

            'tipo_usuario.in' =>
                'El tipo de usuario seleccionado no es válido.',

            'estado.required' =>
                'El estado del módulo es obligatorio.',

            'estado.in' =>
                'El estado seleccionado no es válido.',
        ]);


        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.objetos')
                ->withErrors([
                    'modulo' =>
                        'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        $objeto = DB::table('tbl_objeto')

            ->where(
                'id_objeto',
                (int) $idObjeto
            )

            ->where(
                'estado_activo',
                1
            )

            ->first();


        if (!$objeto) {

            return redirect()
                ->route('seguridad.objetos')
                ->withErrors([
                    'modulo' =>
                        'El módulo seleccionado no existe.'
                ]);
        }


        if (
            strtoupper(
                trim(
                    (string) $objeto->tipo_objeto
                )
            ) !== 'PANTALLA'
        ) {

            return redirect()
                ->route('seguridad.objetos')
                ->withErrors([
                    'modulo' =>
                        'Los módulos internos del sistema no pueden ponerse en mantenimiento desde una carrera.'
                ]);
        }


        $res = DB::select(
            'CALL UPD_ESTADO_MODULO_ROL_CARRERA(?, ?, ?, ?, ?)',
            [
                $idCarreraActual,

                (int) $idObjeto,

                strtoupper(
                    trim(
                        (string) $request->tipo_usuario
                    )
                ),

                (int) $request->estado,

                Auth::id()
            ]
        );


        $row =
            $res[0]
            ?? null;


        $resultado =
            $row->resultado
            ?? 'ERROR';


        $mensaje =
            $row->mensaje
            ?? 'No se pudo actualizar la disponibilidad del módulo.';


        if ($resultado !== 'OK') {

            return redirect()
                ->route('seguridad.objetos')
                ->withErrors([
                    'modulo' =>
                        $mensaje
                ]);
        }


        return redirect()
            ->route('seguridad.objetos')
            ->with(
                'status',
                $mensaje
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PERMISOS POR ROL
    |--------------------------------------------------------------------------
    */

    public function accesos()
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'acceso' =>
                        'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $roles = DB::table('tbl_rol')
            ->select(
                'id_rol',
                'nombre_rol',
                'descripcion',
                'estado_activo'
            )
            ->whereIn(
                'id_rol',
                [2, 4, 5]
            )
            ->where(
                'estado_activo',
                1
            )
            ->orderBy(
                'nombre_rol'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | MÓDULOS
        |--------------------------------------------------------------------------
        */

        $objetos = DB::table('tbl_objeto')
            ->select(
                'id_objeto',
                'nombre_objeto',
                'tipo_objeto',
                'estado_activo'
            )
            ->where(
                'estado_activo',
                1
            )
            ->whereRaw(
                "UPPER(TRIM(tipo_objeto)) = 'PANTALLA'"
            )
            ->orderBy(
                'nombre_objeto'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PERMISOS
        |--------------------------------------------------------------------------
        */

        $permisos = DB::table('tbl_permiso')
            ->select(
                'id_permiso',
                'nombre_permiso',
                'descripcion',
                'estado_activo'
            )
            ->where(
                'estado_activo',
                1
            )
            ->orderBy(
                'id_permiso'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ACCESOS ACTIVOS E INACTIVOS
        |--------------------------------------------------------------------------
        |
        | Se consultan directamente para mostrar también los permisos inactivos
        | y permitir que puedan reactivarse desde esta misma pantalla.
        |
        */

        $accesos = DB::table('tbl_rol_permiso_carrera_v2 as rpc')

            ->join(
                'tbl_rol as r',
                'r.id_rol',
                '=',
                'rpc.id_rol'
            )

            ->join(
                'tbl_permiso as p',
                'p.id_permiso',
                '=',
                'rpc.id_permiso'
            )

            ->join(
                'tbl_objeto as o',
                'o.id_objeto',
                '=',
                'rpc.id_objeto'
            )

            ->where(
                'rpc.id_carrera',
                $idCarreraActual
            )

            ->whereRaw(
                "UPPER(TRIM(o.tipo_objeto)) = 'PANTALLA'"
            )

            ->select(
                'rpc.id_rol_permiso_carrera',
                'rpc.id_carrera',
                'rpc.id_rol',
                'rpc.id_permiso',
                'rpc.id_objeto',
                'rpc.estado_activo',
                'rpc.fecha_asignacion',

                DB::raw(
                    'UPPER(r.nombre_rol) AS nombre_rol'
                ),

                DB::raw(
                    'UPPER(p.nombre_permiso) AS nombre_permiso'
                ),

                DB::raw(
                    'UPPER(o.nombre_objeto) AS nombre_objeto'
                ),

                DB::raw(
                    'UPPER(o.tipo_objeto) AS tipo_objeto'
                )
            )

            ->orderByDesc(
                'rpc.id_rol_permiso_carrera'
            )

            ->get();


        return view('rol_seguridad_accesos', [
            'accesos' => collect($accesos),
            'roles' => $roles,
            'objetos' => $objetos,
            'permisos' => $permisos,

            'idCarreraActual' => $idCarreraActual,

            'esCoordinador' => true,
            'esSecretariaGeneral' => false,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ASIGNAR UNO O VARIOS PERMISOS
    |--------------------------------------------------------------------------
    */

    public function storeAcceso(Request $request)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDACIÓN
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'id_rol' =>
                'required|integer|in:2,4,5',

            'id_permisos' =>
                'required|array|min:1',

            'id_permisos.*' =>
                'required|integer|distinct|exists:tbl_permiso,id_permiso',

            'id_objeto' =>
                'required|integer|exists:tbl_objeto,id_objeto',
        ], [
            'id_rol.required' =>
                'Debes seleccionar un rol.',

            'id_rol.integer' =>
                'El rol seleccionado no es válido.',

            'id_rol.in' =>
                'El rol seleccionado no puede ser administrado desde una carrera.',


            'id_permisos.required' =>
                'Debes seleccionar al menos un permiso.',

            'id_permisos.array' =>
                'Los permisos seleccionados no son válidos.',

            'id_permisos.min' =>
                'Debes seleccionar al menos un permiso.',

            'id_permisos.*.required' =>
                'Uno de los permisos seleccionados no es válido.',

            'id_permisos.*.integer' =>
                'Uno de los permisos seleccionados no es válido.',

            'id_permisos.*.distinct' =>
                'No puedes seleccionar el mismo permiso más de una vez.',

            'id_permisos.*.exists' =>
                'Uno de los permisos seleccionados no existe.',


            'id_objeto.required' =>
                'Debes seleccionar un módulo.',

            'id_objeto.integer' =>
                'El módulo seleccionado no es válido.',

            'id_objeto.exists' =>
                'El módulo seleccionado no existe.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CARRERA DEL COORDINADOR
        |--------------------------------------------------------------------------
        */

        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' =>
                        'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR MÓDULO
        |--------------------------------------------------------------------------
        */

        $objeto = DB::table('tbl_objeto')
            ->where(
                'id_objeto',
                (int) $request->id_objeto
            )
            ->where(
                'estado_activo',
                1
            )
            ->first();


        if (!$objeto) {

            return back()
                ->withErrors([
                    'acceso' =>
                        'El módulo seleccionado no existe o está inactivo.'
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | SOLO PANTALLA
        |--------------------------------------------------------------------------
        */

        if (
            strtoupper(
                trim(
                    (string) $objeto->tipo_objeto
                )
            ) !== 'PANTALLA'
        ) {

            return back()
                ->withErrors([
                    'acceso' =>
                        'Solo se pueden administrar permisos sobre módulos tipo PANTALLA.'
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | ASIGNAR PERMISOS SELECCIONADOS
        |--------------------------------------------------------------------------
        */

        $permisosSeleccionados =
            collect($request->id_permisos)
                ->map(function ($idPermiso) {
                    return (int) $idPermiso;
                })
                ->unique()
                ->values();


        $asignados = 0;
        $reactivados = 0;
        $existentes = 0;
        $errores = [];


        foreach ($permisosSeleccionados as $idPermiso) {

            $res = DB::select(
                'CALL SP_ACCESO_CARRERA_SEGURIDAD_V2(?, ?, ?, ?, ?, ?, ?)',
                [
                    'CREAR',

                    null,

                    $idCarreraActual,

                    (int) $request->id_rol,

                    $idPermiso,

                    (int) $request->id_objeto,

                    Auth::id()
                ]
            );


            $row =
                $res[0]
                ?? null;


            $resultado =
                $row->resultado
                ?? 'ERROR';


            $mensaje =
                $row->mensaje
                ?? 'No se pudo asignar uno de los permisos.';


            if ($resultado === 'OK') {

                if (
                    stripos(
                        $mensaje,
                        'reactivado'
                    ) !== false
                ) {
                    $reactivados++;
                } else {
                    $asignados++;
                }

                continue;
            }


            if ($resultado === 'EXISTE') {

                $existentes++;

                continue;
            }


            $errores[] = $mensaje;
        }


        /*
        |--------------------------------------------------------------------------
        | ERRORES
        |--------------------------------------------------------------------------
        */

        if (!empty($errores)) {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' =>
                        implode(' ', array_unique($errores))
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MENSAJE FINAL
        |--------------------------------------------------------------------------
        */

        $partes = [];


        if ($asignados > 0) {

            $partes[] =
                $asignados === 1

                ? '1 permiso fue asignado correctamente.'

                : $asignados . ' permisos fueron asignados correctamente.';
        }


        if ($reactivados > 0) {

            $partes[] =
                $reactivados === 1

                ? '1 permiso fue reactivado.'

                : $reactivados . ' permisos fueron reactivados.';
        }


        if ($existentes > 0) {

            $partes[] =
                $existentes === 1

                ? '1 permiso ya estaba asignado.'

                : $existentes . ' permisos ya estaban asignados.';
        }


        if (empty($partes)) {

            $partes[] =
                'No se realizaron cambios en los permisos.';
        }


        return redirect()
            ->route('seguridad.accesos')
            ->with(
                'status',
                implode(' ', $partes)
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR PERMISO POR ROL
    |--------------------------------------------------------------------------
    */

    public function updateAcceso(Request $request, $id)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        $request->validate([
            'id_rol' =>
                'required|integer|in:2,4,5',

            'id_permiso' =>
                'required|integer|exists:tbl_permiso,id_permiso',

            'id_objeto' =>
                'required|integer|exists:tbl_objeto,id_objeto',
        ], [
            'id_rol.required' =>
                'Debes seleccionar un rol.',

            'id_rol.integer' =>
                'El rol seleccionado no es válido.',

            'id_rol.in' =>
                'El rol seleccionado no puede ser administrado desde una carrera.',

            'id_permiso.required' =>
                'Debes seleccionar un permiso.',

            'id_permiso.integer' =>
                'El permiso seleccionado no es válido.',

            'id_permiso.exists' =>
                'El permiso seleccionado no existe.',

            'id_objeto.required' =>
                'Debes seleccionar un módulo.',

            'id_objeto.integer' =>
                'El módulo seleccionado no es válido.',

            'id_objeto.exists' =>
                'El módulo seleccionado no existe.',
        ]);


        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' =>
                        'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        $objeto = DB::table('tbl_objeto')
            ->where(
                'id_objeto',
                (int) $request->id_objeto
            )
            ->where(
                'estado_activo',
                1
            )
            ->first();


        if (!$objeto) {

            return back()
                ->withErrors([
                    'acceso' =>
                        'El módulo seleccionado no existe o está inactivo.'
                ])
                ->withInput();
        }


        if (
            strtoupper(
                trim(
                    (string) $objeto->tipo_objeto
                )
            ) !== 'PANTALLA'
        ) {

            return back()
                ->withErrors([
                    'acceso' =>
                        'Solo se pueden administrar permisos sobre módulos tipo PANTALLA.'
                ])
                ->withInput();
        }


        $res = DB::select(
            'CALL SP_ACCESO_CARRERA_SEGURIDAD_V2(?, ?, ?, ?, ?, ?, ?)',
            [
                'ACTUALIZAR',

                (int) $id,

                $idCarreraActual,

                (int) $request->id_rol,

                (int) $request->id_permiso,

                (int) $request->id_objeto,

                Auth::id()
            ]
        );


        $row =
            $res[0]
            ?? null;


        $resultado =
            $row->resultado
            ?? 'ERROR';


        $mensaje =
            $row->mensaje
            ?? 'No se pudo actualizar el permiso.';


        if ($resultado === 'EXISTE') {

            return back()
                ->withErrors([
                    'acceso' => $mensaje
                ])
                ->withInput();
        }


        if ($resultado !== 'OK') {

            return back()
                ->withErrors([
                    'acceso' => $mensaje
                ])
                ->withInput();
        }


        return redirect()
            ->route('seguridad.accesos')
            ->with(
                'status',
                $mensaje
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESACTIVAR PERMISO
    |--------------------------------------------------------------------------
    */

    public function deleteAcceso($id)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' =>
                        'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        $res = DB::select(
            'CALL SP_ACCESO_CARRERA_SEGURIDAD_V2(?, ?, ?, ?, ?, ?, ?)',
            [
                'DESACTIVAR',

                (int) $id,

                $idCarreraActual,

                null,
                null,
                null,

                Auth::id()
            ]
        );


        $row =
            $res[0]
            ?? null;


        $resultado =
            $row->resultado
            ?? 'ERROR';


        $mensaje =
            $row->mensaje
            ?? 'No se pudo desactivar el permiso.';


        if ($resultado !== 'OK') {

            return back()
                ->withErrors([
                    'acceso' => $mensaje
                ]);
        }


        return redirect()
            ->route('seguridad.accesos')
            ->with(
                'status',
                $mensaje
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR PERMISO
    |--------------------------------------------------------------------------
    */

    public function updateEstadoAcceso(
        Request $request,
        $id
    ) {

        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR ESTADO
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'estado' => 'required|in:0,1',
        ], [
            'estado.required' =>
                'Debes indicar el estado del permiso.',

            'estado.in' =>
                'El estado seleccionado no es válido.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CARRERA DEL COORDINADOR
        |--------------------------------------------------------------------------
        */

        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();


        if (!$idCarreraActual) {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' =>
                        'No fue posible determinar la carrera del coordinador.'
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | OBTENER ASIGNACIÓN
        |--------------------------------------------------------------------------
        */

        $acceso = DB::table(
            'tbl_rol_permiso_carrera_v2'
        )
            ->where(
                'id_rol_permiso_carrera',
                (int) $id
            )
            ->where(
                'id_carrera',
                (int) $idCarreraActual
            )
            ->first();


        if (!$acceso) {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' =>
                        'El permiso seleccionado no existe o no pertenece a tu carrera.'
                ]);
        }


        $nuevoEstado =
            (int) $request->estado;


        /*
        |--------------------------------------------------------------------------
        | SIN CAMBIOS
        |--------------------------------------------------------------------------
        */

        if (
            (int) $acceso->estado_activo
            === $nuevoEstado
        ) {

            return redirect()
                ->route('seguridad.accesos')
                ->with(
                    'status',
                    $nuevoEstado === 1
                        ? 'El permiso ya se encuentra activo.'
                        : 'El permiso ya se encuentra desactivado.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | DESACTIVAR
        |--------------------------------------------------------------------------
        */

        if ($nuevoEstado === 0) {

            $res = DB::select(
                'CALL SP_ACCESO_CARRERA_SEGURIDAD_V2(?, ?, ?, ?, ?, ?, ?)',
                [
                    'DESACTIVAR',

                    (int) $id,

                    $idCarreraActual,

                    null,
                    null,
                    null,

                    Auth::id()
                ]
            );


            $row =
                $res[0]
                ?? null;


            $resultado =
                $row->resultado
                ?? 'ERROR';


            $mensaje =
                $row->mensaje
                ?? 'No se pudo desactivar el permiso.';


            if ($resultado !== 'OK') {

                return redirect()
                    ->route('seguridad.accesos')
                    ->withErrors([
                        'acceso' => $mensaje
                    ]);
            }


            return redirect()
                ->route('seguridad.accesos')
                ->with(
                    'status',
                    $mensaje
                );
        }


        /*
        |--------------------------------------------------------------------------
        | REACTIVAR
        |--------------------------------------------------------------------------
        |
        | La acción CREAR del procedimiento ya contempla reactivar una asignación
        | existente que se encuentre inactiva.
        |
        */

        $res = DB::select(
            'CALL SP_ACCESO_CARRERA_SEGURIDAD_V2(?, ?, ?, ?, ?, ?, ?)',
            [
                'CREAR',

                null,

                $idCarreraActual,

                (int) $acceso->id_rol,

                (int) $acceso->id_permiso,

                (int) $acceso->id_objeto,

                Auth::id()
            ]
        );


        $row =
            $res[0]
            ?? null;


        $resultado =
            $row->resultado
            ?? 'ERROR';


        $mensaje =
            $row->mensaje
            ?? 'No se pudo activar el permiso.';


        if ($resultado !== 'OK') {

            return redirect()
                ->route('seguridad.accesos')
                ->withErrors([
                    'acceso' => $mensaje
                ]);
        }


        return redirect()
            ->route('seguridad.accesos')
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
    | VALIDAR COORDINADOR
    |--------------------------------------------------------------------------
    */

    private function esCoordinador(): bool
    {
        return $this->rolActual()
            === 'coordinador';
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECCIÓN SIN PERMISO
    |--------------------------------------------------------------------------
    */

    private function redirigirSinPermiso()
    {
        $ruta =
            session('login_tipo') === 'estudiante'

            ? route('dashboard')

            : route('empleado.dashboard');


        return redirect($ruta)
            ->withErrors([
                'seguridad' =>
                    'No tienes permiso para acceder al módulo de Seguridad.'
            ]);
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


        $row =
            $res[0]
            ?? null;


        if (
            !$row
            || ($row->resultado ?? 'ERROR') !== 'OK'
            || empty($row->id_carrera)
        ) {

            return null;
        }


        return (int) $row->id_carrera;
    }
}
