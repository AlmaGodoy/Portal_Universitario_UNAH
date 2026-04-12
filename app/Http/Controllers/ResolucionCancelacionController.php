<?php

namespace App\Http\Controllers;

use App\Models\Resolucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResolucionCancelacionController extends Controller
{
    /**
     * Vista principal del módulo
     */
    public function vista()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $contexto = $this->obtenerContextoCoordinador();

        if (!$contexto) {
            abort(403, 'Este módulo es exclusivo para coordinadores activos.');
        }

        return view('resolucion_cancelacion_coordinador', [
            'coordinador' => $contexto,
        ]);
    }

    /**
     * API: listar solicitudes de cancelación
     */
    public function listar(Request $request)
    {
        try {
            $contexto = $this->obtenerContextoCoordinador();

            if (!$contexto) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'Acceso no autorizado.',
                ], 403);
            }

            $tramites = $this->queryTramitesPermitidos($contexto)
                ->leftJoin(DB::raw('
                    (
                        SELECT id_tramite, COUNT(*) AS total_documentos
                        FROM tbl_documento
                        WHERE estado = 1
                        GROUP BY id_tramite
                    ) docs
                '), 'docs.id_tramite', '=', 't.id_tramite')
                ->leftJoin(DB::raw('
                    (
                        SELECT MAX(id_resolucion) AS id_resolucion, id_tramite
                        FROM tbl_resolucion
                        WHERE estado = 1
                        GROUP BY id_tramite
                    ) ult_res
                '), 'ult_res.id_tramite', '=', 't.id_tramite')
                ->leftJoin('tbl_resolucion as r', 'r.id_resolucion', '=', 'ult_res.id_resolucion')
                ->select(
                    't.id_tramite',
                    't.id_persona',
                    't.fecha_solicitud',
                    't.tipo_tramite_academico',
                    't.resolucion_de_tramite_academico',
                    'c.id_cancelacion',
                    'c.motivo',
                    'c.explicacion',
                    'p.nombre_persona as estudiante',
                    'ca.id_carrera',
                    'ca.nombre_carrera',
                    DB::raw("COALESCE(r.estado_validacion, t.resolucion_de_tramite_academico, 'pendiente') as estado_actual"),
                    DB::raw('COALESCE(docs.total_documentos, 0) as total_documentos')
                )
                ->orderByDesc('t.fecha_solicitud')
                ->get();

            return response()->json([
                'resultado' => 'OK',
                'usuario'   => $contexto,
                'data'      => $tramites,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'No se pudo obtener el listado de cancelaciones.',
                'detalle'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: detalle de una solicitud
     */
    public function detalle($idTramite)
    {
        if (!is_numeric($idTramite)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'ID de trámite inválido.',
            ], 400);
        }

        try {
            $contexto = $this->obtenerContextoCoordinador();

            if (!$contexto) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'Acceso no autorizado.',
                ], 403);
            }

            $detalle = $this->queryTramitesPermitidos($contexto)
                ->where('t.id_tramite', (int) $idTramite)
                ->select(
                    't.id_tramite',
                    't.id_persona',
                    't.tipo_tramite_academico',
                    't.fecha_solicitud',
                    't.resolucion_de_tramite_academico',
                    't.estado as estado_tramite',
                    'c.id_cancelacion',
                    'c.motivo',
                    'c.explicacion',
                    'c.fecha_solicitud as fecha_cancelacion',
                    'p.nombre_persona as estudiante',
                    'ca.id_carrera',
                    'ca.nombre_carrera'
                )
                ->first();

            if (!$detalle) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No se encontró la solicitud o no pertenece a su ámbito.',
                ], 404);
            }

            $documentos = DB::table('tbl_documento')
                ->where('id_tramite', (int) $idTramite)
                ->where('estado', 1)
                ->orderByDesc('fecha_carga')
                ->get([
                    'id_documento',
                    'id_tramite',
                    'numero_folio',
                    'tipo_documento',
                    'nombre_documento',
                    'hash_contenido',
                    'ruta_archivo',
                    'fecha_carga',
                    'autenticidad_documento',
                    'estado',
                ]);

            $resolucionActual = DB::table('tbl_resolucion')
                ->where('id_tramite', (int) $idTramite)
                ->where('estado', 1)
                ->orderByDesc('id_resolucion')
                ->first([
                    'id_resolucion',
                    'id_tramite',
                    'id_coordinador',
                    'estado_validacion',
                    'observaciones',
                    'fecha_resolucion',
                    'fecha_anulacion',
                    'documento_resolucion',
                    'estado',
                ]);

            return response()->json([
                'resultado' => 'OK',
                'data' => [
                    'detalle'          => $detalle,
                    'documentos'       => $documentos,
                    'resolucionActual' => $resolucionActual,
                    'usuario'          => $contexto,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'No se pudo obtener el detalle de la solicitud.',
                'detalle'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: guardar dictamen
     */
    public function resolver(Request $request, $idTramite)
    {
        if (!is_numeric($idTramite)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'ID de trámite inválido.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'decision'      => 'required|string|max:20',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'decision.required' => 'Debe seleccionar una decisión.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'Error de validación.',
                'errors'    => $validator->errors(),
            ], 422);
        }

        $decision = $this->normalizarEstado($request->input('decision'));
        $observaciones = trim((string) $request->input('observaciones'));

        if (!$decision) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'La decisión seleccionada no es válida.',
            ], 422);
        }

        if (in_array($decision, ['rechazada', 'revision'], true) && $observaciones === '') {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'Debe escribir observaciones cuando rechaza o envía a revisión.',
            ], 422);
        }

        if ($decision === 'aprobada' && $observaciones === '') {
            $observaciones = 'Dictamen emitido por coordinación.';
        }

        try {
            $contexto = $this->obtenerContextoCoordinador();

            if (!$contexto) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'Acceso no autorizado.',
                ], 403);
            }

            $tramite = $this->queryTramitesPermitidos($contexto)
                ->where('t.id_tramite', (int) $idTramite)
                ->select('t.id_tramite', 't.resolucion_de_tramite_academico', 'c.id_cancelacion')
                ->first();

            if (!$tramite) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'El trámite no existe o no pertenece a su ámbito.',
                ], 404);
            }

            DB::transaction(function () use ($idTramite, $decision, $observaciones, $contexto) {
                DB::table('tbl_tramite')
                    ->where('id_tramite', (int) $idTramite)
                    ->update([
                        'resolucion_de_tramite_academico' => $decision,
                    ]);

                Resolucion::query()
                    ->where('id_tramite', (int) $idTramite)
                    ->where('estado', 1)
                    ->update([
                        'estado'          => 0,
                        'fecha_anulacion' => now(),
                    ]);

                Resolucion::create([
                    'id_tramite'           => (int) $idTramite,
                    'id_coordinador'       => $contexto->id_coordinador,
                    'estado_validacion'    => $decision,
                    'observaciones'        => $observaciones,
                    'fecha_resolucion'     => now(),
                    'fecha_anulacion'      => null,
                    'documento_resolucion' => null,
                    'estado'               => 1,
                ]);

                try {
                    DB::table('tbl_bitacora')->insert([
                        'id_usuario'   => $contexto->id_usuario,
                        'id_objeto'    => 1,
                        'accion'       => 'RESOLVER_CANCELACION',
                        'fecha_accion' => now(),
                        'descripcion'  => 'Se emitió dictamen al trámite #' . $idTramite . ' con estado: ' . $decision . '. Observaciones: ' . $observaciones,
                    ]);
                } catch (\Throwable $e) {
                    // No romper el flujo principal por bitácora
                }
            });

            return response()->json([
                'resultado' => 'OK',
                'mensaje'   => 'El dictamen se guardó correctamente.',
                'data'      => [
                    'id_tramite'     => (int) $idTramite,
                    'estado'         => $decision,
                    'id_coordinador' => $contexto->id_coordinador,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'No se pudo guardar el dictamen.',
                'detalle'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API/WEB: abrir documento adjunto autorizado
     */
    public function documento($idDocumento)
    {
        if (!is_numeric($idDocumento)) {
            abort(404);
        }

        $contexto = $this->obtenerContextoCoordinador();

        if (!$contexto) {
            abort(403, 'Acceso no autorizado.');
        }

        $documento = DB::table('tbl_documento')
            ->where('id_documento', (int) $idDocumento)
            ->where('estado', 1)
            ->first();

        if (!$documento) {
            abort(404, 'Documento no encontrado.');
        }

        $tramiteAutorizado = $this->queryTramitesPermitidos($contexto)
            ->where('t.id_tramite', $documento->id_tramite)
            ->select('t.id_tramite')
            ->first();

        if (!$tramiteAutorizado) {
            abort(404, 'El documento no pertenece a un trámite autorizado.');
        }

        $rutaAbsoluta = $this->resolverRutaDocumento($documento);

        if (!$rutaAbsoluta) {
            abort(404, 'El archivo existe en la BD, pero no se encontró físicamente.');
        }

        return response()->file($rutaAbsoluta);
    }

    /**
     * Query base de trámites visibles para la coordinadora
     */
    private function queryTramitesPermitidos(object $contexto)
    {
        return DB::table('tbl_tramite as t')
            ->join('tbl_cancelacion as c', 'c.id_tramite', '=', 't.id_tramite')
            ->join('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
            ->leftJoin('tbl_estudiante as e', 'e.id_persona', '=', 't.id_persona')
            ->leftJoin('tbl_carrera as ca', 'ca.id_carrera', '=', 'e.id_carrera')
            ->where('t.estado', 1)
            ->whereRaw("LOWER(t.tipo_tramite_academico) IN ('cancelacion', 'cancelacion_excepcional')")
            ->where('ca.id_departamento', $contexto->id_departamento);
    }

    /**
     * Obtiene el contexto del coordinador autenticado
     */
    private function obtenerContextoCoordinador(): ?object
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();
        $idPersona = $user->id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        $coordinador = DB::table('tbl_coordinador as c')
            ->join('tbl_persona as p', 'p.id_persona', '=', 'c.id_persona')
            ->where('c.id_persona', $idPersona)
            ->where('c.estado_coordinador', 1)
            ->select(
                'c.id_coordinador',
                'c.id_persona',
                'c.id_departamento',
                'c.estado_coordinador',
                'p.nombre_persona as nombre_coordinador'
            )
            ->first();

        if (!$coordinador) {
            return null;
        }

        return (object) [
            'id_usuario'         => Auth::id(),
            'id_persona'         => $coordinador->id_persona,
            'id_coordinador'     => $coordinador->id_coordinador,
            'id_departamento'    => $coordinador->id_departamento,
            'nombre_coordinador' => $coordinador->nombre_coordinador,
            'es_coordinador_bd'  => true,
        ];
    }

    /**
     * Normaliza el estado del dictamen
     */
    private function normalizarEstado($valor): ?string
    {
        $valor = mb_strtolower(trim((string) $valor));

        return match ($valor) {
            'aprobada', 'aprobado', 'aceptada', 'aceptado' => 'aprobada',
            'rechazada', 'rechazado', 'denegada', 'denegado' => 'rechazada',
            'revision', 'revisión', 'devuelto', 'devuelta' => 'revision',
            default => null,
        };
    }

    /**
     * Resuelve la ruta física del archivo
     */
    private function resolverRutaDocumento(object $documento): ?string
    {
        $rutaOriginal = trim(str_replace('\\', '/', (string) ($documento->ruta_archivo ?? '')), '/');
        $nombreDocumento = trim((string) ($documento->nombre_documento ?? ''));
        $nombreBase = $rutaOriginal !== '' ? basename($rutaOriginal) : '';

        $rutaSinPublic = preg_replace('#^public/#', '', $rutaOriginal);
        $rutaSinStorage = preg_replace('#^storage/#', '', $rutaOriginal);

        $candidatas = array_values(array_unique(array_filter([
            $rutaOriginal ? storage_path('app/' . $rutaOriginal) : null,
            $rutaSinPublic ? storage_path('app/' . $rutaSinPublic) : null,
            $rutaOriginal ? storage_path('app/public/' . $rutaOriginal) : null,
            $rutaSinPublic ? storage_path('app/public/' . $rutaSinPublic) : null,

            $rutaOriginal ? public_path($rutaOriginal) : null,
            $rutaSinStorage ? public_path($rutaSinStorage) : null,
            $rutaOriginal ? public_path('storage/' . $rutaOriginal) : null,
            $rutaSinPublic ? public_path('storage/' . $rutaSinPublic) : null,

            $nombreBase ? storage_path('app/documentos/' . $nombreBase) : null,
            $nombreBase ? storage_path('app/public/documentos/' . $nombreBase) : null,
            $nombreBase ? public_path('storage/documentos/' . $nombreBase) : null,
            $nombreBase ? public_path('documentos/' . $nombreBase) : null,

            $nombreDocumento ? storage_path('app/documentos/' . $nombreDocumento) : null,
            $nombreDocumento ? storage_path('app/public/documentos/' . $nombreDocumento) : null,
            $nombreDocumento ? public_path('storage/documentos/' . $nombreDocumento) : null,
            $nombreDocumento ? public_path('documentos/' . $nombreDocumento) : null,
        ])));

        foreach ($candidatas as $rutaAbsoluta) {
            if ($rutaAbsoluta && file_exists($rutaAbsoluta) && is_file($rutaAbsoluta)) {
                return $rutaAbsoluta;
            }
        }

        return $this->buscarArchivoPorNombre($nombreDocumento ?: $nombreBase);
    }

    /**
     * Busca archivo por nombre
     */
    private function buscarArchivoPorNombre(string $nombreArchivo): ?string
    {
        $nombreArchivo = trim($nombreArchivo);

        if ($nombreArchivo === '') {
            return null;
        }

        $directorios = [
            storage_path('app'),
            storage_path('app/public'),
            public_path('storage'),
            public_path('documentos'),
        ];

        foreach ($directorios as $directorio) {
            if (!$directorio || !is_dir($directorio)) {
                continue;
            }

            try {
                $iterador = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directorio, \FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterador as $archivo) {
                    if ($archivo->isFile() && strcasecmp($archivo->getFilename(), $nombreArchivo) === 0) {
                        return $archivo->getPathname();
                    }
                }
            } catch (\Throwable $e) {
                // Ignorar errores de lectura
            }
        }

        return null;
    }
}