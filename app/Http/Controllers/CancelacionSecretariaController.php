<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CancelacionSecretariaController extends Controller
{
    /**
     * Estados que Secretaría puede ver/gestionar.
     * Nota: aquí se manejan en mayúscula porque luego se comparan con strtoupper().
     */
    private const ESTADOS_SECRETARIA = [
        'REVISION',
        'DEVUELTO',
        'LISTO_COORDINADORA',
    ];

    /**
     * Vista principal: bandeja de revisión documental.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $this->normalizarEstado($request->input('estado', ''));

        $query = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
            ->whereRaw('LOWER(TRIM(t.tipo_tramite_academico)) = ?', ['cancelacion'])
            ->whereIn(
                DB::raw("UPPER(TRIM(COALESCE(NULLIF(t.resolucion_de_tramite_academico, ''), 'revision')))"),
                self::ESTADOS_SECRETARIA
            )
            ->select([
                't.id_tramite',
                't.id_persona',
                't.fecha_solicitud',
                't.resolucion_de_tramite_academico',
                't.estado',
                DB::raw("COALESCE(p.nombre_persona, 'Sin nombre') as nombre_estudiante"),
            ])
            ->orderByDesc('t.id_tramite');

        if ($estado !== '' && in_array($estado, self::ESTADOS_SECRETARIA, true)) {
            $query->whereRaw(
                "UPPER(TRIM(COALESCE(NULLIF(t.resolucion_de_tramite_academico, ''), 'revision'))) = ?",
                [$estado]
            );
        }

        if ($buscar !== '') {
            $query->where(function ($sub) use ($buscar) {
                $sub->where('t.id_tramite', 'like', '%' . $buscar . '%')
                    ->orWhere('p.nombre_persona', 'like', '%' . $buscar . '%');
            });
        }

        $tramites = $query->paginate(10)->withQueryString();

        return view('cancelacion_secretaria_index', [
            'tramites' => $tramites,
            'buscar'   => $buscar,
            'estado'   => $estado,
            'estados'  => self::ESTADOS_SECRETARIA,
        ]);
    }

    /**
     * Ver detalle del trámite y documentos adjuntos.
     */
    public function detalle(int $id_tramite)
    {
        $tramite = $this->obtenerTramiteCancelacion($id_tramite);

        if (!$tramite) {
            return redirect()
                ->route('cancelacion.secretaria.index')
                ->withErrors(['error' => 'No se encontró el trámite de cancelación solicitado.']);
        }

        $documentos = DB::table('tbl_documento')
            ->where('id_tramite', $id_tramite)
            ->where('estado', 1)
            ->orderByDesc('fecha_carga')
            ->get()
            ->map(function ($doc) {
                $doc->nombre_legible = $this->nombreDocumento($doc->tipo_documento ?? '');
                return $doc;
            });

        return view('cancelacion_secretaria_detalle', [
            'tramite'    => $tramite,
            'documentos' => $documentos,
            'estado'     => $this->normalizarEstado($tramite->resolucion_de_tramite_academico ?? 'revision'),
        ]);
    }

    /**
     * Secretaría devuelve documentación al estudiante.
     * No guarda observación todavía porque tbl_tramite no tiene columna descripcion.
     */
    public function devolverDocumentacion(Request $request, int $id_tramite)
    {
        $request->validate([
            'observacion' => ['required', 'string', 'max:1000'],
        ], [
            'observacion.required' => 'Debe escribir la observación para devolver la documentación.',
            'observacion.max'      => 'La observación no puede superar 1000 caracteres.',
        ]);

        $tramite = $this->obtenerTramiteCancelacion($id_tramite);

        if (!$tramite) {
            return redirect()
                ->route('cancelacion.secretaria.index')
                ->withErrors(['error' => 'No se encontró el trámite de cancelación solicitado.']);
        }

        $estadoActual = $this->normalizarEstado($tramite->resolucion_de_tramite_academico ?? 'revision');

        if (!$this->secretariaPuedeGestionar($estadoActual)) {
            return redirect()
                ->route('cancelacion.secretaria.detalle', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'Secretaría no puede devolver documentación para este estado del trámite.']);
        }

        try {
            DB::transaction(function () use ($id_tramite) {
                DB::table('tbl_tramite')
                    ->where('id_tramite', $id_tramite)
                    ->update([
                        // BD en minúsculas
                        'resolucion_de_tramite_academico' => 'devuelto',
                    ]);
            });

            return redirect()
                ->route('cancelacion.secretaria.detalle', ['id_tramite' => $id_tramite])
                ->with('success', 'La documentación fue devuelta al estudiante correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al devolver documentación en Secretaría - cancelación', [
                'id_tramite' => $id_tramite,
                'error'      => $e->getMessage(),
            ]);

            return redirect()
                ->route('cancelacion.secretaria.detalle', ['id_tramite' => $id_tramite])
                ->withErrors([
                    'error' => 'No fue posible devolver la documentación al estudiante. ' .
                        (app()->isLocal() ? 'Detalle: ' . $e->getMessage() : '')
                ]);
        }
    }

    /**
     * Secretaría marca el trámite como listo para coordinadora.
     * No guarda observación todavía porque tbl_tramite no tiene columna descripcion.
     */
    public function marcarListoParaCoordinadora(Request $request, int $id_tramite)
    {
        $request->validate([
            'observacion' => ['nullable', 'string', 'max:1000'],
        ], [
            'observacion.max' => 'La observación no puede superar 1000 caracteres.',
        ]);

        $tramite = $this->obtenerTramiteCancelacion($id_tramite);

        if (!$tramite) {
            return redirect()
                ->route('cancelacion.secretaria.index')
                ->withErrors(['error' => 'No se encontró el trámite de cancelación solicitado.']);
        }

        $estadoActual = $this->normalizarEstado($tramite->resolucion_de_tramite_academico ?? 'revision');

        if (!$this->secretariaPuedeGestionar($estadoActual)) {
            return redirect()
                ->route('cancelacion.secretaria.detalle', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'Secretaría no puede enviar este trámite a Coordinadora desde su estado actual.']);
        }

        try {
            DB::transaction(function () use ($id_tramite) {
                DB::table('tbl_tramite')
                    ->where('id_tramite', $id_tramite)
                    ->update([
                        // BD en minúsculas
                        'resolucion_de_tramite_academico' => 'listo_coordinadora',
                    ]);
            });

            return redirect()
                ->route('cancelacion.secretaria.detalle', ['id_tramite' => $id_tramite])
                ->with('success', 'El trámite quedó listo para revisión de Coordinadora.');
        } catch (\Throwable $e) {
            Log::error('Error al marcar listo para Coordinadora - Secretaría cancelación', [
                'id_tramite' => $id_tramite,
                'error'      => $e->getMessage(),
            ]);

            return redirect()
                ->route('cancelacion.secretaria.detalle', ['id_tramite' => $id_tramite])
                ->withErrors([
                    'error' => 'No fue posible enviar el trámite a Coordinadora. ' .
                        (app()->isLocal() ? 'Detalle: ' . $e->getMessage() : '')
                ]);
        }
    }

    /**
     * Ver documento adjunto de cancelación.
     */
    public function verDocumento(int $id_documento)
    {
        $documento = DB::table('tbl_documento as d')
            ->join('tbl_tramite as t', 't.id_tramite', '=', 'd.id_tramite')
            ->where('d.id_documento', $id_documento)
            ->where('d.estado', 1)
            ->whereRaw('LOWER(TRIM(t.tipo_tramite_academico)) = ?', ['cancelacion'])
            ->select([
                'd.id_documento',
                'd.nombre_documento',
                'd.ruta_archivo',
                'd.tipo_documento',
                'd.id_tramite',
            ])
            ->first();

        if (!$documento) {
            abort(404, 'Documento no encontrado.');
        }

        $rutaFisica = $this->resolverRutaDocumento($documento->ruta_archivo ?? '');

        if (!$rutaFisica) {
            Log::warning('Documento no encontrado en almacenamiento', [
                'id_documento' => $documento->id_documento,
                'id_tramite'   => $documento->id_tramite,
                'ruta_archivo' => $documento->ruta_archivo,
            ]);

            abort(404, 'El archivo no existe en almacenamiento.');
        }

        return response()->file($rutaFisica, [
            'Content-Disposition' => 'inline; filename="' . ($documento->nombre_documento ?? 'documento') . '"',
        ]);
    }

    /* ============================================================
     * MÉTODOS PRIVADOS
     * ============================================================
     */

    private function obtenerTramiteCancelacion(int $id_tramite): ?object
    {
        return DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
            ->where('t.id_tramite', $id_tramite)
            ->whereRaw('LOWER(TRIM(t.tipo_tramite_academico)) = ?', ['cancelacion'])
            ->select([
                't.*',
                DB::raw('NULL as descripcion'),
                DB::raw("COALESCE(p.nombre_persona, 'Sin nombre') as nombre_estudiante"),
            ])
            ->first();
    }

    private function normalizarEstado(?string $estado): string
    {
        $estado = strtoupper(trim((string) $estado));
        return $estado === '' ? 'REVISION' : $estado;
    }

    private function secretariaPuedeGestionar(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_SECRETARIA, true);
    }

    private function resolverRutaDocumento(string $ruta): ?string
    {
        $ruta = trim($ruta);

        if ($ruta === '') {
            return null;
        }

        $ruta = ltrim($ruta, '/');

        $candidatasAbsolutas = [
            storage_path('app/public/' . $ruta),
            storage_path('app/' . $ruta),
            public_path('storage/' . $ruta),
        ];

        foreach ($candidatasAbsolutas as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        try {
            if (Storage::disk('public')->exists($ruta)) {
                return Storage::disk('public')->path($ruta);
            }
        } catch (\Throwable $e) {
            Log::warning('Error verificando Storage::disk(public)', [
                'ruta'  => $ruta,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function nombreDocumento(string $tipo): string
    {
        return match (strtoupper(trim($tipo))) {
            'DNI_FRENTE'          => 'Tarjeta de identidad (frente)',
            'DNI_REVERSO'         => 'Tarjeta de identidad (reverso)',
            'HISTORIAL_ACADEMICO' => 'Historial académico',
            'FORMA_003'           => 'Forma 003',
            'CONSTANCIA_MEDICA'   => 'Constancia médica',
            'CONSTANCIA_LABORAL'  => 'Constancia laboral',
            'RESPALDO_CALAMIDAD'  => 'Respaldo de calamidad doméstica',
            'ACTA_DEFUNCION'      => 'Acta de defunción',
            'TESTIMONIO_PADRES'   => 'Testimonio de padres',
            'OTRO_RESPALDO'       => 'Otro documento de respaldo',
            default               => $tipo,
        };
    }
}