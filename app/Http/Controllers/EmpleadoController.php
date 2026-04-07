<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. Obtener el rol
        $rol = $this->resolverRol($user);

        // 2. Obtener nombre a mostrar
        $userName = $this->resolverNombre($user);

        // 3. Datos comunes
        $data = [
            'titulo'   => $this->tituloSegunRol($rol),
            'userName' => $userName,
            'userRole' => $this->etiquetaRol($rol),
        ];

        // 4. Retornar la vista correcta según rol
        return match (true) {
            in_array($rol, ['secretario', 'secretaria']) => view('secre_carrera', $data),

            $rol === 'secretaria_general' => view('secre_academica', array_merge($data, [
                'titulo'   => 'Secretaría Académica - FCEAC',
                'userRole' => 'Secretaría Académica',
            ])),

            in_array($rol, ['coordinador', 'admin', 'administrador']) => view('coordinador_carrera', $data),

            default => redirect()->route('dashboard')->with('error', 'No se encontró una vista válida para el rol del empleado.'),
        };
    }

    /**
     * Resuelve el rol del usuario con varios fallbacks.
     */
    private function resolverRol($user): string
    {
        // Nivel 1: sesión
        if (session()->has('rol_texto') && !empty(session('rol_texto'))) {
            return strtolower(trim(session('rol_texto')));
        }

        // Nivel 2: campos directos en usuario
        $camposRol = ['rol', 'role', 'tipo_usuario', 'tipo'];
        foreach ($camposRol as $campo) {
            if (!empty($user->$campo)) {
                return strtolower(trim($user->$campo));
            }
        }

        // Nivel 3: relación por BD
        try {
            $idUsuario = $user->id_usuario ?? $user->id ?? null;

            if ($idUsuario) {
                $rolDb = DB::table('tbl_rol')
                    ->join('tbl_usuario_rol', 'tbl_rol.id_rol', '=', 'tbl_usuario_rol.id_rol')
                    ->where('tbl_usuario_rol.id_usuario', $idUsuario)
                    ->value('tbl_rol.nombre_rol');

                if (!empty($rolDb)) {
                    session(['rol_texto' => $rolDb]);
                    return strtolower(trim($rolDb));
                }
            }
        } catch (\Exception $e) {
            // Ignorar si la estructura de tablas cambia
        }

        return 'sin_rol';
    }

    /**
     * Resuelve el nombre para mostrar.
     */
    private function resolverNombre($user): string
    {
        if (isset($user->persona) && !empty($user->persona->nombre_persona)) {
            return trim($user->persona->nombre_persona);
        }

        if (!empty($user->nombre_persona)) {
            return trim($user->nombre_persona);
        }

        if (!empty($user->name)) {
            return trim($user->name);
        }

        if (!empty($user->email)) {
            return trim($user->email);
        }

        return 'Usuario';
    }

    /**
     * Título de página según rol.
     */
    private function tituloSegunRol(string $rol): string
    {
        return match (true) {
            in_array($rol, ['secretario', 'secretaria']) => 'Secretaría de Carrera - FCEAC',
            $rol === 'secretaria_general' => 'Secretaría Académica - FCEAC',
            in_array($rol, ['coordinador', 'admin', 'administrador']) => 'Panel de Coordinación - FCEAC',
            default => 'Portal FCEAC',
        };
    }

    /**
     * Etiqueta legible del rol.
     */
    private function etiquetaRol(string $rol): string
    {
        return match (true) {
            in_array($rol, ['secretario', 'secretaria']) => 'Secretaría de Carrera',
            $rol === 'secretaria_general' => 'Secretaría Académica',
            in_array($rol, ['coordinador', 'admin', 'administrador']) => 'Coordinación',
            default => 'Empleado',
        };
    }

    public function getEstadisticas()
    {
        return response()->json(['aprobados' => 312]);
    }

    public function listarPorUnidad()
    {
        return response()->json([]);
    }

    public function getNotificaciones()
    {
        return response()->json([]);
    }
}