<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Graficas extends Model
{
    protected $table = null;
    public $timestamps = false;
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS DESDE PROCEDIMIENTO
    |--------------------------------------------------------------------------
    */
    protected function obtenerCatalogos(): Collection
    {
        return collect(DB::select('CALL SEL_GRAFICAS_CATALOGOS()'));
    }

    public function obtenerAniosDisponibles(): array
    {
        return $this->obtenerCatalogos()
            ->where('tipo_catalogo', 'anio')
            ->pluck('id_referencia')
            ->map(fn($anio) => (int) $anio)
            ->sortDesc()
            ->values()
            ->toArray();
    }

    public function obtenerCarrerasDisponibles(): Collection
    {
        return $this->obtenerCatalogos()
            ->where('tipo_catalogo', 'carrera')
            ->map(function ($item) {
                return (object) [
                    'id_carrera' => (int) $item->id_referencia,
                    'nombre_carrera' => $item->nombre_referencia,
                    'id_departamento' => $item->id_padre ? (int) $item->id_padre : null,
                ];
            })
            ->values();
    }

    public function obtenerDepartamentosDisponibles(): Collection
    {
        return $this->obtenerCatalogos()
            ->where('tipo_catalogo', 'departamento')
            ->map(function ($item) {
                return (object) [
                    'id_departamento' => (int) $item->id_referencia,
                    'nombre_departamento' => $item->nombre_referencia,
                ];
            })
            ->values();
    }

    public function obtenerNombreCarrera(?int $idCarrera): ?string
    {
        if (empty($idCarrera)) {
            return null;
        }

        $carrera = $this->obtenerCarrerasDisponibles()
            ->firstWhere('id_carrera', (int) $idCarrera);

        return $carrera->nombre_carrera ?? null;
    }

    public function obtenerNombreDepartamento(?int $idDepartamento): ?string
    {
        if (empty($idDepartamento)) {
            return null;
        }

        $departamento = $this->obtenerDepartamentosDisponibles()
            ->firstWhere('id_departamento', (int) $idDepartamento);

        return $departamento->nombre_departamento ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | TOTALES
    |--------------------------------------------------------------------------
    */
    public function obtenerCancelacionesPorPeriodoYAnio(
        ?int $anio = null,
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?int $mes = null
    ): array {
        return $this->obtenerTotalesTramites(
            'CANCELACION',
            $anio,
            $mes,
            $idDepartamento,
            $idCarrera
        );
    }

    public function obtenerCambiosCarreraPorPeriodoYAnio(
        ?int $anio = null,
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?int $mes = null
    ): array {
        return $this->obtenerTotalesTramites(
            'CAMBIO_CARRERA',
            $anio,
            $mes,
            $idDepartamento,
            $idCarrera
        );
    }

    protected function obtenerTotalesTramites(
        string $tipoTramite,
        ?int $anio = null,
        ?int $mes = null,
        ?int $idDepartamento = null,
        ?int $idCarrera = null
    ): array {
        $rows = collect(DB::select('CALL SEL_GRAFICAS_TOTALES(?, ?, ?, ?, ?)', [
            $tipoTramite,
            $anio,
            $mes,
            $idDepartamento,
            $idCarrera
        ]));

        $labels = [];
        $data = [];
        $totalAnual = 0;
        $detalle = [];

        foreach ($rows as $row) {
            $label = $row->mes_nombre ?? ('Mes ' . ($row->mes_numero ?? ''));
            $valor = (int) ($row->total ?? 0);

            $labels[] = $label;
            $data[] = $valor;
            $totalAnual += $valor;
            $detalle[] = $row;
        }

        return [
            'anio'        => $anio,
            'mes'         => $mes,
            'labels'      => $labels,
            'data'        => $data,
            'total_anual' => $totalAnual,
            'detalle'     => $detalle,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DISTRIBUCIONES
    |--------------------------------------------------------------------------
    */
    public function obtenerDistribucionCancelaciones(
        ?int $anio = null,
        string $agrupacion = 'departamento',
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?int $mes = null
    ): array {
        return $this->obtenerDistribucionTramites(
            'CANCELACION',
            $agrupacion,
            $anio,
            $mes,
            $idDepartamento,
            $idCarrera
        );
    }

    public function obtenerDistribucionCambiosCarrera(
        ?int $anio = null,
        string $agrupacion = 'departamento',
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?int $mes = null
    ): array {
        return $this->obtenerDistribucionTramites(
            'CAMBIO_CARRERA',
            $agrupacion,
            $anio,
            $mes,
            $idDepartamento,
            $idCarrera
        );
    }

    protected function obtenerDistribucionTramites(
        string $tipoTramite,
        string $agrupacion = 'departamento',
        ?int $anio = null,
        ?int $mes = null,
        ?int $idDepartamento = null,
        ?int $idCarrera = null
    ): array {
        $rows = collect(DB::select('CALL SEL_GRAFICAS_DISTRIBUCION(?, ?, ?, ?, ?, ?)', [
            $tipoTramite,
            $agrupacion,
            $anio,
            $mes,
            $idDepartamento,
            $idCarrera
        ]));

        $labels = [];
        $data = [];
        $totalGeneral = 0;
        $detalle = [];

        foreach ($rows as $row) {
            $label = $this->normalizarEtiquetaEspecial($row->nombre_agrupacion ?? '');
            $valor = (int) ($row->total ?? 0);

            $labels[] = $label;
            $data[] = $valor;
            $totalGeneral += $valor;
            $detalle[] = (object) [
                'id_agrupacion' => $row->id_agrupacion ?? null,
                'nombre_agrupacion' => $label,
                'total' => $valor,
            ];
        }

        return [
            'anio'        => $anio,
            'mes'         => $mes,
            'labels'      => $labels,
            'data'        => $data,
            'total_anual' => $totalGeneral,
            'detalle'     => $detalle,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADOS REALES DE TRÁMITES
    |--------------------------------------------------------------------------
    */
    public function obtenerEstadosTramites(
        ?int $anio = null,
        ?int $idDepartamento = null,
        ?int $idCarrera = null,
        ?int $mes = null
    ): array {
        $rows = collect(DB::select("
            SELECT
                CASE
                    WHEN LOWER(TRIM(COALESCE(NULLIF(TRIM(t.resolucion_de_tramite_academico), ''), 'pendiente'))) IN ('aprobada', 'aprobado')
                        THEN 'aprobado'

                    WHEN LOWER(TRIM(COALESCE(NULLIF(TRIM(t.resolucion_de_tramite_academico), ''), 'pendiente'))) IN ('rechazada', 'rechazado')
                        THEN 'rechazado'

                    WHEN LOWER(TRIM(COALESCE(NULLIF(TRIM(t.resolucion_de_tramite_academico), ''), 'pendiente'))) IN ('revision', 'revisión', 'en_revision', 'en revisión')
                        THEN 'revision'

                    ELSE 'pendiente'
                END AS estado,
                COUNT(DISTINCT t.id_tramite) AS total
            FROM tbl_tramite t
            INNER JOIN tbl_persona p
                ON p.id_persona = t.id_persona
            INNER JOIN tbl_calendario_academico ca
                ON ca.id_calendario_academico = t.id_calendario_academico
            LEFT JOIN tbl_estudiante es
                ON es.id_persona = t.id_persona
            LEFT JOIN tbl_carrera c
                ON c.id_carrera = CASE
                    WHEN LOWER(TRIM(t.tipo_tramite_academico)) = 'cambio_carrera'
                        THEN t.id_carrera_destino
                    ELSE es.id_carrera
                END
            LEFT JOIN tbl_departamento d
                ON d.id_departamento = c.id_departamento
            WHERE t.estado = 1
              AND p.estado = 1
              AND LOWER(TRIM(t.tipo_tramite_academico)) IN ('cancelacion', 'cambio_carrera')
              AND (? IS NULL OR YEAR(ca.fecha_inicio_calendario_academico) = ?)
              AND (? IS NULL OR MONTH(ca.fecha_inicio_calendario_academico) = ?)
              AND (? IS NULL OR d.id_departamento = ?)
              AND (? IS NULL OR c.id_carrera = ?)
            GROUP BY estado
        ", [
            $anio, $anio,
            $mes, $mes,
            $idDepartamento, $idDepartamento,
            $idCarrera, $idCarrera,
        ]));

        $estados = [
            'pendiente' => 0,
            'revision'  => 0,
            'aprobado'  => 0,
            'rechazado' => 0,
        ];

        foreach ($rows as $row) {
            $estado = $row->estado ?? 'pendiente';

            if (array_key_exists($estado, $estados)) {
                $estados[$estado] = (int) ($row->total ?? 0);
            }
        }

        return $estados;
    }

    /*
    |--------------------------------------------------------------------------
    | PENDIENTES / PLACEHOLDERS
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | AUXILIARES
    |--------------------------------------------------------------------------
    */
    protected function normalizarEtiquetaEspecial(string $label): string
    {
        $text = trim($label);

        if (preg_match('/^sin carrera asignada$/i', $text)) {
            return 'Sin carrera relacionada';
        }

        if (preg_match('/^sin departamento asignado$/i', $text)) {
            return 'Sin departamento relacionado';
        }

        return $text;
    }
}

