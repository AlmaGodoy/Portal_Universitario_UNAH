<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class CambioCarreraController extends Controller
{
    public function crear(Request $request)
    {
        $request->validate([
            'id_persona'         => 'required|integer',
            'id_calendario'      => 'required|integer',
            'id_carrera_destino' => 'required|integer',
            'direccion'          => 'required|string|max:255',
        ]);

        try {
            DB::statement('CALL INS_CAMBIO_CARRERA(?, ?, ?, ?, ?)', [
                $request->id_persona,
                $request->id_calendario,
                $request->id_carrera_destino,
                $request->direccion,
                12 // ID de usuario para bitácora
            ]);

            $ultimoTramite = DB::table('tbl_tramite')
                ->where('id_persona', $request->id_persona)
                ->orderByDesc('id_tramite')
                ->first();

            return response()->json([
                'resultado'  => 'OK',
                'mensaje'    => 'Trámite y bitácora registrados exitosamente',
                'id_tramite' => $ultimoTramite->id_tramite ?? null
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $this->obtenerMensajeLimpio(
                    $e,
                    'No fue posible crear la solicitud de cambio de carrera.'
                )
            ], $this->obtenerCodigoHttp($e, 500));
        }
    }

    // Consultar trámite
    public function ver($codigo)
    {
        try {
            $data = DB::select('CALL SEL_CAMBIO_CARRERA(?, ?)', [
                'tramite',
                $codigo
            ]);

            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    public function estadoActual()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'Usuario no autenticado.'
                ], 401);
            }

            $idPersona = Auth::user()->id_persona ?? null;

            if (!$idPersona) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No se encontró la persona asociada al usuario autenticado.'
                ], 403);
            }

            $tramite = DB::table('tbl_tramite as t')
                ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
                ->select(
                    't.id_tramite',
                    't.id_persona',
                    't.fecha_solicitud',
                    't.direccion',
                    't.resolucion_de_tramite_academico as estado_tramite',
                    't.observacion_dictamen as dictamen',
                    'c.nombre_carrera as carrera_destino',
                    't.estado'
                )
                ->where('t.id_persona', $idPersona)
                ->where('t.tipo_tramite_academico', 'cambio_carrera')
                ->where('t.estado', 1)
                ->orderByRaw("
                    CASE
                        WHEN LOWER(t.resolucion_de_tramite_academico) = 'revision' THEN 1
                        WHEN LOWER(t.resolucion_de_tramite_academico) = 'pendiente' THEN 2
                        ELSE 3
                    END
                ")
                ->orderByDesc('t.id_tramite')
                ->first();

            if (!$tramite) {
                return response()->json([
                    'resultado' => 'OK',
                    'data'      => null,
                    'mensaje'   => 'No tienes trámites registrados.'
                ], 200);
            }

            return response()->json([
                'resultado' => 'OK',
                'data'      => $tramite
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar estado/resolución del trámite
    public function actualizarEstado(Request $request, $id_tramite)
    {
        $request->validate([
            'estado' => 'required|string|max:20',
        ]);

        try {
            $data = DB::select('CALL UPD_CAMBIO_CARRERA(?, ?)', [
                $id_tramite,
                $request->estado
            ]);

            return response()->json($data[0] ?? $data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    public function eliminar($id_tramite)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'Usuario no autenticado.'
                ], 401);
            }

            $idPersona = Auth::user()->id_persona ?? null;

            if (!$idPersona) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No se encontró la persona asociada al usuario autenticado.'
                ], 403);
            }

            $tramite = DB::table('tbl_tramite')
                ->where('id_tramite', $id_tramite)
                ->where('id_persona', $idPersona)
                ->where('tipo_tramite_academico', 'cambio_carrera')
                ->first();

            if (!$tramite) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No autorizado. El trámite no pertenece al estudiante autenticado.'
                ], 403);
            }

            $data = DB::select('CALL SOFT_DEL_CAMBIO_CARRERA(?)', [$id_tramite]);

            $respuesta = (array) ($data[0] ?? []);

            if (($respuesta['resultado'] ?? '') === 'OK') {
                $respuesta['mensaje'] = 'Trámite cancelado correctamente.';
            }

            return response()->json($respuesta, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    public function calendarioVigente()
    {
        try {
            $data = DB::select('CALL SEL_CALENDARIO_VIGENTE_CAMBIO_CARRERA()');

            return response()->json($data[0] ?? null, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function carreras()
    {
        try {
            $data = DB::select('CALL SEL_CARRERAS_ACTIVAS()');
            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }

    public function listadoSecretaria()
    {
        $idCarreraSecretaria = $this->obtenerCarreraSecretariaAutenticada();

        if (!$idCarreraSecretaria) {
            return response()->json([]);
        }

        $tramites = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')
            ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
            ->select(
                't.id_tramite',
                'p.nombre_persona as nombre_persona',
                't.fecha_solicitud',
                't.resolucion_de_tramite_academico as estado_tramite',
                'c.nombre_carrera as carrera_destino'
            )
            ->where('t.tipo_tramite_academico', 'cambio_carrera')
            ->where('t.estado', 1)
            ->whereNotIn('t.resolucion_de_tramite_academico', ['aprobada', 'rechazada'])
            ->where('t.id_carrera_destino', $idCarreraSecretaria)
            ->orderByDesc('t.id_tramite')
            ->get();

        return response()->json($tramites);
    }

    public function detalleSecretaria($id_tramite)
    {
        $idCarreraSecretaria = $this->obtenerCarreraSecretariaAutenticada();

        if (!$idCarreraSecretaria) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No tienes una carrera asignada como secretaria.'
            ], 403);
        }

        $tramite = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')
            ->leftJoin('tbl_estudiante as e', 't.id_persona', '=', 'e.id_persona')
            ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
            ->select(
                't.id_tramite',
                't.id_persona',
                't.fecha_solicitud',
                't.direccion',
                't.resolucion_de_tramite_academico as estado_tramite',
                'p.nombre_persona as estudiante',
                'c.nombre_carrera as carrera_destino',
                'e.indice_periodo',
                'e.indice_global',
                'e.cantidad_clases_aprobadas'
            )
            ->where('t.id_tramite', $id_tramite)
            ->where('t.id_carrera_destino', $idCarreraSecretaria)
            ->first();

        if (!$tramite) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No puedes revisar un trámite que no pertenece a tu carrera.'
            ], 403);
        }

        return response()->json($tramite);
    }

    public function guardarRevisionSecretaria(Request $request)
    {
        $request->validate([
            'id_tramite' => 'required|integer',
            'indice_periodo' => 'nullable|numeric',
            'indice_global' => 'nullable|numeric',
            'clases_aprobadas' => 'nullable|integer',
        ]);

        $idCarreraSecretaria = $this->obtenerCarreraSecretariaAutenticada();

        if (!$idCarreraSecretaria) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No tienes una carrera asignada como secretaria.'
            ], 403);
        }

        $tramite = DB::table('tbl_tramite')
            ->where('id_tramite', $request->id_tramite)
            ->where('id_carrera_destino', $idCarreraSecretaria)
            ->first();

        if (!$tramite) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No puedes revisar un trámite que no pertenece a tu carrera.'
            ], 403);
        }

        DB::table('tbl_estudiante')
            ->where('id_persona', $tramite->id_persona)
            ->update([
                'indice_periodo' => $request->indice_periodo,
                'indice_global' => $request->indice_global,
                'cantidad_clases_aprobadas' => $request->clases_aprobadas
            ]);

        DB::table('tbl_tramite')
            ->where('id_tramite', $request->id_tramite)
            ->update([
                'resolucion_de_tramite_academico' => 'revision'
            ]);

        return response()->json([
            'resultado' => 'OK',
            'mensaje' => 'Revisión de Secretaría guardada correctamente.'
        ]);
    }

    public function listadoCoordinacion()
    {
        $idCarreraCoordinador = $this->obtenerCarreraCoordinadorAutenticado();

        if (!$idCarreraCoordinador) {
            return response()->json([]);
        }

        $tramites = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')
            ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
            ->select(
                't.id_tramite',
                'p.nombre_persona as nombre_persona',
                't.fecha_solicitud',
                't.resolucion_de_tramite_academico as estado_tramite',
                'c.nombre_carrera as carrera_destino'
            )
            ->where('t.tipo_tramite_academico', 'cambio_carrera')
            ->where('t.estado', 1)
            ->where('t.resolucion_de_tramite_academico', 'revision')
            ->where('t.id_carrera_destino', $idCarreraCoordinador)
            ->orderByDesc('t.id_tramite')
            ->get();

        return response()->json($tramites);
    }

    public function detalleCoordinacion($id_tramite)
    {
        $idCarreraCoordinador = $this->obtenerCarreraCoordinadorAutenticado();

        if (!$idCarreraCoordinador) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No tienes una carrera asignada como coordinador.'
            ], 403);
        }

        $tramite = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 't.id_persona', '=', 'p.id_persona')
            ->leftJoin('tbl_estudiante as e', 't.id_persona', '=', 'e.id_persona')
            ->leftJoin('tbl_carrera as c', 't.id_carrera_destino', '=', 'c.id_carrera')
            ->select(
                't.id_tramite',
                't.id_persona',
                't.fecha_solicitud',
                't.direccion',
                't.resolucion_de_tramite_academico as estado_tramite',
                't.observacion_dictamen',
                'p.nombre_persona as estudiante',
                'c.nombre_carrera as carrera_destino',
                'e.indice_periodo',
                'e.indice_global',
                'e.cantidad_clases_aprobadas'
            )
            ->where('t.id_tramite', $id_tramite)
            ->where('t.id_carrera_destino', $idCarreraCoordinador)
            ->first();

        if (!$tramite) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No puedes revisar un trámite que no pertenece a tu carrera.'
            ], 403);
        }

        $tramite->puede_dictaminar = strtolower((string) $tramite->estado_tramite) === 'revision';

        if (!$tramite->puede_dictaminar) {
            $tramite->mensaje_estado = 'Este trámite ya fue dictaminado y solo se muestra en modo lectura.';
        } else {
            $tramite->mensaje_estado = null;
        }

        return response()->json([
            'resultado' => 'OK',
            'data' => $tramite
        ], 200);
    }

    public function dictaminarCoordinacion(Request $request, $id_tramite)
    {
        $request->validate([
            'estado' => 'required|in:aprobada,rechazada',
            'observacion_dictamen' => 'nullable|string'
        ]);

        $idCarreraCoordinador = $this->obtenerCarreraCoordinadorAutenticado();

        if (!$idCarreraCoordinador) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No tienes una carrera asignada como coordinador.'
            ], 403);
        }

        $tramite = DB::table('tbl_tramite')
            ->where('id_tramite', $id_tramite)
            ->where('id_carrera_destino', $idCarreraCoordinador)
            ->where('resolucion_de_tramite_academico', 'revision')
            ->first();

        if (!$tramite) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje' => 'No puedes dictaminar un trámite que no pertenece a tu carrera o que aún no está en revisión.'
            ], 403);
        }

        DB::table('tbl_tramite')
            ->where('id_tramite', $id_tramite)
            ->update([
                'resolucion_de_tramite_academico' => $request->estado,
                'observacion_dictamen' => $request->observacion_dictamen
            ]);

        return response()->json([
            'resultado' => 'OK',
            'mensaje' => 'Dictamen final guardado correctamente.'
        ]);
    }

    public function crearCalendarioAcademico(Request $request)
    {
        $request->validate([
            'tipo_tramite_academico' => 'required|string|in:cambio_carrera,cancelacion',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date',
        ]);

        try {
            $data = DB::select('CALL INS_CALENDARIO_ACADEMICO(?, ?, ?)', [
                $request->tipo_tramite_academico,
                $request->fecha_inicio,
                $request->fecha_fin
            ]);

            return response()->json($data[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Calendario creado correctamente.'
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarCalendarioAcademico(Request $request, $id_calendario)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date',
        ]);

        try {
            $data = DB::select('CALL UPD_CALENDARIO_ACADEMICO(?, ?, ?)', [
                $id_calendario,
                $request->fecha_inicio,
                $request->fecha_fin
            ]);

            return response()->json($data[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Calendario actualizado correctamente.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstadoCalendarioAcademico($id_calendario)
    {
        try {
            $data = DB::select('CALL UPD_ESTADO_CALENDARIO_ACADEMICO(?)', [
                $id_calendario
            ]);

            return response()->json($data[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Estado del calendario actualizado correctamente.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerMensajeLimpio(Throwable $e, string $mensajeGenerico): string
    {
        if ($e instanceof QueryException) {
            $mensajeBD = trim((string) ($e->errorInfo[2] ?? ''));

            if ($mensajeBD !== '') {
                // Quita prefijos numéricos tipo "1644 "
                $mensajeBD = preg_replace('/^\d+\s*/', '', $mensajeBD);

                // Quita "SQLSTATE[45000]: <<Unknown error>>:"
                $mensajeBD = preg_replace('/^SQLSTATE\[[^\]]+\]:\s*<<[^>]+>>:\s*/i', '', $mensajeBD);

                // Quita "(Connection: mysql ...)"
                $mensajeBD = preg_replace('/\s*\(Connection:.*$/u', '', $mensajeBD);

                // Quita "SQL: CALL ..."
                $mensajeBD = preg_replace('/\s*SQL:\s.*$/iu', '', $mensajeBD);

                return trim($mensajeBD);
            }
        }

        $mensajeCompleto = trim($e->getMessage());

        if ($mensajeCompleto !== '') {
            // Caso común de Laravel/MySQL
            if (preg_match('/:\s*\d+\s+(.*?)(?:\s+\(Connection:|\s+SQL:|$)/u', $mensajeCompleto, $coincidencias)) {
                return trim($coincidencias[1]);
            }

            // Limpiezas adicionales
            $mensajeCompleto = preg_replace('/^SQLSTATE\[[^\]]+\]:\s*<<[^>]+>>:\s*/i', '', $mensajeCompleto);
            $mensajeCompleto = preg_replace('/\s*\(Connection:.*$/u', '', $mensajeCompleto);
            $mensajeCompleto = preg_replace('/\s*SQL:\s.*$/iu', '', $mensajeCompleto);

            return trim($mensajeCompleto);
        }

        return $mensajeGenerico;
    }


    private function obtenerCodigoHttp(Throwable $e, int $codigoPorDefecto = 500): int
    {
        if ($e instanceof QueryException) {
            $codigoMysql = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;

            if ($codigoMysql === 1644) {
                return 422;
            }
        }

        return $codigoPorDefecto;
    }

    public function vistaCoordinador()
    {
        return view('cambio_carrera_coordinacion');
    }

    private function obtenerCarreraCoordinadorAutenticado(): ?int
    {
        if (!Auth::check()) {
            return null;
        }

        $idPersona = Auth::user()->id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        $empleado = DB::table('tbl_empleados')
            ->where('id_persona', $idPersona)
            ->where('tipo_usuario', 'coordinador')
            ->first();

        if (!$empleado || empty($empleado->id_carrera)) {
            return null;
        }

        return (int) $empleado->id_carrera;
    }

    private function obtenerCarreraSecretariaAutenticada(): ?int
    {
        if (!Auth::check()) {
            return null;
        }

        $idPersona = Auth::user()->id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        $empleado = DB::table('tbl_empleados')
            ->where('id_persona', $idPersona)
            ->where('tipo_usuario', 'secretario')
            ->first();

        if (!$empleado || empty($empleado->id_carrera)) {
            return null;
        }

        return (int) $empleado->id_carrera;
    }

    public function eliminarCalendarioAcademico($id_calendario)
    {
        try {
            $data = DB::select('CALL DEL_LOGICO_CALENDARIO_ACADEMICO(?)', [
                $id_calendario
            ]);

            return response()->json($data[0] ?? [
                'resultado' => 'OK',
                'mensaje'   => 'Calendario eliminado correctamente.'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    public function verDocumento($id_tramite)
    {
        try {
            $documento = DB::table('tbl_documento')
                ->where('id_tramite', $id_tramite)
                ->where('tipo_documento', 'historial_academico')
                ->first();

            if (!$documento) {
                return response("No se encontró registro del documento para el trámite #{$id_tramite}.", 404);
            }

            if (empty($documento->ruta_archivo)) {
                return response("El documento fue encontrado, pero ruta_archivo está vacío.", 500);
            }

            $rutaRelativa = ltrim($documento->ruta_archivo, '/');

            $rutaArchivo = storage_path('app/' . $rutaRelativa);

            if (!file_exists($rutaArchivo)) {
                $rutaArchivo = storage_path('app/public/' . $rutaRelativa);
            }

            if (!file_exists($rutaArchivo)) {
                return response("El archivo no existe en ninguna ruta válida. Revisado: app/ y app/public/ para {$rutaRelativa}", 404);
            }

            return response()->file($rutaArchivo, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($rutaArchivo) . '"'
            ]);

        } catch (Throwable $e) {
            // CAMBIO: ya no mostrar todo el error técnico al usuario
            return response(
                "Error al abrir el documento: " . $this->obtenerMensajeLimpio(
                    $e,
                    'No fue posible abrir el documento.'
                ),
                $this->obtenerCodigoHttp($e, 500)
            );
        }
    }

    public function listarCalendariosAcademicos()
    {
        try {
            $data = DB::select('CALL SEL_CALENDARIOS_ACADEMICOS()');

            return response()->json($data, 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }
}