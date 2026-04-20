<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class RolSeguridadController extends Controller
{
    public function index()
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $modulos = [
            [
                'titulo' => 'Gestión de Roles',
                'descripcion' => 'Administración de roles correspondientes únicamente a tu carrera.',
                'ruta' => route('seguridad.roles'),
                'icono' => 'fas fa-user-tag'
            ],
            [
                'titulo' => 'Gestión de Usuarios',
                'descripcion' => 'Administración de usuarios pertenecientes únicamente a tu carrera.',
                'ruta' => route('seguridad.usuarios'),
                'icono' => 'fas fa-users'
            ],
            [
                'titulo' => 'Gestión de Objetos',
                'descripcion' => 'Administración de objetos o módulos correspondientes únicamente a tu carrera.',
                'ruta' => route('seguridad.objetos'),
                'icono' => 'fas fa-cubes'
            ],
            [
                'titulo' => 'Gestión de Accesos',
                'descripcion' => 'Administración de accesos y permisos correspondientes únicamente a tu carrera.',
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

        $carrerasRes = DB::select('CALL SEL_CARRERAS_SEGURIDAD()');

        $rolesRes = DB::select('CALL SP_ROL_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null,
            null
        ]);

        $roles = collect($rolesRes)->map(function ($rol) {
            return (object) [
                'id_rol_carrera' => $rol->id_rol_carrera ?? $rol->id_rol ?? null,
                'id_rol'         => $rol->id_rol ?? $rol->id_rol_carrera ?? null,
                'nombre_rol'     => $rol->nombre_rol ?? '',
                'descripcion'    => $rol->descripcion ?? '',
                'estado_activo'  => $rol->estado_activo ?? 0,
            ];
        });

        $filtroBusqueda = trim((string) $request->get('buscar', ''));
        $filtroTipo = trim((string) $request->get('tipo_usuario', ''));
        $filtroEstado = $request->get('estado_cuenta', '');
        $filtroRolTexto = trim((string) $request->get('id_rol', ''));

        $usuariosRes = DB::select('CALL SEL_USUARIOS_SEGURIDAD_FILTRO(?, ?, ?, ?, ?, ?)', [
            'COORDINADOR',
            $idCarreraActual,
            $filtroBusqueda !== '' ? $filtroBusqueda : null,
            $filtroTipo !== '' ? $filtroTipo : null,
            $filtroRolTexto !== '' ? $filtroRolTexto : null,
            ($filtroEstado !== '' && in_array($filtroEstado, ['0', '1'], true)) ? (int) $filtroEstado : null
        ]);

        $usuariosCollection = collect($usuariosRes);
        $perPage = 10;
        $currentPage = (int) $request->get('page', 1);
        $pagedData = $usuariosCollection->forPage($currentPage, $perPage)->values();

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
            'carreras' => collect($carrerasRes),
            'rolActual' => $this->rolActual(),
            'esSecretariaGeneral' => false,
            'esCoordinador' => true,
            'idCarreraActual' => $idCarreraActual,
            'filtros' => [
                'buscar' => $filtroBusqueda,
                'tipo_usuario' => $filtroTipo,
                'id_rol' => $filtroRolTexto,
                'estado_cuenta' => $filtroEstado,
                'id_carrera' => $idCarreraActual,
            ],
        ]);
    }

    public function updateEstadoUsuario(Request $request, $id)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'estado_cuenta' => 'required|in:0,1',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $usuarioCarreraRes = DB::select('CALL SEL_USUARIO_CARRERA_SEGURIDAD(?)', [
            (int) $id
        ]);

        $rowUsuarioCarrera = $usuarioCarreraRes[0] ?? null;

        if (
            !$rowUsuarioCarrera
            || ($rowUsuarioCarrera->resultado ?? 'ERROR') !== 'OK'
            || (int) $rowUsuarioCarrera->id_carrera !== (int) $idCarreraActual
        ) {
            return redirect()
                ->route('seguridad.usuarios')
                ->withErrors([
                    'usuario' => 'No tienes permiso para modificar usuarios fuera de tu carrera.'
                ]);
        }

        $res = DB::select('CALL UPD_ESTADO_USUARIO_SEGURIDAD(?, ?, ?)', [
            (int) $id,
            (int) $request->estado_cuenta,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo actualizar el estado del usuario.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['usuario' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.usuarios')
            ->with('status', $mensaje);
    }

    public function objetos()
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $objetos = DB::select('CALL SP_OBJETO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null
        ]);

        return view('rol_seguridad_objetos', [
            'objetos' => collect($objetos),
            'esCoordinador' => true,
            'esSecretariaGeneral' => false,
        ]);
    }

    public function storeObjeto(Request $request)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'nombre_objeto' => 'required|string|max:100',
            'tipo_objeto' => 'required|string|max:50',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $res = DB::select('CALL SP_OBJETO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'CREAR',
            null,
            $idCarreraActual,
            $request->nombre_objeto,
            $request->tipo_objeto,
            1,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo crear el objeto por carrera.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['objeto' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.objetos')
            ->with('status', $mensaje);
    }

    public function updateObjeto(Request $request, $id)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'nombre_objeto' => 'required|string|max:100',
            'tipo_objeto' => 'required|string|max:50',
            'estado_activo' => 'required|in:0,1',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $res = DB::select('CALL SP_OBJETO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'ACTUALIZAR',
            (int) $id,
            $idCarreraActual,
            $request->nombre_objeto,
            $request->tipo_objeto,
            (int) $request->estado_activo,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo actualizar el objeto por carrera.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['objeto' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.objetos')
            ->with('status', $mensaje);
    }

    public function accesos()
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $accesos = DB::select('CALL SP_ACCESO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null
        ]);

        $rolesRaw = DB::select('CALL SP_ROL_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null,
            null
        ]);

        $objetosRaw = DB::select('CALL SP_OBJETO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null
        ]);

        $permisos = DB::select('CALL SEL_PERMISOS_SEGURIDAD()');

        $roles = collect($rolesRaw)->map(function ($rol) {
            return (object) [
                'id_rol_carrera' => $rol->id_rol_carrera ?? $rol->id_rol ?? null,
                'id_rol'         => $rol->id_rol ?? $rol->id_rol_carrera ?? null,
                'nombre_rol'     => $rol->nombre_rol ?? '',
                'descripcion'    => $rol->descripcion ?? '',
                'estado_activo'  => $rol->estado_activo ?? 0,
            ];
        });

        $objetos = collect($objetosRaw)->map(function ($objeto) {
            return (object) [
                'id_objeto_carrera' => $objeto->id_objeto_carrera ?? $objeto->id_objeto ?? null,
                'id_objeto'         => $objeto->id_objeto ?? $objeto->id_objeto_carrera ?? null,
                'nombre_objeto'     => $objeto->nombre_objeto ?? '',
                'tipo_objeto'       => $objeto->tipo_objeto ?? '',
                'estado_activo'     => $objeto->estado_activo ?? 0,
            ];
        });

        return view('rol_seguridad_accesos', [
            'accesos' => collect($accesos),
            'roles' => $roles,
            'objetos' => $objetos,
            'permisos' => collect($permisos),
            'esCoordinador' => true,
            'esSecretariaGeneral' => false,
        ]);
    }

    public function storeAcceso(Request $request)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'id_rol_carrera' => 'required|integer',
            'id_objeto_carrera' => 'required|integer',
            'id_permiso' => 'required|integer',
        ], [
            'id_rol_carrera.required' => 'Debes seleccionar un rol.',
            'id_objeto_carrera.required' => 'Debes seleccionar un objeto.',
            'id_permiso.required' => 'Debes seleccionar un permiso.',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $res = DB::select('CALL SP_ACCESO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'CREAR',
            null,
            $idCarreraActual,
            (int) $request->id_rol_carrera,
            (int) $request->id_permiso,
            (int) $request->id_objeto_carrera,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo crear el acceso por carrera.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['acceso' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.accesos')
            ->with('status', $mensaje);
    }

    public function deleteAcceso($id)
    {
        if (!$this->esCoordinador()) {
            return $this->redirigirSinPermiso();
        }

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $res = DB::select('CALL SP_ACCESO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'DESACTIVAR',
            (int) $id,
            $idCarreraActual,
            null,
            null,
            null,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo desactivar el acceso por carrera.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['acceso' => $mensaje]);
        }

        return redirect()->route('seguridad.accesos')
            ->with('status', $mensaje);
    }

    private function rolActual(): string
    {
        return strtolower((string) session('rol_texto', 'sin_rol'));
    }

    private function esCoordinador(): bool
    {
        return $this->rolActual() === 'coordinador';
    }

    private function redirigirSinPermiso()
    {
        $ruta = session('login_tipo') === 'estudiante'
            ? route('dashboard')
            : route('empleado.dashboard');

        return redirect($ruta)->withErrors([
            'seguridad' => 'No tienes permiso para acceder al módulo de Seguridad.'
        ]);
    }

    private function obtenerIdPersonaAutenticada(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return isset($user->id_persona) ? (int) $user->id_persona : null;
    }

    private function obtenerIdCarreraEmpleadoActual(): ?int
    {
        $personaId = $this->obtenerIdPersonaAutenticada();

        if (!$personaId) {
            return null;
        }

        $res = DB::select('CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)', [
            $personaId
        ]);

        $row = $res[0] ?? null;

        if (!$row || ($row->resultado ?? 'ERROR') !== 'OK' || empty($row->id_carrera)) {
            return null;
        }

        return (int) $row->id_carrera;
    }
}