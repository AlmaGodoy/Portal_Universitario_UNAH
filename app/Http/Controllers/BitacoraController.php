<?php

namespace App\Http\Controllers;

use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BitacoraController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Identificadores de roles
    |--------------------------------------------------------------------------
    |
    | 1 = Secretaría General
    | 4 = Coordinador
    | 5 = Secretaría de Carrera
    |
    */

    private const ROL_SECRETARIA_GENERAL = 1;
    private const ROL_COORDINADOR = 4;
    private const ROL_SECRETARIA_CARRERA = 5;

    /**
     * Inyecta el servicio encargado de consultar la bitácora.
     */
    public function __construct(
        private readonly BitacoraService $bitacoraService
    ) {
    }

    /**
     * Redirige al usuario autenticado a la bitácora correspondiente
     * según su rol.
     */
    public function index()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        return match ((int) $usuario->id_rol) {
            self::ROL_SECRETARIA_GENERAL =>
                redirect()->route('bitacora.secretaria_general'),

            self::ROL_COORDINADOR =>
                redirect()->route('bitacora.coordinador'),

            self::ROL_SECRETARIA_CARRERA =>
                redirect()->route('bitacora.secretaria_carrera'),

            default => abort(
                403,
                'No tiene autorización para consultar la bitácora.'
            ),
        };
    }

    /**
     * Muestra la bitácora de trámites para el Coordinador.
     *
     * El Coordinador únicamente puede consultar información
     * relacionada con su carrera asignada.
     */
    public function coordinador(Request $request)
    {
        $usuario = $this->usuarioAutorizado([
            self::ROL_COORDINADOR,
        ]);

        $idCarrera = $this->obtenerCarreraEmpleado(
            isset($usuario->id_persona)
                ? (int) $usuario->id_persona
                : null
        );

        if (!$idCarrera) {
            return back()->with(
                'error',
                'El coordinador no tiene una carrera asignada.'
            );
        }

        $filtros = $this->validarFiltros($request);

        $bitacoras = $this->bitacoraService
            ->consultarBitacoraTramites(
                request: $request,
                filtros: $filtros,
                carreraContextoObligatoria: $idCarrera
            );

        return view(
            'bitacora_coordinador',
            compact(
                'bitacoras',
                'filtros'
            )
        );
    }

    /**
     * Muestra la bitácora de trámites para Secretaría de Carrera.
     *
     * La Secretaría de Carrera únicamente puede consultar
     * información relacionada con su carrera asignada.
     */
    public function secretariaCarrera(Request $request)
    {
        $usuario = $this->usuarioAutorizado([
            self::ROL_SECRETARIA_CARRERA,
        ]);

        $idCarrera = $this->obtenerCarreraEmpleado(
            isset($usuario->id_persona)
                ? (int) $usuario->id_persona
                : null
        );

        if (!$idCarrera) {
            return back()->with(
                'error',
                'La Secretaría de Carrera no tiene una carrera asignada.'
            );
        }

        $filtros = $this->validarFiltros($request);

        $bitacoras = $this->bitacoraService
            ->consultarBitacoraTramites(
                request: $request,
                filtros: $filtros,
                carreraContextoObligatoria: $idCarrera
            );

        return view(
            'bitacora_secretaria_carrera',
            compact(
                'bitacoras',
                'filtros'
            )
        );
    }

    /**
     * Muestra la bitácora general del sistema para Secretaría General.
     *
     * Secretaría General puede consultar registros de todas las
     * carreras, módulos y usuarios.
     */
    public function secretariaGeneral(Request $request)
    {
        $this->usuarioAutorizado([
            self::ROL_SECRETARIA_GENERAL,
        ]);

        $filtros = $this->validarFiltros($request);

        $carreras = DB::table('tbl_carrera')
            ->select(
                'id_carrera',
                'nombre_carrera'
            )
            ->orderBy('nombre_carrera')
            ->get();

        $bitacoras = $this->bitacoraService
            ->consultarBitacoraSistema(
                request: $request,
                filtros: $filtros
            );

        return view(
            'bitacora_secretaria_general',
            compact(
                'bitacoras',
                'carreras',
                'filtros'
            )
        );
    }

    /**
     * Valida y normaliza los filtros enviados desde las vistas.
     */
    private function validarFiltros(
        Request $request
    ): array {
        /*
         * Cuando las fechas no son enviadas o llegan vacías,
         * se utiliza el período desde el inicio del año hasta hoy.
         */
        $request->merge([
            'fecha_inicio' => $request->filled('fecha_inicio')
                ? $request->input('fecha_inicio')
                : now()->startOfYear()->toDateString(),

            'fecha_fin' => $request->filled('fecha_fin')
                ? $request->input('fecha_fin')
                : now()->toDateString(),
        ]);

        $datos = $request->validate([
            'fecha_inicio' => [
                'required',
                'date',
            ],

            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],

            'id_usuario' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'id_rol' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'id_carrera_actor' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'id_carrera_contexto' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'id_objeto' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'id_tramite' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'modulo' => [
                'nullable',
                'string',
                'max:100',
            ],

            'accion' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tipo_tramite' => [
                'nullable',
                'in:cancelacion,cambio_carrera,reposicion',
            ],

            'estado' => [
                'nullable',
                'string',
                'max:50',
            ],

            'nivel' => [
                'nullable',
                'in:INFO,ADVERTENCIA,ERROR,SEGURIDAD,CRITICO',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],
        ]);

        return [
            'fecha_inicio' =>
                $datos['fecha_inicio'],

            'fecha_fin' =>
                $datos['fecha_fin'],

            'id_usuario' =>
                $datos['id_usuario'] ?? null,

            'id_rol' =>
                $datos['id_rol'] ?? null,

            'id_carrera_actor' =>
                $datos['id_carrera_actor'] ?? null,

            'id_carrera_contexto' =>
                $datos['id_carrera_contexto'] ?? null,

            'id_objeto' =>
                $datos['id_objeto'] ?? null,

            'id_tramite' =>
                $datos['id_tramite'] ?? null,

            'modulo' =>
                $this->textoONull(
                    $datos['modulo'] ?? null
                ),

            'accion' =>
                $this->textoONull(
                    $datos['accion'] ?? null
                ),

            'tipo_tramite' =>
                $datos['tipo_tramite'] ?? null,

            'estado' =>
                $this->textoONull(
                    $datos['estado'] ?? null
                ),

            'nivel' =>
                $datos['nivel'] ?? null,
        ];
    }

    /**
     * Convierte cadenas vacías en NULL y elimina espacios
     * al inicio y al final.
     */
    private function textoONull(
        mixed $valor
    ): ?string {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto !== ''
            ? $texto
            : null;
    }

    /**
     * Obtiene la carrera asignada al empleado.
     */
    private function obtenerCarreraEmpleado(
        ?int $idPersona
    ): ?int {
        if (!$idPersona) {
            return null;
        }

        $idCarrera = DB::table('tbl_empleados')
            ->where('id_persona', $idPersona)
            ->value('id_carrera');

        return $idCarrera !== null
            ? (int) $idCarrera
            : null;
    }

    /**
     * Verifica que el usuario esté autenticado y posea
     * uno de los roles permitidos.
     */
    private function usuarioAutorizado(
        array $rolesPermitidos
    ) {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        if (
            !in_array(
                (int) $usuario->id_rol,
                $rolesPermitidos,
                true
            )
        ) {
            abort(
                403,
                'No tiene autorización para consultar esta bitácora.'
            );
        }

        return $usuario;
    }
}