<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolSeguridadController extends Controller
{
    public function index()
    {
        if (!$this->puedeEntrarSeguridad()) {
            return $this->redirigirSinPermiso();
        }

        $rol = $this->rolActual();

        if ($this->esSecretariaGeneral()) {
            $modulos = [
                [
                    'titulo' => 'Gestión de Roles',
                    'descripcion' => 'Crear, editar, activar o desactivar roles del sistema.',
                    'ruta' => route('seguridad.roles'),
                    'icono' => 'fas fa-user-tag'
                ],
                [
                    'titulo' => 'Gestión de Usuarios',
                    'descripcion' => 'Administrar usuarios del sistema, filtrarlos por carrera y revisar su estado.',
                    'ruta' => route('seguridad.usuarios'),
                    'icono' => 'fas fa-users'
                ],
                [
                    'titulo' => 'Gestión de Objetos',
                    'descripcion' => 'Administrar módulos, pantallas y submódulos de seguridad.',
                    'ruta' => route('seguridad.objetos'),
                    'icono' => 'fas fa-cubes'
                ],
                [
                    'titulo' => 'Gestión de Accesos',
                    'descripcion' => 'Administrar accesos y permisos por rol y objeto.',
                    'ruta' => route('seguridad.accesos'),
                    'icono' => 'fas fa-user-shield'
                ],
            ];
        } else {
            $modulos = [
                [
                    'titulo' => 'Gestión de Usuarios',
                    'descripcion' => 'Consulta y administración de usuarios pertenecientes únicamente a tu carrera.',
                    'ruta' => route('seguridad.usuarios'),
                    'icono' => 'fas fa-users'
                ],
            ];
        }

        return view('rol_seguridad_index', [
            'modulos' => $modulos,
            'rolActual' => $rol,
            'esSecretariaGeneral' => $this->esSecretariaGeneral(),
            'esCoordinador' => $this->esCoordinador(),
        ]);
    }

    public function usuarios(Request $request)
    {
        if (!$this->puedeEntrarSeguridad()) {
            return $this->redirigirSinPermiso();
        }

        $rolActual = $this->rolActual();
        $esSecretariaGeneral = $this->esSecretariaGeneral();
        $esCoordinador = $this->esCoordinador();

        $idCarreraActual = null;

        if (!$esSecretariaGeneral) {
            $personaId = $this->obtenerIdPersonaAutenticada();

            $carreraActualRes = DB::select('CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)', [
                $personaId
            ]);

            $rowCarrera = $carreraActualRes[0] ?? null;

            if (!$rowCarrera || ($rowCarrera->resultado ?? 'ERROR') !== 'OK' || empty($rowCarrera->id_carrera)) {
                return redirect()
                    ->route('seguridad.index')
                    ->withErrors([
                        'usuario' => 'No fue posible determinar la carrera asociada al usuario autenticado.'
                    ]);
            }

            $idCarreraActual = (int) $rowCarrera->id_carrera;
        }

        $carreras = DB::select('CALL SEL_CARRERAS_SEGURIDAD()');

        if ($esSecretariaGeneral) {
            $roles = DB::select('CALL SEL_ROLES_SEGURIDAD_FILTRO(?, ?)', [
                null,
                null
            ]);
        } else {
            $roles = collect([
                (object) ['valor' => 'coordinador', 'etiqueta' => 'COORDINADOR'],
                (object) ['valor' => 'secretaria', 'etiqueta' => 'SECRETARÍA'],
                (object) ['valor' => 'estudiante', 'etiqueta' => 'ESTUDIANTE'],
            ]);
        }

        $filtroBusqueda = trim((string) $request->get('buscar', ''));
        $filtroTipo = trim((string) $request->get('tipo_usuario', ''));
        $filtroEstado = $request->get('estado_cuenta', '');
        $filtroCarrera = $request->filled('id_carrera') ? (int) $request->get('id_carrera') : null;

        $filtroRolId = $esSecretariaGeneral
            ? ($request->filled('id_rol') ? (int) $request->get('id_rol') : null)
            : null;

        $filtroRolTexto = $esSecretariaGeneral
            ? null
            : trim((string) $request->get('id_rol', ''));

        $usuarios = DB::select('CALL SEL_USUARIOS_SEGURIDAD_FILTRO(?, ?, ?, ?, ?, ?, ?, ?)', [
            $rolActual,
            $idCarreraActual,
            $filtroBusqueda !== '' ? $filtroBusqueda : null,
            $filtroTipo !== '' ? $filtroTipo : null,
            $filtroRolId,
            $filtroRolTexto !== '' ? $filtroRolTexto : null,
            ($filtroEstado !== '' && in_array($filtroEstado, ['0', '1'], true)) ? (int) $filtroEstado : null,
            $esSecretariaGeneral ? $filtroCarrera : null,
        ]);

        // Paginación manual simple
        $usuariosCollection = collect($usuarios);
        $perPage = 10;
        $currentPage = (int) request()->get('page', 1);
        $pagedData = $usuariosCollection->forPage($currentPage, $perPage)->values();

        $usuariosPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $usuariosCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('rol_seguridad_usuarios', [
            'usuarios' => $usuariosPaginator,
            'roles' => collect($roles),
            'carreras' => collect($carreras),
            'rolActual' => $rolActual,
            'esSecretariaGeneral' => $esSecretariaGeneral,
            'esCoordinador' => $esCoordinador,
            'idCarreraActual' => $idCarreraActual,
            'filtros' => [
                'buscar' => $filtroBusqueda,
                'tipo_usuario' => $filtroTipo,
                'id_rol' => $esSecretariaGeneral ? $filtroRolId : $filtroRolTexto,
                'estado_cuenta' => $filtroEstado,
                'id_carrera' => $esSecretariaGeneral ? $filtroCarrera : $idCarreraActual,
            ],
        ]);
    }

    public function updateEstadoUsuario(Request $request, $id)
    {
        if (!$this->puedeEntrarSeguridad()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'estado_cuenta' => 'required|in:0,1',
        ]);

        if (!$this->esSecretariaGeneral()) {
            $personaId = $this->obtenerIdPersonaAutenticada();

            $carreraActualRes = DB::select('CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)', [
                $personaId
            ]);

            $rowCarrera = $carreraActualRes[0] ?? null;
            $idCarreraActual = ($rowCarrera && ($rowCarrera->resultado ?? 'ERROR') === 'OK')
                ? (int) $rowCarrera->id_carrera
                : null;

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
                        'usuario' => 'No tienes permiso para modificar usuarios fuera de tu ámbito autorizado.'
                    ]);
            }
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
        if (!$this->esSecretariaGeneral()) {
            return $this->redirigirSinPermiso();
        }

        $objetos = DB::select('CALL SEL_MODULOS_SEGURIDAD()');

        return view('rol_seguridad_objetos', compact('objetos'));
    }

    public function storeObjeto(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'nombre_objeto' => 'required|string|max:100',
            'tipo_objeto' => 'required|string|max:50',
        ]);

        $res = DB::select('CALL INS_MODULOS_SEGURIDAD(?, ?, ?)', [
            $request->nombre_objeto,
            $request->tipo_objeto,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo crear el módulo/objeto.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['objeto' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.objetos')
            ->with('status', $mensaje);
    }

    public function updateObjeto(Request $request, $id)
    {
        if (!$this->esSecretariaGeneral()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'nombre_objeto' => 'required|string|max:100',
            'tipo_objeto' => 'required|string|max:50',
        ]);

        $res = DB::select('CALL UPD_MODULOS_SEGURIDAD(?, ?, ?, ?)', [
            (int) $id,
            $request->nombre_objeto,
            $request->tipo_objeto,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo actualizar el módulo/objeto.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['objeto' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.objetos')
            ->with('status', $mensaje);
    }

    public function accesos()
    {
        if (!$this->esSecretariaGeneral()) {
            return $this->redirigirSinPermiso();
        }

        $accesos = DB::select('CALL SEL_ACCESOS_SEGURIDAD()');
        $roles = DB::select('CALL SEL_ROLES_SEGURIDAD_FILTRO(?, ?)', [null, 1]);
        $permisos = DB::select('CALL SEL_PERMISOS_SEGURIDAD()');
        $objetos = DB::select('CALL SEL_MODULOS_SEGURIDAD()');

        return view('rol_seguridad_accesos', [
            'accesos' => $accesos,
            'roles' => collect($roles),
            'permisos' => collect($permisos),
            'objetos' => collect($objetos),
        ]);
    }

    public function storeAcceso(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return $this->redirigirSinPermiso();
        }

        $request->validate([
            'id_rol' => 'required|integer',
            'id_permiso' => 'required|integer',
            'id_objeto' => 'required|integer',
        ]);

        $res = DB::select('CALL INS_ACCESOS_SEGURIDAD(?, ?, ?, ?)', [
            (int) $request->id_rol,
            (int) $request->id_permiso,
            (int) $request->id_objeto,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo asignar el acceso.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['acceso' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.accesos')
            ->with('status', $mensaje);
    }

    public function deleteAcceso($id)
    {
        if (!$this->esSecretariaGeneral()) {
            return $this->redirigirSinPermiso();
        }

        $res = DB::select('CALL SOFT_DEL_ACCESOS_SEGURIDAD(?, ?)', [
            (int) $id,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo desactivar el acceso.';

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

    private function esSecretariaGeneral(): bool
    {
        return $this->rolActual() === 'secretaria_general';
    }

    private function esCoordinador(): bool
    {
        return $this->rolActual() === 'coordinador';
    }

    private function puedeEntrarSeguridad(): bool
    {
        return in_array($this->rolActual(), ['coordinador', 'secretaria_general'], true);
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
}
