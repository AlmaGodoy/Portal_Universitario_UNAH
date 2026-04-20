<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function panelRoles(Request $request)
    {
        if (!$this->esCoordinador()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'Solo el coordinador puede administrar roles.'
                ]);
        }

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }

        $buscar = trim((string) $request->get('buscar', ''));
        $estado = $request->get('estado_activo', '');

        $rolesRaw = DB::select('CALL SP_ROL_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            ($estado !== '' && in_array($estado, ['0', '1'], true)) ? (int) $estado : null,
            $buscar !== '' ? $buscar : null,
            null
        ]);

        $roles = collect($rolesRaw)->map(function ($rol) {
            return (object) [
                'id_rol_carrera' => $rol->id_rol_carrera ?? $rol->id_rol ?? null,
                'id_rol'         => $rol->id_rol ?? $rol->id_rol_carrera ?? null,
                'nombre_rol'     => $rol->nombre_rol ?? '',
                'descripcion'    => $rol->descripcion ?? '',
                'estado_activo'  => $rol->estado_activo ?? 0,
            ];
        });

        $permisos = DB::select('CALL SEL_PERMISOS_SEGURIDAD()');

        $objetos = DB::select('CALL SP_OBJETO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null
        ]);

        $rolPermisos = DB::select('CALL SP_ACCESO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'LISTAR',
            null,
            $idCarreraActual,
            null,
            null,
            null,
            null
        ]);

        return view('rol_seguridad_roles', [
            'roles' => $roles,
            'permisos' => collect($permisos),
            'objetos' => collect($objetos),
            'rolPermisos' => collect($rolPermisos),
            'esCoordinador' => true,
            'esSecretariaGeneral' => false,
            'filtros' => [
                'buscar' => $buscar,
                'estado_activo' => $estado,
            ],
        ]);
    }

    public function storeRol(Request $request)
    {
        if (!$this->esCoordinador()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'Solo el coordinador puede crear roles.'
                ]);
        }

        $request->validate([
            'nombre_rol' => 'required|string|max:100',
            'descripcion' => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }

        $res = DB::select('CALL SP_ROL_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?, ?)', [
            'CREAR',
            null,
            $idCarreraActual,
            $request->nombre_rol,
            $request->descripcion,
            (int) $request->estado_activo,
            null,
            auth()->id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo crear el rol por carrera.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['rol' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.roles')
            ->with('status', $mensaje);
    }

    public function updateRol(Request $request, $id)
    {
        if (!$this->esCoordinador()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'Solo el coordinador puede actualizar roles.'
                ]);
        }

        $request->validate([
            'nombre_rol' => 'required|string|max:100',
            'descripcion' => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }

        $res = DB::select('CALL SP_ROL_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?, ?)', [
            'ACTUALIZAR',
            (int) $id,
            $idCarreraActual,
            $request->nombre_rol,
            $request->descripcion,
            (int) $request->estado_activo,
            null,
            auth()->id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo actualizar el rol por carrera.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['rol' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.roles')
            ->with('status', $mensaje);
    }

    public function asignarPermisosObjeto(Request $request)
    {
        if (!$this->esCoordinador()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'permiso' => 'Solo el coordinador puede asignar permisos.'
                ]);
        }

        $request->validate([
            'id_rol_carrera' => 'required|integer',
            'id_objeto_carrera' => 'required|integer',
            'permisos' => 'required|array|min:1',
            'permisos.*' => 'integer',
        ], [
            'id_rol_carrera.required' => 'Debes seleccionar un rol.',
            'id_objeto_carrera.required' => 'Debes seleccionar un objeto.',
            'permisos.required' => 'Debes seleccionar al menos un acceso.',
        ]);

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'permiso' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }

        $errores = [];
        $creados = 0;
        $existentes = 0;

        foreach ($request->permisos as $idPermiso) {
            $res = DB::select('CALL SP_ACCESO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
                'CREAR',
                null,
                $idCarreraActual,
                (int) $request->id_rol_carrera,
                (int) $idPermiso,
                (int) $request->id_objeto_carrera,
                auth()->id()
            ]);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje = $row->mensaje ?? 'No se pudo asignar el acceso.';

            if ($resultado === 'OK') {
                $creados++;
            } elseif ($resultado === 'EXISTE') {
                $existentes++;
            } else {
                $errores[] = $mensaje;
            }
        }

        if (!empty($errores)) {
            return back()->withErrors([
                'permiso' => implode(' | ', $errores)
            ])->withInput();
        }

        $mensajeFinal = "Permisos procesados correctamente. Nuevos: {$creados}. Ya existentes: {$existentes}.";

        return redirect()->route('seguridad.roles')
            ->with('status', $mensajeFinal);
    }

    public function deleteAsignacion($id)
    {
        if (!$this->esCoordinador()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'permiso' => 'Solo el coordinador puede desactivar asignaciones.'
                ]);
        }

        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        if (!$idCarreraActual) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'permiso' => 'No fue posible determinar la carrera asociada al coordinador autenticado.'
                ]);
        }

        $res = DB::select('CALL SP_ACCESO_CARRERA_SEGURIDAD(?, ?, ?, ?, ?, ?, ?)', [
            'DESACTIVAR',
            (int) $id,
            $idCarreraActual,
            null,
            null,
            null,
            auth()->id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo desactivar la asignación.';

        if ($resultado !== 'OK') {
            return back()->withErrors([
                'permiso' => $mensaje
            ]);
        }

        return redirect()->route('seguridad.roles')
            ->with('status', $mensaje);
    }

    private function esCoordinador(): bool
    {
        return strtolower((string) session('rol_texto', 'sin_rol')) === 'coordinador';
    }

    private function obtenerIdCarreraEmpleadoActual(): ?int
    {
        $user = auth()->user();

        if (!$user || !isset($user->id_persona)) {
            return null;
        }

        $res = DB::select('CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)', [
            (int) $user->id_persona
        ]);

        $row = $res[0] ?? null;

        if (!$row || ($row->resultado ?? 'ERROR') !== 'OK' || empty($row->id_carrera)) {
            return null;
        }

        return (int) $row->id_carrera;
    }
}