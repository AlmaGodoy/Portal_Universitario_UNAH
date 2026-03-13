<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function panelRoles()
    {
        $roles = DB::table('tbl_rol')
            ->orderBy('id_rol')
            ->get();

        $permisos = DB::table('tbl_permiso')
            ->orderBy('id_permiso')
            ->get();

        $objetos = DB::table('tbl_objeto')
            ->orderBy('id_objeto')
            ->get();

        $rolPermisos = DB::table('tbl_rol_permiso as rp')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'rp.id_rol')
            ->join('tbl_permiso as p', 'p.id_permiso', '=', 'rp.id_permiso')
            ->join('tbl_objeto as o', 'o.id_objeto', '=', 'rp.id_objeto')
            ->select(
                'rp.id_rol_permiso',
                'rp.id_rol',
                'rp.id_permiso',
                'rp.id_objeto',
                'rp.fecha_asignacion',
                'r.nombre_rol',
                'p.nombre_permiso',
                'o.nombre_objeto',
                'o.tipo_objeto'
            )
            ->orderBy('r.nombre_rol')
            ->orderBy('o.nombre_objeto')
            ->orderBy('p.nombre_permiso')
            ->get();

        return view('rol_seguridad_roles', compact(
            'roles',
            'permisos',
            'objetos',
            'rolPermisos'
        ));
    }

    public function storeRol(Request $request)
    {
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
        $request->validate([
            'nombre_rol'    => 'required|string|max:100',
            'descripcion'   => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        $res = DB::select('CALL UPD_ROL_SEGURIDAD(?, ?, ?, ?, ?)', [
            $id,
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

    private function registrarBitacora(?int $idUsuario, string $accion, string $descripcion, ?int $idObjeto = null): void
    {
        try {
            DB::table('tbl_bitacora')->insert([
                'id_usuario'   => $idUsuario,
                'id_objeto'    => $idObjeto,
                'accion'       => $accion,
                'fecha_accion' => now(),
                'descripcion'  => $descripcion,
            ]);
        } catch (\Throwable $e) {
        }
    }

    public function asignarPermisosObjeto(Request $request)
    {
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

        $rol = DB::table('tbl_rol')->where('id_rol', $request->id_rol)->first();
        $objeto = DB::table('tbl_objeto')->where('id_objeto', $request->id_objeto)->first();

        foreach ($request->permisos as $idPermiso) {
            $existe = DB::table('tbl_rol_permiso')
                ->where('id_rol', $request->id_rol)
                ->where('id_permiso', $idPermiso)
                ->where('id_objeto', $request->id_objeto)
                ->exists();

            if (!$existe) {
                DB::table('tbl_rol_permiso')->insert([
                    'id_rol'           => $request->id_rol,
                    'id_permiso'       => $idPermiso,
                    'id_objeto'        => $request->id_objeto,
                    'fecha_asignacion' => now(),
                ]);
            }
        }

        $this->registrarBitacora(
            auth()->id(),
            'asignar_permiso_rol',
            'Se asignaron permisos al rol ' . strtoupper($rol->nombre_rol ?? '') .
            ' sobre el objeto ' . strtoupper($objeto->nombre_objeto ?? ''),
            $request->id_objeto
        );

        return redirect()->route('seguridad.roles')
            ->with('status', 'Permisos asignados correctamente.');
    }

    public function deleteAsignacion($id)
    {
        $asignacion = DB::table('tbl_rol_permiso as rp')
            ->join('tbl_rol as r', 'r.id_rol', '=', 'rp.id_rol')
            ->join('tbl_permiso as p', 'p.id_permiso', '=', 'rp.id_permiso')
            ->join('tbl_objeto as o', 'o.id_objeto', '=', 'rp.id_objeto')
            ->select(
                'rp.id_rol_permiso',
                'rp.id_objeto',
                'r.nombre_rol',
                'p.nombre_permiso',
                'o.nombre_objeto'
            )
            ->where('rp.id_rol_permiso', $id)
            ->first();

        DB::table('tbl_rol_permiso')
            ->where('id_rol_permiso', $id)
            ->delete();

        if ($asignacion) {
            $this->registrarBitacora(
                auth()->id(),
                'eliminar_permiso_rol',
                'Se eliminó el permiso ' . strtoupper($asignacion->nombre_permiso) .
                ' del rol ' . strtoupper($asignacion->nombre_rol) .
                ' sobre el objeto ' . strtoupper($asignacion->nombre_objeto),
                $asignacion->id_objeto
            );
        }

        return redirect()->route('seguridad.roles')
            ->with('status', 'Asignación eliminada correctamente.');
    }
}
