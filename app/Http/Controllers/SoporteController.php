<?php

namespace App\Http\Controllers;

use App\Models\SoporteTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Throwable;

class SoporteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VISTA GENERAL DE SOPORTE
    |--------------------------------------------------------------------------
    | Estudiante: carga soporte.index o soporte.
    | Empleados: redirige a soporte.secretaria.
    */
    public function vista()
    {
        $idRol = $this->obtenerIdRolActual();
        $rolTexto = $this->obtenerRolActual();

        if (
            in_array($idRol, [1, 4, 5], true)
            || in_array($rolTexto, [
                'secretario',
                'secretaria',
                'coordinador',
                'coordinadora',
                'secretaria academica',
                'secretaría académica',
                'secretaria general',
                'secretaría general',
            ], true)
        ) {
            return Route::has('soporte.secretaria')
                ? redirect()->route('soporte.secretaria')
                : redirect('/soporte/secretaria');
        }

        return $this->renderizarVistaDisponible([
            'soporte.index',
            'soporte',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SOPORTE PARA EMPLEADOS
    |--------------------------------------------------------------------------
    */
    public function vistaSecretaria()
    {
        return $this->renderizarVistaDisponible([
            'soporte.secretaria',
            'soporte_secretaria',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS
    |--------------------------------------------------------------------------
    */
    public function catalogos(): JsonResponse
    {
        return $this->respuestaJson([
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

    /*
    |--------------------------------------------------------------------------
    | CREAR SOLICITUD
    |--------------------------------------------------------------------------
    */
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
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se pudo identificar la persona autenticada.',
            ], 401);
        }

        /*
        | Obtiene ID y nombre de carrera desde el mismo registro.
        | La carrera enviada por el formulario no se utiliza como fuente principal.
        */
        $carreraEstudiante = $this->obtenerCarreraEstudiante($idPersona);

        if (!$carreraEstudiante) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se encontró una carrera válida para el estudiante autenticado.',
            ], 422);
        }

        $idCarrera = (int) $carreraEstudiante->id_carrera;
        $nombreCarrera = trim((string) $carreraEstudiante->nombre_carrera);

        $model = new SoporteTicket();

        DB::beginTransaction();

        try {
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
                DB::rollBack();

                return $this->respuestaJson([
                    'ok' => false,
                    'message' => $resultado['mensaje']
                        ?? 'No fue posible crear la solicitud de soporte.',
                ], 500);
            }

            /*
            | INS_SOPORTE debe devolver id_soporte. Si no lo devuelve,
            | se localiza el último ticket creado por la misma persona.
            */
            $idSoporte = !empty($resultado['id_soporte'])
                ? (int) $resultado['id_soporte']
                : null;

            if (!$idSoporte) {
                $idSoporte = DB::table('tbl_soporte')
                    ->where('id_persona_solicitante', $idPersona)
                    ->where('asunto', $validated['asunto'])
                    ->where('estado', 1)
                    ->orderByDesc('id_soporte')
                    ->value('id_soporte');

                $idSoporte = $idSoporte
                    ? (int) $idSoporte
                    : null;
            }

            if (!$idSoporte) {
                DB::rollBack();

                return $this->respuestaJson([
                    'ok' => false,
                    'message' => 'La solicitud fue procesada, pero no fue posible identificar el ticket creado.',
                ], 500);
            }

            /*
            | Corrección definitiva:
            | verifica y fuerza la carrera realmente almacenada.
            | Esto evita que un procedimiento desactualizado guarde otra carrera.
            */
            DB::table('tbl_soporte')
                ->where('id_soporte', $idSoporte)
                ->update([
                    'id_carrera' => $idCarrera,
                    'carrera' => $nombreCarrera,
                ]);

            $registroGuardado = DB::table('tbl_soporte')
                ->where('id_soporte', $idSoporte)
                ->select([
                    'id_soporte',
                    'id_persona_solicitante',
                    'id_carrera',
                    'carrera',
                ])
                ->first();

            if (
                !$registroGuardado
                || (int) $registroGuardado->id_carrera !== $idCarrera
            ) {
                DB::rollBack();

                return $this->respuestaJson([
                    'ok' => false,
                    'message' => 'La solicitud no pudo asociarse correctamente a la carrera del estudiante.',
                ], 500);
            }

            DB::commit();

            $ticket = $model->obtenerTicketPorId($idSoporte);

            return $this->respuestaJson([
                'ok' => true,
                'message' => $resultado['mensaje']
                    ?? 'Solicitud de soporte creada correctamente.',
                'data' => $ticket,
                'id_soporte' => $idSoporte,
                'id_carrera' => $idCarrera,
                'carrera' => $nombreCarrera,
                'actualizado_en' => now()->toIso8601String(),
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return $this->respuestaJson([
                'ok' => false,
                'message' => 'Ocurrió un error al crear y dirigir la solicitud de soporte.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SOLICITUDES DEL ESTUDIANTE
    |--------------------------------------------------------------------------
    */
    public function misSolicitudes(): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se pudo identificar la persona autenticada.',
            ], 401);
        }

        $model = new SoporteTicket();
        $tickets = $model->obtenerTicketsPorEstudiante($idPersona);

        return $this->respuestaJson([
            'ok' => true,
            'data' => $tickets->values(),
            'total' => $tickets->count(),
            'actualizado_en' => now()->toIso8601String(),
        ]);
    }

    public function verMiSolicitud(int $idSoporte): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se pudo identificar la persona autenticada.',
            ], 401);
        }

        $model = new SoporteTicket();
        $ticket = $model->obtenerTicketPorId($idSoporte);

        if (!$ticket) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        if (
            (int) ($ticket['id_persona_solicitante'] ?? 0)
            !== (int) $idPersona
        ) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No tiene permisos para consultar esta solicitud.',
            ], 403);
        }

        return $this->respuestaJson([
            'ok' => true,
            'data' => $ticket,
            'actualizado_en' => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BANDEJA DE SECRETARÍA
    |--------------------------------------------------------------------------
    */
    public function bandejaSecretaria(): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se pudo identificar a la persona autenticada.',
            ], 401);
        }

        $idCarrera = $this->obtenerIdCarreraSecretaria($idPersona);

        /*
        | Secretaría de Carrera debe tener una carrera asignada.
        | Evita que un valor null muestre todos los tickets o ninguno.
        */
        if (!$idCarrera && $this->obtenerIdRolActual() === 5) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'La Secretaría de Carrera autenticada no tiene una carrera asignada.',
                'data' => [],
                'resumen' => [
                    'total' => 0,
                    'pendientes' => 0,
                    'en_proceso' => 0,
                    'resueltos' => 0,
                ],
            ], 422);
        }

        $model = new SoporteTicket();
        $tickets = $model->obtenerTicketsParaSecretaria($idCarrera);

        return $this->respuestaJson([
            'ok' => true,
            'data' => $tickets->values(),
            'total' => $tickets->count(),
            'resumen' => $model->obtenerResumen($tickets),
            'id_carrera' => $idCarrera,
            'carrera' => $idCarrera
                ? $this->obtenerNombreCarreraPorId($idCarrera)
                : null,
            'actualizado_en' => now()->toIso8601String(),
        ]);
    }

    public function verParaSecretaria(int $idSoporte): JsonResponse
    {
        $idPersona = $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se pudo identificar a la persona autenticada.',
            ], 401);
        }

        $idCarrera = $this->obtenerIdCarreraSecretaria($idPersona);

        if (!$idCarrera && $this->obtenerIdRolActual() === 5) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'La Secretaría de Carrera autenticada no tiene una carrera asignada.',
            ], 422);
        }

        $model = new SoporteTicket();
        $ticket = $model->obtenerTicketPorId($idSoporte);

        if (!$ticket) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        if (
            $idCarrera !== null
            && (int) ($ticket['id_carrera'] ?? 0) !== (int) $idCarrera
        ) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No tiene permisos para consultar esta solicitud.',
            ], 403);
        }

        return $this->respuestaJson([
            'ok' => true,
            'data' => $ticket,
            'actualizado_en' => now()->toIso8601String(),
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

        if (!$idPersona) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'No se pudo identificar a la persona autenticada.',
            ], 401);
        }

        $idCarrera = $this->obtenerIdCarreraSecretaria($idPersona);
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idCarrera && $this->obtenerIdRolActual() === 5) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'La Secretaría de Carrera autenticada no tiene una carrera asignada.',
            ], 422);
        }

        $model = new SoporteTicket();
        $ticket = $model->obtenerTicketPorId($idSoporte);

        if (!$ticket) {
            return $this->respuestaJson([
                'ok' => false,
                'message' => 'Solicitud no encontrada.',
            ], 404);
        }

        if (
            $idCarrera !== null
            && (int) ($ticket['id_carrera'] ?? 0) !== (int) $idCarrera
        ) {
            return $this->respuestaJson([
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
            return $this->respuestaJson([
                'ok' => false,
                'message' => $resultado['mensaje']
                    ?? 'No fue posible actualizar el estado.',
            ], 500);
        }

        $ticketActualizado = $model->obtenerTicketPorId($idSoporte);

        return $this->respuestaJson([
            'ok' => true,
            'message' => $mensajeOk,
            'data' => $ticketActualizado,
            'actualizado_en' => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DE AUTENTICACIÓN
    |--------------------------------------------------------------------------
    */
    private function obtenerIdPersonaAutenticada(): ?int
    {
        $usuario = Auth::user();

        if ($usuario && !empty($usuario->id_persona)) {
            return (int) $usuario->id_persona;
        }

        if (
            $usuario
            && isset($usuario->persona)
            && !empty($usuario->persona->id_persona)
        ) {
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

    private function obtenerIdRolActual(): ?int
    {
        $usuario = Auth::user();

        if ($usuario && isset($usuario->id_rol)) {
            return (int) $usuario->id_rol;
        }

        if (session()->has('id_rol')) {
            return (int) session('id_rol');
        }

        if (session()->has('usuario.id_rol')) {
            return (int) session('usuario.id_rol');
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

    /*
    |--------------------------------------------------------------------------
    | CARRERA DEL ESTUDIANTE
    |--------------------------------------------------------------------------
    | Devuelve el ID y el nombre desde el mismo registro.
    | Si existen registros duplicados, utiliza el de id_estudiante más reciente.
    */
    private function obtenerCarreraEstudiante(?int $idPersona): ?object
    {
        if (!$idPersona) {
            return null;
        }

        return DB::table('tbl_estudiante as e')
            ->join(
                'tbl_carrera as c',
                'c.id_carrera',
                '=',
                'e.id_carrera'
            )
            ->where('e.id_persona', $idPersona)
            ->whereNotNull('e.id_carrera')
            ->select([
                'e.id_estudiante',
                'e.id_carrera',
                'c.nombre_carrera',
            ])
            ->orderByDesc('e.id_estudiante')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | CARRERA DE LA SECRETARÍA
    |--------------------------------------------------------------------------
    | Primero consulta directamente tbl_empleados.
    | Si no obtiene resultado, usa el procedimiento existente.
    */
    private function obtenerIdCarreraSecretaria(?int $idPersona): ?int
    {
        if (!$idPersona) {
            return null;
        }

        $empleado = DB::table('tbl_empleados')
            ->where('id_persona', $idPersona)
            ->whereNotNull('id_carrera')
            ->select('id_carrera')
            ->first();

        if ($empleado && !empty($empleado->id_carrera)) {
            return (int) $empleado->id_carrera;
        }

        try {
            $resultado = DB::select(
                'CALL SEL_CARRERA_EMPLEADO_POR_PERSONA(?)',
                [$idPersona]
            );

            if (
                !empty($resultado[0])
                && isset($resultado[0]->resultado)
                && strtoupper((string) $resultado[0]->resultado) === 'OK'
                && !empty($resultado[0]->id_carrera)
            ) {
                return (int) $resultado[0]->id_carrera;
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    private function obtenerNombreCarreraPorId(?int $idCarrera): ?string
    {
        if (!$idCarrera) {
            return null;
        }

        return DB::table('tbl_carrera')
            ->where('id_carrera', $idCarrera)
            ->value('nombre_carrera');
    }

    /*
    |--------------------------------------------------------------------------
    | RESPUESTA JSON SIN CACHÉ
    |--------------------------------------------------------------------------
    | Permite que cada consulta de la bandeja obtenga los datos más recientes.
    */
    private function respuestaJson(
        array $contenido,
        int $estado = 200
    ): JsonResponse {
        return response()
            ->json($contenido, $estado)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function renderizarVistaDisponible(array $vistas)
    {
        foreach ($vistas as $vista) {
            if (View::exists($vista)) {
                return view($vista);
            }
        }

        return view($vistas[0]);
    }
}