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
    @endphp

    <section class="content graf-wrap">
        <div id="graficasDashboard"
            data-api-url="{{ route('api.graficas.secretaria_academica') }}"
            data-scope-label="facultad"
            data-scope-note="Mostrando estadísticas de todos los departamentos."
            data-breakdown-label="departamento">

            {{-- BANNER NUEVO ESTILO ESTUDIANTE --}}
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

            <div class="top-search-row">
                <div class="tsr-input-wrap">
                    <input type="text" placeholder="Buscar expediente en toda la facultad..." id="top-search">
                </div>
                <div class="tsr-user">
                    <div class="tsr-avatar" style="background: var(--blue-unah); color: white;">
                        {{ strtoupper(substr($userName, 0, 1)) }}{{ strtoupper(substr(explode(' ', $userName)[1] ?? '', 0, 1)) }}
                    </div>
                    <span class="tsr-name">{{ $userName }}</span>
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

                    <select id="filtroDepartamento" class="graf-select">
                        <option value="">Todos los departamentos</option>
                        @foreach($departamentos as $departamento)
                            <option value="{{ $departamento->id_departamento }}"
                                {{ (string)$idDepartamentoSeleccionado === (string)$departamento->id_departamento ? 'selected' : '' }}>
                                {{ $departamento->nombre_departamento }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="graf-status" id="estadoCargaGraficas">
                    Listo para consultar estadísticas de facultad
                </div>
            </div>

            <div class="scope-note-box" id="scopeNoteGraficas">
                Mostrando estadísticas de todos los departamentos.
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
                        <h4>Distribución de Cancelaciones por Departamento</h4>
                        <p>Pasa el cursor sobre el gráfico para ver el total por departamento</p>
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
                        <h4>Distribución de Cambios de Carrera por Departamento</h4>
                        <p>Pasa el cursor sobre el gráfico para ver el total por departamento</p>
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
