<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use Illuminate\Support\Facades\Auth;

class EmpleadoController extends Controller
{
    /**
     * PROCEDIMIENTO PRINCIPAL: Dirigir al empleado a su Dashboard correcto
     */
    public function index()
    {
        // 1. Obtener usuario autenticado
        $user = Auth::user();

        // 2. Seguridad extra: Si no hay sesión, al portal
        if (!$user) {
            return redirect()->route('portal');
        }

        // 3. Lógica de redirección según el ROL (1=Admin, 2=Empleado)
        // Aquí usamos el campo 'tipo_empleado' para decidir la vista
        $tipo = $user->tipo_empleado;

        return match ($tipo) {
            'docente'        => $this->vistaDocente($user),
            'administrativo' => $this->vistaAdmin($user),
            'mantenimiento'  => $this->vistaManto($user),
            default          => redirect()->route('portal')->with('error', 'Rol no reconocido'),
        };
    }

    private function vistaDocente($user)
    {

        $datos = ['titulo' => 'Portal del Docente', 'usuario' => $user->name];
        return view('empleados.docente', compact('datos'));
    }

    private function vistaAdmin($user)
    {
        $datos = ['titulo' => 'Gestión Administrativa', 'usuario' => $user->name];
        return view('empleados.administrativo', compact('datos'));
    }

    private function vistaManto($user)
    {
        $datos = ['titulo' => 'Panel de Mantenimiento', 'usuario' => $user->name];
        return view('empleados.mantenimiento', compact('datos'));
    }
}
