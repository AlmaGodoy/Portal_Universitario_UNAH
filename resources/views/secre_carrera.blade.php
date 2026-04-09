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
    @endphp

    <section class="content graf-wrap">
        <div id="graficasDashboard"
            data-api-url="{{ route('api.graficas.secretaria_carrera') }}"
            data-scope-label="carrera"
            data-scope-note="Mostrando estadísticas de todas las carreras."
            data-breakdown-label="carrera">

            {{-- BANNER NUEVO ESTILO ESTUDIANTE --}}
            <div class="hero-banner">
                <div class="hero-banner-bg"></div>
                <div class="hero-wave wave-one"></div>
                <div class="hero-wave wave-two"></div>
                <div class="hero-gold-ribbon"></div>

                <div class="hero-photo">
    <img src="{{ asset('images/FCEAC.jpg') }}" alt="Edificio FCEAC" class="hero-photo-img">
</div>

                <div class="hero-content">
                    <div class="hero-top-title">Secretaría de Carrera UNAH</div>

                    <div class="hero-breadcrumb">
                        <i class="fas fa-house"></i>
                        <span>Inicio</span>
                        <i class="fas fa-angle-right sep"></i>
                        <span>Gestión de Carrera</span>
                    </div>

                    <div class="hero-faculty-title">
                        FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                        ADMINISTRATIVAS Y CONTABLES
                    </div>
                </div>
            </div>

            <div class="top-search-row">
                <div class="tsr-input-wrap">
                    <input type="text" placeholder="Buscar trámite o estudiante..." id="top-search">
                </div>
                <div class="tsr-user">
                    <div class="tsr-avatar" id="top-initials">
                        {{ strtoupper(substr($userName, 0, 1)) }}{{ strtoupper(substr(explode(' ', $userName)[1] ?? '', 0, 1)) }}
                    </div>
                    <span class="tsr-name" id="top-username">{{ $userName }}</span>
                </div>
            </div>

            <div class="graf-toolbar">
                <div class="graf-toolbar-left">
                    <p class="graf-label mb-0">
                        <i class="fas fa-filter"></i> Filtrar por año
                    </p>

                    <select id="anioSelectGraficas" class="graf-select">
                        @forelse($aniosDisponibles as $anioItem)
                            <option value="{{ $anioItem }}" {{ (string)$anioSeleccionado === (string)$anioItem ? 'selected' : '' }}>
                                {{ $anioItem }}
                            </option>
                        @empty
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @endforelse
                    </select>

                    <select id="filtroCarrera" class="graf-select">
                        <option value="">Todas las carreras</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id_carrera }}"
                                {{ (string)$idCarreraSeleccionada === (string)$carrera->id_carrera ? 'selected' : '' }}>
                                {{ $carrera->nombre_carrera }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="graf-status" id="estadoCargaGraficas">
                    Listo para consultar estadísticas de carrera
                </div>
            </div>

            <div class="scope-note-box" id="scopeNoteGraficas">
                Mostrando estadísticas de todas las carreras.
            </div>

            <div class="stats-grid">
                <div class="stat-card bg-a">
                    <i class="fas fa-ban bg"></i>
                    <div class="title">Cancelaciones Excepcionales</div>
                    <div class="value" id="totalCancelaciones">0</div>
                    <div class="foot">Total anual registrado</div>
                </div>

                <div class="stat-card bg-b">
                    <i class="fas fa-right-left bg"></i>
                    <div class="title">Cambios de Carrera</div>
                    <div class="value" id="totalCambios">0</div>
                    <div class="foot">Total anual registrado</div>
                </div>
            </div>

            <div class="donut-grid">
                <div class="chart-card donut-card">
                    <div class="chart-head">
                        <h4>Distribución de Cancelaciones por Carrera</h4>
                        <p>Pasa el cursor sobre el gráfico para ver el total por carrera</p>
                    </div>
                    <div class="chart-body donut-body">
                        <div class="donut-canvas-wrap">
                            <canvas id="donutCancelaciones"></canvas>
                        </div>
                        <div class="donut-empty" id="donutEmptyCancelaciones">Esperando datos...</div>
                    </div>
                </div>

                <div class="chart-card donut-card">
                    <div class="chart-head">
                        <h4>Distribución de Cambios de Carrera por Carrera</h4>
                        <p>Pasa el cursor sobre el gráfico para ver el total por carrera</p>
                    </div>
                    <div class="chart-body donut-body">
                        <div class="donut-canvas-wrap">
                            <canvas id="donutCambios"></canvas>
                        </div>
                        <div class="donut-empty" id="donutEmptyCambios">Esperando datos...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
