<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Graficas extends Model
{
    protected $table = null;
    public $timestamps = false;
    protected $guarded = [];

    public function obtenerCancelacionesPorPeriodoYAnio(?int $anio = null, ?int $idDepartamento = null, ?int $idCarrera = null): array
    {
        return $this->obtenerTramitesPorCalendario(
            'CANCELACION',
            $anio,
            $idDepartamento,
            $idCarrera,
            'cancelacion'
        );
    }

    public function obtenerCambiosCarreraPorPeriodoYAnio(?int $anio = null, ?int $idDepartamento = null, ?int $idCarrera = null): array
    {
        return $this->obtenerTramitesPorCalendario(
            'CAMBIO_CARRERA',
            $anio,
            $idDepartamento,
            $idCarrera,
            'cambio_carrera'
        );
    }

    public function obtenerDistribucionCancelaciones(
        ?int $anio = null,
        string $agrupacion = 'departamento',
        ?int $idDepartamento = null,
        ?int $idCarrera = null
    ): array {
        return $this->obtenerDistribucionTramites(
            'CANCELACION',
            $anio,
            $agrupacion,
            $idDepartamento,
            $idCarrera,
            'cancelacion'
        );
    }

    public function obtenerDistribucionCambiosCarrera(
        ?int $anio = null,
        string $agrupacion = 'departamento',
        ?int $idDepartamento = null,
        ?int $idCarrera = null
    ): array {
        return $this->obtenerDistribucionTramites(
            'CAMBIO_CARRERA',
            $anio,
            $agrupacion,
            $idDepartamento,
            $idCarrera,
            'cambio_carrera'
        );
    }

    protected function obtenerDistribucionTramites(
        string $tipoTramite,
        ?int $anio = null,
        string $agrupacion = 'departamento',
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?string $modo = null
    ): array {
        $query = DB::table('tbl_tramite as t')
            ->join('tbl_calendario_academico as ca', 't.id_calendario_academico', '=', 'ca.id_calendario_academico')
            ->whereRaw('UPPER(t.tipo_tramite_academico) = ?', [strtoupper($tipoTramite)])
            ->where('t.estado', '=', 1);

        /*
        |--------------------------------------------------------------------------
        | RELACIONES PARA AGRUPAR
        |--------------------------------------------------------------------------
        | Se usan LEFT JOIN para no perder registros.
        */
        if ($modo === 'cancelacion') {
            $query->leftJoin('tbl_estudiante as e', 'e.id_persona', '=', 't.id_persona')
                ->leftJoin('tbl_carrera as c', 'c.id_carrera', '=', 'e.id_carrera')
                ->leftJoin('tbl_departamento as d', 'd.id_departamento', '=', 'c.id_departamento');
        }

        if ($modo === 'cambio_carrera') {
            $query->leftJoin('tbl_carrera as c', 'c.id_carrera', '=', 't.id_carrera_destino')
                ->leftJoin('tbl_departamento as d', 'd.id_departamento', '=', 'c.id_departamento');
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */
        if (!empty($anio)) {
            $query->whereYear('ca.fecha_inicio_calendario_academico', $anio);
        }

        if (!empty($idCarrera)) {
            $query->where('c.id_carrera', '=', $idCarrera);
        } elseif (!empty($idDepartamento)) {
            $query->where('c.id_departamento', '=', $idDepartamento);
        }

        /*
        |--------------------------------------------------------------------------
        | AGRUPACIÓN
        |--------------------------------------------------------------------------
        | Si no tiene carrera/departamento, igual se cuenta.
        */
        if ($agrupacion === 'carrera') {
            $query->selectRaw("
                COALESCE(c.id_carrera, 0) as id_agrupacion,
                COALESCE(c.nombre_carrera, 'Sin carrera asignada') as nombre_agrupacion,
                COUNT(DISTINCT t.id_tramite) as total
            ")
            ->groupBy(
                DB::raw("COALESCE(c.id_carrera, 0)"),
                DB::raw("COALESCE(c.nombre_carrera, 'Sin carrera asignada')")
            )
            ->orderByDesc('total');
        } else {
            $query->selectRaw("
                COALESCE(d.id_departamento, 0) as id_agrupacion,
                COALESCE(d.nombre_departamento, 'Sin departamento asignado') as nombre_agrupacion,
                COUNT(DISTINCT t.id_tramite) as total
            ")
            ->groupBy(
                DB::raw("COALESCE(d.id_departamento, 0)"),
                DB::raw("COALESCE(d.nombre_departamento, 'Sin departamento asignado')")
            )
            ->orderByDesc('total');
        }

        $rows = $query->get();

        $labels = [];
        $data = [];
        $totalGeneral = 0;

        foreach ($rows as $row) {
            $labels[] = $row->nombre_agrupacion;
            $data[] = (int) $row->total;
            $totalGeneral += (int) $row->total;
        }

        return [
            'anio'        => $anio,
            'labels'      => $labels,
            'data'        => $data,
            'total_anual' => $totalGeneral,
            'detalle'     => $rows,
        ];
    }

    protected function obtenerTramitesPorCalendario(
        string $tipoTramite,
        ?int $anio = null,
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?string $modo = null
    ): array {
        $query = DB::table('tbl_tramite as t')
            ->join('tbl_calendario_academico as ca', 't.id_calendario_academico', '=', 'ca.id_calendario_academico')
            ->whereRaw('UPPER(t.tipo_tramite_academico) = ?', [strtoupper($tipoTramite)])
            ->where('t.estado', '=', 1);

        /*
        |--------------------------------------------------------------------------
        | RELACIONES PARA FILTRAR
        |--------------------------------------------------------------------------
        */
        if ($modo === 'cancelacion') {
            $query->leftJoin('tbl_estudiante as e', 'e.id_persona', '=', 't.id_persona')
                ->leftJoin('tbl_carrera as c', 'c.id_carrera', '=', 'e.id_carrera');
        }

        if ($modo === 'cambio_carrera') {
            $query->leftJoin('tbl_carrera as c', 'c.id_carrera', '=', 't.id_carrera_destino');
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */
        if (!empty($idCarrera)) {
            $query->where('c.id_carrera', '=', $idCarrera);
        } elseif (!empty($idDepartamento)) {
            $query->where('c.id_departamento', '=', $idDepartamento);
        }

        if (!empty($anio)) {
            $query->whereYear('ca.fecha_inicio_calendario_academico', $anio);
        }

        $query->selectRaw("
            YEAR(ca.fecha_inicio_calendario_academico) AS anio,
            ca.id_calendario_academico,
            CONCAT(
                DATE_FORMAT(ca.fecha_inicio_calendario_academico, '%d/%m/%Y'),
                ' - ',
                DATE_FORMAT(ca.fecha_final_calendario_academico, '%d/%m/%Y')
            ) AS periodo,
            COUNT(DISTINCT t.id_tramite) AS total
        ")
        ->groupBy(
            'ca.id_calendario_academico',
            'ca.fecha_inicio_calendario_academico',
            'ca.fecha_final_calendario_academico'
        )
        ->orderBy('ca.fecha_inicio_calendario_academico', 'asc');

        $rows = $query->get();

        $labels = [];
        $data = [];
        $anioSeleccionado = $anio;
        $totalAnual = 0;

        foreach ($rows as $row) {
            $labels[] = $row->periodo;
            $data[] = (int) $row->total;
            $totalAnual += (int) $row->total;

            if (empty($anioSeleccionado)) {
                $anioSeleccionado = (int) $row->anio;
            }
        }

        return [
            'anio'        => $anioSeleccionado,
            'labels'      => $labels,
            'data'        => $data,
            'total_anual' => $totalAnual,
            'detalle'     => $rows,
        ];
    }

    public function obtenerLoginsAlumnosPorPeriodoYAnio(?int $anio = null): array
    {
        return [
            'anio'        => $anio,
            'labels'      => [],
            'data'        => [],
            'total_anual' => 0,
            'detalle'     => [],
            'pendiente'   => true,
            'mensaje'     => 'Pendiente definir tabla o procedimiento de login de alumnos.',
        ];
    }

    public function obtenerIncidentesPorPeriodoYAnio(?int $anio = null): array
    {
        return [
            'anio'        => $anio,
            'labels'      => [],
            'data'        => [],
            'total_anual' => 0,
            'detalle'     => [],
            'pendiente'   => true,
            'mensaje'     => 'Pendiente definir tabla o procedimiento de incidentes.',
        ];
    }

    public function obtenerAniosDisponibles(): array
    {
        return DB::table('tbl_calendario_academico')
            ->selectRaw('YEAR(fecha_inicio_calendario_academico) AS anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->map(fn ($anio) => (int) $anio)
            ->toArray();
    }
}
