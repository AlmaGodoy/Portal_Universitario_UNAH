<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function index()
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

        return view('seguridad.index', compact(
            'roles',
            'permisos',
            'objetos',
            'rolPermisos'
        ));
    }

    public function storeRol(Request $request)
    {
        $request->validate([
            'nombre_rol' => 'required|string|max:100|unique:tbl_rol,nombre_rol',
            'descripcion' => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        DB::table('tbl_rol')->insert([
            'nombre_rol' => strtoupper(trim($request->nombre_rol)),
            'descripcion' => trim($request->descripcion),
            'estado_activo' => (int) $request->estado_activo,
        ]);

        return redirect()->route('seguridad.index')
            ->with('status', 'Rol creado correctamente.');
    }

    public function updateRol(Request $request, $id)
    {
        $request->validate([
            'nombre_rol' => 'required|string|max:100|unique:tbl_rol,nombre_rol,' . $id . ',id_rol',
            'descripcion' => 'required|string|max:255',
            'estado_activo' => 'required|in:0,1',
        ]);

        DB::table('tbl_rol')
            ->where('id_rol', $id)
            ->update([
                'nombre_rol' => strtoupper(trim($request->nombre_rol)),
                'descripcion' => trim($request->descripcion),
                'estado_activo' => (int) $request->estado_activo,
            ]);

        return redirect()->route('seguridad.index')
            ->with('status', 'Rol actualizado correctamente.');
    }

    public function asignarPermisosObjeto(Request $request)
    {
        $request->validate([
            'id_rol' => 'required|integer',
            'id_objeto' => 'required|integer',
            'permisos' => 'required|array|min:1',
            'permisos.*' => 'integer',
        ], [
            'id_rol.required' => 'Debes seleccionar un rol.',
            'id_objeto.required' => 'Debes seleccionar una pantalla.',
            'permisos.required' => 'Debes seleccionar al menos un acceso.',
        ]);

        foreach ($request->permisos as $idPermiso) {
            $existe = DB::table('tbl_rol_permiso')
                ->where('id_rol', $request->id_rol)
                ->where('id_permiso', $idPermiso)
                ->where('id_objeto', $request->id_objeto)
                ->exists();

            if (!$existe) {
                DB::table('tbl_rol_permiso')->insert([
                    'id_rol' => $request->id_rol,
                    'id_permiso' => $idPermiso,
                    'id_objeto' => $request->id_objeto,
                    'fecha_asignacion' => now(),
                ]);
            }
        }

        return redirect()->route('seguridad.index')
            ->with('status', 'Permisos asignados correctamente.');
    }

    public function deleteAsignacion($id)
    {
        DB::table('tbl_rol_permiso')
            ->where('id_rol_permiso', $id)
            ->delete();

        return redirect()->route('seguridad.index')
            ->with('status', 'Asignación eliminada correctamente.');
    }
}
