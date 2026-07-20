<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditoriaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLES DEL SISTEMA
    |--------------------------------------------------------------------------
    |
    | 1 = Secretaría Académica / Secretaría General
    | 4 = Coordinador de Carrera
    | 5 = Secretaría de Carrera
    |
    */

    private const ROL_SECRETARIA_GENERAL = 1;
    private const ROL_COORDINADOR = 4;
    private const ROL_SECRETARIA_CARRERA = 5;

    /**
     * Guarda temporalmente las columnas consultadas para evitar
     * realizar SHOW COLUMNS repetidamente durante la misma solicitud.
     */
    private array $columnasCache = [];

    /*
    |--------------------------------------------------------------------------
    | REDIRECCIÓN PRINCIPAL
    |--------------------------------------------------------------------------
    */

    /**
     * Punto de entrada general del módulo de Auditoría.
     */
    public function index()
    {
        return $this->redirectAuditoria();
    }

    /**
     * Redirige al usuario a la auditoría correspondiente según su rol.
     */
    public function redirectAuditoria()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        return match ((int) $usuario->id_rol) {
            self::ROL_SECRETARIA_GENERAL =>
                redirect()->route('auditoria.administrativa'),

            self::ROL_COORDINADOR =>
                redirect()->route('auditoria.coordinador'),

            self::ROL_SECRETARIA_CARRERA =>
                redirect()->route('auditoria.general'),

            default => abort(
                403,
                'No tiene autorización para consultar la auditoría.'
            ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | SECRETARÍA ACADÉMICA / GENERAL
    |--------------------------------------------------------------------------
    |
    | Rol 1: acceso global a todas las carreras.
    |
    */

    public function administrativa(Request $request)
    {
        $usuario = $this->usuarioAutorizado([
            self::ROL_SECRETARIA_GENERAL,
        ]);

        $filtros = $this->validarFiltros($request);

        $auditorias = $this->consultarAuditorias(
            request: $request,
            filtros: $filtros,
            carreraObligatoria: null
        );

        $datosVista = $this->construirDatosVista(
            usuario: $usuario,
            auditorias: $auditorias,
            filtros: $filtros,
            carreras: $this->obtenerCarreras(),
            idCarreraAsignada: null
        );

        return view(
            'auditoria_secretaria_academica',
            $datosVista
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COORDINADOR DE CARRERA
    |--------------------------------------------------------------------------
    |
    | Rol 4: únicamente puede consultar auditorías relacionadas con
    | su carrera asignada.
    |
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

        $auditorias = $this->consultarAuditorias(
            request: $request,
            filtros: $filtros,
            carreraObligatoria: $idCarrera
        );

        $datosVista = $this->construirDatosVista(
            usuario: $usuario,
            auditorias: $auditorias,
            filtros: $filtros,
            carreras: $this->obtenerCarreraPorId($idCarrera),
            idCarreraAsignada: $idCarrera
        );

        return view(
            'auditoria_coordinador',
            $datosVista
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    |
    | Rol 5: únicamente puede consultar auditorías relacionadas con
    | su carrera asignada.
    |
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

        $auditorias = $this->consultarAuditorias(
            request: $request,
            filtros: $filtros,
            carreraObligatoria: $idCarrera
        );

        $datosVista = $this->construirDatosVista(
            usuario: $usuario,
            auditorias: $auditorias,
            filtros: $filtros,
            carreras: $this->obtenerCarreraPorId($idCarrera),
            idCarreraAsignada: $idCarrera
        );

        return view(
            'auditoria_secretaria_carrera',
            $datosVista
        );
    }

    /**
     * Mantiene compatibilidad con la ruta existente:
     *
     * auditoria.general
     *
     * Esa ruta puede seguir apuntando a general() sin provocar errores.
     */
    public function general(Request $request)
    {
        return $this->secretariaCarrera($request);
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTA PRINCIPAL
    |--------------------------------------------------------------------------
    */

    /**
     * Consulta los registros de auditoría.
     *
     * Cuando se recibe una carrera obligatoria, el usuario no puede
     * consultar datos de otra carrera aunque modifique manualmente la URL.
     */
    private function consultarAuditorias(
        Request $request,
        array $filtros,
        ?int $carreraObligatoria = null
    ) {
        $columnaFecha = $this->resolverColumnaFechaAuditoria();

        $consulta = DB::table('tbl_auditoria as a');

        $columnasSeleccionadas = [
            'a.*',
        ];

        /*
        |--------------------------------------------------------------------------
        | USUARIO RESPONSABLE
        |--------------------------------------------------------------------------
        */

        $personaResponsableUnida = false;

        if (
            $this->tieneColumna('tbl_auditoria', 'id_usuario')
            && $this->tieneColumna('tbl_usuario', 'id_usuario')
        ) {
            $consulta->leftJoin(
                'tbl_usuario as u',
                'u.id_usuario',
                '=',
                'a.id_usuario'
            );

            if (
                $this->tieneColumna('tbl_usuario', 'id_persona')
                && $this->tieneColumna('tbl_persona', 'id_persona')
            ) {
                $consulta->leftJoin(
                    'tbl_persona as responsable',
                    'responsable.id_persona',
                    '=',
                    'u.id_persona'
                );

                $personaResponsableUnida = true;
            }
        }

        if (
            $personaResponsableUnida
            && $this->tieneColumna(
                'tbl_persona',
                'nombre_persona'
            )
        ) {
            $columnasSeleccionadas[] =
                'responsable.nombre_persona as usuario_responsable';
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS usuario_responsable');
        }

        if (
            $personaResponsableUnida
            && $this->tieneColumna(
                'tbl_persona',
                'correo_institucional'
            )
        ) {
            $columnasSeleccionadas[] =
                'responsable.correo_institucional as correo_responsable';
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS correo_responsable');
        }

        /*
        |--------------------------------------------------------------------------
        | ROL
        |--------------------------------------------------------------------------
        */

        if (
            $this->tieneColumna('tbl_auditoria', 'id_rol')
            && $this->tieneColumna('tbl_rol', 'id_rol')
        ) {
            $consulta->leftJoin(
                'tbl_rol as r',
                'r.id_rol',
                '=',
                'a.id_rol'
            );

            if ($this->tieneColumna('tbl_rol', 'nombre_rol')) {
                $columnasSeleccionadas[] =
                    'r.nombre_rol';
            } else {
                $columnasSeleccionadas[] =
                    DB::raw('NULL AS nombre_rol');
            }
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS nombre_rol');
        }

        /*
        |--------------------------------------------------------------------------
        | CARRERA DEL ACTOR
        |--------------------------------------------------------------------------
        */

        if (
            $this->tieneColumna(
                'tbl_auditoria',
                'id_carrera_actor'
            )
            && $this->tieneColumna(
                'tbl_carrera',
                'id_carrera'
            )
        ) {
            $consulta->leftJoin(
                'tbl_carrera as carrera_actor',
                'carrera_actor.id_carrera',
                '=',
                'a.id_carrera_actor'
            );

            if (
                $this->tieneColumna(
                    'tbl_carrera',
                    'nombre_carrera'
                )
            ) {
                $columnasSeleccionadas[] =
                    'carrera_actor.nombre_carrera as carrera_actor';
            } else {
                $columnasSeleccionadas[] =
                    DB::raw('NULL AS carrera_actor');
            }
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS carrera_actor');
        }

        /*
        |--------------------------------------------------------------------------
        | CARRERA DEL CONTEXTO
        |--------------------------------------------------------------------------
        */

        if (
            $this->tieneColumna(
                'tbl_auditoria',
                'id_carrera'
            )
            && $this->tieneColumna(
                'tbl_carrera',
                'id_carrera'
            )
        ) {
            $consulta->leftJoin(
                'tbl_carrera as carrera_contexto',
                'carrera_contexto.id_carrera',
                '=',
                'a.id_carrera'
            );

            if (
                $this->tieneColumna(
                    'tbl_carrera',
                    'nombre_carrera'
                )
            ) {
                $columnasSeleccionadas[] =
                    'carrera_contexto.nombre_carrera as carrera_contexto';
            } else {
                $columnasSeleccionadas[] =
                    DB::raw('NULL AS carrera_contexto');
            }
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS carrera_contexto');
        }

        /*
        |--------------------------------------------------------------------------
        | OBJETO
        |--------------------------------------------------------------------------
        */

        if (
            $this->tieneColumna(
                'tbl_auditoria',
                'id_objeto'
            )
            && $this->tieneColumna(
                'tbl_objeto',
                'id_objeto'
            )
        ) {
            $consulta->leftJoin(
                'tbl_objeto as objeto',
                'objeto.id_objeto',
                '=',
                'a.id_objeto'
            );

            if (
                $this->tieneColumna(
                    'tbl_objeto',
                    'nombre_objeto'
                )
            ) {
                $columnasSeleccionadas[] =
                    'objeto.nombre_objeto';
            } else {
                $columnasSeleccionadas[] =
                    DB::raw('NULL AS nombre_objeto');
            }
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS nombre_objeto');
        }

        /*
        |--------------------------------------------------------------------------
        | TRÁMITE
        |--------------------------------------------------------------------------
        */

        $tramiteUnido = false;

        if (
            $this->tieneColumna(
                'tbl_auditoria',
                'id_tramite'
            )
            && $this->tieneColumna(
                'tbl_tramite',
                'id_tramite'
            )
        ) {
            $consulta->leftJoin(
                'tbl_tramite as tramite',
                'tramite.id_tramite',
                '=',
                'a.id_tramite'
            );

            $tramiteUnido = true;
        }

        if (
            $tramiteUnido
            && $this->tieneColumna(
                'tbl_tramite',
                'tipo_tramite_academico'
            )
        ) {
            $columnasSeleccionadas[] =
                'tramite.tipo_tramite_academico';
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS tipo_tramite_academico');
        }

        if (
            $tramiteUnido
            && $this->tieneColumna(
                'tbl_tramite',
                'resolucion_de_tramite_academico'
            )
        ) {
            $columnasSeleccionadas[] =
                'tramite.resolucion_de_tramite_academico as estado_tramite';
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS estado_tramite');
        }

        /*
        |--------------------------------------------------------------------------
        | ESTUDIANTE DEL TRÁMITE
        |--------------------------------------------------------------------------
        */

        $personaSolicitanteUnida = false;

        if (
            $tramiteUnido
            && $this->tieneColumna(
                'tbl_tramite',
                'id_persona'
            )
            && $this->tieneColumna(
                'tbl_persona',
                'id_persona'
            )
        ) {
            $consulta->leftJoin(
                'tbl_persona as solicitante',
                'solicitante.id_persona',
                '=',
                'tramite.id_persona'
            );

            $personaSolicitanteUnida = true;
        }

        if (
            $personaSolicitanteUnida
            && $this->tieneColumna(
                'tbl_persona',
                'nombre_persona'
            )
        ) {
            $columnasSeleccionadas[] =
                'solicitante.nombre_persona as estudiante';
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS estudiante');
        }

        if (
            $personaSolicitanteUnida
            && $this->tieneColumna(
                'tbl_persona',
                'correo_institucional'
            )
        ) {
            $columnasSeleccionadas[] =
                'solicitante.correo_institucional as correo_estudiante';
        } else {
            $columnasSeleccionadas[] =
                DB::raw('NULL AS correo_estudiante');
        }

        $consulta->select($columnasSeleccionadas);

        /*
        |--------------------------------------------------------------------------
        | RANGO DE FECHAS
        |--------------------------------------------------------------------------
        */

        $fechaInicio = Carbon::parse(
            $filtros['fecha_inicio']
        )->startOfDay();

        $fechaFinExclusiva = Carbon::parse(
            $filtros['fecha_fin']
        )->addDay()->startOfDay();

        $consulta
            ->where(
                'a.' . $columnaFecha,
                '>=',
                $fechaInicio
            )
            ->where(
                'a.' . $columnaFecha,
                '<',
                $fechaFinExclusiva
            );

        /*
        |--------------------------------------------------------------------------
        | RESTRICCIÓN POR CARRERA
        |--------------------------------------------------------------------------
        */

        if ($carreraObligatoria !== null) {
            $this->aplicarFiltroCarrera(
                consulta: $consulta,
                idCarrera: $carreraObligatoria,
                obligatorio: true
            );
        } elseif ($filtros['id_carrera'] !== null) {
            $this->aplicarFiltroCarrera(
                consulta: $consulta,
                idCarrera: $filtros['id_carrera'],
                obligatorio: false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTROS OPCIONALES
        |--------------------------------------------------------------------------
        */

        if (
            $filtros['id_usuario'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'id_usuario'
            )
        ) {
            $consulta->where(
                'a.id_usuario',
                $filtros['id_usuario']
            );
        }

        if (
            $filtros['id_rol'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'id_rol'
            )
        ) {
            $consulta->where(
                'a.id_rol',
                $filtros['id_rol']
            );
        }

        if (
            $filtros['id_objeto'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'id_objeto'
            )
        ) {
            $consulta->where(
                'a.id_objeto',
                $filtros['id_objeto']
            );
        }

        if (
            $filtros['id_tramite'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'id_tramite'
            )
        ) {
            $consulta->where(
                'a.id_tramite',
                $filtros['id_tramite']
            );
        }

        if (
            $filtros['accion'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'accion'
            )
        ) {
            $consulta->where(
                'a.accion',
                'like',
                '%' . $filtros['accion'] . '%'
            );
        }

        if (
            $filtros['modulo'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'modulo'
            )
        ) {
            $consulta->where(
                'a.modulo',
                'like',
                '%' . $filtros['modulo'] . '%'
            );
        }

        if (
            $filtros['operacion'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'operacion'
            )
        ) {
            $consulta->where(
                'a.operacion',
                $filtros['operacion']
            );
        }

        if (
            $filtros['tabla_afectada'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'tabla_afectada'
            )
        ) {
            $consulta->where(
                'a.tabla_afectada',
                'like',
                '%' . $filtros['tabla_afectada'] . '%'
            );
        }

        if (
            $filtros['nivel'] !== null
            && $this->tieneColumna(
                'tbl_auditoria',
                'nivel'
            )
        ) {
            $consulta->where(
                'a.nivel',
                $filtros['nivel']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ORDEN Y PAGINACIÓN
        |--------------------------------------------------------------------------
        */

        $consulta->orderByDesc(
            'a.' . $columnaFecha
        );

        if (
            $this->tieneColumna(
                'tbl_auditoria',
                'id_auditoria'
            )
        ) {
            $consulta->orderByDesc(
                'a.id_auditoria'
            );
        }

        return $consulta
            ->paginate($filtros['per_page'])
            ->withQueryString();
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO DE CARRERA
    |--------------------------------------------------------------------------
    */

    private function aplicarFiltroCarrera(
        Builder $consulta,
        int $idCarrera,
        bool $obligatorio
    ): void {
        $tieneCarreraContexto = $this->tieneColumna(
            'tbl_auditoria',
            'id_carrera'
        );

        $tieneCarreraActor = $this->tieneColumna(
            'tbl_auditoria',
            'id_carrera_actor'
        );

        if (
            !$tieneCarreraContexto
            && !$tieneCarreraActor
        ) {
            if ($obligatorio) {
                abort(
                    500,
                    'La tabla de auditoría no contiene una columna '
                    . 'que permita restringir los registros por carrera.'
                );
            }

            return;
        }

        $consulta->where(
            function (Builder $query) use (
                $idCarrera,
                $tieneCarreraContexto,
                $tieneCarreraActor
            ) {
                if ($tieneCarreraContexto) {
                    $query->where(
                        'a.id_carrera',
                        $idCarrera
                    );
                }

                if ($tieneCarreraActor) {
                    if ($tieneCarreraContexto) {
                        $query->orWhere(
                            'a.id_carrera_actor',
                            $idCarrera
                        );
                    } else {
                        $query->where(
                            'a.id_carrera_actor',
                            $idCarrera
                        );
                    }
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN DE FILTROS
    |--------------------------------------------------------------------------
    */

    private function validarFiltros(
        Request $request
    ): array {
        $request->merge([
            'fecha_inicio' =>
                $request->filled('fecha_inicio')
                    ? $request->input('fecha_inicio')
                    : now()->subMonth()->toDateString(),

            'fecha_fin' =>
                $request->filled('fecha_fin')
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

            'id_carrera' => [
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

            'accion' => [
                'nullable',
                'string',
                'max:100',
            ],

            'modulo' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tabla_afectada' => [
                'nullable',
                'string',
                'max:100',
            ],

            'operacion' => [
                'nullable',
                'in:INSERT,UPDATE,DELETE,LOGIN,LOGOUT,OTRA',
            ],

            'nivel' => [
                'nullable',
                'in:INFO,ADVERTENCIA,ERROR,SEGURIDAD',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'in:10,20,50,100',
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

            'id_carrera' =>
                $datos['id_carrera'] ?? null,

            'id_objeto' =>
                $datos['id_objeto'] ?? null,

            'id_tramite' =>
                $datos['id_tramite'] ?? null,

            'accion' =>
                $this->textoONull(
                    $datos['accion'] ?? null
                ),

            'modulo' =>
                $this->textoONull(
                    $datos['modulo'] ?? null
                ),

            'tabla_afectada' =>
                $this->textoONull(
                    $datos['tabla_afectada'] ?? null
                ),

            'operacion' =>
                $datos['operacion'] ?? null,

            'nivel' =>
                $datos['nivel'] ?? null,

            'per_page' =>
                (int) ($datos['per_page'] ?? 20),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS PARA LAS VISTAS
    |--------------------------------------------------------------------------
    */

    private function construirDatosVista(
        object $usuario,
        $auditorias,
        array $filtros,
        Collection $carreras,
        ?int $idCarreraAsignada
    ): array {
        return [
            'auditorias' =>
                $auditorias,

            /*
             * Se conserva este alias para que las vistas anteriores
             * que utilizan $registros continúen funcionando.
             */
            'registros' =>
                $auditorias,

            'carreras' =>
                $carreras,

            'roles' =>
                $this->obtenerRoles(),

            'objetos' =>
                $this->obtenerObjetos(),

            'filtros' =>
                $filtros,

            'fecha_inicio' =>
                $filtros['fecha_inicio'],

            'fecha_fin' =>
                $filtros['fecha_fin'],

            'id_rol' =>
                (int) $usuario->id_rol,

            'idCarreraAsignada' =>
                $idCarreraAsignada,

            'operaciones' => [
                'INSERT' => 'Creación',
                'UPDATE' => 'Actualización',
                'DELETE' => 'Eliminación',
                'LOGIN' => 'Inicio de sesión',
                'LOGOUT' => 'Cierre de sesión',
                'OTRA' => 'Otra operación',
            ],

            'niveles' => [
                'INFO' => 'Información',
                'ADVERTENCIA' => 'Advertencia',
                'ERROR' => 'Error',
                'SEGURIDAD' => 'Seguridad',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS
    |--------------------------------------------------------------------------
    */

    private function obtenerCarreras(): Collection
    {
        if (
            !$this->tieneColumna(
                'tbl_carrera',
                'id_carrera'
            )
        ) {
            return collect();
        }

        $consulta = DB::table('tbl_carrera')
            ->select('id_carrera');

        if (
            $this->tieneColumna(
                'tbl_carrera',
                'nombre_carrera'
            )
        ) {
            $consulta->addSelect(
                'nombre_carrera'
            );

            $consulta->orderBy(
                'nombre_carrera'
            );
        }

        return $consulta->get();
    }

    private function obtenerCarreraPorId(
        int $idCarrera
    ): Collection {
        if (
            !$this->tieneColumna(
                'tbl_carrera',
                'id_carrera'
            )
        ) {
            return collect();
        }

        $consulta = DB::table('tbl_carrera')
            ->select('id_carrera')
            ->where(
                'id_carrera',
                $idCarrera
            );

        if (
            $this->tieneColumna(
                'tbl_carrera',
                'nombre_carrera'
            )
        ) {
            $consulta->addSelect(
                'nombre_carrera'
            );
        }

        return $consulta->get();
    }

    private function obtenerRoles(): Collection
    {
        if (
            !$this->tieneColumna(
                'tbl_rol',
                'id_rol'
            )
        ) {
            return collect();
        }

        $consulta = DB::table('tbl_rol')
            ->select('id_rol');

        if (
            $this->tieneColumna(
                'tbl_rol',
                'nombre_rol'
            )
        ) {
            $consulta->addSelect(
                'nombre_rol'
            );

            $consulta->orderBy(
                'nombre_rol'
            );
        }

        return $consulta->get();
    }

    private function obtenerObjetos(): Collection
    {
        if (
            !$this->tieneColumna(
                'tbl_objeto',
                'id_objeto'
            )
        ) {
            return collect();
        }

        $consulta = DB::table('tbl_objeto')
            ->select('id_objeto');

        if (
            $this->tieneColumna(
                'tbl_objeto',
                'nombre_objeto'
            )
        ) {
            $consulta->addSelect(
                'nombre_objeto'
            );

            $consulta->orderBy(
                'nombre_objeto'
            );
        }

        return $consulta->get();
    }

    /*
    |--------------------------------------------------------------------------
    | EMPLEADO Y AUTORIZACIÓN
    |--------------------------------------------------------------------------
    */

    private function obtenerCarreraEmpleado(
        ?int $idPersona
    ): ?int {
        if (
            !$idPersona
            || !$this->tieneColumna(
                'tbl_empleados',
                'id_persona'
            )
            || !$this->tieneColumna(
                'tbl_empleados',
                'id_carrera'
            )
        ) {
            return null;
        }

        $idCarrera = DB::table('tbl_empleados')
            ->where(
                'id_persona',
                $idPersona
            )
            ->value('id_carrera');

        return $idCarrera !== null
            ? (int) $idCarrera
            : null;
    }

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
                'No tiene autorización para consultar la auditoría.'
            );
        }

        return $usuario;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS DE BASE DE DATOS
    |--------------------------------------------------------------------------
    */

    /**
     * Determina el nombre real de la columna de fecha.
     */
    private function resolverColumnaFechaAuditoria(): string
    {
        foreach (
            [
                'fecha',
                'fecha_accion',
                'created_at',
            ] as $columna
        ) {
            if (
                $this->tieneColumna(
                    'tbl_auditoria',
                    $columna
                )
            ) {
                return $columna;
            }
        }

        abort(
            500,
            'La tabla tbl_auditoria no contiene una columna '
            . 'de fecha reconocida.'
        );
    }

    /**
     * Comprueba si una tabla contiene una columna determinada.
     */
    private function tieneColumna(
        string $tabla,
        string $columna
    ): bool {
        if (!array_key_exists($tabla, $this->columnasCache)) {
            $this->columnasCache[$tabla] =
                Schema::hasTable($tabla)
                    ? Schema::getColumnListing($tabla)
                    : [];
        }

        return in_array(
            $columna,
            $this->columnasCache[$tabla],
            true
        );
    }

    /**
     * Convierte cadenas vacías en null y elimina espacios.
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
}