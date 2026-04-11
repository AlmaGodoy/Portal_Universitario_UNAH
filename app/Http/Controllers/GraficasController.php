<?php

namespace App\Http\Controllers;

use App\Models\Graficas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $mes = $request->get('mes');

        $aniosDisponibles = $this->graficas->obtenerAniosDisponibles();

        if ($this->esSecretariaGeneral()) {
            $idCarreraSeleccionada = $request->get('id_carrera');
            $carreras = $this->graficas->obtenerCarrerasDisponibles();
        } else {
            $idCarreraSeleccionada = $this->obtenerIdCarreraEmpleadoActual();
            $carreras = collect();

            if ($idCarreraSeleccionada) {
                $carrera = $this->graficas->obtenerCarrerasDisponibles()
                    ->firstWhere('id_carrera', $idCarreraSeleccionada);

                if ($carrera) {
                    $carreras = collect([$carrera]);
                }
            }
        }

        return view('secre_carrera', compact(
            'anio',
            'mes',
            'aniosDisponibles',
            'carreras',
            'idCarreraSeleccionada'
        ));
    }

    public function vistaSecretariaAcademica(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return redirect()
                ->route('empleado.dashboard')
                ->withErrors([
                    'graficas' => 'Solo Secretaría General puede visualizar las gráficas globales.'
                ]);
        }

        $anio = $request->get('anio');
        $mes = $request->get('mes');
        $idDepartamentoSeleccionado = $request->get('id_departamento');

        $aniosDisponibles = $this->graficas->obtenerAniosDisponibles();
        $departamentos = $this->graficas->obtenerDepartamentosDisponibles();

        return view('secre_academica', compact(
            'anio',
            'mes',
            'aniosDisponibles',
            'departamentos',
            'idDepartamentoSeleccionado'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | API - SECRETARÍA GENERAL
    |--------------------------------------------------------------------------
    */
    public function datosSecretariaAcademica(Request $request)
    {
        if (!$this->esSecretariaGeneral()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No autorizado para visualizar estadísticas globales.'
            ], 403);
        }

        $anio = $this->resolverAnio($request);
        $mes = $this->resolverMes($request);

        $idDepartamento = $request->filled('id_departamento')
            ? (int) $request->get('id_departamento')
            : null;

        $nombreDepartamento = $idDepartamento
            ? $this->graficas->obtenerNombreDepartamento($idDepartamento)
            : null;

        return response()->json([
            'ok'                         => true,
            'vista'                      => 'secretaria_academica',
            'anio'                       => $anio,
            'mes'                        => $mes,
            'cancelaciones'              => $this->graficas->obtenerCancelacionesPorPeriodoYAnio($anio, $idDepartamento, null, $mes),
            'cambio_carrera'             => $this->graficas->obtenerCambiosCarreraPorPeriodoYAnio($anio, $idDepartamento, null, $mes),
            'distribucion_cancelaciones' => $this->graficas->obtenerDistribucionCancelaciones($anio, 'departamento', $idDepartamento, null, $mes),
            'distribucion_cambios'       => $this->graficas->obtenerDistribucionCambiosCarrera($anio, 'departamento', $idDepartamento, null, $mes),
            'logins'                     => $this->graficas->obtenerLoginsAlumnosPorPeriodoYAnio($anio),
            'incidentes'                 => $this->graficas->obtenerIncidentesPorPeriodoYAnio($anio),
            'nota'                       => $idDepartamento && $nombreDepartamento
                ? "Mostrando datos de: {$nombreDepartamento}."
                : "Mostrando datos de todos los trámites de la facultad.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | API - COORDINADOR / SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    */
    public function datosSecretariaCarrera(Request $request)
    {
        $anio = $this->resolverAnio($request);
        $mes = $this->resolverMes($request);

        if ($this->esSecretariaGeneral()) {
            $idCarrera = $request->filled('id_carrera')
                ? (int) $request->get('id_carrera')
                : null;
        } else {
            $idCarrera = $this->obtenerIdCarreraEmpleadoActual();
        }

        $nombreCarrera = $idCarrera
            ? $this->graficas->obtenerNombreCarrera($idCarrera)
            : null;

        return response()->json([
            'ok'                         => true,
            'vista'                      => 'secretaria_carrera',
            'anio'                       => $anio,
            'mes'                        => $mes,
            'cancelaciones'              => $this->graficas->obtenerCancelacionesPorPeriodoYAnio($anio, null, $idCarrera, $mes),
            'cambio_carrera'             => $this->graficas->obtenerCambiosCarreraPorPeriodoYAnio($anio, null, $idCarrera, $mes),
            'distribucion_cancelaciones' => $this->graficas->obtenerDistribucionCancelaciones($anio, 'carrera', null, $idCarrera, $mes),
            'distribucion_cambios'       => $this->graficas->obtenerDistribucionCambiosCarrera($anio, 'carrera', null, $idCarrera, $mes),
            'logins'                     => $this->graficas->obtenerLoginsAlumnosPorPeriodoYAnio($anio),
            'incidentes'                 => $this->graficas->obtenerIncidentesPorPeriodoYAnio($anio),
            'nota'                       => $idCarrera && $nombreCarrera
                ? "Mostrando datos de la carrera: {$nombreCarrera}."
                : "Mostrando datos generales.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
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

    protected function resolverMes(Request $request): ?int
    {
        $mes = $request->get('mes');

        if (empty($mes)) {
            return null;
        }

        if (!is_numeric($mes)) {
            return null;
        }

        $mes = (int) $mes;

        return ($mes >= 1 && $mes <= 12) ? $mes : null;
    }

    protected function rolActual(): string
    {
        return strtolower((string) session('rol_texto', 'sin_rol'));
    }

    protected function esSecretariaGeneral(): bool
    {
        return $this->rolActual() === 'secretaria_general';
    }

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
