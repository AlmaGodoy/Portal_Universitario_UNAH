<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CancelacionCoordinadoraController extends Controller
{
    /**
     * Estados visibles para Coordinadora.
     */
    private const ESTADOS_COORDINADORA = [
        'LISTO_COORDINADORA',
        'APROBADA',
        'RECHAZADA',
    ];

    /**
     * Bandeja principal de Coordinadora.
     */
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $this->normalizarEstado($request->input('estado', ''));

        $query = DB::table('tbl_tramite as t')
            ->leftJoin('tbl_persona as p', 'p.id_persona', '=', 't.id_persona')
            ->whereRaw('LOWER(TRIM(t.tipo_tramite_academico)) = ?', ['cancelacion'])
            ->whereIn(
                DB::raw("UPPER(TRIM(COALESCE(NULLIF(t.resolucion_de_tramite_academico, ''), 'LISTO_COORDINADORA')))"),
                self::ESTADOS_COORDINADORA
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

        if ($estado !== '' && in_array($estado, self::ESTADOS_COORDINADORA, true)) {
            $query->whereRaw(
                "UPPER(TRIM(COALESCE(NULLIF(t.resolucion_de_tramite_academico, ''), 'LISTO_COORDINADORA'))) = ?",
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

        return view('cancelacion_coordinadora_index', [
            'tramites' => $tramites,
            'buscar'   => $buscar,
            'estado'   => $estado,
            'estados'  => self::ESTADOS_COORDINADORA,
        ]);
    }

    /**
     * Ver detalle del trámite y documentos.
     */
    public function detalle(int $id_tramite)
    {
        $tramite = $this->obtenerTramiteCancelacion($id_tramite);

        if (!$tramite) {
            return redirect()
                ->route('cancelacion.coordinadora.index')
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

        return view('cancelacion_coordinadora_detalle', [
            'tramite'    => $tramite,
            'documentos' => $documentos,
            'estado'     => $this->normalizarEstado($tramite->resolucion_de_tramite_academico ?? 'LISTO_COORDINADORA'),
        ]);
    }

    /**
     * Aprobar trámite.
     */
    public function aprobar(Request $request, int $id_tramite)
    {
        $request->validate([
            'observacion' => ['nullable', 'string', 'max:1000'],
        ], [
            'observacion.max' => 'La observación no puede superar 1000 caracteres.',
        ]);

        $tramite = $this->obtenerTramiteCancelacion($id_tramite);

        if (!$tramite) {
            return redirect()
                ->route('cancelacion.coordinadora.index')
                ->withErrors(['error' => 'No se encontró el trámite de cancelación solicitado.']);
        }

        $estadoActual = $this->normalizarEstado($tramite->resolucion_de_tramite_academico ?? 'LISTO_COORDINADORA');

        if (!$this->coordinadoraPuedeDictaminar($estadoActual)) {
            return redirect()
                ->route('cancelacion.coordinadora.detalle', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'Este trámite ya no puede ser dictaminado por Coordinadora.']);
        }

        try {
            DB::transaction(function () use ($id_tramite) {
                DB::table('tbl_tramite')
                    ->where('id_tramite', $id_tramite)
                    ->update([
                        'resolucion_de_tramite_academico' => 'APROBADA',
                    ]);
            });

            return redirect()
                ->route('cancelacion.coordinadora.detalle', ['id_tramite' => $id_tramite])
                ->with('success', 'El trámite fue aprobado correctamente por Coordinadora.');
        } catch (\Throwable $e) {
            Log::error('Error al aprobar trámite de cancelación - Coordinadora', [
                'id_tramite' => $id_tramite,
                'error'      => $e->getMessage(),
            ]);

            return redirect()
                ->route('cancelacion.coordinadora.detalle', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'No fue posible aprobar el trámite.']);
        }
    }

    /**
     * Rechazar trámite.
     */
    public function rechazar(Request $request, int $id_tramite)
    {
        $request->validate([
            'observacion' => ['required', 'string', 'max:1000'],
        ], [
            'observacion.required' => 'Debe escribir una observación para rechazar el trámite.',
            'observacion.max'      => 'La observación no puede superar 1000 caracteres.',
        ]);

        $tramite = $this->obtenerTramiteCancelacion($id_tramite);

        if (!$tramite) {
            return redirect()
                ->route('cancelacion.coordinadora.index')
                ->withErrors(['error' => 'No se encontró el trámite de cancelación solicitado.']);
        }

        $estadoActual = $this->normalizarEstado($tramite->resolucion_de_tramite_academico ?? 'LISTO_COORDINADORA');

        if (!$this->coordinadoraPuedeDictaminar($estadoActual)) {
            return redirect()
                ->route('cancelacion.coordinadora.detalle', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'Este trámite ya no puede ser dictaminado por Coordinadora.']);
        }

        try {
            DB::transaction(function () use ($id_tramite) {
                DB::table('tbl_tramite')
                    ->where('id_tramite', $id_tramite)
                    ->update([
                        'resolucion_de_tramite_academico' => 'RECHAZADA',
                    ]);
            });

            return redirect()
                ->route('cancelacion.coordinadora.detalle', ['id_tramite' => $id_tramite])
                ->with('success', 'El trámite fue rechazado correctamente por Coordinadora.');
        } catch (\Throwable $e) {
            Log::error('Error al rechazar trámite de cancelación - Coordinadora', [
                'id_tramite' => $id_tramite,
                'error'      => $e->getMessage(),
            ]);

            return redirect()
                ->route('cancelacion.coordinadora.detalle', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'No fue posible rechazar el trámite.']);
        }
    }

    /**
     * Ver documento adjunto.
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
            Log::warning('Documento no encontrado en almacenamiento - Coordinadora', [
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
        return $estado === '' ? 'LISTO_COORDINADORA' : $estado;
    }

    private function coordinadoraPuedeDictaminar(string $estado): bool
    {
        return $estado === 'LISTO_COORDINADORA';
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
            Log::warning('Error verificando Storage::disk(public) - Coordinadora', [
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