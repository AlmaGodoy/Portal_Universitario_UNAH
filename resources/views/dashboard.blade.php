@extends('layouts.app-estudiantes')

@section('titulo', 'Panel del Alumno')

@section('content')

    {{-- BANNER --}}
    <div class="hero-banner">
        <div class="hero-banner-bg"></div>
        <div class="hero-wave wave-one"></div>
        <div class="hero-wave wave-two"></div>
        <div class="hero-gold-ribbon"></div>

        <div class="hero-photo" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>

        <div class="hero-content">
            <div class="hero-top-title">Portal Estudiantil UNAH</div>

            <div class="hero-breadcrumb">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
                <i class="fas fa-angle-right sep"></i>
                <span>Dashboard del Alumno</span>
            </div>

            <div class="hero-faculty-title">
                FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                ADMINISTRATIVAS Y CONTABLES
            </div>
        </div>
    </div>

    {{-- FRANJA SIMPLE --}}
    <div class="student-intro-strip">
        <div class="student-intro-text">
            <h2>Panel estudiantil</h2>
            <p>Selecciona el trámite académico o la opción que deseas gestionar dentro del sistema.</p>
        </div>

        <div class="student-user-chip">
            <div class="student-user-chip-avatar">{{ $initials ?? 'A' }}</div>
            <div class="student-user-chip-name">{{ $displayName ?? 'Alumno' }}</div>
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
                        <p>Elige entre Cambio de Carrera o Cancelación de Clases.</p>
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
                    <i class="fas fa-arrow-right"></i>
                    Ir al módulo
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
                    <i class="fas fa-arrow-right"></i>
                    Ir al módulo
                </a>
            </div>
        </div>
    </div>

@endsection