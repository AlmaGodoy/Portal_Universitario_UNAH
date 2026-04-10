<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function panelRoles(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'Solo Secretaría General puede administrar roles.'
                ]);
        }

        $buscar = trim((string) $request->get('buscar', ''));
        $estado = $request->get('estado_activo', '');

        $roles = DB::select('CALL SEL_ROLES_SEGURIDAD_FILTRO(?, ?)', [
            $buscar !== '' ? $buscar : null,
            ($estado !== '' && in_array($estado, ['0', '1'], true)) ? (int) $estado : null
        ]);

        $permisos = DB::select('CALL SEL_PERMISOS_SEGURIDAD()');
        $objetos = DB::select('CALL SEL_MODULOS_SEGURIDAD()');
        $rolPermisos = DB::select('CALL SEL_ACCESOS_SEGURIDAD()');

        return view('rol_seguridad_roles', [
            'roles' => collect($roles),
            'permisos' => collect($permisos),
            'objetos' => collect($objetos),
            'rolPermisos' => collect($rolPermisos),
            'filtros' => [
                'buscar' => $buscar,
                'estado_activo' => $estado,
            ],
        ]);
    }

    public function storeRol(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'Solo Secretaría General puede crear roles.'
                ]);
        }

        $request->validate([
            'nombre_rol'    => 'required|string|max:100',
            'descripcion'   => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        $res = DB::select('CALL INS_ROL_SEGURIDAD(?, ?, ?, ?)', [
            $request->nombre_rol,
            $request->descripcion,
            (int) $request->estado_activo,
            auth()->id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo crear el rol.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['rol' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.roles')
            ->with('status', $mensaje);
    }

    public function updateRol(Request $request, $id)
    {
        if (!$this->esSecretariaGeneral()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'rol' => 'Solo Secretaría General puede actualizar roles.'
                ]);
        }

        $request->validate([
            'nombre_rol'    => 'required|string|max:100',
            'descripcion'   => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        $res = DB::select('CALL UPD_ROL_SEGURIDAD(?, ?, ?, ?, ?)', [
            (int) $id,
            $request->nombre_rol,
            $request->descripcion,
            (int) $request->estado_activo,
            auth()->id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo actualizar el rol.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['rol' => $mensaje])->withInput();
        }

        return redirect()->route('seguridad.roles')
            ->with('status', $mensaje);
    }

    public function asignarPermisosObjeto(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'permiso' => 'Solo Secretaría General puede asignar permisos a los roles.'
                ]);
        }

        $request->validate([
            'id_rol'     => 'required|integer',
            'id_objeto'  => 'required|integer',
            'permisos'   => 'required|array|min:1',
            'permisos.*' => 'integer',
        ], [
            'id_rol.required'    => 'Debes seleccionar un rol.',
            'id_objeto.required' => 'Debes seleccionar una pantalla.',
            'permisos.required'  => 'Debes seleccionar al menos un acceso.',
        ]);

        $errores = [];
        $creados = 0;
        $existentes = 0;

        foreach ($request->permisos as $idPermiso) {
            $res = DB::select('CALL INS_PERMISO_ROL_OBJETO_SEGURIDAD(?, ?, ?, ?)', [
                (int) $request->id_rol,
                (int) $idPermiso,
                (int) $request->id_objeto,
                auth()->id()
            ]);

            $row = $res[0] ?? null;
            $resultado = $row->resultado ?? 'ERROR';
            $mensaje = $row->mensaje ?? 'No se pudo asignar el permiso.';

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

        $mensajeFinal = 'Permisos procesados correctamente.';
        if ($creados > 0 || $existentes > 0) {
            $mensajeFinal = "Permisos procesados correctamente. Nuevos: {$creados}. Ya existentes: {$existentes}.";
        }

        return redirect()->route('seguridad.roles')
            ->with('status', $mensajeFinal);
    }

    public function deleteAsignacion($id)
    {
        if (!$this->esSecretariaGeneral()) {
            return redirect()
                ->route('seguridad.index')
                ->withErrors([
                    'permiso' => 'Solo Secretaría General puede desactivar asignaciones.'
                ]);
        }

        $res = DB::select('CALL SOFT_DEL_PERMISO_ROL_OBJETO_SEGURIDAD(?, ?)', [
            (int) $id,
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

    private function esSecretariaGeneral(): bool
    {
        return strtolower((string) session('rol_texto', 'sin_rol')) === 'secretaria_general';
    }
}
