<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EmpleadoController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $rol = session('rol_texto') ?? 'sin_rol';

        $data = [
            'titulo'   => 'Gestión de Carrera - FCEAC',
            'userName' => $user->name ?? 'Usuario',
            'userRole' => $rol,
        ];

        return match ($rol) {
            'secretario', 'secretaria' => view('secre_carrera', $data),
            'secretaria_general'       => view('secre_academica', $data),
            'coordinador'              => view('coordinador_carrera', $data),
            default                    => view('dashboard', $data),
        };
    }

    public function getEstadisticas() { return response()->json(['aprobados' => 312]); }
    public function listarPorUnidad() { return response()->json([]); }
    public function getNotificaciones() { return response()->json([]); }
}
