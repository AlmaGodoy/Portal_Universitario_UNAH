@extends('layouts.app-secretaria-academica')

@section('titulo', 'Control Académico - FCEAC')

@section('content')
    @vite(['resources/css/graficas_secretarias.css', 'resources/js/graficas_secretarias.js'])

    @php
        $authUser = auth()->user();
        $userName = $userName ?? optional($authUser->persona)->nombre_persona ?? 'Usuario';
        $aniosDisponibles = $aniosDisponibles ?? [date('Y')];
        $anioSeleccionado = $anio ?? request('anio') ?? ($aniosDisponibles[0] ?? date('Y'));
        $idDepartamentoSeleccionado = $idDepartamentoSeleccionado ?? request('id_departamento') ?? '';

        $partesNombre = preg_split('/\s+/', trim($userName));
        $iniciales = '';

        foreach (array_slice($partesNombre, 0, 2) as $parte) {
            if (!empty($parte)) {
                $iniciales .= strtoupper(mb_substr($parte, 0, 1));
            }
        }

        if ($iniciales === '') {
            $iniciales = 'SA';
        }
    @endphp

    {{-- BANNER --}}
    <div class="hero-banner">
        <div class="hero-banner-bg"></div>
        <div class="hero-wave wave-one"></div>
        <div class="hero-wave wave-two"></div>
        <div class="hero-gold-ribbon"></div>

        <div class="hero-photo" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>

        <div class="hero-content">
            <div class="hero-top-title">Secretaría Académica UNAH</div>

            <div class="hero-breadcrumb">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
                <i class="fas fa-angle-right sep"></i>
                <span>Control Facultad</span>
            </div>

            <div class="hero-faculty-title">
                FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                ADMINISTRATIVAS Y CONTABLES
            </div>
        </div>
    </div>

    <div class="student-intro-strip">
        <div class="student-intro-text">
            <h2>Resumen gráfico de trámites</h2>
            <p>
                Visualiza el comportamiento global de cancelaciones excepcionales y cambios de carrera
                de toda la facultad.
            </p>
        </div>

        <div class="student-user-chip">
            <div class="student-user-chip-avatar">{{ $iniciales }}</div>
            <div class="student-user-chip-name">{{ $userName }}</div>
        </div>
    </div>

    @include('graficas_dashboard', [
        'apiUrl' => route('api.graficas.secretaria_academica'),
        'scopeLabel' => 'facultad',
        'scopeNote' => 'Mostrando estadísticas de todos los trámites de la facultad.',
        'breakdownLabel' => 'departamento',
        'modoFiltro' => 'departamento',
        'aniosDisponibles' => $aniosDisponibles ?? [],
        'departamentos' => $departamentos ?? collect(),
        'idDepartamentoSeleccionado' => $idDepartamentoSeleccionado ?? null,
        'anio' => $anioSeleccionado ?? null,
        'rootId' => 'graficasDashboard',
    ])
@endsection
