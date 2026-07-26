@extends('layouts.app-secretaria')

@section('titulo', 'Mi perfil')

@vite([
    'resources/css/perfil_secretaria.css',
    'resources/js/perfil_secretaria.js'
])

@section('content')

@php
    $usuario = $usuario ?? auth()->user();
    $perfilEmpleado = $perfilEmpleado ?? [];
    $resumenTrabajo = $resumenTrabajo ?? [];
    $actividadReciente = $actividadReciente ?? [];

    $nombreCompleto = data_get($perfilEmpleado, 'nombre_completo')
        ?? data_get($perfilEmpleado, 'nombre')
        ?? data_get($usuario, 'nombre_persona')
        ?? data_get($usuario, 'name')
        ?? 'No disponible';

    $correo = data_get($perfilEmpleado, 'correo')
        ?? data_get($perfilEmpleado, 'email')
        ?? data_get($usuario, 'email')
        ?? data_get($usuario, 'correo_institucional')
        ?? 'No disponible';

    $codigoEmpleado = data_get(
        $perfilEmpleado,
        'codigo_empleado',
        data_get($usuario, 'codigo_empleado', 'No disponible')
    );

    $rolActual = data_get(
        $perfilEmpleado,
        'rol',
        'Secretaría de Carrera'
    );

    $estadoCuenta = data_get(
        $perfilEmpleado,
        'estado_cuenta',
        'Activa'
    );

    $facultad = data_get(
        $perfilEmpleado,
        'facultad',
        'Facultad de Ciencias Económicas, Administrativas y Contables'
    );

    $departamento = data_get(
        $perfilEmpleado,
        'departamento',
        'No disponible'
    );

    $carreraAsignada = data_get(
        $perfilEmpleado,
        'carrera_asignada',
        'No disponible'
    );

    $centroUniversitario = data_get(
        $perfilEmpleado,
        'centro_universitario',
        'Ciudad Universitaria'
    );

    $alcanceAtencion = data_get(
        $perfilEmpleado,
        'alcance_atencion',
        'Estudiantes y trámites de la carrera asignada'
    );

    $correoVerificado = data_get($perfilEmpleado, 'correo_verificado')
        ?? data_get($usuario, 'email_verified_at');

    $twofaActivo = data_get($perfilEmpleado, 'twofa_activado')
        ?? data_get($usuario, 'twofa_verified_at');

    $ultimaVerificacion2FA = data_get(
        $perfilEmpleado,
        'ultima_verificacion_2fa',
        data_get($usuario, 'twofa_verified_at', 'No disponible')
    );

    $ultimoInicioSesion = data_get(
        $perfilEmpleado,
        'ultimo_inicio_sesion',
        data_get($usuario, 'ultimo_inicio_sesion', 'No disponible')
    );

    $tramitesRecibidos = data_get($resumenTrabajo, 'recibidos', 0);
    $pendientesRevision = data_get($resumenTrabajo, 'pendientes', 0);
    $conObservaciones = data_get($resumenTrabajo, 'observaciones', 0);
    $documentacionCompleta = data_get($resumenTrabajo, 'completos', 0);
    $devueltos = data_get($resumenTrabajo, 'devueltos', 0);
    $remitidosCoordinacion = data_get($resumenTrabajo, 'remitidos', 0);

    $partesNombre = preg_split('/\s+/', trim($nombreCompleto));
    $iniciales = '';

    foreach (array_slice($partesNombre, 0, 2) as $parte) {
        if (!empty($parte)) {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
        }
    }

    if ($iniciales === '') {
        $iniciales = 'SC';
    }

    if (empty($actividadReciente)) {
        $actividadReciente = [
            [
                'icono' => 'fas fa-file-circle-check',
                'titulo' => 'Último trámite revisado',
                'detalle' => 'No hay actividad registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'blue',
            ],
            [
                'icono' => 'fas fa-comment-dots',
                'titulo' => 'Última observación registrada',
                'detalle' => 'No hay actividad registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'gold',
            ],
            [
                'icono' => 'fas fa-share-from-square',
                'titulo' => 'Último expediente remitido',
                'detalle' => 'No hay actividad registrada.',
                'fecha' => 'Sin información',
                'tipo' => 'green',
            ],
        ];
    }
@endphp

<div class="perfil-secretaria-page">

    <section class="perfil-secretaria-header">

        <div class="perfil-secretaria-header-content">

            <div class="perfil-secretaria-avatar">
                {{ $iniciales }}
            </div>

            <div class="perfil-secretaria-header-info">

                <span class="perfil-secretaria-label">
                    Perfil de Secretaría de Carrera
                </span>

                <h1>{{ $nombreCompleto }}</h1>

                <p class="perfil-secretaria-email">
                    <i class="fas fa-envelope"></i>
                    <span id="correoSecretaria">{{ $correo }}</span>
                </p>

                <div class="perfil-secretaria-badges">

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

        <div class="perfil-secretaria-header-actions">

            <button
                type="button"
                class="btn-secretaria-secundario"
                data-copy="{{ $correo }}"
                data-label="correo"
            >
                <i class="fas fa-copy"></i>
                Copiar correo
            </button>

            <a
                href="{{ url('/secretaria-carrera/tramites') }}"
                class="btn-secretaria-acento"
            >
                <i class="fas fa-inbox"></i>
                Ir a trámites asignados
            </a>

        </div>

    </section>

    <div class="perfil-secretaria-grid">

        <section class="perfil-secretaria-card">

            <div class="perfil-secretaria-card-header">
                <div>
                    <h2>
                        <i class="fas fa-user-tie"></i>
                        Información del empleado
                    </h2>

                    <p>Datos laborales registrados en el sistema.</p>
                </div>
            </div>

            <div class="perfil-secretaria-card-body">

                <div class="perfil-secretaria-info-list">

                    <div class="perfil-secretaria-info-item">
                        <span>Nombre completo</span>
                        <strong>{{ $nombreCompleto }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Correo institucional</span>
                        <strong>{{ $correo }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
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

                    <div class="perfil-secretaria-info-item">
                        <span>Tipo de usuario</span>
                        <strong>Empleado</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Rol actual</span>
                        <strong>{{ $rolActual }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Estado de cuenta</span>

                        <strong>
                            <span class="estado-secretaria estado-secretaria-activo">
                                {{ $estadoCuenta }}
                            </span>
                        </strong>
                    </div>

                </div>

            </div>

        </section>

        <section class="perfil-secretaria-card">

            <div class="perfil-secretaria-card-header">
                <div>
                    <h2>
                        <i class="fas fa-building-columns"></i>
                        Asignación institucional
                    </h2>

                    <p>Área académica y alcance de atención asignados.</p>
                </div>
            </div>

            <div class="perfil-secretaria-card-body">

                <div class="perfil-secretaria-info-list">

                    <div class="perfil-secretaria-info-item">
                        <span>Facultad</span>
                        <strong>{{ $facultad }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Departamento</span>
                        <strong>{{ $departamento }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Carrera asignada</span>
                        <strong>{{ $carreraAsignada }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Centro universitario</span>
                        <strong>{{ $centroUniversitario }}</strong>
                    </div>

                    <div class="perfil-secretaria-info-item">
                        <span>Alcance de atención</span>
                        <strong>{{ $alcanceAtencion }}</strong>
                    </div>

                </div>

            </div>

        </section>

    </div>

    <section class="perfil-secretaria-card perfil-secretaria-resumen">

        <div class="perfil-secretaria-card-header">

            <div>
                <h2>
                    <i class="fas fa-chart-column"></i>
                    Resumen de trabajo
                </h2>

                <p>Estado general de los trámites asignados a la secretaría.</p>
            </div>

            <a
                href="{{ url('/secretaria-carrera/tramites') }}"
                class="enlace-secretaria"
            >
                Ver bandeja
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>

        <div class="perfil-secretaria-card-body">

            <div class="perfil-secretaria-estadisticas">

                <article class="estadistica-secretaria estadistica-blue">
                    <div class="estadistica-icono">
                        <i class="fas fa-inbox"></i>
                    </div>

                    <div>
                        <strong>{{ $tramitesRecibidos }}</strong>
                        <span>Trámites recibidos</span>
                    </div>
                </article>

                <article class="estadistica-secretaria estadistica-gold">
                    <div class="estadistica-icono">
                        <i class="fas fa-clock"></i>
                    </div>

                    <div>
                        <strong>{{ $pendientesRevision }}</strong>
                        <span>Pendientes de revisión</span>
                    </div>
                </article>

                <article class="estadistica-secretaria estadistica-orange">
                    <div class="estadistica-icono">
                        <i class="fas fa-comment-dots"></i>
                    </div>

                    <div>
                        <strong>{{ $conObservaciones }}</strong>
                        <span>Con observaciones</span>
                    </div>
                </article>

                <article class="estadistica-secretaria estadistica-green">
                    <div class="estadistica-icono">
                        <i class="fas fa-file-circle-check"></i>
                    </div>

                    <div>
                        <strong>{{ $documentacionCompleta }}</strong>
                        <span>Documentación completa</span>
                    </div>
                </article>

                <article class="estadistica-secretaria estadistica-red">
                    <div class="estadistica-icono">
                        <i class="fas fa-rotate-left"></i>
                    </div>

                    <div>
                        <strong>{{ $devueltos }}</strong>
                        <span>Devueltos al estudiante</span>
                    </div>
                </article>

                <article class="estadistica-secretaria estadistica-purple">
                    <div class="estadistica-icono">
                        <i class="fas fa-share-from-square"></i>
                    </div>

                    <div>
                        <strong>{{ $remitidosCoordinacion }}</strong>
                        <span>Remitidos a coordinación</span>
                    </div>
                </article>

            </div>

        </div>

    </section>

    <div class="perfil-secretaria-grid perfil-secretaria-grid-secundario">

        <section class="perfil-secretaria-card">

            <div class="perfil-secretaria-card-header">
                <div>
                    <h2>
                        <i class="fas fa-list-check"></i>
                        Funciones asignadas
                    </h2>

                    <p>Responsabilidades principales de Secretaría de Carrera.</p>
                </div>
            </div>

            <div class="perfil-secretaria-card-body">

                <div class="funciones-secretaria-list">

                    <div class="funcion-secretaria-item">
                        <span class="funcion-numero">1</span>
                        <p>Revisar la información registrada en las solicitudes.</p>
                    </div>

                    <div class="funcion-secretaria-item">
                        <span class="funcion-numero">2</span>
                        <p>Validar los documentos presentados por los estudiantes.</p>
                    </div>

                    <div class="funcion-secretaria-item">
                        <span class="funcion-numero">3</span>
                        <p>Registrar observaciones claras dentro de los expedientes.</p>
                    </div>

                    <div class="funcion-secretaria-item">
                        <span class="funcion-numero">4</span>
                        <p>Devolver trámites cuando la documentación esté incompleta.</p>
                    </div>

                    <div class="funcion-secretaria-item">
                        <span class="funcion-numero">5</span>
                        <p>Marcar expedientes completos y remitirlos a coordinación.</p>
                    </div>

                    <div class="funcion-secretaria-item">
                        <span class="funcion-numero">6</span>
                        <p>Consultar el seguimiento y la bitácora de los trámites.</p>
                    </div>

                </div>

            </div>

        </section>

        <section class="perfil-secretaria-card">

            <div class="perfil-secretaria-card-header">
                <div>
                    <h2>
                        <i class="fas fa-shield-halved"></i>
                        Seguridad de la cuenta
                    </h2>

                    <p>Estado general de protección del acceso.</p>
                </div>
            </div>

            <div class="perfil-secretaria-card-body">

                <div class="seguridad-secretaria-list">

                    <div class="seguridad-secretaria-item">
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

                    <div class="seguridad-secretaria-item">
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

                    <div class="seguridad-secretaria-item">
                        <div>
                            <span>Última verificación 2FA</span>
                            <strong>{{ $ultimaVerificacion2FA }}</strong>
                        </div>

                        <span class="seguridad-icono seguridad-info">
                            <i class="fas fa-clock"></i>
                        </span>
                    </div>

                    <div class="seguridad-secretaria-item">
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
                    href="{{ url('/configuracion') }}"
                    class="btn-secretaria-primario btn-secretaria-full"
                >
                    <i class="fas fa-gear"></i>
                    Gestionar seguridad en configuración
                </a>

            </div>

        </section>

    </div>

    <section class="perfil-secretaria-card actividad-secretaria-card">

        <div class="perfil-secretaria-card-header">

            <div>
                <h2>
                    <i class="fas fa-clock-rotate-left"></i>
                    Actividad reciente
                </h2>

                <p>Resumen de las últimas acciones realizadas en los trámites.</p>
            </div>

            <a
                href="{{ url('/bitacora/secretaria-carrera') }}"
                class="enlace-secretaria"
            >
                Ver bitácora
                <i class="fas fa-arrow-right"></i>
            </a>

        </div>

        <div class="perfil-secretaria-card-body">

            <div class="actividad-secretaria-list">

                @foreach($actividadReciente as $actividad)

                    <article class="actividad-secretaria-item">

                        <div class="actividad-secretaria-icono actividad-{{ data_get($actividad, 'tipo', 'blue') }}">
                            <i class="{{ data_get($actividad, 'icono', 'fas fa-file') }}"></i>
                        </div>

                        <div class="actividad-secretaria-contenido">
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
        id="perfilSecretariaToast"
        class="perfil-secretaria-toast"
        role="status"
        aria-live="polite"
    ></div>

</div>

@endsection