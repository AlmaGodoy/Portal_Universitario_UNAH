<?php

namespace App\Http\Controllers;

use App\Models\SoporteTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoporteController extends Controller
{
    public function vista()
    {
        $rol = $this->obtenerRolActual();

        if ($rol === 'secretario') {
            return view('soporte_secretaria');
        }

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
                    ['label' => 'Soporte', 'value' => 'Soporte'],
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

        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar la persona autenticada.',
            ], 401);
        }

        $idCarrera = $this->obtenerIdCarreraEstudiante($idPersona);
        $nombreCarrera = $validated['carrera'] ?? $this->obtenerNombreCarreraPersona($idPersona);

        $model = new SoporteTicket();

        $resultado = $model->crearTicket([
            'id_persona_solicitante' => $idPersona,
            'id_carrera' => $idCarrera,
            'asunto' => $validated['asunto'],
            'tipo' => $validated['tipo'],
            'prioridad' => $validated['prioridad'],
            'modulo' => $validated['modulo'],
            'descripcion' => $validated['descripcion'],
            'canal' => $validated['canal'] ?? 'Portal estudiantil',
            'carrera' => $nombreCarrera,
        ]);

        if (($resultado['resultado'] ?? null) !== 'OK') {
            return response()->json([
                'ok' => false,
                'message' => $resultado['mensaje'] ?? 'No fue posible crear la solicitud de soporte.',
            ], 500);
        }

        $ticket = null;
        if (!empty($resultado['id_soporte'])) {
            $ticket = $model->obtenerTicketPorId((int) $resultado['id_soporte']);
        }

        return response()->json([
            'ok' => true,
            'message' => $resultado['mensaje'] ?? 'Solicitud de soporte creada correctamente.',
            'data' => $ticket,
        ], 201);
    }

    public function misSolicitudes(): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar la persona autenticada.',
            ], 401);
        }

        $model = new SoporteTicket();
        $tickets = $model->obtenerTicketsPorEstudiante($idPersona);

        return response()->json([
            'ok' => true,
            'data' => $tickets->values(),
            'total' => $tickets->count(),
        ]);
    }

    public function verMiSolicitud(int $idSoporte): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar la persona autenticada.',
            ], 401);
        }

        $model = new SoporteTicket();
        $ticket = $model->obtenerTicketPorId($idSoporte);

        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        if ((int) ($ticket['id_persona_solicitante'] ?? 0) !== (int) $idPersona) {
            return response()->json([
                'ok' => false,
                'message' => 'No tiene permisos para consultar esta solicitud.',
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'data' => $ticket,
        ]);
    }

    public function bandejaSecretaria(): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();
        $idCarrera = $this->obtenerIdCarreraSecretaria($idPersona);

        $model = new SoporteTicket();
        $tickets = $model->obtenerTicketsParaSecretaria($idCarrera);

        return response()->json([
            'ok' => true,
            'data' => $tickets->values(),
            'resumen' => $model->obtenerResumen($tickets),
        ]);
    }

    public function verParaSecretaria(int $idSoporte): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();
        $idCarrera = $this->obtenerIdCarreraSecretaria($idPersona);

        $model = new SoporteTicket();
        $ticket = $model->obtenerTicketPorId($idSoporte);

        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        if ($idCarrera !== null && (int) ($ticket['id_carrera'] ?? 0) !== (int) $idCarrera) {
            return response()->json([
                'ok' => false,
                'message' => 'No tiene permisos para consultar esta solicitud.',
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'data' => $ticket,
        ]);
    }

    public function tomarCaso(int $idSoporte): JsonResponse
    {
        return $this->actualizarEstadoSoporte(
            $idSoporte,
            'En proceso',
            'Caso tomado correctamente.'
        );
    }

    public function resolver(int $idSoporte): JsonResponse
    {
        return $this->actualizarEstadoSoporte(
            $idSoporte,
            'Resuelto',
            'Caso marcado como resuelto.'
        );
    }

    private function actualizarEstadoSoporte(
        int $idSoporte,
        string $estado,
        string $mensajeOk
    ): JsonResponse {
        $idPersona = $this->obtenerIdPersonaAutenticada();
        $idCarrera = $this->obtenerIdCarreraSecretaria($idPersona);
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        $model = new SoporteTicket();
        $ticket = $model->obtenerTicketPorId($idSoporte);

        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        if ($idCarrera !== null && (int) ($ticket['id_carrera'] ?? 0) !== (int) $idCarrera) {
            return response()->json([
                'ok' => false,
                'message' => 'No tiene permisos para actualizar esta solicitud.',
            ], 403);
        }

        $resultado = $model->actualizarEstado(
            $idSoporte,
            $estado,
            $idUsuario,
            $mensajeOk
        );

        if (($resultado['resultado'] ?? null) !== 'OK') {
            return response()->json([
                'ok' => false,
                'message' => $resultado['mensaje'] ?? 'No fue posible actualizar el estado.',
            ], 500);
        }

        $ticketActualizado = $model->obtenerTicketPorId($idSoporte);

        return response()->json([
            'ok' => true,
            'message' => $mensajeOk,
            'data' => $ticketActualizado,
        ]);
    }

    private function obtenerIdPersonaAutenticada(): ?int
    {
        $usuario = Auth::user();

        if ($usuario && !empty($usuario->id_persona)) {
            return (int) $usuario->id_persona;
        }

        if ($usuario && isset($usuario->persona) && !empty($usuario->persona->id_persona)) {
            return (int) $usuario->persona->id_persona;
        }

        if ($usuario && !empty($usuario->id_usuario)) {
            $registro = DB::table('tbl_usuario')
                ->where('id_usuario', $usuario->id_usuario)
                ->select('id_persona')
                ->first();

            if ($registro && !empty($registro->id_persona)) {
                return (int) $registro->id_persona;
            }
        }

        if ($usuario && !empty($usuario->id)) {
            $registro = DB::table('tbl_usuario')
                ->where('id_usuario', $usuario->id)
                ->select('id_persona')
                ->first();

            if ($registro && !empty($registro->id_persona)) {
                return (int) $registro->id_persona;
            }
        }

        if (session()->has('id_persona')) {
            return (int) session('id_persona');
        }

        if (session()->has('usuario.id_persona')) {
            return (int) session('usuario.id_persona');
        }

        return null;
    }

    private function obtenerIdUsuarioAutenticado(): ?int
    {
        $usuario = Auth::user();

        if ($usuario && !empty($usuario->id_usuario)) {
            return (int) $usuario->id_usuario;
        }

        if ($usuario && !empty($usuario->id)) {
            return (int) $usuario->id;
        }

        if (session()->has('id_usuario')) {
            return (int) session('id_usuario');
        }

        if (session()->has('usuario.id_usuario')) {
            return (int) session('usuario.id_usuario');
        }

        return null;
    }

    private function obtenerRolActual(): ?string
    {
        $usuario = Auth::user();

        if (session()->has('rol_texto')) {
            return strtolower(trim((string) session('rol_texto')));
        }

        if ($usuario && !empty($usuario->rol_texto)) {
            return strtolower(trim((string) $usuario->rol_texto));
        }

        if ($usuario && !empty($usuario->role)) {
            return strtolower(trim((string) $usuario->role));
        }

        return null;
    }

    private function obtenerIdCarreraEstudiante(?int $idPersona): ?int
    {
        if (!$idPersona) {
            return null;
        }

        $registro = DB::table('tbl_estudiante')
            ->where('id_persona', $idPersona)
            ->select('id_carrera')
            ->first();

        return $registro && !empty($registro->id_carrera)
            ? (int) $registro->id_carrera
            : null;
    }

    private function obtenerIdCarreraSecretaria(?int $idPersona): ?int
    {
        if (!$idPersona) {
            return null;
        }

        $resultado = DB::select('CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)', [$idPersona]);

        if (!empty($resultado[0]) && isset($resultado[0]->resultado) && $resultado[0]->resultado === 'OK') {
            return !empty($resultado[0]->id_carrera)
                ? (int) $resultado[0]->id_carrera
                : null;
        }

        return null;
    }

    private function obtenerNombreCarreraPersona(?int $idPersona): ?string
    {
        if (!$idPersona) {
            return null;
        }

        $registro = DB::table('tbl_estudiante as e')
            ->leftJoin('tbl_carrera as c', 'c.id_carrera', '=', 'e.id_carrera')
            ->where('e.id_persona', $idPersona)
            ->select('c.nombre_carrera')
            ->first();

        return $registro->nombre_carrera ?? null;
    }
}
