<?php

namespace App\Http\Controllers;

use App\Models\SoporteTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SoporteController extends Controller
{
    public function vista()
    {
        return view('soporte');
    }

    public function catalogos(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => [
                'tipos' => [
                    ['label' => 'Acceso al sistema', 'value' => 'Acceso al sistema'],
                    ['label' => 'Problema con trámite', 'value' => 'Problema con trámite'],
                    ['label' => 'Problema con documentos', 'value' => 'Problema con documentos'],
                    ['label' => 'Error visual en la plataforma', 'value' => 'Error visual en la plataforma'],
                    ['label' => 'Consulta general', 'value' => 'Consulta general'],
                ],
                'prioridades' => [
                    ['label' => 'Alta', 'value' => 'Alta'],
                    ['label' => 'Media', 'value' => 'Media'],
                    ['label' => 'Baja', 'value' => 'Baja'],
                ],
                'modulos' => [
                    ['label' => 'Panel institucional', 'value' => 'Panel institucional'],
                    ['label' => 'Equivalencias', 'value' => 'Equivalencias'],
                    ['label' => 'Mis trámites', 'value' => 'Mis trámites'],
                    ['label' => 'Configuración', 'value' => 'Configuración'],
                    ['label' => 'Otro', 'value' => 'Otro'],
                ],
                'estados' => [
                    ['label' => 'Pendiente', 'value' => 'Pendiente'],
                    ['label' => 'En proceso', 'value' => 'En proceso'],
                    ['label' => 'Resuelto', 'value' => 'Resuelto'],
                ],
            ],
        ]);
    }

    public function crear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asunto' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'string', 'max:100'],
            'prioridad' => ['required', 'string', 'max:20'],
            'modulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'canal' => ['nullable', 'string', 'max:100'],
            'carrera' => ['nullable', 'string', 'max:150'],
        ]);

        $tickets = $this->ticketsCombinados();
        $nextId = ((int) ($tickets->max('id_soporte') ?? 0)) + 1;

        $nuevoTicket = [
            'id_soporte' => $nextId,
            'codigo' => 'ST-' . now()->format('Y') . '-' . str_pad((string) $nextId, 3, '0', STR_PAD_LEFT),
            'usuario' => $this->obtenerNombreUsuario(),
            'correo' => $this->obtenerCorreoUsuario(),
            'carrera' => $validated['carrera'] ?? 'Sin carrera relacionada',
            'tipo' => $validated['tipo'],
            'tipo_key' => $this->mapTipoKey($validated['tipo']),
            'prioridad' => $validated['prioridad'],
            'prioridad_key' => $this->mapPrioridadKey($validated['prioridad']),
            'estado' => 'Pendiente',
            'estado_key' => 'pendiente',
            'canal' => $validated['canal'] ?? 'Portal estudiantil',
            'fecha' => now()->format('Y-m-d H:i'),
            'descripcion' => $validated['descripcion'],
            'solucion_sugerida' => 'Solicitud registrada correctamente. Pendiente de revisión.',
            'modulo' => $validated['modulo'],
            'asunto' => $validated['asunto'],
            'id_persona_solicitante' => session('id_persona'),
        ];

        $creados = session('soporte_tickets_extra', []);
        $creados[] = $nuevoTicket;

        session([
            'soporte_tickets_extra' => $creados,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Solicitud de soporte creada correctamente.',
            'data' => $nuevoTicket,
        ], 201);
    }

    public function misSolicitudes(): JsonResponse
    {
        $tickets = $this->ticketsCombinados();
        $idPersona = session('id_persona');
        $correo = $this->obtenerCorreoUsuario();

        $filtrados = $tickets->filter(function ($ticket) use ($idPersona, $correo) {
            $matchPersona = false;
            $matchCorreo = false;

            if ($idPersona !== null && isset($ticket['id_persona_solicitante'])) {
                $matchPersona = (string) $ticket['id_persona_solicitante'] === (string) $idPersona;
            }

            if (!empty($correo) && !empty($ticket['correo'])) {
                $matchCorreo = mb_strtolower($ticket['correo']) === mb_strtolower($correo);
            }

            return $matchPersona || $matchCorreo;
        })->values();

        /*
        |----------------------------------------------------------------------
        | Mientras estás en demo, si no encuentra tickets propios,
        | devuelve todos para que puedas seguir probando.
        |----------------------------------------------------------------------
        */
        if ($filtrados->isEmpty()) {
            $filtrados = $tickets->values();
        }

        return response()->json([
            'ok' => true,
            'data' => $filtrados,
            'total' => $filtrados->count(),
        ]);
    }

    public function verMiSolicitud(int $idSoporte): JsonResponse
    {
        $ticket = $this->ticketsCombinados()->firstWhere('id_soporte', $idSoporte);

        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $ticket,
        ]);
    }

    public function bandejaSecretaria(): JsonResponse
    {
        $tickets = $this->ticketsCombinados()->values();

        return response()->json([
            'ok' => true,
            'data' => $tickets,
            'resumen' => [
                'total' => $tickets->count(),
                'pendientes' => $tickets->where('estado_key', 'pendiente')->count(),
                'en_proceso' => $tickets->where('estado_key', 'en_proceso')->count(),
                'resueltos' => $tickets->where('estado_key', 'resuelto')->count(),
            ],
        ]);
    }

    public function verParaSecretaria(int $idSoporte): JsonResponse
    {
        $ticket = $this->ticketsCombinados()->firstWhere('id_soporte', $idSoporte);

        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $ticket,
        ]);
    }

    public function tomarCaso(int $idSoporte): JsonResponse
    {
        return $this->actualizarEstado(
            $idSoporte,
            'En proceso',
            'en_proceso',
            'Caso tomado correctamente.'
        );
    }

    public function resolver(int $idSoporte): JsonResponse
    {
        return $this->actualizarEstado(
            $idSoporte,
            'Resuelto',
            'resuelto',
            'Caso marcado como resuelto.'
        );
    }

    private function actualizarEstado(
        int $idSoporte,
        string $estado,
        string $estadoKey,
        string $mensaje
    ): JsonResponse {
        $ticket = $this->ticketsCombinados()->firstWhere('id_soporte', $idSoporte);

        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        $this->guardarOverride($idSoporte, [
            'estado' => $estado,
            'estado_key' => $estadoKey,
        ]);

        $actualizado = array_merge($ticket, [
            'estado' => $estado,
            'estado_key' => $estadoKey,
        ]);

        return response()->json([
            'ok' => true,
            'message' => $mensaje,
            'data' => $actualizado,
        ]);
    }

    private function ticketsBase(): Collection
    {
        $idPersona = session('id_persona');

        $model = new SoporteTicket();

        return $model->obtenerTicketsPorEmpleado($idPersona)
            ->values()
            ->map(function ($ticket, $index) {
                $ticket['id_soporte'] = $ticket['id_soporte'] ?? ($index + 1);
                $ticket['asunto'] = $ticket['asunto'] ?? $ticket['tipo'];
                $ticket['modulo'] = $ticket['modulo'] ?? 'Portal estudiantil';
                $ticket['id_persona_solicitante'] = $ticket['id_persona_solicitante'] ?? null;

                return $ticket;
            });
    }

    private function ticketsCombinados(): Collection
    {
        $base = $this->ticketsBase();
        $extra = collect(session('soporte_tickets_extra', []));

        $todos = $base
            ->concat($extra)
            ->values()
            ->map(function ($ticket, $index) {
                $ticket['id_soporte'] = $ticket['id_soporte'] ?? ($index + 1);
                $ticket['asunto'] = $ticket['asunto'] ?? $ticket['tipo'];
                $ticket['modulo'] = $ticket['modulo'] ?? 'Portal estudiantil';

                return $ticket;
            });

        $overrides = session('soporte_ticket_overrides', []);

        return $todos->map(function ($ticket) use ($overrides) {
            $id = $ticket['id_soporte'];

            if (isset($overrides[$id]) && is_array($overrides[$id])) {
                return array_merge($ticket, $overrides[$id]);
            }

            return $ticket;
        })->values();
    }

    private function guardarOverride(int $idSoporte, array $datos): void
    {
        $overrides = session('soporte_ticket_overrides', []);

        $overrides[$idSoporte] = array_merge($overrides[$idSoporte] ?? [], $datos);

        session([
            'soporte_ticket_overrides' => $overrides,
        ]);
    }

    private function obtenerNombreUsuario(): string
    {
        $user = Auth::user();

        if (!$user) {
            return 'Alumno';
        }

        if (isset($user->persona) && $user->persona && !empty($user->persona->nombre_persona)) {
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

        return 'Alumno';
    }

    private function obtenerCorreoUsuario(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        return !empty($user->email) ? trim($user->email) : '';
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
}