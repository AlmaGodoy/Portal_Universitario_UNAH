<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CancelacionPaso2Controller extends Controller
{
    /**
     * Documentos base obligatorios.
     */
    private const TIPOS_BASE = [
        'DNI_FRENTE',
        'DNI_REVERSO',
        'HISTORIAL_ACADEMICO',
        'FORMA_003',
    ];

    /**
     * Documentos de alto riesgo.
     */
    private const TIPOS_RIESGO = [
        'CONSTANCIA_MEDICA',
        'CONSTANCIA_LABORAL',
    ];

    /**
     * Documentos flexibles para calamidad doméstica.
     */
    private const TIPOS_FLEX = [
        'RESPALDO_CALAMIDAD',
        'ACTA_DEFUNCION',
        'TESTIMONIO_PADRES',
        'OTRO_RESPALDO',
    ];

    /**
     * Muestra la vista del Paso 2.
     */
    public function index(int $id_tramite)
    {
        $tramite = $this->obtenerTramiteDelUsuario($id_tramite);

        if (!$tramite) {
            return redirect()->route('cancelacion.index')
                ->withErrors(['error' => 'No se encontró el trámite solicitado o no pertenece al usuario autenticado.']);
        }

        $motivoActual = session('cancelacion_excepcional.causa_justificada');

        $documentos = DB::table('tbl_documento')
            ->where('id_tramite', $id_tramite)
            ->where('estado', 1)
            ->orderByDesc('fecha_carga')
            ->get();

        return view('cancelacion_excepcional_Paso2', [
            'tramite'      => $tramite,
            'motivoActual' => $motivoActual,
            'documentos'   => $documentos,
        ]);
    }

    /**
     * Muestra la vista del Paso 3 / Éxito.
     */
    public function paso3(int $id_tramite)
    {
        $tramite = $this->obtenerTramiteDelUsuario($id_tramite);

        if (!$tramite) {
            return redirect()->route('cancelacion.index')
                ->withErrors(['error' => 'No se encontró el trámite solicitado o no pertenece al usuario autenticado.']);
        }

        $paso2Validado = session('cancelacion_excepcional.paso2_validado', false);
        $tramiteValidado = session('cancelacion_excepcional.id_tramite_validado');

        if (!$paso2Validado || (int) $tramiteValidado !== $id_tramite) {
            return redirect()->route('cancelacion.paso2', ['id_tramite' => $id_tramite])
                ->withErrors(['error' => 'Primero debe validar la documentación del paso 2.']);
        }

        return view('cancelacion_exito', [
            'mensaje' => 'Su solicitud fue procesada correctamente.',
            'tramite' => $tramite,
        ]);
    }

    /**
     * API: subir documento base.
     */
    public function subirDocumentoBase(Request $request, int $id_tramite)
    {
        if (!$this->obtenerTramiteDelUsuario($id_tramite)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Trámite no encontrado o sin permisos.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo_documento' => ['required', Rule::in(self::TIPOS_BASE)],
            'archivo'        => ['required', 'file'],
        ], [
            'tipo_documento.required' => 'Debe indicar el tipo de documento.',
            'tipo_documento.in'       => 'El tipo de documento base no es válido.',
            'archivo.required'        => 'Debe seleccionar un archivo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $validator->errors()->first(),
            ], 422);
        }

        $tipoDocumento = strtoupper(trim($request->tipo_documento));
        $archivo       = $request->file('archivo');

        try {
            $this->validarArchivoBase($tipoDocumento, $archivo);

            $resultado = $this->guardarDocumento(
                idTramite:     $id_tramite,
                tipoDocumento: $tipoDocumento,
                archivo:       $archivo,
            );

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Documento cargado correctamente.',
                'data'    => $resultado,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error subirDocumentoBase', [
                'id_tramite'     => $id_tramite,
                'tipo_documento' => $tipoDocumento,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => $this->limpiarMensaje($e),
            ], 500);
        }
    }

    /**
     * API: subir documento de alto riesgo.
     */
    public function subirDocumentoAltoRiesgo(Request $request, int $id_tramite)
    {
        if (!$this->obtenerTramiteDelUsuario($id_tramite)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Trámite no encontrado o sin permisos.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo_documento'   => ['required', Rule::in(self::TIPOS_RIESGO)],
            'archivo'          => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'tiene_referencia' => ['nullable', 'boolean'],
            'numero_folio'     => ['nullable', 'string', 'max:100'],
        ], [
            'tipo_documento.required' => 'Debe indicar el tipo de documento.',
            'tipo_documento.in'       => 'El tipo de documento de respaldo no es válido.',
            'archivo.required'        => 'Debe seleccionar un archivo.',
            'archivo.mimes'           => 'El archivo debe ser PDF, JPG o PNG.',
            'archivo.max'             => 'El archivo no puede superar los 10 MB.',
            'numero_folio.max'        => 'La referencia del documento no puede superar 100 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $validator->errors()->first(),
            ], 422);
        }

        $tipoDocumento   = strtoupper(trim($request->tipo_documento));
        $archivo         = $request->file('archivo');
        $tieneReferencia = filter_var($request->input('tiene_referencia', false), FILTER_VALIDATE_BOOL);
        $numeroFolio     = trim((string) $request->input('numero_folio', ''));

        if ($tieneReferencia && $numeroFolio === '') {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Debe ingresar la referencia visible del documento.',
            ], 422);
        }

        try {
            $resultado = $this->guardarDocumento(
                idTramite:     $id_tramite,
                tipoDocumento: $tipoDocumento,
                archivo:       $archivo,
                numeroFolio:   ($tieneReferencia && $numeroFolio !== '') ? $numeroFolio : null,
                antifraude:    ($tieneReferencia && $numeroFolio !== ''),
            );

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Documento cargado correctamente.',
                'data'    => $resultado,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error subirDocumentoAltoRiesgo', [
                'id_tramite'     => $id_tramite,
                'tipo_documento' => $tipoDocumento,
                'numero_folio'   => $numeroFolio,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => $this->limpiarMensaje($e),
            ], 500);
        }
    }

    /**
     * API: subir documento flexible.
     */
    public function subirDocumentoFlexible(Request $request, int $id_tramite)
    {
        if (!$this->obtenerTramiteDelUsuario($id_tramite)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Trámite no encontrado o sin permisos.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo_documento' => ['required', Rule::in(self::TIPOS_FLEX)],
            'archivo'        => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'tipo_documento.required' => 'Debe indicar el tipo de documento.',
            'tipo_documento.in'       => 'El tipo de documento flexible no es válido.',
            'archivo.required'        => 'Debe seleccionar un archivo.',
            'archivo.mimes'           => 'El archivo debe ser PDF, JPG o PNG.',
            'archivo.max'             => 'El archivo no puede superar los 10 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $validator->errors()->first(),
            ], 422);
        }

        $tipoDocumento = strtoupper(trim($request->tipo_documento));
        $archivo       = $request->file('archivo');

        try {
            $resultado = $this->guardarDocumento(
                idTramite:     $id_tramite,
                tipoDocumento: $tipoDocumento,
                archivo:       $archivo,
            );

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Documento cargado correctamente.',
                'data'    => $resultado,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error subirDocumentoFlexible', [
                'id_tramite'     => $id_tramite,
                'tipo_documento' => $tipoDocumento,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => $this->limpiarMensaje($e),
            ], 500);
        }
    }

    /**
     * API: eliminar documento.
     */
    public function eliminarDocumento(int $id_tramite, int $id_documento)
    {
        if (!$this->obtenerTramiteDelUsuario($id_tramite)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Trámite no encontrado o sin permisos.',
            ], 404);
        }

        $documento = DB::table('tbl_documento')
            ->where('id_documento', $id_documento)
            ->where('id_tramite', $id_tramite)
            ->where('estado', 1)
            ->first();

        if (!$documento) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Documento no encontrado.',
            ], 404);
        }

        try {
            DB::statement('CALL SOFT_DEL_DOC_EXCEPCIONAL(?, ?, ?)', [
                $id_documento,
                Auth::user()->id ?? null,
                'Eliminado por el usuario desde el formulario',
            ]);

            if (!empty($documento->ruta_archivo) && Storage::disk('public')->exists($documento->ruta_archivo)) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Documento eliminado correctamente.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error eliminarDocumento', [
                'id_tramite'   => $id_tramite,
                'id_documento' => $id_documento,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => $this->limpiarMensaje($e),
            ], 500);
        }
    }

    /**
     * API: validar paso 2.
     */
    public function validarPaso2(Request $request, int $id_tramite)
    {
        if (!$this->obtenerTramiteDelUsuario($id_tramite)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Trámite no encontrado o sin permisos.',
            ], 404);
        }

        $motivoActual = session('cancelacion_excepcional.causa_justificada');

        if (!$motivoActual) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No se encontró el motivo de cancelación en la sesión. Regrese al paso anterior.',
            ], 422);
        }

        $faltantes = [];

        foreach (self::TIPOS_BASE as $tipoBase) {
            if (!$this->existeDocumentoActivo($id_tramite, $tipoBase)) {
                $faltantes[] = $this->nombreDocumento($tipoBase);
            }
        }

        if ($motivoActual === 'ENFERMEDAD_ACCIDENTE' && !$this->existeDocumentoActivo($id_tramite, 'CONSTANCIA_MEDICA')) {
            $faltantes[] = 'Constancia médica';
        }

        if ($motivoActual === 'PROBLEMAS_LABORALES' && !$this->existeDocumentoActivo($id_tramite, 'CONSTANCIA_LABORAL')) {
            $faltantes[] = 'Constancia laboral';
        }

        if ($motivoActual === 'CALAMIDAD_DOMESTICA') {
            $hayRespaldo =
                $this->existeDocumentoActivo($id_tramite, 'RESPALDO_CALAMIDAD') ||
                $this->existeDocumentoActivo($id_tramite, 'ACTA_DEFUNCION') ||
                $this->existeDocumentoActivo($id_tramite, 'TESTIMONIO_PADRES') ||
                $this->existeDocumentoActivo($id_tramite, 'OTRO_RESPALDO');

            if (!$hayRespaldo) {
                $faltantes[] = 'Documento de respaldo de calamidad doméstica';
            }
        }

        if (!empty($faltantes)) {
            return response()->json([
                'ok'        => false,
                'mensaje'   => 'Aún faltan documentos por cargar.',
                'faltantes' => $faltantes,
            ], 422);
        }

        try {
            $resultado = DB::selectOne('CALL VAL_TRAMITE_ANTIFRAUDE(?)', [$id_tramite]);

            if ($resultado && isset($resultado->resultado) && $resultado->resultado !== 'OK') {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => $resultado->mensaje ?? 'La validación antifraude no fue exitosa.',
                ], 422);
            }

            session([
                'cancelacion_excepcional.paso2_validado' => true,
                'cancelacion_excepcional.id_tramite_validado' => $id_tramite,
            ]);

            return response()->json([
                'ok'       => true,
                'mensaje'  => 'Paso 2 validado correctamente.',
                'redirect' => route('cancelacion.paso3', ['id_tramite' => $id_tramite]),
            ]);

        } catch (\Throwable $e) {
            Log::error('Error validarPaso2', [
                'id_tramite' => $id_tramite,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'mensaje' => $this->limpiarMensaje($e),
            ], 500);
        }
    }

    /* ============================================================
     * MÉTODOS PRIVADOS
     * ============================================================
     */

    private function obtenerTramiteDelUsuario(int $idTramite): ?object
    {
        $idPersona = Auth::user()->id_persona ?? null;

        if (!$idPersona) {
            return null;
        }

        return DB::table('tbl_tramite')
            ->where('id_tramite', $idTramite)
            ->where('id_persona', $idPersona)
            ->first();
    }

    private function existeDocumentoActivo(int $idTramite, string $tipoDocumento): bool
    {
        return (bool) DB::selectOne(
            'SELECT FN_EXISTE_DOCUMENTO(?, ?) AS existe',
            [$idTramite, $tipoDocumento]
        )?->existe;
    }

    private function guardarDocumento(
        int $idTramite,
        string $tipoDocumento,
        UploadedFile $archivo,
        ?string $numeroFolio = null,
        bool $antifraude = false,
    ): array {
        if ($antifraude) {
            $idPersona = Auth::user()->id_persona ?? null;

            if (!$idPersona) {
                throw new \RuntimeException('No fue posible identificar la persona autenticada.');
            }
        }

        $hash          = $this->generarHashArchivo($archivo);
        $nombreArchivo = $this->generarNombreArchivo($tipoDocumento, $archivo);
        $rutaArchivo   = $this->guardarArchivo($archivo, $nombreArchivo, $idTramite);

        try {
            if ($antifraude) {
                DB::statement('CALL INS_DOCUMENTO_ANTIFRAUDE(?, ?, ?, ?, ?, ?, ?, ?)', [
                    $idTramite,
                    $idPersona,
                    $this->obtenerIdBitacora(),
                    $tipoDocumento,
                    $nombreArchivo,
                    $hash,
                    $rutaArchivo,
                    $numeroFolio,
                ]);
            } else {
                DB::statement('CALL INS_SUBIR_DOCUMENTO(?, ?, ?, ?, ?)', [
                    $idTramite,
                    $tipoDocumento,
                    $nombreArchivo,
                    $hash,
                    $rutaArchivo,
                ]);
            }

            return [
                'tipo_documento'   => $tipoDocumento,
                'nombre_documento' => $nombreArchivo,
                'ruta_archivo'     => $rutaArchivo,
                'numero_folio'     => $numeroFolio,
            ];

        } catch (\Throwable $e) {
            if (Storage::disk('public')->exists($rutaArchivo)) {
                Storage::disk('public')->delete($rutaArchivo);
            }

            throw $e;
        }
    }

    private function validarArchivoBase(string $tipoDocumento, UploadedFile $archivo): void
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        $sizeKb    = (int) round($archivo->getSize() / 1024);

        if (in_array($tipoDocumento, ['DNI_FRENTE', 'DNI_REVERSO', 'FORMA_003'], true)) {
            if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                throw new \RuntimeException('El archivo debe ser PDF, JPG o PNG.');
            }

            if ($sizeKb > 8192) {
                throw new \RuntimeException('El archivo supera el tamaño máximo permitido de 8 MB.');
            }
        }

        if ($tipoDocumento === 'HISTORIAL_ACADEMICO') {
            if ($extension !== 'pdf') {
                throw new \RuntimeException('El historial académico debe subirse en PDF.');
            }

            if ($sizeKb > 10240) {
                throw new \RuntimeException('El historial académico supera el tamaño máximo permitido de 10 MB.');
            }
        }
    }

    private function generarHashArchivo(UploadedFile $archivo): string
    {
        return hash_file('sha256', $archivo->getRealPath());
    }

    private function generarNombreArchivo(string $tipoDocumento, UploadedFile $archivo): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        $base      = Str::slug($tipoDocumento . '_' . now()->format('Ymd_His') . '_' . Str::random(8));

        return $base . '.' . $extension;
    }

    private function guardarArchivo(UploadedFile $archivo, string $nombreArchivo, int $idTramite): string
    {
        $carpeta = 'cancelacion_excepcional/' . $idTramite;

        return $archivo->storeAs($carpeta, $nombreArchivo, 'public');
    }

    private function obtenerIdBitacora(): ?int
    {
        try {
            $fila = DB::table('tbl_bitacora')
                ->selectRaw('MAX(id_bitacora) AS id_bitacora')
                ->first();

            return $fila?->id_bitacora ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nombreDocumento(string $tipo): string
    {
        return match ($tipo) {
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

    private function limpiarMensaje(\Throwable $e): string
    {
        $mensaje = $e->getMessage();

        if (str_contains($mensaje, 'Duplicate entry')) {
            return 'Ya existe un documento con un identificador igual en el sistema.';
        }

        if (str_contains($mensaje, 'El folio ya está registrado en el sistema')) {
            return 'No fue posible validar el documento cargado. Verifique la información ingresada o adjunte un documento válido.';
        }

        if (str_contains($mensaje, 'ALERTA:')) {
            return 'No fue posible validar el documento cargado. Verifique la información ingresada o adjunte un documento válido.';
        }

        if (str_contains($mensaje, 'ERROR:')) {
            return trim(str_replace('ERROR:', '', $mensaje));
        }

        return 'Ocurrió un problema al procesar el documento. Intente nuevamente.';
    }
}