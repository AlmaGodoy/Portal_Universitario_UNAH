@extends('layouts.app-coordinador')

@section('titulo', 'Mi perfil')

@vite([
    'resources/css/perfil_coordinador.css',
    'resources/js/perfil_coordinador.js'
])

@section('content')

@php
    $usuario = $usuario ?? auth()->user();
    $perfilCoordinador = $perfilCoordinador ?? [];
    $resumenDictamenes = $resumenDictamenes ?? [];
    $actividadReciente = $actividadReciente ?? [];

    /*
    |--------------------------------------------------------------------------
    | INFORMACIÓN DEL COORDINADOR
    |--------------------------------------------------------------------------
    */

    $nombreCompleto = data_get($perfilCoordinador, 'nombre_completo')
        ?? data_get($usuario, 'nombre_persona')
        ?? data_get($usuario, 'name')
        ?? 'No disponible';

    $correo = data_get($perfilCoordinador, 'correo')
        ?? data_get($usuario, 'email')
        ?? data_get($usuario, 'correo_institucional')
        ?? 'No disponible';

    $codigoEmpleado = data_get(
        $perfilCoordinador,
        'codigo_empleado',
        'No disponible'
    );

    $rolActual = data_get(
        $perfilCoordinador,
        'rol',
        'Coordinador de Carrera'
    );

    $estadoCuenta = data_get(
        $perfilCoordinador,
        'estado_cuenta',
        'Activa'
    );

    /*
    |--------------------------------------------------------------------------
    | ASIGNACIÓN INSTITUCIONAL
    |--------------------------------------------------------------------------
    */

    $facultad = data_get(
        $perfilCoordinador,
        'facultad',
        'Facultad de Ciencias Económicas, Administrativas y Contables'
    );

    $departamento = data_get(
        $perfilCoordinador,
        'departamento',
        'No disponible'
    );

    $carreraAsignada = data_get(
        $perfilCoordinador,
        'carrera_asignada',
        'No disponible'
    );

    $centroUniversitario = data_get(
        $perfilCoordinador,
        'centro_universitario',
        'Ciudad Universitaria'
    );

    $alcanceGestion = data_get(
        $perfilCoordinador,
        'alcance_gestion',
        'Resolución y seguimiento de los trámites de la carrera asignada'
    );

    /*
    |--------------------------------------------------------------------------
    | SEGURIDAD
    |--------------------------------------------------------------------------
    */

    $correoVerificado = data_get($perfilCoordinador, 'correo_verificado')
        ?? data_get($usuario, 'email_verified_at');

    $twofaActivo = data_get($perfilCoordinador, 'twofa_activado')
        ?? data_get($usuario, 'twofa_verified_at');

    $ultimaVerificacion2FA = data_get(
        $perfilCoordinador,
        'ultima_verificacion_2fa',
        data_get($usuario, 'twofa_verified_at', 'No disponible')
    );

    $ultimoInicioSesion = data_get(
        $perfilCoordinador,
        'ultimo_inicio_sesion',
        'No disponible'
    );

    /*
    |--------------------------------------------------------------------------
    | RESUMEN DE DICTÁMENES
    |--------------------------------------------------------------------------
    */

    $tramitesRecibidos = data_get($resumenDictamenes, 'recibidos', 0);
    $pendientesDictamen = data_get($resumenDictamenes, 'pendientes', 0);
    $tramitesAprobados = data_get($resumenDictamenes, 'aprobados', 0);
    $tramitesRechazados = data_get($resumenDictamenes, 'rechazados', 0);
    $devueltosSecretaria = data_get($resumenDictamenes, 'devueltos', 0);
    $totalResueltos = data_get($resumenDictamenes, 'resueltos', 0);

    /*
    |--------------------------------------------------------------------------
    | INICIALES
    |--------------------------------------------------------------------------
    */

    $partesNombre = preg_split('/\s+/', trim($nombreCompleto));
    $iniciales = '';

    foreach (array_slice($partesNombre, 0, 2) as $parte) {
        if (!empty($parte)) {
            $iniciales .= mb_strtoupper(
                mb_substr($parte, 0, 1)
            );
        }
    }

    if ($iniciales === '') {
        $iniciales = 'CO';
    }

    /*
    |--------------------------------------------------------------------------
    | RUTAS SEGURAS
    |--------------------------------------------------------------------------
    */

    $resolverRuta = function (array $nombres, string $alternativa = '#') {
        foreach ($nombres as $nombre) {
            if (\Illuminate\Support\Facades\Route::has($nombre)) {
                return route($nombre);
            }
        }

        return $alternativa;
    };

    $tramitesUrl = $resolverRuta([
        'cambio-carrera.coordinador',
        'cambio-carrera.coordinador.index',
        'coordinador.tramites',
        'tramites.coordinador'
    ]);

    $reportesUrl = $resolverRuta([
        'reportes.coordinador',
        'reporte.coordinador',
        'coordinador.reportes'
    ]);

    $configuracionUrl = $resolverRuta([
        'configuracion.index',
        'coordinador.configuracion'
    ], url('/configuracion'));

    $bitacoraUrl = $resolverRuta([
        'bitacora.coordinador',
        'bitacora.index'
    ], url('/bitacora'));

    /*
    |--------------------------------------------------------------------------
    | ACTIVIDAD PREDETERMINADA
    |--------------------------------------------------------------------------
    */

    if (empty($actividadReciente)) {
        $actividadReciente = [
            [
                'icono' => 'fas fa-file-signature',
                'titulo' => 'Último trámite dictaminado',
                'detalle' => 'No hay actividad registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'blue',
            ],
            [
                'icono' => 'fas fa-circle-check',
                'titulo' => 'Última resolución aprobada',
                'detalle' => 'No hay actividad registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'green',
            ],
            [
                'icono' => 'fas fa-circle-xmark',
                'titulo' => 'Última resolución rechazada',
                'detalle' => 'No hay actividad registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'red',
            ],
        ];
    }
@endphp

<div class="perfil-coordinador-page">

    {{-- =========================================================
        ENCABEZADO
    ========================================================== --}}

    <section class="perfil-coordinador-header">

        <div class="perfil-coordinador-header-content">

            <div class="perfil-coordinador-avatar">
                {{ $iniciales }}
            </div>

            <div class="perfil-coordinador-header-info">

                <span class="perfil-coordinador-label">
                    Perfil del Coordinador de Carrera
                </span>

                <h1>{{ $nombreCompleto }}</h1>

                <p class="perfil-coordinador-email">
                    <i class="fas fa-envelope"></i>

                    <span id="correoCoordinador">
                        {{ $correo }}
                    </span>
                </p>

                <div class="perfil-coordinador-badges">

                    <span>
                        <i class="fas fa-id-badge"></i>
                        Código: {{ $codigoEmpleado }}
                    </span>

                    <span>
                        <i class="fas fa-user-tie"></i>
                        {{ $rolActual }}
                    </span>

                    <span class="badge-activo">
                        <i class="fas fa-circle-check"></i>
                        {{ $estadoCuenta }}
                    </span>

                </div>

            </div>

        </div>

        <div class="perfil-coordinador-header-actions">

            <button
                type="button"
                class="btn-coordinador-secundario"
                data-copy="{{ $correo }}"
                data-label="correo"
            >
                <i class="fas fa-copy"></i>
                Copiar correo
            </button>

            <a
                href="{{ $tramitesUrl }}"
                class="btn-coordinador-acento"
            >
                <i class="fas fa-file-signature"></i>
                Ir a trámites por dictaminar
            </a>

        </div>

    </section>

    {{-- =========================================================
        INFORMACIÓN Y ASIGNACIÓN
    ========================================================== --}}

    <div class="perfil-coordinador-grid">

        <section class="perfil-coordinador-card">

            <div class="perfil-coordinador-card-header">

                <div>
                    <h2>
                        <i class="fas fa-user-tie"></i>
                        Información del empleado
                    </h2>

                    <p>
                        Datos laborales registrados dentro del sistema.
                    </p>
                </div>

            </div>

            <div class="perfil-coordinador-card-body">

                <div class="perfil-coordinador-info-list">

                    <div class="perfil-coordinador-info-item">
                        <span>Nombre completo</span>
                        <strong>{{ $nombreCompleto }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Correo institucional</span>
                        <strong>{{ $correo }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">

                        <span>Código de empleado</span>

                        <div class="dato-con-accion">

                            <strong>{{ $codigoEmpleado }}</strong>

                            @if($codigoEmpleado !== 'No disponible')
                                <button
                                    type="button"
                                    class="btn-copiar-dato"
                                    data-copy="{{ $codigoEmpleado }}"
                                    data-label="código de empleado"
                                    title="Copiar código de empleado"
                                    aria-label="Copiar código de empleado"
                                >
                                    <i class="fas fa-copy"></i>
                                </button>
                            @endif

                        </div>

                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Tipo de usuario</span>
                        <strong>Empleado</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Rol actual</span>
                        <strong>{{ $rolActual }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">

                        <span>Estado de cuenta</span>

                        <strong>
                            <span class="estado-coordinador estado-coordinador-activo">
                                {{ $estadoCuenta }}
                            </span>
                        </strong>

                    </div>

                </div>

            </div>

        </section>

        <section class="perfil-coordinador-card">

            <div class="perfil-coordinador-card-header">

                <div>
                    <h2>
                        <i class="fas fa-building-columns"></i>
                        Asignación institucional
                    </h2>

                    <p>
                        Área académica y alcance de gestión asignados.
                    </p>
                </div>

            </div>

            <div class="perfil-coordinador-card-body">

                <div class="perfil-coordinador-info-list">

                    <div class="perfil-coordinador-info-item">
                        <span>Facultad</span>
                        <strong>{{ $facultad }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Departamento</span>
                        <strong>{{ $departamento }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Carrera asignada</span>
                        <strong>{{ $carreraAsignada }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Centro universitario</span>
                        <strong>{{ $centroUniversitario }}</strong>
                    </div>

                    <div class="perfil-coordinador-info-item">
                        <span>Alcance de gestión</span>
                        <strong>{{ $alcanceGestion }}</strong>
                    </div>

                </div>

            </div>

        </section>

    </div>

    {{-- =========================================================
        RESUMEN DE DICTÁMENES
    ========================================================== --}}

    <section class="perfil-coordinador-card perfil-coordinador-resumen">

        <div class="perfil-coordinador-card-header">

            <div>
                <h2>
                    <i class="fas fa-chart-column"></i>
                    Resumen de dictámenes
                </h2>

                <p>
                    Estado general de los trámites recibidos para resolución.
                </p>
            </div>

            <a
                href="{{ $tramitesUrl }}"
                class="enlace-coordinador"
            >
                Ver bandeja
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>

        <div class="perfil-coordinador-card-body">

            <div class="perfil-coordinador-estadisticas">

                <article class="estadistica-coordinador estadistica-blue">

                    <div class="estadistica-icono">
                        <i class="fas fa-inbox"></i>
                    </div>

                    <div>
                        <strong>{{ $tramitesRecibidos }}</strong>
                        <span>Trámites recibidos</span>
                    </div>

                </article>

                <article class="estadistica-coordinador estadistica-gold">

                    <div class="estadistica-icono">
                        <i class="fas fa-hourglass-half"></i>
                    </div>

                    <div>
                        <strong>{{ $pendientesDictamen }}</strong>
                        <span>Pendientes de dictamen</span>
                    </div>

                </article>

                <article class="estadistica-coordinador estadistica-green">

                    <div class="estadistica-icono">
                        <i class="fas fa-circle-check"></i>
                    </div>

                    <div>
                        <strong>{{ $tramitesAprobados }}</strong>
                        <span>Trámites aprobados</span>
                    </div>

                </article>

                <article class="estadistica-coordinador estadistica-red">

                    <div class="estadistica-icono">
                        <i class="fas fa-circle-xmark"></i>
                    </div>

                    <div>
                        <strong>{{ $tramitesRechazados }}</strong>
                        <span>Trámites rechazados</span>
                    </div>

                </article>

                <article class="estadistica-coordinador estadistica-orange">

                    <div class="estadistica-icono">
                        <i class="fas fa-rotate-left"></i>
                    </div>

                    <div>
                        <strong>{{ $devueltosSecretaria }}</strong>
                        <span>Devueltos a secretaría</span>
                    </div>

                </article>

                <article class="estadistica-coordinador estadistica-purple">

                    <div class="estadistica-icono">
                        <i class="fas fa-file-circle-check"></i>
                    </div>

                    <div>
                        <strong>{{ $totalResueltos }}</strong>
                        <span>Total resueltos</span>
                    </div>

                </article>

            </div>

        </div>

    </section>

    {{-- =========================================================
        FUNCIONES Y SEGURIDAD
    ========================================================== --}}

    <div class="perfil-coordinador-grid perfil-coordinador-grid-secundario">

        <section class="perfil-coordinador-card">

            <div class="perfil-coordinador-card-header">

                <div>
                    <h2>
                        <i class="fas fa-list-check"></i>
                        Funciones asignadas
                    </h2>

                    <p>
                        Responsabilidades principales del Coordinador de Carrera.
                    </p>
                </div>

            </div>

            <div class="perfil-coordinador-card-body">

                <div class="funciones-coordinador-list">

                    <div class="funcion-coordinador-item">
                        <span class="funcion-numero">1</span>

                        <p>
                            Revisar los expedientes remitidos por Secretaría de Carrera.
                        </p>
                    </div>

                    <div class="funcion-coordinador-item">
                        <span class="funcion-numero">2</span>

                        <p>
                            Verificar que la documentación haya sido validada correctamente.
                        </p>
                    </div>

                    <div class="funcion-coordinador-item">
                        <span class="funcion-numero">3</span>

                        <p>
                            Emitir el dictamen final de aprobación o rechazo.
                        </p>
                    </div>

                    <div class="funcion-coordinador-item">
                        <span class="funcion-numero">4</span>

                        <p>
                            Registrar el motivo correspondiente cuando un trámite sea rechazado.
                        </p>
                    </div>

                    <div class="funcion-coordinador-item">
                        <span class="funcion-numero">5</span>

                        <p>
                            Devolver expedientes a secretaría cuando requieran correcciones.
                        </p>
                    </div>

                    <div class="funcion-coordinador-item">
                        <span class="funcion-numero">6</span>

                        <p>
                            Consultar reportes, estadísticas y bitácoras de los trámites.
                        </p>
                    </div>

                </div>

                <a
                    href="{{ $reportesUrl }}"
                    class="btn-coordinador-reporte"
                >
                    <i class="fas fa-chart-line"></i>
                    Consultar reportes
                </a>

            </div>

        </section>

        <section class="perfil-coordinador-card">

            <div class="perfil-coordinador-card-header">

                <div>
                    <h2>
                        <i class="fas fa-shield-halved"></i>
                        Seguridad de la cuenta
                    </h2>

                    <p>
                        Estado general de protección y acceso.
                    </p>
                </div>

            </div>

            <div class="perfil-coordinador-card-body">

                <div class="seguridad-coordinador-list">

                    <div class="seguridad-coordinador-item">

                        <div>
                            <span>Correo verificado</span>

                            <strong>
                                {{ $correoVerificado ? 'Verificado' : 'Pendiente' }}
                            </strong>
                        </div>

                        <span class="seguridad-icono {{ $correoVerificado ? 'seguridad-ok' : 'seguridad-alerta' }}">
                            <i class="fas {{ $correoVerificado ? 'fa-check' : 'fa-exclamation' }}"></i>
                        </span>

                    </div>

                    <div class="seguridad-coordinador-item">

                        <div>
                            <span>Autenticación 2FA</span>

                            <strong>
                                {{ $twofaActivo ? 'Activada' : 'Pendiente' }}
                            </strong>
                        </div>

                        <span class="seguridad-icono {{ $twofaActivo ? 'seguridad-ok' : 'seguridad-alerta' }}">
                            <i class="fas {{ $twofaActivo ? 'fa-check' : 'fa-exclamation' }}"></i>
                        </span>

                    </div>

                    <div class="seguridad-coordinador-item">

                        <div>
                            <span>Última verificación 2FA</span>
                            <strong>{{ $ultimaVerificacion2FA }}</strong>
                        </div>

                        <span class="seguridad-icono seguridad-info">
                            <i class="fas fa-clock"></i>
                        </span>

                    </div>

                    <div class="seguridad-coordinador-item">

                        <div>
                            <span>Último inicio de sesión</span>
                            <strong>{{ $ultimoInicioSesion }}</strong>
                        </div>

                        <span class="seguridad-icono seguridad-info">
                            <i class="fas fa-right-to-bracket"></i>
                        </span>

                    </div>

                </div>

                <a
                    href="{{ $configuracionUrl }}"
                    class="btn-coordinador-primario btn-coordinador-full"
                >
                    <i class="fas fa-gear"></i>
                    Gestionar seguridad en configuración
                </a>

            </div>

        </section>

    </div>

    {{-- =========================================================
        ACTIVIDAD RECIENTE
    ========================================================== --}}

    <section class="perfil-coordinador-card actividad-coordinador-card">

        <div class="perfil-coordinador-card-header">

            <div>
                <h2>
                    <i class="fas fa-clock-rotate-left"></i>
                    Actividad reciente
                </h2>

                <p>
                    Resumen de las últimas resoluciones registradas.
                </p>
            </div>

            <a
                href="{{ $bitacoraUrl }}"
                class="enlace-coordinador"
            >
                Ver bitácora
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>

        <div class="perfil-coordinador-card-body">

            <div class="actividad-coordinador-list">

                @foreach($actividadReciente as $actividad)

                    <article class="actividad-coordinador-item">

                        <div class="actividad-coordinador-icono actividad-{{ data_get($actividad, 'tipo', 'blue') }}">
                            <i class="{{ data_get($actividad, 'icono', 'fas fa-file-signature') }}"></i>
                        </div>

                        <div class="actividad-coordinador-contenido">

                            <h3>
                                {{ data_get($actividad, 'titulo', 'Actividad') }}
                            </h3>

                            <p>
                                {{ data_get($actividad, 'detalle', 'Sin detalles disponibles.') }}
                            </p>

                        </div>

                        <time>
                            {{ data_get($actividad, 'fecha', 'Sin información') }}
                        </time>

                    </article>

                @endforeach

            </div>

        </div>

    </section>

    <div
        id="perfilCoordinadorToast"
        class="perfil-coordinador-toast"
        role="status"
        aria-live="polite"
    ></div>

</div>

@endsection