<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class EmpleadoController extends Controller
{
    public function index()
    {
        // 1. Obtener usuario con su relación de empleado cargada
        $user = Auth::user()->load('empleado');

        // 2. Seguridad: Si no tiene datos de empleado o está inactivo
        if (!$user || !$user->empleado || $user->empleado->estado !== 'activo') {
            return redirect()->route('portal')->with('error', 'Acceso denegado o cuenta inactiva.');
        }

        // 3. Obtener el tipo desde la tabla empleados
        $tipo = $user->empleado->tipo_empleado;

        return match ($tipo) {
            'secretaria_carrera', 'secretaria_academica' => $this->vistaSecretarias($user, $tipo),
            'coordinador'                               => $this->vistaCoordinador($user),
            'administrador'                             => $this->vistaAdministrador($user),
            default => redirect()->route('portal')->with('error', 'Rol no reconocido.'),
        };
    }

    // VISTA 1: Secretarías (Compartida para las dos secretarias)
    private function vistaSecretarias($user, $tipo)
    {
        $datos = [
            'titulo' => ($tipo == 'secretaria_carrera') ? 'Gestión de Carrera' : 'Gestión Académica',
            'usuario' => $user->id_persona,
            'rol' => $tipo
        ];
        return view('empleados.gestion_secretaria', compact('datos'));
    }

    // VISTA 2: Coordinador (Tu panel)
    private function vistaCoordinador($user)
    {
        $datos = [
            'titulo' => 'Panel de Control: Coordinación',
            'usuario' => $user->id_persona
        ];
        return view('empleados.panel_coordinador', compact('datos'));
    }

    // VISTA 3: Administrador
    private function vistaAdministrador($user)
    {
        $datos = [
            'titulo' => 'Mantenimiento del Sistema',
            'usuario' => $user->id_persona
        ];
        return view('empleados.administracion_sistema', compact('datos'));
    }
}
