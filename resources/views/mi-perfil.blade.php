@extends('layouts.app-estudiantes')

@section('titulo', 'Mi perfil')

@vite([
    'resources/css/mi-perfil.css',
    'resources/js/mi-perfil.js'
])

@section('content')

@php
    $usuario = $usuario ?? auth()->user();
    $perfil = $perfil ?? null;
    $resumenTramites = $resumenTramites ?? [];

    $nombreCompleto = data_get($perfil, 'nombre_completo')
        ?? trim((data_get($perfil, 'nombres', '') . ' ' . data_get($perfil, 'apellidos', '')))
        ?: data_get($usuario, 'name', 'No disponible');

    $correo = data_get($perfil, 'correo')
        ?? data_get($perfil, 'email')
        ?? data_get($usuario, 'email', 'No disponible');

    $numeroCuenta = data_get($perfil, 'numero_cuenta', 'No disponible');

    $carrera = data_get($perfil, 'carrera_actual', data_get($perfil, 'carrera', 'No disponible'));
    $facultad = data_get($perfil, 'facultad', 'Facultad de Ciencias Económicas, Administrativas y Contables');
    $centro = data_get($perfil, 'centro_universitario', 'Ciudad Universitaria');
    $estadoEstudiante = data_get($perfil, 'estado_estudiante', data_get($perfil, 'estado_academico', 'Activo'));
    $estadoCuenta = data_get($perfil, 'estado_cuenta', 'Activa');

    $correoVerificado = data_get($perfil, 'correo_verificado')
        ?? data_get($usuario, 'email_verified_at');

    $twofaActivo = data_get($perfil, 'twofa_activado')
        ?? data_get($perfil, 'twofa_verified_at');

    $ultimaVerificacion2FA = data_get($perfil, 'ultima_verificacion_2fa')
        ?? data_get($perfil, 'twofa_verified_at', 'No disponible');

    $ultimoLogin = data_get($perfil, 'ultimo_inicio_sesion', 'No disponible');

    $tramitesEnviados = data_get($resumenTramites, 'enviados', 0);
    $tramitesPendientes = data_get($resumenTramites, 'pendientes', 0);
    $tramitesAprobados = data_get($resumenTramites, 'aprobados', 0);
    $tramitesRechazados = data_get($resumenTramites, 'rechazados', 0);

    $partesNombre = explode(' ', trim($nombreCompleto));
    $iniciales = '';

    if (count($partesNombre) >= 2) {
        $iniciales = mb_substr($partesNombre[0], 0, 1) . mb_substr(end($partesNombre), 0, 1);
    } else {
        $iniciales = mb_substr($nombreCompleto, 0, 2);
    }

    $iniciales = strtoupper($iniciales);
@endphp

<div class="perfil-page">

    <div class="perfil-breadcrumb">
        <a href="{{ url('/dashboard') }}">
            <i class="fas fa-home"></i> Inicio
        </a>
        <span>/</span>
        <strong>Mi perfil</strong>
    </div>

    <section class="perfil-hero">
        <div class="perfil-hero-content">
            <div class="perfil-avatar">
                {{ $iniciales }}
            </div>

            <div class="perfil-hero-info">
                <span class="perfil-label">Perfil del estudiante</span>

                <h1>{{ $nombreCompleto }}</h1>

                <p>
                    <i class="fas fa-envelope"></i>
                    <span id="perfilCorreo">{{ $correo }}</span>
                </p>

                <div class="perfil-hero-badges">
                    <span>
                        <i class="fas fa-id-card"></i>
                        Cuenta: {{ $numeroCuenta }}
                    </span>

                    <span>
                        <i class="fas fa-user-graduate"></i>
                        Estudiante
                    </span>
                </div>
            </div>
        </div>

        <div class="perfil-hero-actions">
            <button type="button" class="btn-perfil-secundario" id="btnCopiarCorreo">
                <i class="fas fa-copy"></i>
                Copiar correo
            </button>
        </div>
    </section>

    <div class="perfil-grid">

        <section class="perfil-card">
            <div class="perfil-card-header">
                <div>
                    <h2>Datos del estudiante</h2>
                    <p>Información principal registrada en el portal estudiantil.</p>
                </div>

                <i class="fas fa-user perfil-card-icon"></i>
            </div>

            <div class="perfil-info-list">
                <div class="perfil-info-item">
                    <span>Nombre completo</span>
                    <strong>{{ $nombreCompleto }}</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Correo institucional</span>
                    <strong>{{ $correo }}</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Número de cuenta</span>
                    <strong>{{ $numeroCuenta }}</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Tipo de usuario</span>
                    <strong>Estudiante</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Estado de cuenta</span>
                    <strong>
                        <span class="estado-badge estado-activo">
                            {{ $estadoCuenta }}
                        </span>
                    </strong>
                </div>
            </div>
        </section>

        <section class="perfil-card">
            <div class="perfil-card-header">
                <div>
                    <h2>Información académica</h2>
                    <p>Datos relacionados con tu carrera y centro universitario.</p>
                </div>

                <i class="fas fa-graduation-cap perfil-card-icon"></i>
            </div>

            <div class="perfil-info-list">
                <div class="perfil-info-item">
                    <span>Carrera actual</span>
                    <strong>{{ $carrera }}</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Facultad</span>
                    <strong>{{ $facultad }}</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Centro universitario</span>
                    <strong>{{ $centro }}</strong>
                </div>

                <div class="perfil-info-item">
                    <span>Estado del estudiante</span>
                    <strong>
                        <span class="estado-badge estado-activo">
                            {{ $estadoEstudiante }}
                        </span>
                    </strong>
                </div>
            </div>
        </section>

    </div>

    <div class="perfil-grid perfil-grid-bottom">

        <section class="perfil-card">
            <div class="perfil-card-header">
                <div>
                    <h2>Seguridad de la cuenta</h2>
                    <p>Estado general de verificación y protección del acceso.</p>
                </div>

                <i class="fas fa-shield-alt perfil-card-icon"></i>
            </div>

            <div class="seguridad-list">
                <div class="seguridad-item">
                    <div>
                        <span>Correo verificado</span>
                        <strong>{{ $correoVerificado ? 'Verificado' : 'Pendiente' }}</strong>
                    </div>

                    <span class="seguridad-estado {{ $correoVerificado ? 'seguridad-ok' : 'seguridad-alerta' }}">
                        <i class="fas {{ $correoVerificado ? 'fa-check' : 'fa-exclamation' }}"></i>
                    </span>
                </div>

                <div class="seguridad-item">
                    <div>
                        <span>Autenticación 2FA</span>
                        <strong>{{ $twofaActivo ? 'Activada' : 'Pendiente' }}</strong>
                    </div>

                    <span class="seguridad-estado {{ $twofaActivo ? 'seguridad-ok' : 'seguridad-alerta' }}">
                        <i class="fas {{ $twofaActivo ? 'fa-check' : 'fa-exclamation' }}"></i>
                    </span>
                </div>

                <div class="seguridad-item">
                    <div>
                        <span>Última verificación 2FA</span>
                        <strong>{{ $ultimaVerificacion2FA }}</strong>
                    </div>

                    <span class="seguridad-estado seguridad-info">
                        <i class="fas fa-clock"></i>
                    </span>
                </div>

                <div class="seguridad-item">
                    <div>
                        <span>Último inicio de sesión</span>
                        <strong>{{ $ultimoLogin }}</strong>
                    </div>

                    <span class="seguridad-estado seguridad-info">
                        <i class="fas fa-sign-in-alt"></i>
                    </span>
                </div>
            </div>

            <a href="{{ url('/configuracion') }}" class="btn-configuracion-seguridad">
                <i class="fas fa-gear"></i>
                Gestionar seguridad en configuración
            </a>
        </section>

        <section class="perfil-card">
            <div class="perfil-card-header">
                <div>
                    <h2>Resumen de trámites</h2>
                    <p>Vista rápida del estado general de tus solicitudes.</p>
                </div>

                <i class="fas fa-chart-pie perfil-card-icon"></i>
            </div>

            <div class="tramites-resumen">
                <div class="tramite-box">
                    <span>{{ $tramitesEnviados }}</span>
                    <p>Enviados</p>
                </div>

                <div class="tramite-box">
                    <span>{{ $tramitesPendientes }}</span>
                    <p>Pendientes</p>
                </div>

                <div class="tramite-box">
                    <span>{{ $tramitesAprobados }}</span>
                    <p>Aprobados</p>
                </div>

                <div class="tramite-box">
                    <span>{{ $tramitesRechazados }}</span>
                    <p>Rechazados</p>
                </div>
            </div>

            <div class="perfil-alerta">
                <i class="fas fa-info-circle"></i>
                <p>
                    Este resumen solo muestra cantidades generales. Para ver el detalle completo,
                    ingresa a la sección de trámites.
                </p>
            </div>

            <div class="perfil-card-actions">
                <a href="{{ url('/mis-tramites') }}" class="btn-ver-tramites">
                    <i class="fas fa-folder-open"></i>
                    Ver mis trámites
                </a>
            </div>
        </section>

    </div>

</div>

@endsection