@extends('layouts.app-secretaria')

@section('titulo', $titulo ?? 'Secretaria de Carrera')

@section('content')
    @vite(['resources/css/graficas_secretarias.css', 'resources/js/graficas_secretarias.js'])

    @php
        $authUser = auth()->user();

        $userName = $userName
            ?? optional($authUser->persona)->nombre_persona
            ?? $authUser->name
            ?? 'Secretaría';

        $correoInstitucional =
            $authUser->email
            ?? $authUser->correo_institucional
            ?? optional($authUser->persona)->correo_institucional
            ?? 'secretaria@unah.hn';

        $titulo = $titulo ?? 'Gestión de Carrera - FCEAC';
        $aniosDisponibles = $aniosDisponibles ?? [date('Y')];
        $anioSeleccionado = $anio ?? request('anio') ?? ($aniosDisponibles[0] ?? date('Y'));
        $idCarreraSeleccionada = $idCarreraSeleccionada ?? request('id_carrera') ?? '';

        $partesNombre = preg_split('/\s+/', trim($userName));
        $inicialesUsuario = '';

        foreach (array_slice($partesNombre, 0, 2) as $parte) {
            if (!empty($parte)) {
                $inicialesUsuario .= strtoupper(mb_substr($parte, 0, 1));
            }
        }

        if ($inicialesUsuario === '') {
            $inicialesUsuario = 'SC';
        }

        $nombreCarrera =
            $nombreCarrera
            ?? $carreraNombre
            ?? $carreraSeleccionadaNombre
            ?? 'Carrera asignada';

        $totalActivos = $totalActivos ?? 2;
        $totalRevision = $totalRevision ?? 1;
        $totalAprobados = $totalAprobados ?? 3;
    @endphp

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
            <div class="hero-faculty-title">
                FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                ADMINISTRATIVAS Y CONTABLES
            </div>

            <div class="hero-stats-strip">
                <div class="hero-stat">
                    <i class="fas fa-folder-open"></i>
                    <span>Trámites activos: <strong>{{ $totalActivos }}</strong></span>
                </div>

                <div class="hero-stat-divider"></div>

                <div class="hero-stat">
                    <i class="fas fa-clock"></i>
                    <span>En revisión: <strong>{{ $totalRevision }}</strong></span>
                </div>

                <div class="hero-stat-divider"></div>

                <div class="hero-stat">
                    <i class="fas fa-circle-check"></i>
                    <span>Aprobados: <strong>{{ $totalAprobados }}</strong></span>
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
                    <li>Revisa las gráficas para identificar tendencias y carga de trabajo en la carrera.</li>
                    <li>Utiliza el panel para dar seguimiento oportuno a trámites pendientes o en revisión.</li>
                    <li>Verifica el año de gestión antes de interpretar los resultados mostrados.</li>
                    <li>Usa esta vista como apoyo para la administración académica de la carrera asignada.</li>
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
                        <strong>Consultar el resumen</strong>
                        <p>Visualiza el comportamiento general de los trámites académicos de la carrera asignada.</p>
                    </div>
                </div>

                <div class="info-step">
                    <span class="step-number">2</span>
                    <div>
                        <strong>Monitorear estados</strong>
                        <p>Identifica cuáles solicitudes están activas, en revisión o ya fueron aprobadas.</p>
                    </div>
                </div>

                <div class="info-step">
                    <span class="step-number">3</span>
                    <div>
                        <strong>Apoyar la gestión</strong>
                        <p>Usa la información gráfica para fortalecer el seguimiento académico y administrativo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICAS --}}
    @include('graficas_dashboard', [
        'apiUrl' => route('api.graficas.secretaria_carrera'),
        'scopeLabel' => 'carrera',
        'scopeNote' => 'Mostrando estadísticas de la carrera asignada a secretaría.',
        'breakdownLabel' => 'carrera',
        'modoFiltro' => 'ninguno',
        'aniosDisponibles' => $aniosDisponibles ?? [],
        'carreras' => $carreras ?? collect(),
        'idCarreraSeleccionada' => $idCarreraSeleccionada ?? null,
        'anio' => $anioSeleccionado ?? null,
        'rootId' => 'graficasDashboard',
    ])
@endsection