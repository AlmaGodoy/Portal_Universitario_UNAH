@extends('layouts.app-secretaria')

@section('titulo', $titulo ?? 'Secretaria de Carrera')

@section('content')
    @vite(['resources/css/graficas_secretarias.css', 'resources/js/graficas_secretarias.js'])

    @php
        $authUser = auth()->user();
        $userName = $userName ?? optional($authUser->persona)->nombre_persona ?? 'Usuario';
        $titulo = $titulo ?? 'Gestión de Carrera';
        $aniosDisponibles = $aniosDisponibles ?? [date('Y')];
        $anioSeleccionado = $anio ?? request('anio') ?? ($aniosDisponibles[0] ?? date('Y'));
        $idCarreraSeleccionada = $idCarreraSeleccionada ?? request('id_carrera') ?? '';
        $inicialesUsuario = '';

        $partesNombre = preg_split('/\s+/', trim($userName));
        foreach (array_slice($partesNombre, 0, 2) as $parte) {
            if (!empty($parte)) {
                $inicialesUsuario .= strtoupper(mb_substr($parte, 0, 1));
            }
        }

        if ($inicialesUsuario === '') {
            $inicialesUsuario = 'S';
        }
    @endphp

    {{-- BANNER --}}
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
                    <i class="fas fa-building-columns"></i>
                    <span>Carrera asignada: <strong>Activa</strong></span>
                </div>

                <div class="hero-stat-divider"></div>

                <div class="hero-stat">
                    <i class="fas fa-calendar-days"></i>
                    <span>Año de gestión: <strong>{{ $anioSeleccionado }}</strong></span>
                </div>

                <div class="hero-stat-divider"></div>

                <div class="hero-stat">
                    <i class="fas fa-chart-column"></i>
                    <span>Seguimiento: <strong>Gráfico</strong></span>
                </div>
            </div>
        </div>
    </div>

    <div class="student-intro-strip">
        <div class="student-intro-text">
            <h2>Resumen gráfico de trámites</h2>
            <p>
                Visualiza el comportamiento de cancelaciones excepcionales y cambios de carrera
                correspondientes a la carrera asignada a secretaría.
            </p>
        </div>

        <div class="student-user-chip">
            <div class="student-user-chip-avatar">{{ $inicialesUsuario }}</div>
            <div class="student-user-chip-name">{{ $userName }}</div>
        </div>
    </div>

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
