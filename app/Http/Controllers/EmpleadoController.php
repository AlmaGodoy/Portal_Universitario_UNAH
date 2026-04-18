<?php

namespace App\Http\Controllers;

use App\Models\Graficas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    protected Graficas $graficas;

    public function __construct(Graficas $graficas)
    {
        $this->graficas = $graficas;
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('portal'); // ✅ corregido: era route('login') que no existe
        }

        $user = Auth::user();

        $rol = strtolower(trim((string) (session('rol_texto') ?? 'sin_rol')));

        $anio = $request->get('anio');
        $aniosDisponibles = $this->graficas->obtenerAniosDisponibles();

        $data = [
            'titulo'           => 'Gestión de Carrera - FCEAC',
            'userName'         => $user->persona->nombre_persona ?? ($user->name ?? 'Usuario'),
            'userRole'         => $rol,
            'anio'             => $anio,
            'aniosDisponibles' => $aniosDisponibles,
        ];

        return match ($rol) {
            'secretario'         => $this->vistaSecretariaCarrera($data),
            'secretaria_general' => $this->vistaSecretariaAcademica($data),
            'coordinador',
            'administrador'      => $this->vistaCoordinador($data),
            default              => view('dashboard', $data),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA COORDINADOR / ADMINISTRADOR
    |--------------------------------------------------------------------------
    */
    protected function vistaCoordinador(array $data)
    {
        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $carreras = collect();
        if ($idCarreraActual) {
            $carrera = $this->graficas->obtenerCarrerasDisponibles()
                ->firstWhere('id_carrera', $idCarreraActual);

            if ($carrera) {
                $carreras = collect([$carrera]);
            }
        }

        return view('coordinador_carrera', array_merge($data, [
            'carreras'              => $carreras,
            'idCarreraSeleccionada' => $idCarreraActual,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    */
    protected function vistaSecretariaCarrera(array $data)
    {
        $idCarreraActual = $this->obtenerIdCarreraEmpleadoActual();

        $carreras = collect();
        if ($idCarreraActual) {
            $carrera = $this->graficas->obtenerCarrerasDisponibles()
                ->firstWhere('id_carrera', $idCarreraActual);

            if ($carrera) {
                $carreras = collect([$carrera]);
            }
        }

        return view('secre_carrera', array_merge($data, [
            'carreras'              => $carreras,
            'idCarreraSeleccionada' => $idCarreraActual,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SECRETARÍA GENERAL
    |--------------------------------------------------------------------------
    */
    protected function vistaSecretariaAcademica(array $data)
    {
        $departamentos = $this->graficas->obtenerDepartamentosDisponibles();

        $idDepartamentoSeleccionado = request('id_departamento');

        return view('secre_academica', array_merge($data, [
            'departamentos'              => $departamentos,
            'idDepartamentoSeleccionado' => $idDepartamentoSeleccionado,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | API AUXILIAR
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    protected function obtenerIdPersonaAutenticada(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return isset($user->id_persona) ? (int) $user->id_persona : null;
    }

    protected function obtenerIdCarreraEmpleadoActual(): ?int
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return null;
        }

        $res = DB::select('CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)', [
            $idPersona
        ]);

        $row = $res[0] ?? null;

        if (!$row || ($row->resultado ?? 'ERROR') !== 'OK') {
            return null;
        }

        return !empty($row->id_carrera)
            ? (int) $row->id_carrera
            : null;
    }
}