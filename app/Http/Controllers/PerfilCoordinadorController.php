<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PerfilCoordinadorController extends Controller
{
    /**
     * Muestra el perfil del Coordinador de Carrera.
     */
    public function index()
    {
        $usuario = Auth::user();

        abort_if(
            !$usuario,
            401,
            'Debe iniciar sesión para acceder al perfil.'
        );

        $idPersona = data_get($usuario, 'id_persona');

        $persona = $this->obtenerPersona($idPersona);
        $empleado = $this->obtenerEmpleado($idPersona);

        $idRol = $this->primerValorDisponible(
            $empleado,
            ['id_rol'],
            data_get($usuario, 'id_rol')
        );

        $idDepartamento = $this->primerValorDisponible(
            $empleado,
            ['id_departamento'],
            data_get($usuario, 'id_departamento')
        );

        $idCarrera = $this->primerValorDisponible(
            $empleado,
            ['id_carrera'],
            data_get($usuario, 'id_carrera')
        );

        $rol = $this->obtenerRegistroPorId(
            'tbl_rol',
            'id_rol',
            $idRol
        );

        $departamento = $this->obtenerRegistroPorId(
            'tbl_departamento',
            'id_departamento',
            $idDepartamento
        );

        $carrera = $this->obtenerRegistroPorId(
            'tbl_carrera',
            'id_carrera',
            $idCarrera
        );

        $nombreCompleto = $this->construirNombreCompleto(
            $persona,
            $usuario
        );

        $correo = $this->primerValorDisponible(
            $persona,
            [
                'correo_institucional',
                'correo',
                'email',
            ],
            $this->primerValorDisponible(
                $usuario,
                [
                    'correo_institucional',
                    'correo',
                    'email',
                ],
                'No disponible'
            )
        );

        $codigoEmpleado = $this->primerValorDisponible(
            $empleado,
            [
                'codigo_empleado',
                'numero_empleado',
                'cod_empleado',
            ],
            'No disponible'
        );

        $nombreRol = $this->primerValorDisponible(
            $rol,
            [
                'nombre_rol',
                'rol',
                'nombre',
                'descripcion',
            ],
            'Coordinador de Carrera'
        );

        $nombreDepartamento = $this->primerValorDisponible(
            $departamento,
            [
                'nombre_departamento',
                'departamento',
                'nombre',
            ],
            'No disponible'
        );

        $nombreCarrera = $this->primerValorDisponible(
            $carrera,
            [
                'nombre_carrera',
                'carrera',
                'nombre',
            ],
            'No disponible'
        );

        $estadoUsuario = $this->primerValorDisponible(
            $usuario,
            [
                'estado',
                'estado_usuario',
                'activo',
            ],
            1
        );

        $correoVerificado = $this->primerValorDisponible(
            $usuario,
            [
                'email_verified_at',
                'correo_verificado_at',
                'correo_verificado',
            ]
        );

        $ultimaVerificacion2FA = $this->primerValorDisponible(
            $usuario,
            [
                'twofa_verified_at',
                'two_factor_verified_at',
                'ultima_verificacion_2fa',
            ]
        );

        $ultimoInicioSesion = $this->primerValorDisponible(
            $usuario,
            [
                'ultimo_inicio_sesion',
                'last_login_at',
                'ultimo_login',
                'fecha_ultimo_login',
            ]
        );

        $perfilCoordinador = [
            'nombre_completo' => $nombreCompleto,
            'correo' => $correo,
            'codigo_empleado' => $codigoEmpleado,
            'rol' => $nombreRol,

            'estado_cuenta' => $this->formatearEstadoCuenta(
                $estadoUsuario
            ),

            'facultad' => $this->obtenerFacultad(
                $departamento,
                $carrera
            ),

            'departamento' => $nombreDepartamento,
            'carrera_asignada' => $nombreCarrera,

            'centro_universitario' => $this->obtenerCentroUniversitario(
                $empleado,
                $departamento,
                $carrera
            ),

            'alcance_gestion' => $nombreCarrera !== 'No disponible'
                ? 'Resolución y seguimiento de los trámites de ' . $nombreCarrera
                : 'Resolución y seguimiento de los trámites de la carrera asignada',

            'correo_verificado' => !empty($correoVerificado),
            'twofa_activado' => !empty($ultimaVerificacion2FA),

            'ultima_verificacion_2fa' => $this->formatearFecha(
                $ultimaVerificacion2FA
            ),

            'ultimo_inicio_sesion' => $this->formatearFecha(
                $ultimoInicioSesion
            ),
        ];

        $resumenDictamenes = $this->obtenerResumenDictamenes(
            $idPersona,
            $idDepartamento,
            $idCarrera
        );

        $actividadReciente = $this->obtenerActividadReciente(
            $usuario,
            $idPersona
        );

        return view('perfil_coordinador', compact(
            'usuario',
            'perfilCoordinador',
            'resumenDictamenes',
            'actividadReciente'
        ));
    }

    /**
     * Obtiene la persona asociada al usuario.
     */
    private function obtenerPersona($idPersona): ?object
    {
        if (
            empty($idPersona) ||
            !Schema::hasTable('tbl_persona') ||
            !Schema::hasColumn('tbl_persona', 'id_persona')
        ) {
            return null;
        }

        try {
            return DB::table('tbl_persona')
                ->where('id_persona', $idPersona)
                ->first();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Obtiene la información laboral del coordinador.
     */
    private function obtenerEmpleado($idPersona): ?object
    {
        if (empty($idPersona)) {
            return null;
        }

        $tablas = [
            'tbl_empleados',
            'tbl_empleado',
        ];

        foreach ($tablas as $tabla) {
            if (
                !Schema::hasTable($tabla) ||
                !Schema::hasColumn($tabla, 'id_persona')
            ) {
                continue;
            }

            try {
                $empleado = DB::table($tabla)
                    ->where('id_persona', $idPersona)
                    ->first();

                if ($empleado) {
                    return $empleado;
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return null;
    }

    /**
     * Obtiene un registro mediante su identificador.
     */
    private function obtenerRegistroPorId(
        string $tabla,
        string $columnaId,
        $valorId
    ): ?object {
        if (
            empty($valorId) ||
            !Schema::hasTable($tabla) ||
            !Schema::hasColumn($tabla, $columnaId)
        ) {
            return null;
        }

        try {
            return DB::table($tabla)
                ->where($columnaId, $valorId)
                ->first();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Construye el nombre completo.
     */
    private function construirNombreCompleto(
        ?object $persona,
        object $usuario
    ): string {
        $nombreGuardado = $this->primerValorDisponible(
            $persona,
            [
                'nombre_completo',
                'nombre_persona',
            ]
        );

        if (!empty($nombreGuardado)) {
            return trim($nombreGuardado);
        }

        $partes = [
            $this->primerValorDisponible(
                $persona,
                [
                    'primer_nombre',
                    'nombre_1',
                    'nombres',
                ]
            ),

            $this->primerValorDisponible(
                $persona,
                [
                    'segundo_nombre',
                    'nombre_2',
                ]
            ),

            $this->primerValorDisponible(
                $persona,
                [
                    'primer_apellido',
                    'apellido_1',
                    'apellidos',
                ]
            ),

            $this->primerValorDisponible(
                $persona,
                [
                    'segundo_apellido',
                    'apellido_2',
                ]
            ),
        ];

        $partes = array_filter(
            $partes,
            fn ($valor) =>
                $valor !== null &&
                trim((string) $valor) !== ''
        );

        $nombreCompleto = trim(
            implode(' ', array_unique($partes))
        );

        if ($nombreCompleto !== '') {
            return $nombreCompleto;
        }

        return $this->primerValorDisponible(
            $usuario,
            [
                'nombre_persona',
                'nombre_completo',
                'name',
                'nombre',
            ],
            'No disponible'
        );
    }

    /**
     * Obtiene el resumen de los trámites del coordinador.
     */
    private function obtenerResumenDictamenes(
        $idPersona,
        $idDepartamento,
        $idCarrera
    ): array {
        $resumen = [
            'recibidos' => 0,
            'pendientes' => 0,
            'aprobados' => 0,
            'rechazados' => 0,
            'devueltos' => 0,
            'resueltos' => 0,
        ];

        if (!Schema::hasTable('tbl_tramite')) {
            return $resumen;
        }

        try {
            $consulta = DB::table('tbl_tramite');

            $filtroAplicado = $this->aplicarFiltroTramites(
                $consulta,
                $idPersona,
                $idDepartamento,
                $idCarrera
            );

            /*
             * Evita contar trámites de todas las carreras cuando no se
             * conoce la asignación institucional del coordinador.
             */
            if (!$filtroAplicado) {
                return $resumen;
            }

            $resumen['recibidos'] = (clone $consulta)->count();

            $columnaEstado = $this->primeraColumnaExistente(
                'tbl_tramite',
                [
                    'estado',
                    'estado_tramite',
                    'nombre_estado',
                    'descripcion_estado',
                ]
            );

            if (!$columnaEstado) {
                return $resumen;
            }

            $estados = (clone $consulta)
                ->pluck($columnaEstado)
                ->map(
                    fn ($estado) =>
                        $this->normalizarTexto($estado)
                );

            $resumen['pendientes'] = $estados
                ->filter(function ($estado) {
                    return $this->contieneAlguno($estado, [
                        'PENDIENTE DICTAMEN',
                        'LISTO PARA DICTAMEN',
                        'EN COORDINACION',
                        'PENDIENTE COORDINACION',
                        'REVISION COORDINADOR',
                        'REVISADO POR SECRETARIA',
                    ]);
                })
                ->count();

            $resumen['aprobados'] = $estados
                ->filter(function ($estado) {
                    return $this->contieneAlguno($estado, [
                        'APROBADO',
                        'APROBADA',
                        'FAVORABLE',
                    ]);
                })
                ->count();

            $resumen['rechazados'] = $estados
                ->filter(function ($estado) {
                    return $this->contieneAlguno($estado, [
                        'RECHAZADO',
                        'RECHAZADA',
                        'DESFAVORABLE',
                    ]);
                })
                ->count();

            $resumen['devueltos'] = $estados
                ->filter(function ($estado) {
                    return $this->contieneAlguno($estado, [
                        'DEVUELTO A SECRETARIA',
                        'DEVUELTA A SECRETARIA',
                        'CORRECCION SECRETARIA',
                        'REQUIERE CORRECCION',
                    ]);
                })
                ->count();

            $resumen['resueltos'] =
                $resumen['aprobados'] +
                $resumen['rechazados'];

            return $resumen;
        } catch (Throwable $exception) {
            report($exception);

            return $resumen;
        }
    }

    /**
     * Aplica el filtro de carrera, departamento o responsable.
     */
    private function aplicarFiltroTramites(
        Builder $consulta,
        $idPersona,
        $idDepartamento,
        $idCarrera
    ): bool {
        if (
            !empty($idCarrera) &&
            Schema::hasColumn('tbl_tramite', 'id_carrera')
        ) {
            $consulta->where('id_carrera', $idCarrera);

            return true;
        }

        if (
            !empty($idDepartamento) &&
            Schema::hasColumn('tbl_tramite', 'id_departamento')
        ) {
            $consulta->where(
                'id_departamento',
                $idDepartamento
            );

            return true;
        }

        if (
            !empty($idPersona) &&
            Schema::hasColumn(
                'tbl_tramite',
                'id_persona_coordinador'
            )
        ) {
            $consulta->where(
                'id_persona_coordinador',
                $idPersona
            );

            return true;
        }

        return false;
    }

    /**
     * Obtiene la actividad reciente del coordinador.
     */
    private function obtenerActividadReciente(
        object $usuario,
        $idPersona
    ): array {
        $tablaBitacora = $this->primeraTablaExistente([
            'tbl_bitacora',
            'tbl_bitacora_tramite',
        ]);

        if (!$tablaBitacora) {
            return [];
        }

        try {
            $consulta = DB::table($tablaBitacora);
            $filtroAplicado = false;

            if (
                !empty($idPersona) &&
                Schema::hasColumn(
                    $tablaBitacora,
                    'id_persona'
                )
            ) {
                $consulta->where(
                    'id_persona',
                    $idPersona
                );

                $filtroAplicado = true;
            } else {
                $idUsuario = $this->primerValorDisponible(
                    $usuario,
                    [
                        'id_usuario',
                        'id',
                    ]
                );

                if (
                    !empty($idUsuario) &&
                    Schema::hasColumn(
                        $tablaBitacora,
                        'id_usuario'
                    )
                ) {
                    $consulta->where(
                        'id_usuario',
                        $idUsuario
                    );

                    $filtroAplicado = true;
                }
            }

            if (!$filtroAplicado) {
                return [];
            }

            $columnaOrden = $this->primeraColumnaExistente(
                $tablaBitacora,
                [
                    'created_at',
                    'fecha_hora',
                    'fecha_registro',
                    'fecha',
                    'id_bitacora',
                ]
            );

            if ($columnaOrden) {
                $consulta->orderByDesc($columnaOrden);
            }

            return $consulta
                ->limit(3)
                ->get()
                ->map(function ($actividad) use ($columnaOrden) {
                    $accion = $this->primerValorDisponible(
                        $actividad,
                        [
                            'accion',
                            'evento',
                            'actividad',
                            'descripcion',
                            'detalle',
                        ],
                        'Actividad registrada'
                    );

                    $detalle = $this->primerValorDisponible(
                        $actividad,
                        [
                            'descripcion',
                            'detalle',
                            'observacion',
                            'mensaje',
                        ],
                        'Se registró una acción en el sistema.'
                    );

                    $fecha = $columnaOrden
                        ? data_get($actividad, $columnaOrden)
                        : null;

                    $presentacion =
                        $this->obtenerPresentacionActividad(
                            $accion
                        );

                    return [
                        'icono' => $presentacion['icono'],
                        'titulo' => $accion,
                        'detalle' => $detalle,
                        'fecha' => $this->formatearFecha($fecha),
                        'tipo' => $presentacion['tipo'],
                    ];
                })
                ->values()
                ->all();
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /**
     * Define el icono y color de la actividad.
     */
    private function obtenerPresentacionActividad(
        string $accion
    ): array {
        $accionNormalizada = $this->normalizarTexto(
            $accion
        );

        if ($this->contieneAlguno(
            $accionNormalizada,
            [
                'APROBADO',
                'APROBADA',
                'FAVORABLE',
            ]
        )) {
            return [
                'icono' => 'fas fa-circle-check',
                'tipo' => 'green',
            ];
        }

        if ($this->contieneAlguno(
            $accionNormalizada,
            [
                'RECHAZADO',
                'RECHAZADA',
                'DESFAVORABLE',
            ]
        )) {
            return [
                'icono' => 'fas fa-circle-xmark',
                'tipo' => 'red',
            ];
        }

        if ($this->contieneAlguno(
            $accionNormalizada,
            [
                'DEVUELTO',
                'CORRECCION',
            ]
        )) {
            return [
                'icono' => 'fas fa-rotate-left',
                'tipo' => 'gold',
            ];
        }

        return [
            'icono' => 'fas fa-file-signature',
            'tipo' => 'blue',
        ];
    }

    /**
     * Obtiene la facultad asignada.
     */
    private function obtenerFacultad(
        ?object $departamento,
        ?object $carrera
    ): string {
        $idFacultad = $this->primerValorDisponible(
            $departamento,
            ['id_facultad'],
            $this->primerValorDisponible(
                $carrera,
                ['id_facultad']
            )
        );

        $facultad = $this->obtenerRegistroPorId(
            'tbl_facultad',
            'id_facultad',
            $idFacultad
        );

        return $this->primerValorDisponible(
            $facultad,
            [
                'nombre_facultad',
                'facultad',
                'nombre',
            ],
            'Facultad de Ciencias Económicas, Administrativas y Contables'
        );
    }

    /**
     * Obtiene el centro universitario.
     */
    private function obtenerCentroUniversitario(
        ?object $empleado,
        ?object $departamento,
        ?object $carrera
    ): string {
        $idCentro = $this->primerValorDisponible(
            $empleado,
            [
                'id_centro_universitario',
                'id_centro',
            ],
            $this->primerValorDisponible(
                $departamento,
                [
                    'id_centro_universitario',
                    'id_centro',
                ],
                $this->primerValorDisponible(
                    $carrera,
                    [
                        'id_centro_universitario',
                        'id_centro',
                    ]
                )
            )
        );

        $tablaCentro = $this->primeraTablaExistente([
            'tbl_centro_universitario',
            'tbl_centro',
        ]);

        if (!$tablaCentro || empty($idCentro)) {
            return 'Ciudad Universitaria';
        }

        $columnaId = $this->primeraColumnaExistente(
            $tablaCentro,
            [
                'id_centro_universitario',
                'id_centro',
            ]
        );

        if (!$columnaId) {
            return 'Ciudad Universitaria';
        }

        $centro = $this->obtenerRegistroPorId(
            $tablaCentro,
            $columnaId,
            $idCentro
        );

        return $this->primerValorDisponible(
            $centro,
            [
                'nombre_centro',
                'centro_universitario',
                'nombre',
            ],
            'Ciudad Universitaria'
        );
    }

    /**
     * Obtiene el primer valor no vacío.
     */
    private function primerValorDisponible(
        object|array|null $origen,
        array $propiedades,
        $valorPredeterminado = null
    ) {
        if (!$origen) {
            return $valorPredeterminado;
        }

        foreach ($propiedades as $propiedad) {
            $valor = data_get($origen, $propiedad);

            if (
                $valor !== null &&
                trim((string) $valor) !== ''
            ) {
                return $valor;
            }
        }

        return $valorPredeterminado;
    }

    /**
     * Obtiene la primera tabla existente.
     */
    private function primeraTablaExistente(
        array $tablas
    ): ?string {
        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                return $tabla;
            }
        }

        return null;
    }

    /**
     * Obtiene la primera columna existente.
     */
    private function primeraColumnaExistente(
        string $tabla,
        array $columnas
    ): ?string {
        if (!Schema::hasTable($tabla)) {
            return null;
        }

        foreach ($columnas as $columna) {
            if (Schema::hasColumn($tabla, $columna)) {
                return $columna;
            }
        }

        return null;
    }

    /**
     * Convierte el estado a un texto legible.
     */
    private function formatearEstadoCuenta(
        $estado
    ): string {
        if ($estado === null || $estado === '') {
            return 'Activa';
        }

        if (
            $estado === true ||
            $estado === 1 ||
            $estado === '1'
        ) {
            return 'Activa';
        }

        if (
            $estado === false ||
            $estado === 0 ||
            $estado === '0'
        ) {
            return 'Inactiva';
        }

        $estadoNormalizado = $this->normalizarTexto(
            $estado
        );

        if (in_array(
            $estadoNormalizado,
            ['ACTIVO', 'ACTIVA', 'A'],
            true
        )) {
            return 'Activa';
        }

        if (in_array(
            $estadoNormalizado,
            ['INACTIVO', 'INACTIVA', 'I'],
            true
        )) {
            return 'Inactiva';
        }

        return ucfirst(
            mb_strtolower((string) $estado)
        );
    }

    /**
     * Formatea las fechas para la vista.
     */
    private function formatearFecha(
        $fecha
    ): string {
        if (empty($fecha)) {
            return 'No disponible';
        }

        try {
            return Carbon::parse($fecha)
                ->format('d/m/Y h:i A');
        } catch (Throwable $exception) {
            return (string) $fecha;
        }
    }

    /**
     * Normaliza textos para comparaciones.
     */
    private function normalizarTexto(
        $texto
    ): string {
        $texto = mb_strtoupper(
            trim((string) $texto),
            'UTF-8'
        );

        return strtr($texto, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ñ' => 'N',
        ]);
    }

    /**
     * Comprueba si el texto contiene alguno de los valores.
     */
    private function contieneAlguno(
        string $texto,
        array $valores
    ): bool {
        foreach ($valores as $valor) {
            if (str_contains($texto, $valor)) {
                return true;
            }
        }

        return false;
    }
}