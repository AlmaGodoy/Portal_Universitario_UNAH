<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SoporteTicket extends Model
{
    protected $table = 'tbl_soporte';
    protected $primaryKey = 'id_soporte';
    public $timestamps = false;
    protected $guarded = [];

    public function crearTicket(array $data): array
    {
        $result = DB::select(
            'CALL INS_SOPORTE(?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['id_persona_solicitante'] ?? null,
                $data['id_carrera'] ?? null,
                $data['asunto'] ?? null,
                $data['tipo'] ?? null,
                $data['prioridad'] ?? null,
                $data['modulo'] ?? null,
                $data['descripcion'] ?? null,
                $data['canal'] ?? null,
                $data['carrera'] ?? null,
            ]
        );

        return isset($result[0]) ? (array) $result[0] : [];
    }

    public function obtenerTicketsPorEstudiante(?int $idPersona): Collection
    {
        if (!$idPersona) {
            return collect();
        }

        $rows = DB::select('CALL SEL_SOPORTE(?, ?, ?)', [
            'ESTUDIANTE',
            $idPersona,
            null,
        ]);

        return collect($rows)->map(function ($row) {
            return $this->normalizarFila((array) $row);
        })->values();
    }

    public function obtenerTicketsParaSecretaria(?int $idCarrera): Collection
    {
        $rows = DB::select('CALL SEL_SOPORTE(?, ?, ?)', [
            'SECRETARIA',
            null,
            $idCarrera,
        ]);

        return collect($rows)->map(function ($row) {
            return $this->normalizarFila((array) $row);
        })->values();
    }

    public function obtenerTicketPorId(int $idSoporte): ?array
    {
        $row = DB::table('tbl_soporte as s')
            ->join('tbl_persona as p', 'p.id_persona', '=', 's.id_persona_solicitante')
            ->select([
                's.id_soporte',
                's.codigo',
                'p.nombre_persona as usuario',
                'p.correo_institucional as correo',
                DB::raw("IFNULL(s.carrera, 'Sin carrera relacionada') as carrera"),
                's.id_carrera',
                's.tipo',
                's.prioridad',
                DB::raw('LOWER(s.prioridad) as prioridad_key'),
                DB::raw('s.estado_soporte as estado'),
                DB::raw("
                    CASE
                        WHEN LOWER(s.estado_soporte) = 'pendiente' THEN 'pendiente'
                        WHEN LOWER(s.estado_soporte) = 'en proceso' THEN 'en_proceso'
                        WHEN LOWER(s.estado_soporte) = 'resuelto' THEN 'resuelto'
                        ELSE 'pendiente'
                    END as estado_key
                "),
                DB::raw("IFNULL(s.canal, 'Portal estudiantil') as canal"),
                DB::raw("DATE_FORMAT(s.fecha_creacion, '%Y-%m-%d %H:%i') as fecha"),
                's.descripcion',
                DB::raw("IFNULL(s.observacion_secretaria, 'Pendiente de revisión por secretaría.') as solucion_sugerida"),
                's.modulo',
                's.asunto',
                's.id_persona_solicitante',
                's.id_usuario_asignado',
            ])
            ->where('s.id_soporte', $idSoporte)
            ->where('s.estado', 1)
            ->first();

        return $row ? $this->normalizarFila((array) $row) : null;
    }

    public function actualizarEstado(
        int $idSoporte,
        string $estadoSoporte,
        ?int $idUsuarioAsignado = null,
        ?string $observacionSecretaria = null
    ): array {
        $result = DB::select(
            'CALL UPD_SOPORTE_ESTADO(?, ?, ?, ?)',
            [
                $idSoporte,
                $estadoSoporte,
                $idUsuarioAsignado,
                $observacionSecretaria,
            ]
        );

        return isset($result[0]) ? (array) $result[0] : [];
    }

    public function obtenerResumen(Collection $tickets): array
    {
        return [
            'total' => $tickets->count(),
            'pendientes' => $tickets->where('estado_key', 'pendiente')->count(),
            'en_proceso' => $tickets->where('estado_key', 'en_proceso')->count(),
            'resueltos' => $tickets->where('estado_key', 'resuelto')->count(),
        ];
    }

    private function normalizarFila(array $ticket): array
    {
        $ticket['id_soporte'] = isset($ticket['id_soporte']) ? (int) $ticket['id_soporte'] : null;
        $ticket['id_persona_solicitante'] = isset($ticket['id_persona_solicitante']) ? (int) $ticket['id_persona_solicitante'] : null;
        $ticket['id_usuario_asignado'] = isset($ticket['id_usuario_asignado']) ? (int) $ticket['id_usuario_asignado'] : null;
        $ticket['id_carrera'] = isset($ticket['id_carrera']) ? (int) $ticket['id_carrera'] : null;

        $ticket['codigo'] = $ticket['codigo'] ?? '';
        $ticket['usuario'] = $ticket['usuario'] ?? 'Alumno';
        $ticket['correo'] = $ticket['correo'] ?? '';
        $ticket['carrera'] = $ticket['carrera'] ?? 'Sin carrera relacionada';
        $ticket['tipo'] = $ticket['tipo'] ?? 'Consulta general';
        $ticket['tipo_key'] = $ticket['tipo_key'] ?? $this->mapTipoKey((string) $ticket['tipo']);
        $ticket['prioridad'] = $ticket['prioridad'] ?? 'Media';
        $ticket['prioridad_key'] = $ticket['prioridad_key'] ?? $this->mapPrioridadKey((string) $ticket['prioridad']);
        $ticket['estado'] = $ticket['estado'] ?? 'Pendiente';
        $ticket['estado_key'] = $ticket['estado_key'] ?? $this->mapEstadoKey((string) $ticket['estado']);
        $ticket['canal'] = $ticket['canal'] ?? 'Portal estudiantil';
        $ticket['fecha'] = $ticket['fecha'] ?? now()->format('Y-m-d H:i');
        $ticket['descripcion'] = $ticket['descripcion'] ?? '';
        $ticket['solucion_sugerida'] = $ticket['solucion_sugerida'] ?? 'Pendiente de revisión por secretaría.';
        $ticket['modulo'] = $ticket['modulo'] ?? 'Otro';
        $ticket['asunto'] = $ticket['asunto'] ?? $ticket['tipo'];

        return $ticket;
    }

    private function mapTipoKey(string $tipo): string
    {
        return match (mb_strtolower(trim($tipo))) {
            'acceso al sistema' => 'acceso',
            'problema con trámite' => 'tramite',
            'problema con documentos' => 'documentos',
            'error visual en la plataforma' => 'visual',
            'consulta general' => 'consulta',
            default => 'general',
        };
    }

    private function mapPrioridadKey(string $prioridad): string
    {
        return match (mb_strtolower(trim($prioridad))) {
            'alta' => 'alta',
            'media' => 'media',
            'baja' => 'baja',
            default => 'media',
        };
    }

    private function mapEstadoKey(string $estado): string
    {
        return match (mb_strtolower(trim($estado))) {
            'pendiente' => 'pendiente',
            'en proceso' => 'en_proceso',
            'resuelto' => 'resuelto',
            default => 'pendiente',
        };
    }
}
