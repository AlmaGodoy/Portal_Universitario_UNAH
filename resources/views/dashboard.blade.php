@extends('layouts.app-estudiantes')

@php
    $user = auth()->user();

    $correoInstitucional =
        $user->email
        ?? $user->correo_institucional
        ?? optional($user->persona)->correo_institucional
        ?? 'estudiante@unah.hn';
@endphp

@section('titulo', 'Panel del Alumno')

@section('content')

    {{-- ══ TOPBAR ══════════════════════════════════════════ --}}
    <div class="student-topbar">

        {{-- Izquierda --}}
        <div class="student-topbar-left">
            <div class="topbar-left-copy">
                <div class="topbar-breadcrumb">
                    <i class="fas fa-house"></i>
                    <span>Inicio</span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="topbar-breadcrumb-active">Panel del Alumno</span>
                </div>
            </div>
        </div>

        {{-- Derecha --}}
        <div class="student-topbar-right">

            {{-- Notificaciones --}}
            <div class="topbar-action-group">
                <button class="topbar-icon-btn" id="btnNotif" title="Notificaciones">
                    <i class="fas fa-bell"></i>
                    <span class="topbar-badge">3</span>
                </button>

                <div class="topbar-dropdown" id="dropNotif">
                    <div class="topbar-dropdown-header">
                        <span>Notificaciones</span>
                        <a href="#" class="topbar-dropdown-mark">Marcar todas</a>
                    </div>
                    <ul class="topbar-dropdown-list">
                        <li class="topbar-dropdown-item unread">
                            <div class="topbar-dropdown-icon blue"><i class="fas fa-file-alt"></i></div>
                            <div class="topbar-dropdown-text">
                                <strong>Trámite actualizado</strong>
                                <span>Tu solicitud de cambio fue revisada.</span>
                                <small>Hace 5 min</small>
                            </div>
                        </li>
                        <li class="topbar-dropdown-item unread">
                            <div class="topbar-dropdown-icon gold"><i class="fas fa-triangle-exclamation"></i></div>
                            <div class="topbar-dropdown-text">
                                <strong>Observación emitida</strong>
                                <span>Revisa tu cancelación de clases.</span>
                                <small>Hace 1 hora</small>
                            </div>
                        </li>
                        <li class="topbar-dropdown-item">
                            <div class="topbar-dropdown-icon green"><i class="fas fa-circle-check"></i></div>
                            <div class="topbar-dropdown-text">
                                <strong>Trámite aprobado</strong>
                                <span>Tu solicitud fue aprobada.</span>
                                <small>Ayer</small>
                            </div>
                        </li>
                    </ul>
                    <div class="topbar-dropdown-footer">
                        <a href="#">Ver todas las notificaciones</a>
                    </div>
                </div>
            </div>

            {{-- Mensajes --}}
            <div class="topbar-action-group">
                <button class="topbar-icon-btn" id="btnMsg" title="Mensajes">
                    <i class="fas fa-envelope"></i>
                    <span class="topbar-badge gold">1</span>
                </button>

                <div class="topbar-dropdown" id="dropMsg">
                    <div class="topbar-dropdown-header">
                        <span>Mensajes</span>
                        <a href="#" class="topbar-dropdown-mark">Ver todos</a>
                    </div>
                    <ul class="topbar-dropdown-list">
                        <li class="topbar-dropdown-item unread">
                            <div class="topbar-dropdown-avatar">SC</div>
                            <div class="topbar-dropdown-text">
                                <strong>Secretaría FCEAC</strong>
                                <span>Tu expediente fue recibido correctamente.</span>
                                <small>Hace 30 min</small>
                            </div>
                        </li>
                    </ul>
                    <div class="topbar-dropdown-footer">
                        <a href="#">Ir a mensajes</a>
                    </div>
                </div>
            </div>

            <div class="topbar-divider"></div>

            {{-- Usuario --}}
            <div class="topbar-action-group">
                <button class="student-user-chip" id="btnUser" title="Mi perfil">
                    <div class="student-user-chip-avatar">{{ $initials ?? 'A' }}</div>
                    <div class="student-user-chip-info">
                        <span class="student-user-chip-name">{{ $displayName ?? 'Alumno' }}</span>
                        <span class="student-user-chip-role">Estudiante</span>
                    </div>
                    <i class="fas fa-chevron-down student-user-chip-arrow"></i>
                </button>

                <div class="topbar-dropdown align-right" id="dropUser">
                    <div class="topbar-user-header">
                        <div class="topbar-user-header-avatar">{{ $initials ?? 'A' }}</div>
                        <div>
                            <strong>{{ $displayName ?? 'Alumno' }}</strong>
                            <span>{{ $correoInstitucional }}</span>
                        </div>
                    </div>
                    <ul class="topbar-dropdown-list">
                        <li class="topbar-dropdown-item sm">
                            <div class="topbar-dropdown-icon blue sm"><i class="fas fa-user"></i></div>
                            <div class="topbar-dropdown-text"><span>Mi perfil</span></div>
                        </li>
                    </ul>
                    <div class="topbar-dropdown-footer danger">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                <i class="fas fa-right-from-bracket"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ BANNER ══════════════════════════════════════════ --}}
    <div class="hero-banner">
        <div class="hero-banner-bg"></div>
        <div class="hero-wave wave-one"></div>
        <div class="hero-wave wave-two"></div>
        <div class="hero-gold-ribbon"></div>

        <div class="hero-photo">
            <img src="{{ asset('images/FCEAC.jpg') }}" alt="Edificio FCEAC" class="hero-photo-img">
        </div>

        <div class="hero-content">

            {{-- Título principal --}}
            <div class="hero-faculty-title">
                FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                ADMINISTRATIVAS Y CONTABLES
            </div>

            {{-- Franja de datos --}}
            <div class="hero-stats-strip">
                <div class="hero-stat">
                    <i class="fas fa-folder-open"></i>
                    <span>Trámites activos: <strong>2</strong></span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <i class="fas fa-clock"></i>
                    <span>En revisión: <strong>1</strong></span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <i class="fas fa-circle-check"></i>
                    <span>Aprobados: <strong>3</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- INFORMACIÓN --}}
    <div class="student-info-grid">
        <div class="info-panel">
            <div class="info-panel-header">
                <i class="fas fa-circle-info"></i>
                <h3>Recomendaciones</h3>
            </div>
            <div class="info-panel-body">
                <ul class="student-tips">
                    <li>Verifica que tus datos personales estén correctos antes de enviar cualquier solicitud.</li>
                    <li>Ten listos tus documentos en formato PDF si el trámite los requiere.</li>
                    <li>Revisa frecuentemente el estado de tus trámites para no perder observaciones o respuestas.</li>
                    <li>Utiliza este panel únicamente para procesos académicos autorizados para estudiantes.</li>
                </ul>
            </div>
        </div>

        <div class="info-panel">
            <div class="info-panel-header">
                <i class="fas fa-list-check"></i>
                <h3>¿Qué puedes hacer aquí?</h3>
            </div>
            <div class="info-panel-body">
                <div class="info-step">
                    <span class="step-number">1</span>
                    <div>
                        <strong>Selecciona el módulo</strong>
                        <p>Elige entre Cambio de Carrera, Cancelación de Clases o consulta de trámites.</p>
                    </div>
                </div>
                <div class="info-step">
                    <span class="step-number">2</span>
                    <div>
                        <strong>Completa tu gestión</strong>
                        <p>Llena los datos requeridos y adjunta los documentos solicitados según el trámite.</p>
                    </div>
                </div>
                <div class="info-step">
                    <span class="step-number">3</span>
                    <div>
                        <strong>Da seguimiento</strong>
                        <p>Consulta el avance de tus trámites y revisa cualquier observación o respuesta emitida.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACCESOS PRINCIPALES --}}
    <div class="student-section-title">
        <h3>Opciones disponibles</h3>
        <p>Accede directamente a los módulos académicos disponibles para el estudiante.</p>
    </div>

    <div class="student-main-grid">
        <div class="student-module-card">
            <div class="module-badge">Trámite Académico</div>
            <div class="module-icon">
                <i class="fas fa-right-left"></i>
            </div>
            <h2>Cambio de Carrera</h2>
            <p>
                Inicia tu solicitud de cambio de carrera, adjunta la documentación necesaria
                y consulta el estado del proceso desde el módulo correspondiente.
            </p>
            <div class="module-actions">
                <a href="{{ route('cambio-carrera.index') }}" class="module-btn primary">
                    <i class="fas fa-arrow-right"></i> Ir al módulo
                </a>
            </div>
        </div>

        <div class="student-module-card">
            <div class="module-badge">Trámite Académico</div>
            <div class="module-icon gold">
                <i class="fas fa-ban"></i>
            </div>
            <h2>Cancelación de Clases</h2>
            <p>
                Accede al proceso de cancelación de clases para registrar tu solicitud
                y dar seguimiento a la resolución correspondiente.
            </p>
            <div class="module-actions">
                <a href="{{ route('cancelacion.index') }}" class="module-btn secondary">
                    <i class="fas fa-arrow-right"></i> Ir al módulo
                </a>
            </div>
        </div>
    </div>

@endsection