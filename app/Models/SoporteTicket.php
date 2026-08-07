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

    /*
    |--------------------------------------------------------------------------
    | CREAR TICKET
    |--------------------------------------------------------------------------
    */
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

        return isset($result[0])
            ? (array) $result[0]
            : [];
    }

    /*
    |--------------------------------------------------------------------------
    | TICKETS DEL ESTUDIANTE
    |--------------------------------------------------------------------------
    */
    public function obtenerTicketsPorEstudiante(?int $idPersona): Collection
    {
        if (!$idPersona) {
            return collect();
        }

        /*
        | Se mantiene el procedimiento existente para no alterar
        | el funcionamiento actual del panel del estudiante.
        */
        $rows = DB::select(
            'CALL SEL_SOPORTE(?, ?, ?)',
            [
                'ESTUDIANTE',
                $idPersona,
                null,
            ]
        );

        return collect($rows)
            ->map(function ($row) {
                return $this->normalizarFila((array) $row);
            })
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | BANDEJA DE SECRETARÍA
    |--------------------------------------------------------------------------
    | Consulta directamente tbl_soporte para garantizar que los casos
    | registrados con la carrera correspondiente aparezcan en la bandeja.
    |--------------------------------------------------------------------------
    */
    public function obtenerTicketsParaSecretaria(?int $idCarrera): Collection
    {
        if (!$idCarrera) {
            return collect();
        }

        $rows = DB::table('tbl_soporte as s')
            ->join(
                'tbl_persona as p',
                'p.id_persona',
                '=',
                's.id_persona_solicitante'
            )
            ->leftJoin(
                'tbl_carrera as c',
                'c.id_carrera',
                '=',
                's.id_carrera'
            )
            ->where('s.estado', 1)
            ->where('s.id_carrera', $idCarrera)
            ->select([
                's.id_soporte',
                's.codigo',
                's.id_persona_solicitante',
                's.id_usuario_asignado',
                's.id_carrera',

                'p.nombre_persona as usuario',

                DB::raw("
                    COALESCE(
                        p.correo_institucional,
                        ''
                    ) as correo
                "),

                DB::raw("
                    COALESCE(
                        c.nombre_carrera,
                        s.carrera,
                        'Sin carrera relacionada'
                    ) as carrera
                "),

                's.tipo',

                DB::raw("
                    CASE
                        WHEN LOWER(TRIM(s.tipo)) = 'acceso al sistema'
                            THEN 'acceso'
                        WHEN LOWER(TRIM(s.tipo)) = 'problema con trámite'
                            THEN 'tramite'
                        WHEN LOWER(TRIM(s.tipo)) = 'problema con documentos'
                            THEN 'documentos'
                        WHEN LOWER(TRIM(s.tipo)) = 'error visual en la plataforma'
                            THEN 'visual'
                        WHEN LOWER(TRIM(s.tipo)) = 'consulta general'
                            THEN 'consulta'
                        ELSE 'general'
                    END as tipo_key
                "),

                's.prioridad',

                DB::raw("
                    CASE
                        WHEN LOWER(TRIM(s.prioridad)) = 'alta'
                            THEN 'alta'
                        WHEN LOWER(TRIM(s.prioridad)) = 'baja'
                            THEN 'baja'
                        ELSE 'media'
                    END as prioridad_key
                "),

                DB::raw("
                    COALESCE(
                        s.estado_soporte,
                        'Pendiente'
                    ) as estado
                "),

                DB::raw("
                    CASE
                        WHEN LOWER(TRIM(s.estado_soporte)) = 'en proceso'
                            THEN 'en_proceso'
                        WHEN LOWER(TRIM(s.estado_soporte)) = 'resuelto'
                            THEN 'resuelto'
                        ELSE 'pendiente'
                    END as estado_key
                "),

                DB::raw("
                    COALESCE(
                        s.canal,
                        'Portal estudiantil'
                    ) as canal
                "),

                DB::raw("
                    DATE_FORMAT(
                        s.fecha_creacion,
                        '%Y-%m-%d %H:%i'
                    ) as fecha
                "),

                's.descripcion',

                DB::raw("
                    COALESCE(
                        s.observacion_secretaria,
                        'Pendiente de revisión por secretaría.'
                    ) as solucion_sugerida
                "),

                DB::raw("
                    COALESCE(
                        s.modulo,
                        'Otro'
                    ) as modulo
                "),

                DB::raw("
                    COALESCE(
                        s.asunto,
                        s.tipo,
                        'Solicitud de soporte'
                    ) as asunto
                "),
            ])
            ->orderByRaw("
                CASE
                    WHEN LOWER(TRIM(s.estado_soporte)) = 'pendiente' THEN 1
                    WHEN LOWER(TRIM(s.estado_soporte)) = 'en proceso' THEN 2
                    WHEN LOWER(TRIM(s.estado_soporte)) = 'resuelto' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('s.fecha_creacion')
            ->get();

        return $rows
            ->map(function ($row) {
                return $this->normalizarFila((array) $row);
            })
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER TICKET POR ID
    |--------------------------------------------------------------------------
    */
    public function obtenerTicketPorId(int $idSoporte): ?array
    {
        $row = DB::table('tbl_soporte as s')
            ->join(
                'tbl_persona as p',
                'p.id_persona',
                '=',
                's.id_persona_solicitante'
            )
            ->leftJoin(
                'tbl_carrera as c',
                'c.id_carrera',
                '=',
                's.id_carrera'
            )
            ->select([
                's.id_soporte',
                's.codigo',

                'p.nombre_persona as usuario',

                DB::raw("
                    COALESCE(
                        p.correo_institucional,
                        ''
                    ) as correo
                "),

                DB::raw("
                    COALESCE(
                        c.nombre_carrera,
                        s.carrera,
                        'Sin carrera relacionada'
                    ) as carrera
                "),

                's.id_carrera',
                's.tipo',
                's.prioridad',

                DB::raw("
                    LOWER(
                        COALESCE(s.prioridad, 'Media')
                    ) as prioridad_key
                "),

                DB::raw("
                    COALESCE(
                        s.estado_soporte,
                        'Pendiente'
                    ) as estado
                "),

                DB::raw("
                    CASE
                        WHEN LOWER(TRIM(s.estado_soporte)) = 'pendiente'
                            THEN 'pendiente'
                        WHEN LOWER(TRIM(s.estado_soporte)) = 'en proceso'
                            THEN 'en_proceso'
                        WHEN LOWER(TRIM(s.estado_soporte)) = 'resuelto'
                            THEN 'resuelto'
                        ELSE 'pendiente'
                    END as estado_key
                "),

                DB::raw("
                    COALESCE(
                        s.canal,
                        'Portal estudiantil'
                    ) as canal
                "),

                DB::raw("
                    DATE_FORMAT(
                        s.fecha_creacion,
                        '%Y-%m-%d %H:%i'
                    ) as fecha
                "),

                's.descripcion',

                DB::raw("
                    COALESCE(
                        s.observacion_secretaria,
                        'Pendiente de revisión por secretaría.'
                    ) as solucion_sugerida
                "),

                DB::raw("
                    COALESCE(
                        s.modulo,
                        'Otro'
                    ) as modulo
                "),

                DB::raw("
                    COALESCE(
                        s.asunto,
                        s.tipo,
                        'Solicitud de soporte'
                    ) as asunto
                "),

                's.id_persona_solicitante',
                's.id_usuario_asignado',
            ])
            ->where('s.id_soporte', $idSoporte)
            ->where('s.estado', 1)
            ->first();

        return $row
            ? $this->normalizarFila((array) $row)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR ESTADO
    |--------------------------------------------------------------------------
    */
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

        return isset($result[0])
            ? (array) $result[0]
            : [];
    }

    /*
    |--------------------------------------------------------------------------
    | RESUMEN
    |--------------------------------------------------------------------------
    */
    public function obtenerResumen(Collection $tickets): array
    {
        return [
            'total' => $tickets->count(),

            'pendientes' => $tickets
                ->where('estado_key', 'pendiente')
                ->count(),

            'en_proceso' => $tickets
                ->where('estado_key', 'en_proceso')
                ->count(),

            'resueltos' => $tickets
                ->where('estado_key', 'resuelto')
                ->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR FILA
    |--------------------------------------------------------------------------
    */
    private function normalizarFila(array $ticket): array
    {
        $ticket['id_soporte'] = isset($ticket['id_soporte'])
            ? (int) $ticket['id_soporte']
            : null;

        $ticket['id_persona_solicitante'] =
            isset($ticket['id_persona_solicitante'])
                ? (int) $ticket['id_persona_solicitante']
                : null;

        $ticket['id_usuario_asignado'] =
            isset($ticket['id_usuario_asignado'])
                && $ticket['id_usuario_asignado'] !== null
                    ? (int) $ticket['id_usuario_asignado']
                    : null;

        $ticket['id_carrera'] =
            isset($ticket['id_carrera'])
                && $ticket['id_carrera'] !== null
                    ? (int) $ticket['id_carrera']
                    : null;

        $ticket['codigo'] =
            $ticket['codigo']
            ?? '';

        $ticket['usuario'] =
            $ticket['usuario']
            ?? 'Alumno';

        $ticket['correo'] =
            $ticket['correo']
            ?? '';

        $ticket['carrera'] =
            $ticket['carrera']
            ?? 'Sin carrera relacionada';

        $ticket['tipo'] =
            $ticket['tipo']
            ?? 'Consulta general';

        $ticket['tipo_key'] =
            $ticket['tipo_key']
            ?? $this->mapTipoKey(
                (string) $ticket['tipo']
            );

        $ticket['prioridad'] =
            $ticket['prioridad']
            ?? 'Media';

        $ticket['prioridad_key'] =
            $ticket['prioridad_key']
            ?? $this->mapPrioridadKey(
                (string) $ticket['prioridad']
            );

        $ticket['estado'] =
            $ticket['estado']
            ?? 'Pendiente';

        $ticket['estado_key'] =
            $ticket['estado_key']
            ?? $this->mapEstadoKey(
                (string) $ticket['estado']
            );

        $ticket['canal'] =
            $ticket['canal']
            ?? 'Portal estudiantil';

        $ticket['fecha'] =
            $ticket['fecha']
            ?? now()->format('Y-m-d H:i');

        $ticket['descripcion'] =
            $ticket['descripcion']
            ?? '';

        $ticket['solucion_sugerida'] =
            $ticket['solucion_sugerida']
            ?? 'Pendiente de revisión por secretaría.';

        $ticket['modulo'] =
            $ticket['modulo']
            ?? 'Otro';

        $ticket['asunto'] =
            $ticket['asunto']
            ?? $ticket['tipo'];

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