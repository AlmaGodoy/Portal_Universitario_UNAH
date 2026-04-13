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
