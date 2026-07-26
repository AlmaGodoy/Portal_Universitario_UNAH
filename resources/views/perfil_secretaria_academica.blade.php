@extends('layouts.app-secretaria-academica')

@section('titulo', 'Mi perfil')

@section('page-assets')
    @vite([
        'resources/css/perfil_secretaria_academica.css',
        'resources/js/perfil_secretaria_academica.js'
    ])
@endsection

@section('content')

@php
    use Illuminate\Support\Facades\Route;

    $usuario = $usuario ?? auth()->user();
    $perfilSecretariaAcademica = $perfilSecretariaAcademica ?? [];
    $resumenGlobal = $resumenGlobal ?? [];
    $coberturaInstitucional = $coberturaInstitucional ?? [];
    $actividadReciente = $actividadReciente ?? [];

    $nombreCompleto = data_get($perfilSecretariaAcademica, 'nombre_completo')
        ?? data_get($usuario, 'nombre_persona')
        ?? data_get($usuario, 'name')
        ?? 'No disponible';

    $correo = data_get($perfilSecretariaAcademica, 'correo')
        ?? data_get($usuario, 'email')
        ?? data_get($usuario, 'correo_institucional')
        ?? 'No disponible';

    $codigoEmpleado = data_get(
        $perfilSecretariaAcademica,
        'codigo_empleado',
        'No disponible'
    );

    $rolActual = data_get(
        $perfilSecretariaAcademica,
        'rol',
        'Secretaría Académica'
    );

    $estadoCuenta = data_get(
        $perfilSecretariaAcademica,
        'estado_cuenta',
        'Activa'
    );

    $facultad = data_get(
        $perfilSecretariaAcademica,
        'facultad',
        'Facultad de Ciencias Económicas, Administrativas y Contables'
    );

    $unidadAdministrativa = data_get(
        $perfilSecretariaAcademica,
        'unidad_administrativa',
        'Secretaría Académica'
    );

    $centroUniversitario = data_get(
        $perfilSecretariaAcademica,
        'centro_universitario',
        'Ciudad Universitaria'
    );

    $nivelSupervision = data_get(
        $perfilSecretariaAcademica,
        'nivel_supervision',
        'Global'
    );

    $alcanceGestion = data_get(
        $perfilSecretariaAcademica,
        'alcance_gestion',
        'Todas las carreras y departamentos autorizados'
    );

    $correoVerificado = data_get($perfilSecretariaAcademica, 'correo_verificado')
        ?? data_get($usuario, 'email_verified_at');

    $twofaActivo = data_get($perfilSecretariaAcademica, 'twofa_activado')
        ?? data_get($usuario, 'twofa_verified_at');

    $ultimaVerificacion2FA = data_get(
        $perfilSecretariaAcademica,
        'ultima_verificacion_2fa',
        data_get($usuario, 'twofa_verified_at', 'No disponible')
    );

    $ultimoInicioSesion = data_get(
        $perfilSecretariaAcademica,
        'ultimo_inicio_sesion',
        'No disponible'
    );

    $totalTramites = data_get($resumenGlobal, 'total', 0);
    $tramitesPendientes = data_get($resumenGlobal, 'pendientes', 0);
    $tramitesRevision = data_get($resumenGlobal, 'revision', 0);
    $tramitesAprobados = data_get($resumenGlobal, 'aprobados', 0);
    $tramitesRechazados = data_get($resumenGlobal, 'rechazados', 0);
    $tramitesFinalizados = data_get($resumenGlobal, 'finalizados', 0);

    $carrerasSupervisadas = data_get($coberturaInstitucional, 'carreras', 0);
    $departamentosRegistrados = data_get($coberturaInstitucional, 'departamentos', 0);
    $secretariasActivas = data_get($coberturaInstitucional, 'secretarias', 0);
    $coordinadoresActivos = data_get($coberturaInstitucional, 'coordinadores', 0);
    $estudiantesConTramites = data_get($coberturaInstitucional, 'estudiantes', 0);
    $calendariosVigentes = data_get($coberturaInstitucional, 'calendarios_vigentes', 0);

    $partesNombre = preg_split('/\s+/', trim($nombreCompleto));
    $iniciales = '';

    foreach (array_slice($partesNombre, 0, 2) as $parte) {
        if (!empty($parte)) {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
        }
    }

    if ($iniciales === '') {
        $iniciales = 'SA';
    }

    $resolverRuta = function (array $rutas, string $alternativa = 'javascript:void(0)') {
        foreach ($rutas as $ruta) {
            if (Route::has($ruta)) {
                return route($ruta);
            }
        }

        return $alternativa;
    };

    $reportesUrl = $resolverRuta([
        'reporte.tramites.secretaria_general.vista',
        'reporte.tramites.vista'
    ]);

    $auditoriaUrl = $resolverRuta([
        'auditoria',
        'auditoria.administrativa'
    ]);

    $bitacoraUrl = $resolverRuta([
        'bitacora.index',
        'bitacora.secretaria-academica'
    ]);

    $seguridadUrl = $resolverRuta([
        'seguridad.index'
    ]);

    $respaldoUrl = $resolverRuta([
        'backup.index'
    ]);

    $configuracionUrl = $resolverRuta([
        'configuracion.index'
    ]);

    if (empty($actividadReciente)) {
        $actividadReciente = [
            [
                'icono' => 'fas fa-file-circle-check',
                'titulo' => 'Último trámite actualizado',
                'detalle' => 'No hay actividad global registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'blue',
            ],
            [
                'icono' => 'fas fa-calendar-check',
                'titulo' => 'Último calendario gestionado',
                'detalle' => 'No hay actividad global registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'gold',
            ],
            [
                'icono' => 'fas fa-user-check',
                'titulo' => 'Última actividad administrativa',
                'detalle' => 'No hay actividad global registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'green',
            ],
        ];
    }
@endphp

<div class="perfil-academica-page">

    <section class="perfil-academica-header">
        <div class="perfil-academica-header-content">
            <div class="perfil-academica-avatar">
                {{ $iniciales }}
            </div>

            <div class="perfil-academica-header-info">
                <span class="perfil-academica-label">
                    Perfil de Secretaría Académica
                </span>

                <h1>{{ $nombreCompleto }}</h1>

                <p class="perfil-academica-email">
                    <i class="fas fa-envelope"></i>
                    <span id="correoSecretariaAcademica">{{ $correo }}</span>
                </p>

                <div class="perfil-academica-badges">
                    <span>
                        <i class="fas fa-id-badge"></i>
                        Código: {{ $codigoEmpleado }}
                    </span>

                    <span>
                        <i class="fas fa-user-shield"></i>
                        {{ $rolActual }}
                    </span>

                    <span>
                        <i class="fas fa-globe"></i>
                        Supervisión global
                    </span>

                    <span class="badge-activo">
                        <i class="fas fa-circle-check"></i>
                        {{ $estadoCuenta }}
                    </span>
                </div>
            </div>
        </div>

        <div class="perfil-academica-header-actions">
            <button
                type="button"
                class="btn-academica-secundario"
                data-copy="{{ $correo }}"
                data-label="correo"
            >
                <i class="fas fa-copy"></i>
                Copiar correo
            </button>

            <a href="{{ $reportesUrl }}" class="btn-academica-acento">
                <i class="fas fa-chart-column"></i>
                Ver reporte global
            </a>
        </div>
    </section>

    <div class="perfil-academica-grid">

        <section class="perfil-academica-card">
            <div class="perfil-academica-card-header">
                <div>
                    <h2>
                        <i class="fas fa-user-tie"></i>
                        Información del empleado
                    </h2>
                    <p>Datos laborales registrados dentro del sistema.</p>
                </div>
            </div>

            <div class="perfil-academica-card-body">
                <div class="perfil-academica-info-list">
                    <div class="perfil-academica-info-item">
                        <span>Nombre completo</span>
                        <strong>{{ $nombreCompleto }}</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Correo institucional</span>
                        <strong>{{ $correo }}</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Código de empleado</span>

                        <div class="dato-con-accion">
                            <strong>{{ $codigoEmpleado }}</strong>

                            @if($codigoEmpleado !== 'No disponible')
                                <button
                                    type="button"
                                    class="btn-copiar-dato"
                                    data-copy="{{ $codigoEmpleado }}"
                                    data-label="código de empleado"
                                    aria-label="Copiar código de empleado"
                                    title="Copiar código de empleado"
                                >
                                    <i class="fas fa-copy"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Tipo de usuario</span>
                        <strong>Empleado</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Rol actual</span>
                        <strong>{{ $rolActual }}</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Estado de cuenta</span>
                        <strong>
                            <span class="estado-academica estado-academica-activo">
                                {{ $estadoCuenta }}
                            </span>
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="perfil-academica-card">
            <div class="perfil-academica-card-header">
                <div>
                    <h2>
                        <i class="fas fa-building-columns"></i>
                        Asignación institucional
                    </h2>
                    <p>Unidad, cobertura y alcance de supervisión.</p>
                </div>
            </div>

            <div class="perfil-academica-card-body">
                <div class="perfil-academica-info-list">
                    <div class="perfil-academica-info-item">
                        <span>Facultad</span>
                        <strong>{{ $facultad }}</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Unidad administrativa</span>
                        <strong>{{ $unidadAdministrativa }}</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Centro universitario</span>
                        <strong>{{ $centroUniversitario }}</strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Nivel de supervisión</span>
                        <strong>
                            <span class="estado-academica estado-academica-global">
                                {{ $nivelSupervision }}
                            </span>
                        </strong>
                    </div>

                    <div class="perfil-academica-info-item">
                        <span>Alcance de gestión</span>
                        <strong>{{ $alcanceGestion }}</strong>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <section class="perfil-academica-card perfil-academica-resumen">
        <div class="perfil-academica-card-header">
            <div>
                <h2>
                    <i class="fas fa-chart-line"></i>
                    Resumen global de trámites
                </h2>
                <p>Estado consolidado de todos los trámites autorizados.</p>
            </div>

            <a href="{{ $reportesUrl }}" class="enlace-academica">
                Ver reporte completo
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="perfil-academica-card-body">
            <div class="perfil-academica-estadisticas">
                <article class="estadistica-academica estadistica-blue">
                    <div class="estadistica-icono">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div>
                        <strong>{{ $totalTramites }}</strong>
                        <span>Total de trámites</span>
                    </div>
                </article>

                <article class="estadistica-academica estadistica-gold">
                    <div class="estadistica-icono">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <strong>{{ $tramitesPendientes }}</strong>
                        <span>Pendientes</span>
                    </div>
                </article>

                <article class="estadistica-academica estadistica-purple">
                    <div class="estadistica-icono">
                        <i class="fas fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <strong>{{ $tramitesRevision }}</strong>
                        <span>En revisión</span>
                    </div>
                </article>

                <article class="estadistica-academica estadistica-green">
                    <div class="estadistica-icono">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <strong>{{ $tramitesAprobados }}</strong>
                        <span>Aprobados</span>
                    </div>
                </article>

                <article class="estadistica-academica estadistica-red">
                    <div class="estadistica-icono">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                    <div>
                        <strong>{{ $tramitesRechazados }}</strong>
                        <span>Rechazados</span>
                    </div>
                </article>

                <article class="estadistica-academica estadistica-orange">
                    <div class="estadistica-icono">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div>
                        <strong>{{ $tramitesFinalizados }}</strong>
                        <span>Finalizados</span>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <div class="perfil-academica-grid perfil-academica-grid-secundario">

        <section class="perfil-academica-card">
            <div class="perfil-academica-card-header">
                <div>
                    <h2>
                        <i class="fas fa-sitemap"></i>
                        Cobertura institucional
                    </h2>
                    <p>Indicadores generales de las unidades supervisadas.</p>
                </div>
            </div>

            <div class="perfil-academica-card-body">
                <div class="cobertura-academica-grid">
                    <div class="cobertura-academica-item">
                        <i class="fas fa-graduation-cap"></i>
                        <strong>{{ $carrerasSupervisadas }}</strong>
                        <span>Carreras supervisadas</span>
                    </div>

                    <div class="cobertura-academica-item">
                        <i class="fas fa-building"></i>
                        <strong>{{ $departamentosRegistrados }}</strong>
                        <span>Departamentos</span>
                    </div>

                    <div class="cobertura-academica-item">
                        <i class="fas fa-user-pen"></i>
                        <strong>{{ $secretariasActivas }}</strong>
                        <span>Secretarías activas</span>
                    </div>

                    <div class="cobertura-academica-item">
                        <i class="fas fa-user-tie"></i>
                        <strong>{{ $coordinadoresActivos }}</strong>
                        <span>Coordinadores activos</span>
                    </div>

                    <div class="cobertura-academica-item">
                        <i class="fas fa-users"></i>
                        <strong>{{ $estudiantesConTramites }}</strong>
                        <span>Estudiantes con trámites</span>
                    </div>

                    <div class="cobertura-academica-item">
                        <i class="fas fa-calendar-check"></i>
                        <strong>{{ $calendariosVigentes }}</strong>
                        <span>Calendarios vigentes</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="perfil-academica-card">
            <div class="perfil-academica-card-header">
                <div>
                    <h2>
                        <i class="fas fa-list-check"></i>
                        Funciones asignadas
                    </h2>
                    <p>Responsabilidades principales del rol global.</p>
                </div>
            </div>

            <div class="perfil-academica-card-body">
                <div class="funciones-academica-list">
                    <div class="funcion-academica-item">
                        <span class="funcion-numero">1</span>
                        <p>Supervisar globalmente los trámites académicos.</p>
                    </div>

                    <div class="funcion-academica-item">
                        <span class="funcion-numero">2</span>
                        <p>Consultar información consolidada de todas las carreras autorizadas.</p>
                    </div>

                    <div class="funcion-academica-item">
                        <span class="funcion-numero">3</span>
                        <p>Dar seguimiento a trámites pendientes, atrasados o con incidencias.</p>
                    </div>

                    <div class="funcion-academica-item">
                        <span class="funcion-numero">4</span>
                        <p>Revisar reportes, auditorías y bitácoras institucionales.</p>
                    </div>

                    <div class="funcion-academica-item">
                        <span class="funcion-numero">5</span>
                        <p>Supervisar la actividad de secretarías y coordinadores.</p>
                    </div>

                    <div class="funcion-academica-item">
                        <span class="funcion-numero">6</span>
                        <p>Apoyar la toma de decisiones mediante indicadores globales.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <div class="perfil-academica-grid perfil-academica-grid-secundario">

        <section class="perfil-academica-card">
            <div class="perfil-academica-card-header">
                <div>
                    <h2>
                        <i class="fas fa-shield-halved"></i>
                        Seguridad de la cuenta
                    </h2>
                    <p>Estado general de protección y acceso.</p>
                </div>
            </div>

            <div class="perfil-academica-card-body">
                <div class="seguridad-academica-list">
                    <div class="seguridad-academica-item">
                        <div>
                            <span>Correo verificado</span>
                            <strong>{{ $correoVerificado ? 'Verificado' : 'Pendiente' }}</strong>
                        </div>

                        <span class="seguridad-icono {{ $correoVerificado ? 'seguridad-ok' : 'seguridad-alerta' }}">
                            <i class="fas {{ $correoVerificado ? 'fa-check' : 'fa-exclamation' }}"></i>
                        </span>
                    </div>

                    <div class="seguridad-academica-item">
                        <div>
                            <span>Autenticación 2FA</span>
                            <strong>{{ $twofaActivo ? 'Activada' : 'Pendiente' }}</strong>
                        </div>

                        <span class="seguridad-icono {{ $twofaActivo ? 'seguridad-ok' : 'seguridad-alerta' }}">
                            <i class="fas {{ $twofaActivo ? 'fa-check' : 'fa-exclamation' }}"></i>
                        </span>
                    </div>

                    <div class="seguridad-academica-item">
                        <div>
                            <span>Última verificación 2FA</span>
                            <strong>{{ $ultimaVerificacion2FA }}</strong>
                        </div>

                        <span class="seguridad-icono seguridad-info">
                            <i class="fas fa-clock"></i>
                        </span>
                    </div>

                    <div class="seguridad-academica-item">
                        <div>
                            <span>Último inicio de sesión</span>
                            <strong>{{ $ultimoInicioSesion }}</strong>
                        </div>

                        <span class="seguridad-icono seguridad-info">
                            <i class="fas fa-right-to-bracket"></i>
                        </span>
                    </div>
                </div>

                <a href="{{ $configuracionUrl }}" class="btn-academica-primario btn-academica-full">
                    <i class="fas fa-gear"></i>
                    Gestionar seguridad en configuración
                </a>
            </div>
        </section>

        <section class="perfil-academica-card">
            <div class="perfil-academica-card-header">
                <div>
                    <h2>
                        <i class="fas fa-bolt"></i>
                        Accesos rápidos
                    </h2>
                    <p>Herramientas de supervisión y administración.</p>
                </div>
            </div>

            <div class="perfil-academica-card-body">
                <div class="accesos-academica-grid">
                    <a href="{{ $reportesUrl }}" class="acceso-academica-item">
                        <i class="fas fa-chart-column"></i>
                        <span>Reportes globales</span>
                    </a>

                    <a href="{{ $auditoriaUrl }}" class="acceso-academica-item">
                        <i class="fas fa-magnifying-glass-chart"></i>
                        <span>Auditoría</span>
                    </a>

                    <a href="{{ $bitacoraUrl }}" class="acceso-academica-item">
                        <i class="fas fa-book"></i>
                        <span>Bitácora</span>
                    </a>

                    <a href="{{ $seguridadUrl }}" class="acceso-academica-item">
                        <i class="fas fa-shield-halved"></i>
                        <span>Seguridad</span>
                    </a>

                    <a href="{{ $respaldoUrl }}" class="acceso-academica-item">
                        <i class="fas fa-database"></i>
                        <span>Respaldos</span>
                    </a>

                    <a href="{{ $configuracionUrl }}" class="acceso-academica-item">
                        <i class="fas fa-gear"></i>
                        <span>Configuración</span>
                    </a>
                </div>
            </div>
        </section>

    </div>

    <section class="perfil-academica-card actividad-academica-card">
        <div class="perfil-academica-card-header">
            <div>
                <h2>
                    <i class="fas fa-clock-rotate-left"></i>
                    Actividad reciente global
                </h2>
                <p>Últimas acciones institucionales registradas.</p>
            </div>

            <a href="{{ $bitacoraUrl }}" class="enlace-academica">
                Ver bitácora
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="perfil-academica-card-body">
            <div class="actividad-academica-list">
                @foreach($actividadReciente as $actividad)
                    <article class="actividad-academica-item">
                        <div class="actividad-academica-icono actividad-{{ data_get($actividad, 'tipo', 'blue') }}">
                            <i class="{{ data_get($actividad, 'icono', 'fas fa-file') }}"></i>
                        </div>

                        <div class="actividad-academica-contenido">
                            <h3>{{ data_get($actividad, 'titulo', 'Actividad') }}</h3>
                            <p>{{ data_get($actividad, 'detalle', 'Sin detalles disponibles.') }}</p>
                        </div>

                        <time>{{ data_get($actividad, 'fecha', 'Sin información') }}</time>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <div
        id="perfilSecretariaAcademicaToast"
        class="perfil-academica-toast"
        role="status"
        aria-live="polite"
    ></div>

</div>

@endsection
