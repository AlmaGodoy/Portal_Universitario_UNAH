<?php

namespace App\Http\Controllers;

use App\Models\SolicitudEquivalencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EquivalenciaController extends Controller
{
    /**
     * Vista del alumno.
     */
    public function indexAlumno()
    {
        return view('equivalencias_alumno');
    }

    /**
     * Vista del revisor.
     */
    public function indexRevisor()
    {
        $rol = $this->obtenerRolActual();

        if (!SolicitudEquivalencia::puedeRevisarEquivalencias($rol)) {
            abort(403, 'No tiene permisos para acceder a la revisión de equivalencias.');
        }

        return view('equivalencias_revisor', [
            'rolActual' => $rol,
        ]);
    }

    /**
     * Lista las solicitudes del alumno autenticado.
     */
    public function misSolicitudes()
    {
        try {
            $idPersona = $this->obtenerIdPersonaAutenticada();

            if (!$idPersona) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo identificar la persona autenticada.',
                ], 401);
            }

            $resultado = SolicitudEquivalencia::misSolicitudesPorPersona($idPersona);

            return response()->json([
                'ok' => true,
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al listar solicitudes del alumno', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible listar las solicitudes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lista solicitudes pendientes para revisión.
     */
    public function solicitudesPendientes()
    {
        try {
            $rol = $this->obtenerRolActual();

            if (!SolicitudEquivalencia::puedeRevisarEquivalencias($rol)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para consultar solicitudes pendientes.',
                ], 403);
            }

            $resultado = SolicitudEquivalencia::solicitudesPendientes();

            return response()->json([
                'ok' => true,
                'rol' => $rol,
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al listar solicitudes pendientes', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible listar las solicitudes pendientes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea la solicitud de equivalencia.
     */
    public function crearSolicitud(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'version_plan_viejo' => 'required|integer|in:2019,2022',
                'version_plan_nuevo' => 'nullable|integer|in:2026',
                'documento'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'observacion_alumno' => 'nullable|string|max:255',
            ],
            [
                'version_plan_viejo.required' => 'Debe seleccionar un plan viejo.',
                'version_plan_viejo.integer'  => 'El plan viejo no es válido.',
                'version_plan_viejo.in'       => 'El plan viejo seleccionado no es válido.',
                'version_plan_nuevo.integer'  => 'El plan nuevo no es válido.',
                'version_plan_nuevo.in'       => 'El plan nuevo debe ser 2026.',
                'documento.required'          => 'Debe adjuntar el historial académico.',
                'documento.file'              => 'El archivo adjunto no es válido.',
                'documento.mimes'             => 'El documento debe ser PDF, JPG, JPEG o PNG.',
                'documento.max'               => 'El documento no puede exceder 10 MB.',
                'observacion_alumno.max'      => 'La observación no puede exceder 255 caracteres.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rutaGuardada = null;

        try {
            $idPersona = $this->obtenerIdPersonaAutenticada();

            if (!$idPersona) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo identificar la persona autenticada.',
                ], 401);
            }

            $archivo = $request->file('documento');
            $extension = strtolower($archivo->getClientOriginalExtension());
            $tipoDocumento = strtoupper($extension === 'jpeg' ? 'JPG' : $extension);

            $nombreArchivo = 'equiv_' . $idPersona . '_' . now()->format('Ymd_His') . '.' . $extension;

            $rutaGuardada = $archivo->storeAs(
                'equivalencias/' . now()->format('Y/m'),
                $nombreArchivo
            );

            $resultado = DB::select('CALL INS_SOLICITUD_EQUIVALENCIA(?, ?, ?, ?, ?, ?)', [
                $idPersona,
                (int) $request->version_plan_viejo,
                2026,
                $rutaGuardada,
                $tipoDocumento,
                $request->observacion_alumno,
            ]);

            $idSolicitud = $resultado[0]->id_solicitud_equivalencia ?? null;

            return response()->json([
                'ok' => true,
                'message' => 'Solicitud creada correctamente.',
                'id_solicitud_equivalencia' => $idSolicitud,
                'ruta_documento' => $rutaGuardada,
            ]);
        } catch (\Throwable $e) {
            if (!empty($rutaGuardada) && Storage::exists($rutaGuardada)) {
                Storage::delete($rutaGuardada);
            }

            Log::error('Error al crear solicitud de equivalencia', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible crear la solicitud.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve las asignaturas del plan viejo.
     */
    public function obtenerAsignaturasPlanViejo($versionPlanViejo)
    {
        try {
            $versionPlanViejo = (int) $versionPlanViejo;

            if (!in_array($versionPlanViejo, [2019, 2022])) {
                return response()->json([
                    'ok' => false,
                    'message' => 'La versión del plan viejo no es válida.',
                ], 422);
            }

            $resultado = DB::select('CALL SEL_ASIGNATURAS_PLAN_VIEJO(?)', [
                $versionPlanViejo
            ]);

            return response()->json([
                'ok' => true,
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener asignaturas del plan viejo', [
                'error' => $e->getMessage(),
                'version_plan_viejo' => $versionPlanViejo,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible obtener las asignaturas del plan viejo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guarda las materias seleccionadas por el alumno.
     */
    public function guardarDetalleSolicitud(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_solicitud_equivalencia'               => 'required|integer',
                'version_plan_viejo'                      => 'required|integer|in:2019,2022',
                'asignaturas'                             => 'required|array|min:1',
                'asignaturas.*.codigo_asignatura_viejo'   => 'required|string|max:20',
                'asignaturas.*.seleccionada_alumno'       => 'nullable|boolean',
            ],
            [
                'id_solicitud_equivalencia.required' => 'El id de solicitud es obligatorio.',
                'id_solicitud_equivalencia.integer'  => 'El id de solicitud no es válido.',
                'version_plan_viejo.required'        => 'La versión del plan viejo es obligatoria.',
                'version_plan_viejo.integer'         => 'La versión del plan viejo no es válida.',
                'version_plan_viejo.in'              => 'La versión del plan viejo no es válida.',
                'asignaturas.required'               => 'Debes seleccionar al menos una materia.',
                'asignaturas.array'                  => 'El formato de materias no es válido.',
                'asignaturas.min'                    => 'Debes seleccionar al menos una materia.',
                'asignaturas.*.codigo_asignatura_viejo.required' => 'Cada materia debe tener código.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $idSolicitud = (int) $request->id_solicitud_equivalencia;
            $versionPlanViejo = (int) $request->version_plan_viejo;
            $asignaturas = $request->input('asignaturas', []);
            $idPersona = $this->obtenerIdPersonaAutenticada();

            if (!$idPersona) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo identificar la persona autenticada.',
                ], 401);
            }

            if (!SolicitudEquivalencia::esPropietario($idSolicitud, $idPersona)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para modificar esta solicitud.',
                ], 403);
            }

            if (empty($asignaturas)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se recibió ninguna materia para guardar.',
                ], 422);
            }

            $pdo = DB::connection()->getPdo();

            foreach ($asignaturas as $asignatura) {
                $codigo = trim((string) ($asignatura['codigo_asignatura_viejo'] ?? ''));

                if ($codigo === '') {
                    continue;
                }

                $seleccionada = array_key_exists('seleccionada_alumno', $asignatura)
                    ? (int) ((bool) $asignatura['seleccionada_alumno'])
                    : 1;

                $stmt = $pdo->prepare('CALL INS_SOLICITUD_EQUIVALENCIA_DETALLE(?, ?, ?, ?, ?)');
                $stmt->execute([
                    $idSolicitud,
                    $versionPlanViejo,
                    $codigo,
                    null,
                    $seleccionada,
                ]);

                while ($stmt->nextRowset()) {
                    // Consumir todos los result sets del procedimiento.
                }

                $stmt->closeCursor();
            }

            return response()->json([
                'ok' => true,
                'message' => 'Materias guardadas correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar detalle de la solicitud', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible guardar el detalle.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve la cabecera de una solicitud.
     */
    public function verCabeceraSolicitud($idSolicitud)
    {
        try {
            $idSolicitud = (int) $idSolicitud;
            $rol = $this->obtenerRolActual();
            $idPersona = $this->obtenerIdPersonaAutenticada();

            $cabecera = SolicitudEquivalencia::obtenerCabecera($idSolicitud);

            if (!$cabecera) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Solicitud no encontrada.',
                ], 404);
            }

            if (!SolicitudEquivalencia::puedeAcceder($cabecera, $rol, $idPersona)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para consultar esta solicitud.',
                ], 403);
            }

            return response()->json([
                'ok' => true,
                'rol' => $rol,
                'data' => $cabecera,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al consultar cabecera de solicitud', [
                'error' => $e->getMessage(),
                'id_solicitud_equivalencia' => $idSolicitud,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible consultar la cabecera de la solicitud.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve el detalle de una solicitud.
     */
    public function verDetalleSolicitud($idSolicitud)
    {
        try {
            $idSolicitud = (int) $idSolicitud;
            $rol = $this->obtenerRolActual();
            $idPersona = $this->obtenerIdPersonaAutenticada();

            $cabecera = SolicitudEquivalencia::obtenerCabecera($idSolicitud);

            if (!$cabecera) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Solicitud no encontrada.',
                ], 404);
            }

            if (!SolicitudEquivalencia::puedeAcceder($cabecera, $rol, $idPersona)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para consultar el detalle.',
                ], 403);
            }

            $resultado = SolicitudEquivalencia::obtenerDetalle($idSolicitud);

            return response()->json([
                'ok' => true,
                'rol' => $rol,
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al consultar detalle de solicitud', [
                'error' => $e->getMessage(),
                'id_solicitud_equivalencia' => $idSolicitud,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible consultar el detalle de la solicitud.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Devuelve equivalencias preliminares.
     */
    public function verEquivalenciasPreliminares($idSolicitud)
    {
        try {
            $idSolicitud = (int) $idSolicitud;
            $rol = $this->obtenerRolActual();
            $idPersona = $this->obtenerIdPersonaAutenticada();

            $cabecera = SolicitudEquivalencia::obtenerCabecera($idSolicitud);

            if (!$cabecera) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Solicitud no encontrada.',
                ], 404);
            }

            if (!SolicitudEquivalencia::puedeAcceder($cabecera, $rol, $idPersona)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para consultar estas equivalencias.',
                ], 403);
            }

            $resultado = SolicitudEquivalencia::obtenerEquivalenciasPreliminares($idSolicitud);

            return response()->json([
                'ok' => true,
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al consultar equivalencias preliminares', [
                'error' => $e->getMessage(),
                'id_solicitud_equivalencia' => $idSolicitud,
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible consultar las equivalencias preliminares.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Valida una materia puntual.
     */
    public function validarDetalleSolicitud(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_solicitud_equivalencia' => 'required|integer',
                'version_plan_viejo'        => 'required|integer|in:2019,2022',
                'codigo_asignatura_viejo'   => 'required|string|max:20',
                'validada_revisor'          => 'required|boolean',
                'observacion_revision'      => 'nullable|string|max:255',
            ],
            [
                'id_solicitud_equivalencia.required' => 'El id de solicitud es obligatorio.',
                'version_plan_viejo.required'        => 'La versión del plan viejo es obligatoria.',
                'codigo_asignatura_viejo.required'   => 'El código de asignatura es obligatorio.',
                'validada_revisor.required'          => 'Debe indicar si la materia fue validada.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $rol = $this->obtenerRolActual();

            if (!SolicitudEquivalencia::puedeRevisarEquivalencias($rol)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para validar el detalle.',
                ], 403);
            }

            DB::select('CALL UPD_VALIDACION_SOLICITUD_EQUIVALENCIA_DETALLE(?, ?, ?, ?, ?)', [
                (int) $request->id_solicitud_equivalencia,
                (int) $request->version_plan_viejo,
                trim((string) $request->codigo_asignatura_viejo),
                (int) ((bool) $request->boolean('validada_revisor')),
                $request->observacion_revision,
            ]);

            return response()->json([
                'ok' => true,
                'rol' => $rol,
                'message' => 'Detalle validado correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al validar detalle de solicitud', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible validar el detalle.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambia el estado general de la solicitud.
     */
    public function validarSolicitud(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_solicitud_equivalencia' => 'required|integer',
                'estado_solicitud'          => 'required|string|max:20',
                'observacion_revisor'       => 'nullable|string|max:255',
            ],
            [
                'id_solicitud_equivalencia.required' => 'El id de solicitud es obligatorio.',
                'estado_solicitud.required'          => 'El estado de la solicitud es obligatorio.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $rol = $this->obtenerRolActual();
            $estado = strtoupper(trim((string) $request->estado_solicitud));

            if (!SolicitudEquivalencia::puedeRevisarEquivalencias($rol)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No tiene permisos para validar esta solicitud.',
                ], 403);
            }

            if (SolicitudEquivalencia::esSecretaria($rol) && !in_array($estado, ['PENDIENTE', 'EN_REVISION'])) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Secretaría solo puede dejar la solicitud en PENDIENTE o EN_REVISION.',
                ], 403);
            }

            if (SolicitudEquivalencia::esCoordinacion($rol) && !in_array($estado, ['EN_REVISION', 'APROBADA', 'RECHAZADA'])) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Coordinación solo puede dejar la solicitud en EN_REVISION, APROBADA o RECHAZADA.',
                ], 403);
            }

            $idUsuarioRevisor = $this->obtenerIdUsuarioAutenticado();

            DB::select('CALL UPD_VALIDACION_SOLICITUD_EQUIVALENCIA(?, ?, ?, ?)', [
                (int) $request->id_solicitud_equivalencia,
                $estado,
                $request->observacion_revisor,
                $idUsuarioRevisor,
            ]);

            return response()->json([
                'ok' => true,
                'rol' => $rol,
                'message' => 'Solicitud actualizada correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al validar solicitud', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No fue posible actualizar la solicitud.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descarga el documento adjunto.
     */
    public function descargarDocumento($idSolicitud)
    {
        try {
            $idSolicitud = (int) $idSolicitud;
            $rol = $this->obtenerRolActual();
            $idPersona = $this->obtenerIdPersonaAutenticada();

            $solicitud = SolicitudEquivalencia::obtenerDocumento($idSolicitud);

            if (!$solicitud || empty($solicitud->ruta_documento)) {
                abort(404, 'Documento no encontrado.');
            }

            if (!SolicitudEquivalencia::puedeRevisarEquivalencias($rol)) {
                if (!$idPersona || (int) ($solicitud->id_persona ?? 0) !== $idPersona) {
                    abort(403, 'No tiene permisos para descargar el documento.');
                }
            }

            if (!Storage::exists($solicitud->ruta_documento)) {
                abort(404, 'Documento no encontrado.');
            }

            $extension = strtolower((string) $solicitud->tipo_documento);
            $nombre = 'historial_equivalencia_' . $solicitud->id_persona . '_' . $idSolicitud . '.' . $extension;

            return Storage::download($solicitud->ruta_documento, $nombre);
        } catch (\Throwable $e) {
            Log::error('Error al descargar documento de solicitud', [
                'error' => $e->getMessage(),
                'id_solicitud_equivalencia' => $idSolicitud,
            ]);

            abort(500, 'No fue posible descargar el documento.');
        }
    }

    /**
     * Obtiene id_persona del usuario autenticado.
     */
    private function obtenerIdPersonaAutenticada(): ?int
    {
        $usuario = Auth::user();

        if ($usuario && !empty($usuario->id_persona)) {
            return (int) $usuario->id_persona;
        }

        if (session()->has('id_persona')) {
            return (int) session('id_persona');
        }

        if (session()->has('usuario.id_persona')) {
            return (int) session('usuario.id_persona');
        }

        return null;
    }

    /**
     * Obtiene id del usuario autenticado.
     */
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

    /**
     * Obtiene el rol actual desde sesión o usuario.
     */
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
}


