<?php

namespace App\Http\Controllers;

use App\Models\Graficas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraficasController extends Controller
{
    protected Graficas $graficas;

    public function __construct(Graficas $graficas)
    {
        $this->graficas = $graficas;
    }

    /*
    |--------------------------------------------------------------------------
    | VISTAS
    |--------------------------------------------------------------------------
    */
    public function vistaSecretariaCarrera(Request $request)
    {
        $anio = $request->get('anio');
        $idCarreraSeleccionada = $request->get('id_carrera');

        $aniosDisponibles = $this->graficas->obtenerAniosDisponibles();
        $carreras = DB::table('tbl_carrera')
            ->select('id_carrera', 'nombre_carrera')
            ->orderBy('nombre_carrera')
            ->get();

        return view('secre_carrera', compact(
            'anio',
            'aniosDisponibles',
            'carreras',
            'idCarreraSeleccionada'
        ));
    }

    public function vistaSecretariaAcademica(Request $request)
    {
        $anio = $request->get('anio');
        $idDepartamentoSeleccionado = $request->get('id_departamento');

        $aniosDisponibles = $this->graficas->obtenerAniosDisponibles();
        $departamentos = DB::table('tbl_departamento')
            ->select('id_departamento', 'nombre_departamento')
            ->orderBy('nombre_departamento')
            ->get();

        return view('secre_academica', compact(
            'anio',
            'aniosDisponibles',
            'departamentos',
            'idDepartamentoSeleccionado'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | API - SECRETARÍA ACADÉMICA
    |--------------------------------------------------------------------------
    */
    public function datosSecretariaAcademica(Request $request)
    {
        $anio = $this->resolverAnio($request);
        $idDepartamento = $request->filled('id_departamento')
            ? (int) $request->get('id_departamento')
            : null;

        $nombreDepartamento = null;

        if ($idDepartamento) {
            $nombreDepartamento = DB::table('tbl_departamento')
                ->where('id_departamento', $idDepartamento)
                ->value('nombre_departamento');
        }

        return response()->json([
            'ok'                         => true,
            'vista'                      => 'secretaria_academica',
            'anio'                       => $anio,
            'cancelaciones'              => $this->graficas->obtenerCancelacionesPorPeriodoYAnio($anio, $idDepartamento, null),
            'cambio_carrera'             => $this->graficas->obtenerCambiosCarreraPorPeriodoYAnio($anio, $idDepartamento, null),
            'distribucion_cancelaciones' => $this->graficas->obtenerDistribucionCancelaciones($anio, 'departamento', $idDepartamento, null),
            'distribucion_cambios'       => $this->graficas->obtenerDistribucionCambiosCarrera($anio, 'departamento', $idDepartamento, null),
            'logins'                     => $this->graficas->obtenerLoginsAlumnosPorPeriodoYAnio($anio),
            'incidentes'                 => $this->graficas->obtenerIncidentesPorPeriodoYAnio($anio),
            'nota'                       => $idDepartamento && $nombreDepartamento
                ? "Mostrando datos de: {$nombreDepartamento}."
                : "Mostrando datos de todos los departamentos.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | API - SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    */
    public function datosSecretariaCarrera(Request $request)
    {
        $anio = $this->resolverAnio($request);
        $idCarrera = $request->filled('id_carrera')
            ? (int) $request->get('id_carrera')
            : null;

        $nombreCarrera = null;

        if ($idCarrera) {
            $nombreCarrera = DB::table('tbl_carrera')
                ->where('id_carrera', $idCarrera)
                ->value('nombre_carrera');
        }

        return response()->json([
            'ok'                         => true,
            'vista'                      => 'secretaria_carrera',
            'anio'                       => $anio,
            'cancelaciones'              => $this->graficas->obtenerCancelacionesPorPeriodoYAnio($anio, null, $idCarrera),
            'cambio_carrera'             => $this->graficas->obtenerCambiosCarreraPorPeriodoYAnio($anio, null, $idCarrera),
            'distribucion_cancelaciones' => $this->graficas->obtenerDistribucionCancelaciones($anio, 'carrera', null, $idCarrera),
            'distribucion_cambios'       => $this->graficas->obtenerDistribucionCambiosCarrera($anio, 'carrera', null, $idCarrera),
            'logins'                     => $this->graficas->obtenerLoginsAlumnosPorPeriodoYAnio($anio),
            'incidentes'                 => $this->graficas->obtenerIncidentesPorPeriodoYAnio($anio),
            'nota'                       => $idCarrera && $nombreCarrera
                ? "Mostrando datos de la carrera: {$nombreCarrera}."
                : "Mostrando datos de todas las carreras.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AYUDANTE
    |--------------------------------------------------------------------------
    */
    protected function resolverAnio(Request $request): ?int
    {
        $anio = $request->get('anio');

        if (empty($anio)) {
            return null;
        }

        return is_numeric($anio) ? (int) $anio : null;
    }
}
