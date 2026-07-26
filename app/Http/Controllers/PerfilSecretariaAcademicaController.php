<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PerfilSecretariaAcademicaController extends Controller
{
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

        $rol = $this->obtenerRegistroPorId(
            'tbl_rol',
            'id_rol',
            $idRol
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
            'Secretaría Académica'
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

        $perfilSecretariaAcademica = [
            'nombre_completo' => $nombreCompleto,
            'correo' => $correo,
            'codigo_empleado' => $codigoEmpleado,
            'rol' => $nombreRol,
            'estado_cuenta' => $this->formatearEstadoCuenta($estadoUsuario),
            'facultad' => 'Facultad de Ciencias Económicas, Administrativas y Contables',
            'unidad_administrativa' => 'Secretaría Académica',
            'centro_universitario' => 'Ciudad Universitaria',
            'nivel_supervision' => 'Global',
            'alcance_gestion' => 'Todas las carreras y departamentos autorizados',
            'correo_verificado' => !empty($correoVerificado),
            'twofa_activado' => !empty($ultimaVerificacion2FA),
            'ultima_verificacion_2fa' => $this->formatearFecha($ultimaVerificacion2FA),
            'ultimo_inicio_sesion' => $this->formatearFecha($ultimoInicioSesion),
        ];

        $resumenGlobal = $this->obtenerResumenGlobal();
        $coberturaInstitucional = $this->obtenerCoberturaInstitucional();
        $actividadReciente = $this->obtenerActividadReciente();

        return view('perfil_secretaria_academica', compact(
            'usuario',
            'perfilSecretariaAcademica',
            'resumenGlobal',
            'coberturaInstitucional',
            'actividadReciente'
        ));
    }

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

    private function obtenerEmpleado($idPersona): ?object
    {
        if (empty($idPersona)) {
            return null;
        }

        foreach (['tbl_empleados', 'tbl_empleado'] as $tabla) {
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
            $this->primerValorDisponible($persona, ['primer_nombre', 'nombre_1', 'nombres']),
            $this->primerValorDisponible($persona, ['segundo_nombre', 'nombre_2']),
            $this->primerValorDisponible($persona, ['primer_apellido', 'apellido_1', 'apellidos']),
            $this->primerValorDisponible($persona, ['segundo_apellido', 'apellido_2']),
        ];

        $partes = array_filter(
            $partes,
            fn ($valor) => $valor !== null && trim((string) $valor) !== ''
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

    private function obtenerResumenGlobal(): array
    {
        $resumen = [
            'total' => 0,
            'pendientes' => 0,
            'revision' => 0,
            'aprobados' => 0,
            'rechazados' => 0,
            'finalizados' => 0,
        ];

        if (!Schema::hasTable('tbl_tramite')) {
            return $resumen;
        }

        try {
            $resumen['total'] = DB::table('tbl_tramite')->count();

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

            $estados = DB::table('tbl_tramite')
                ->pluck($columnaEstado)
                ->map(fn ($estado) => $this->normalizarTexto($estado));

            $resumen['pendientes'] = $estados
                ->filter(fn ($estado) => $this->contieneAlguno($estado, [
                    'PENDIENTE',
                    'RECIBIDO',
                    'ENVIADO',
                ]))
                ->count();

            $resumen['revision'] = $estados
                ->filter(fn ($estado) => $this->contieneAlguno($estado, [
                    'REVISION',
                    'EN PROCESO',
                    'EN COORDINACION',
                    'OBSERVACION',
                ]))
                ->count();

            $resumen['aprobados'] = $estados
                ->filter(fn ($estado) => $this->contieneAlguno($estado, [
                    'APROBADO',
                    'APROBADA',
                    'FAVORABLE',
                ]))
                ->count();

            $resumen['rechazados'] = $estados
                ->filter(fn ($estado) => $this->contieneAlguno($estado, [
                    'RECHAZADO',
                    'RECHAZADA',
                    'DESFAVORABLE',
                ]))
                ->count();

            $resumen['finalizados'] = $estados
                ->filter(fn ($estado) => $this->contieneAlguno($estado, [
                    'FINALIZADO',
                    'FINALIZADA',
                    'CERRADO',
                    'CERRADA',
                    'APROBADO',
                    'APROBADA',
                    'RECHAZADO',
                    'RECHAZADA',
                ]))
                ->count();

            return $resumen;
        } catch (Throwable $exception) {
            report($exception);

            return $resumen;
        }
    }

    private function obtenerCoberturaInstitucional(): array
    {
        return [
            'carreras' => $this->contarTabla('tbl_carrera'),
            'departamentos' => $this->contarTabla('tbl_departamento'),
            'secretarias' => $this->contarEmpleadosPorRol([
                'SECRETARIA DE CARRERA',
                'SECRETARIA CARRERA',
            ]),
            'coordinadores' => $this->contarEmpleadosPorRol([
                'COORDINADOR',
                'COORDINADORA',
            ]),
            'estudiantes' => $this->contarEstudiantesConTramites(),
            'calendarios_vigentes' => $this->contarCalendariosVigentes(),
        ];
    }

    private function contarTabla(string $tabla): int
    {
        if (!Schema::hasTable($tabla)) {
            return 0;
        }

        try {
            return DB::table($tabla)->count();
        } catch (Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    private function contarEmpleadosPorRol(array $palabras): int
    {
        $tablaEmpleado = Schema::hasTable('tbl_empleados')
            ? 'tbl_empleados'
            : (Schema::hasTable('tbl_empleado') ? 'tbl_empleado' : null);

        if (
            !$tablaEmpleado ||
            !Schema::hasTable('tbl_rol') ||
            !Schema::hasColumn($tablaEmpleado, 'id_rol') ||
            !Schema::hasColumn('tbl_rol', 'id_rol')
        ) {
            return 0;
        }

        $columnaRol = $this->primeraColumnaExistente(
            'tbl_rol',
            [
                'nombre_rol',
                'rol',
                'nombre',
                'descripcion',
            ]
        );

        if (!$columnaRol) {
            return 0;
        }

        try {
            $consulta = DB::table($tablaEmpleado . ' as e')
                ->join('tbl_rol as r', 'r.id_rol', '=', 'e.id_rol');

            $consulta->where(function ($query) use ($palabras, $columnaRol) {
                foreach ($palabras as $indice => $palabra) {
                    if ($indice === 0) {
                        $query->whereRaw(
                            'UPPER(r.' . $columnaRol . ') LIKE ?',
                            ['%' . $palabra . '%']
                        );
                    } else {
                        $query->orWhereRaw(
                            'UPPER(r.' . $columnaRol . ') LIKE ?',
                            ['%' . $palabra . '%']
                        );
                    }
                }
            });

            return $consulta->count();
        } catch (Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    private function contarEstudiantesConTramites(): int
    {
        if (
            !Schema::hasTable('tbl_tramite') ||
            !Schema::hasColumn('tbl_tramite', 'id_persona')
        ) {
            return 0;
        }

        try {
            return DB::table('tbl_tramite')
                ->distinct('id_persona')
                ->count('id_persona');
        } catch (Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    private function contarCalendariosVigentes(): int
    {
        if (!Schema::hasTable('tbl_calendario_academico')) {
            return 0;
        }

        try {
            $consulta = DB::table('tbl_calendario_academico');

            if (Schema::hasColumn('tbl_calendario_academico', 'estado')) {
                $consulta->where('estado', 1);
            }

            if (Schema::hasColumn('tbl_calendario_academico', 'fecha_fin')) {
                $consulta->whereDate('fecha_fin', '>=', now()->toDateString());
            }

            return $consulta->count();
        } catch (Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    private function obtenerActividadReciente(): array
    {
        $tablaActividad = $this->primeraTablaExistente([
            'tbl_bitacora',
            'tbl_auditoria',
            'tbl_bitacora_tramite',
        ]);

        if (!$tablaActividad) {
            return [];
        }

        try {
            $consulta = DB::table($tablaActividad);

            $columnaOrden = $this->primeraColumnaExistente(
                $tablaActividad,
                [
                    'created_at',
                    'fecha_hora',
                    'fecha_registro',
                    'fecha',
                    'id_bitacora',
                    'id_auditoria',
                ]
            );

            if ($columnaOrden) {
                $consulta->orderByDesc($columnaOrden);
            }

            return $consulta
                ->limit(5)
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
                        'Se registró una actividad institucional.'
                    );

                    $fecha = $columnaOrden
                        ? data_get($actividad, $columnaOrden)
                        : null;

                    $presentacion = $this->obtenerPresentacionActividad($accion);

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

    private function obtenerPresentacionActividad(string $accion): array
    {
        $accionNormalizada = $this->normalizarTexto($accion);

        if ($this->contieneAlguno($accionNormalizada, [
            'CALENDARIO',
            'PERIODO',
        ])) {
            return [
                'icono' => 'fas fa-calendar-check',
                'tipo' => 'gold',
            ];
        }

        if ($this->contieneAlguno($accionNormalizada, [
            'APROBADO',
            'APROBADA',
            'VALIDADO',
        ])) {
            return [
                'icono' => 'fas fa-circle-check',
                'tipo' => 'green',
            ];
        }

        if ($this->contieneAlguno($accionNormalizada, [
            'RECHAZADO',
            'RECHAZADA',
            'ERROR',
        ])) {
            return [
                'icono' => 'fas fa-circle-xmark',
                'tipo' => 'red',
            ];
        }

        return [
            'icono' => 'fas fa-file-circle-check',
            'tipo' => 'blue',
        ];
    }

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

            if ($valor !== null && trim((string) $valor) !== '') {
                return $valor;
            }
        }

        return $valorPredeterminado;
    }

    private function primeraTablaExistente(array $tablas): ?string
    {
        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                return $tabla;
            }
        }

        return null;
    }

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

    private function formatearEstadoCuenta($estado): string
    {
        if ($estado === null || $estado === '') {
            return 'Activa';
        }

        if ($estado === true || $estado === 1 || $estado === '1') {
            return 'Activa';
        }

        if ($estado === false || $estado === 0 || $estado === '0') {
            return 'Inactiva';
        }

        $estadoNormalizado = $this->normalizarTexto($estado);

        if (in_array($estadoNormalizado, ['ACTIVO', 'ACTIVA', 'A'], true)) {
            return 'Activa';
        }

        if (in_array($estadoNormalizado, ['INACTIVO', 'INACTIVA', 'I'], true)) {
            return 'Inactiva';
        }

        return ucfirst(mb_strtolower((string) $estado));
    }

    private function formatearFecha($fecha): string
    {
        if (empty($fecha)) {
            return 'No disponible';
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y h:i A');
        } catch (Throwable $exception) {
            return (string) $fecha;
        }
    }

    private function normalizarTexto($texto): string
    {
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
