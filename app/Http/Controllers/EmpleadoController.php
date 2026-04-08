<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Graficas;

class EmpleadoController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $rol = session('rol_texto') ?? 'sin_rol';

        $graficas = app(Graficas::class);

        $anio = request('anio');
        $aniosDisponibles = $graficas->obtenerAniosDisponibles();

        $data = [
            'titulo'   => 'Gestión de Carrera - FCEAC',
            'userName' => $user->persona->nombre_persona ?? ($user->name ?? 'Usuario'),
            'userRole' => $rol,
            'anio'     => $anio,
            'aniosDisponibles' => $aniosDisponibles,
        ];

        return match ($rol) {
            'secretario', 'secretaria' => $this->vistaSecretariaCarrera($data),
            'secretaria_general'       => $this->vistaSecretariaAcademica($data),
            'coordinador'              => view('coordinador_carrera', $data),
            default                    => view('dashboard', $data),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    */
    protected function vistaSecretariaCarrera(array $data)
    {
        $carreras = DB::table('tbl_carrera')
            ->select('id_carrera', 'nombre_carrera')
            ->orderBy('nombre_carrera')
            ->get();

        $idCarreraSeleccionada = request('id_carrera');

        return view('secre_carrera', array_merge($data, [
            'carreras' => $carreras,
            'idCarreraSeleccionada' => $idCarreraSeleccionada,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SECRETARÍA ACADÉMICA
    |--------------------------------------------------------------------------
    */
    protected function vistaSecretariaAcademica(array $data)
    {
        $departamentos = DB::table('tbl_departamento')
            ->select('id_departamento', 'nombre_departamento')
            ->orderBy('nombre_departamento')
            ->get();

        $idDepartamentoSeleccionado = request('id_departamento');

        return view('secre_academica', array_merge($data, [
            'departamentos' => $departamentos,
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
}
