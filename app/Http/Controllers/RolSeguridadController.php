<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolSeguridadController extends Controller
{
    /**
     * Solo coordinador puede entrar al módulo de seguridad.
     */
    private function validarAccesoSeguridad()
    {
        if (!Auth::check()) {
            abort(403, 'No autorizado.');
        }

        // Debe haber iniciado como empleado
        if (session('login_tipo') !== 'empleado') {
            abort(403, 'No autorizado.');
        }

        // Solo coordinador
        $rol = strtolower(trim((string) session('rol_texto', '')));

        if ($rol !== 'coordinador') {
            abort(403, 'No autorizado.');
        }
    }

    public function index()
    {
        $this->validarAccesoSeguridad();

        $modulos = [
            [
                'titulo' => 'Gestión de Roles',
                'descripcion' => 'Crear, editar, activar o desactivar roles.',
                'ruta' => route('seguridad.roles'),
                'icono' => 'fas fa-user-tag'
            ],
            [
                'titulo' => 'Gestión de Usuarios',
                'descripcion' => 'Administrar usuarios del sistema y revisar su estado.',
                'ruta' => route('seguridad.usuarios'),
                'icono' => 'fas fa-users'
            ],
            [
                'titulo' => 'Gestión de Objetos',
                'descripcion' => 'Administrar módulos y submódulos de seguridad.',
                'ruta' => route('seguridad.objetos'),
                'icono' => 'fas fa-cubes'
            ],
            [
                'titulo' => 'Gestión de Accesos',
                'descripcion' => 'Administrar accesos y permisos por módulo.',
                'ruta' => route('seguridad.accesos'),
                'icono' => 'fas fa-user-shield'
            ],
        ];

        return view('rol_seguridad_index', compact('modulos'));
    }

    public function usuarios()
    {
        $this->validarAccesoSeguridad();

        $usuarios = DB::select('CALL SEL_USUARIOS_SEGURIDAD()');

        return view('rol_seguridad_usuarios', compact('usuarios'));
    }

    public function updateEstadoUsuario(Request $request, $id)
    {
        $this->validarAccesoSeguridad();

        $request->validate([
            'estado_cuenta' => 'required|in:0,1',
        ]);

        $res = DB::select('CALL UPD_ESTADO_USUARIO_SEGURIDAD(?, ?, ?)', [
            $id,
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
        $this->validarAccesoSeguridad();

        $objetos = DB::select('CALL SEL_MODULOS_SEGURIDAD()');

        return view('rol_seguridad_objetos', compact('objetos'));
    }

    public function storeObjeto(Request $request)
    {
        $this->validarAccesoSeguridad();

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
        $this->validarAccesoSeguridad();

        $request->validate([
            'nombre_objeto' => 'required|string|max:100',
            'tipo_objeto' => 'required|string|max:50',
        ]);

        $res = DB::select('CALL UPD_MODULOS_SEGURIDAD(?, ?, ?, ?)', [
            $id,
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
        $this->validarAccesoSeguridad();

        $accesos = DB::select('CALL SEL_ACCESOS_SEGURIDAD()');

        $roles = DB::table('tbl_rol')
            ->where('estado_activo', 1)
            ->orderBy('nombre_rol')
            ->get();

        $permisos = DB::table('tbl_permiso')
            ->orderBy('nombre_permiso')
            ->get();

        $objetos = DB::table('tbl_objeto')
            ->orderBy('nombre_objeto')
            ->get();

        return view('rol_seguridad_accesos', compact('accesos', 'roles', 'permisos', 'objetos'));
    }

    public function storeAcceso(Request $request)
    {
        $this->validarAccesoSeguridad();

        $request->validate([
            'id_rol' => 'required|integer',
            'id_permiso' => 'required|integer',
            'id_objeto' => 'required|integer',
        ]);

        $res = DB::select('CALL INS_ACCESOS_SEGURIDAD(?, ?, ?, ?)', [
            $request->id_rol,
            $request->id_permiso,
            $request->id_objeto,
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
        $this->validarAccesoSeguridad();

        $res = DB::select('CALL DEL_ACCESOS_SEGURIDAD(?, ?)', [
            $id,
            Auth::id()
        ]);

        $row = $res[0] ?? null;
        $resultado = $row->resultado ?? 'ERROR';
        $mensaje = $row->mensaje ?? 'No se pudo eliminar el acceso.';

        if ($resultado !== 'OK') {
            return back()->withErrors(['acceso' => $mensaje]);
        }

        return redirect()->route('seguridad.accesos')
            ->with('status', $mensaje);
    }
}
