<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResolucionCancelacionController extends Controller
{
    /**
     * Carga la vista principal del módulo
     */
    public function vista()
    {
        $contexto = $this->obtenerContextoUsuario();

        if (!$contexto) {
            return redirect()->route('login');
        }

        return view('resolucion_cancelacion_coordinador', [
            'coordinador' => $contexto
        ]);
    }

    /**
     * API: listado de solicitudes de cancelación
     */
    public function listar(Request $request)
    {
        try {
            $contexto = $this->obtenerContextoUsuario();

            if (!$contexto) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No hay un usuario autenticado válido.'
                ], 403);
            }

            $tramites = DB::table('tbl_tramite as t')
                ->join('tbl_cancelacion as c', 'c.id_tramite', '=', 't.id_tramite')
                ->join('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
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
                    't.resolucion_de_tramite_academico',
                    'c.id_cancelacion',
                    'c.motivo',
                    'c.explicacion',
                    'p.nombre_persona as estudiante',
                    DB::raw('COALESCE(r.estado_validacion, t.resolucion_de_tramite_academico, "pendiente") as estado_actual'),
                    DB::raw('COALESCE(docs.total_documentos, 0) as total_documentos')
                )
                ->where('t.estado', 1)
                ->where(function ($query) {
                    $query->whereRaw("LOWER(t.tipo_tramite_academico) = 'cancelacion'")
                          ->orWhereRaw("LOWER(t.tipo_tramite_academico) = 'cancelacion_excepcional'");
                })
                ->orderByDesc('t.fecha_solicitud')
                ->get();

            return response()->json([
                'resultado' => 'OK',
                'usuario'   => $contexto,
                'data'      => $tramites
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'No se pudo obtener el listado de cancelaciones.',
                'detalle'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: detalle de una solicitud concreta
     */
    public function detalle($idTramite)
    {
        if (!is_numeric($idTramite)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'ID de trámite inválido.'
            ], 400);
        }

        try {
            $contexto = $this->obtenerContextoUsuario();

            if (!$contexto) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No hay un usuario autenticado válido.'
                ], 403);
            }

            $detalle = DB::table('tbl_tramite as t')
                ->join('tbl_cancelacion as c', 'c.id_tramite', '=', 't.id_tramite')
                ->join('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
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
                    'p.nombre_persona as estudiante'
                )
                ->where('t.id_tramite', $idTramite)
                ->where('t.estado', 1)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No se encontró la solicitud de cancelación.'
                ], 404);
            }

            $documentos = DB::table('tbl_documento')
                ->select(
                    'id_documento',
                    'id_tramite',
                    'numero_folio',
                    'tipo_documento',
                    'nombre_documento',
                    'hash_contenido',
                    'ruta_archivo',
                    'fecha_carga',
                    'autenticidad_documento',
                    'estado'
                )
                ->where('id_tramite', $idTramite)
                ->where('estado', 1)
                ->orderByDesc('fecha_carga')
                ->get();

            $resolucionActual = DB::table('tbl_resolucion')
                ->select(
                    'id_resolucion',
                    'id_tramite',
                    'id_coordinador',
                    'estado_validacion',
                    'observaciones',
                    'fecha_resolucion',
                    'fecha_anulacion',
                    'documento_resolucion',
                    'estado'
                )
                ->where('id_tramite', $idTramite)
                ->where('estado', 1)
                ->orderByDesc('id_resolucion')
                ->first();

            return response()->json([
                'resultado' => 'OK',
                'data' => [
                    'detalle'          => $detalle,
                    'documentos'       => $documentos,
                    'resolucionActual' => $resolucionActual,
                    'usuario'          => $contexto
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'No se pudo obtener el detalle de la solicitud.',
                'detalle'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: guardar resolución
     */
    public function resolver(Request $request, $idTramite)
    {
        if (!is_numeric($idTramite)) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'ID de trámite inválido.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'decision'      => 'required|string|max:20',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'decision.required' => 'Debe seleccionar una decisión.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'Error de validación.',
                'errors'    => $validator->errors()
            ], 422);
        }

        $decision = $this->normalizarEstado($request->input('decision'));
        $observaciones = trim((string) $request->input('observaciones'));

        if (!$decision) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'La decisión seleccionada no es válida.'
            ], 422);
        }

        if (in_array($decision, ['rechazada', 'revision']) && $observaciones === '') {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'Debe escribir observaciones cuando rechaza o envía a revisión.'
            ], 422);
        }

        if ($decision === 'aprobada' && $observaciones === '') {
            $observaciones = 'Resolución emitida por coordinación.';
        }

        try {
            $contexto = $this->obtenerContextoUsuario();

            if (!$contexto) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'No hay un usuario autenticado válido.'
                ], 403);
            }

            $tramite = DB::table('tbl_tramite')
                ->where('id_tramite', $idTramite)
                ->where('estado', 1)
                ->first();

            if (!$tramite) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'El trámite no existe o no está activo.'
                ], 404);
            }

            $cancelacion = DB::table('tbl_cancelacion')
                ->where('id_tramite', $idTramite)
                ->first();

            if (!$cancelacion) {
                return response()->json([
                    'resultado' => 'ERROR',
                    'mensaje'   => 'El trámite indicado no corresponde a una cancelación registrada.'
                ], 404);
            }

            DB::beginTransaction();

            DB::table('tbl_tramite')
                ->where('id_tramite', $idTramite)
                ->update([
                    'resolucion_de_tramite_academico' => $decision,
                ]);

            $resolucionExistente = DB::table('tbl_resolucion')
                ->where('id_tramite', $idTramite)
                ->where('estado', 1)
                ->orderByDesc('id_resolucion')
                ->first();

            if ($resolucionExistente) {
                DB::table('tbl_resolucion')
                    ->where('id_resolucion', $resolucionExistente->id_resolucion)
                    ->update([
                        'id_coordinador'    => $contexto->id_coordinador,
                        'estado_validacion' => $decision,
                        'observaciones'     => $observaciones,
                        'fecha_resolucion'  => now(),
                        'estado'            => 1,
                    ]);
            } else {
                DB::table('tbl_resolucion')->insert([
                    'id_tramite'           => $idTramite,
                    'id_coordinador'       => $contexto->id_coordinador,
                    'estado_validacion'    => $decision,
                    'observaciones'        => $observaciones,
                    'fecha_resolucion'     => now(),
                    'fecha_anulacion'      => null,
                    'documento_resolucion' => null,
                    'estado'               => 1,
                ]);
            }

            try {
                DB::table('tbl_bitacora')->insert([
                    'id_usuario'   => $contexto->id_usuario,
                    'id_objeto'    => 1,
                    'accion'       => 'RESOLVER_CANCELACION',
                    'fecha_accion' => now(),
                    'descripcion'  => 'Se resolvió el trámite #' . $idTramite . ' con estado: ' . $decision . '. Observaciones: ' . $observaciones,
                ]);
            } catch (\Throwable $e) {
                // No detener flujo por bitácora
            }

            DB::commit();

            return response()->json([
                'resultado' => 'OK',
                'mensaje'   => 'La resolución se guardó correctamente.',
                'data'      => [
                    'id_tramite'     => (int) $idTramite,
                    'estado'         => $decision,
                    'id_coordinador' => $contexto->id_coordinador
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => 'No se pudo guardar la resolución.',
                'detalle'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API/WEB: abrir documento físico
     */
    public function documento($idDocumento)
    {
        if (!is_numeric($idDocumento)) {
            abort(404);
        }

        $documento = DB::table('tbl_documento')
            ->where('id_documento', $idDocumento)
            ->where('estado', 1)
            ->first();

        if (!$documento || empty($documento->ruta_archivo)) {
            abort(404, 'Documento no encontrado en la base de datos.');
        }

        $candidatas = $this->construirRutasCandidatas(
            (string) $documento->ruta_archivo,
            (string) ($documento->nombre_documento ?? '')
        );

        foreach ($candidatas as $rutaAbsoluta) {
            if ($rutaAbsoluta && file_exists($rutaAbsoluta) && is_file($rutaAbsoluta)) {
                return response()->file($rutaAbsoluta);
            }
        }

        // Búsqueda más tolerante por nombre dentro de carpetas frecuentes
        $encontradoPorNombre = $this->buscarArchivoPorNombre((string) $documento->nombre_documento);

        if ($encontradoPorNombre) {
            return response()->file($encontradoPorNombre);
        }

        abort(404, 'El archivo existe en la BD pero no se encontró físicamente. Ruta registrada: ' . $documento->ruta_archivo);
    }

    /**
     * Construye varias rutas posibles para encontrar el archivo
     */
    private function construirRutasCandidatas(string $rutaOriginal, string $nombreDocumento = ''): array
    {
        $rutaOriginal = trim(str_replace('\\', '/', $rutaOriginal), '/');

        $rutaSinPublic = preg_replace('#^public/#', '', $rutaOriginal);
        $rutaSinStorage = preg_replace('#^storage/#', '', $rutaOriginal);
        $nombreBase = trim(basename($rutaOriginal));
        $nombreDocumento = trim($nombreDocumento);

        return array_values(array_unique(array_filter([
            storage_path('app/' . $rutaOriginal),
            storage_path('app/' . $rutaSinPublic),
            storage_path('app/public/' . $rutaOriginal),
            storage_path('app/public/' . $rutaSinPublic),

            public_path($rutaOriginal),
            public_path($rutaSinStorage),
            public_path('storage/' . $rutaOriginal),
            public_path('storage/' . $rutaSinPublic),

            $nombreBase ? storage_path('app/documentos/' . $nombreBase) : null,
            $nombreBase ? storage_path('app/public/documentos/' . $nombreBase) : null,
            $nombreBase ? public_path('storage/documentos/' . $nombreBase) : null,
            $nombreBase ? public_path('documentos/' . $nombreBase) : null,

            $nombreDocumento ? storage_path('app/documentos/' . $nombreDocumento) : null,
            $nombreDocumento ? storage_path('app/public/documentos/' . $nombreDocumento) : null,
            $nombreDocumento ? public_path('storage/documentos/' . $nombreDocumento) : null,
            $nombreDocumento ? public_path('documentos/' . $nombreDocumento) : null,
        ])));
    }

    /**
     * Busca el archivo por nombre dentro de varias carpetas comunes
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
                // Ignorar errores de lectura de carpeta y continuar
            }
        }

        return null;
    }

    /**
     * Obtiene el contexto del usuario autenticado.
     * Si existe registro en tbl_coordinador, lo toma.
     * Si no existe, igual permite trabajar con el usuario autenticado.
     */
    private function obtenerContextoUsuario()
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();
        $idPersona = $user->id_persona ?? null;

        $registroCoordinador = null;

        if ($idPersona) {
            $registroCoordinador = DB::table('tbl_coordinador as c')
                ->leftJoin('tbl_persona as p', 'p.id_persona', '=', 'c.id_persona')
                ->select(
                    'c.id_coordinador',
                    'c.id_persona',
                    'c.id_departamento',
                    'c.estado_coordinador',
                    'p.nombre_persona as nombre_coordinador'
                )
                ->where('c.id_persona', $idPersona)
                ->where('c.estado_coordinador', 1)
                ->first();
        }

        $nombre = null;

        if ($registroCoordinador && !empty($registroCoordinador->nombre_coordinador)) {
            $nombre = $registroCoordinador->nombre_coordinador;
        } elseif (isset($user->persona) && $user->persona && !empty($user->persona->nombre_persona)) {
            $nombre = $user->persona->nombre_persona;
        } elseif (!empty($user->nombre_persona)) {
            $nombre = $user->nombre_persona;
        } elseif (!empty($user->name)) {
            $nombre = $user->name;
        } else {
            $nombre = 'Coordinación';
        }

        return (object) [
            'id_usuario'         => Auth::id(),
            'id_persona'         => $idPersona,
            'id_coordinador'     => $registroCoordinador->id_coordinador ?? null,
            'id_departamento'    => $registroCoordinador->id_departamento ?? null,
            'nombre_coordinador' => $nombre,
            'es_coordinador_bd'  => $registroCoordinador ? true : false,
        ];
    }

    /**
     * Normaliza estados para guardarlos según tu tabla real
     */
    private function normalizarEstado($valor)
    {
        $valor = mb_strtolower(trim((string) $valor));

        return match ($valor) {
            'aprobada', 'aprobado', 'aceptada', 'aceptado' => 'aprobada',
            'rechazada', 'rechazado' => 'rechazada',
            'revision', 'revisión', 'devuelto', 'devuelta' => 'revision',
            default => null,
        };
    }
}
