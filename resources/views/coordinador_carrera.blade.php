@extends('layouts.app-coordinador')

@section('titulo', 'Panel de Coordinación')

@section('content')
    @vite(['resources/css/graficas_secretarias.css', 'resources/js/graficas_secretarias.js'])

    @php
        $nombreUsuario = $userName ?? auth()->user()->name ?? 'Coordinador';
        $partesNombre = preg_split('/\s+/', trim($nombreUsuario));
        $iniciales = '';

        foreach (array_slice($partesNombre, 0, 2) as $parte) {
            if (!empty($parte)) {
                $iniciales .= strtoupper(mb_substr($parte, 0, 1));
            }
        }

        if ($iniciales === '') {
            $iniciales = 'C';
        }

        $totalActivos = $totalActivos ?? 2;
        $totalRevision = $totalRevision ?? 1;
        $totalAprobados = $totalAprobados ?? 3;
    @endphp

    {{-- BANNER PRINCIPAL --}}
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

    {{-- FRANJA INFORMATIVA --}}
   <div class="student-intro-strip">
    <div class="student-intro-text">
        <h2>Resumen gráfico de trámites</h2>
        <p>
            Visualiza el comportamiento de cancelaciones excepcionales y cambios de carrera
            correspondientes a la carrera asignada al coordinador.
        </p>
    </div>

    <div class="student-user-chip">
        <div class="student-user-chip-avatar">{{ $iniciales }}</div>
        <div class="student-user-chip-name nombre-usuario-negro">{{ $nombreUsuario }}</div>
    </div>
</div>

    @include('graficas_dashboard', [
        'apiUrl' => route('api.graficas.secretaria_carrera'),
        'scopeLabel' => 'carrera',
        'scopeNote' => 'Mostrando estadísticas de la carrera asignada al coordinador.',
        'breakdownLabel' => 'carrera',
        'modoFiltro' => 'ninguno',
        'aniosDisponibles' => $aniosDisponibles ?? [],
        'carreras' => $carreras ?? collect(),
        'idCarreraSeleccionada' => $idCarreraSeleccionada ?? null,
        'anio' => $anio ?? null,
        'rootId' => 'graficasDashboard',
    ])
@endsection
