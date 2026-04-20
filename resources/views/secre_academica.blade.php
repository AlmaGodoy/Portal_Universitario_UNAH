@extends('layouts.app-secretaria-academica')

@section('titulo', 'Control Académico - FCEAC')

@section('content')

<link rel="stylesheet" href="{{ asset('css/secre_academica.css') }}">

@php
    $nombreUsuario = $userName ?? auth()->user()->name ?? 'Secretaría Académica';

    $partesNombre = preg_split('/\s+/', trim($nombreUsuario));
    $iniciales = '';

    foreach (array_slice($partesNombre, 0, 2) as $parte) {
        if (!empty($parte)) {
            $iniciales .= strtoupper(mb_substr($parte, 0, 1));
        }
    }
                <div class="hero-photo">
                    <img src="{{ asset('images/FCEAC.jpg') }}" alt="Edificio FCEAC" class="hero-photo-img">
                </div>

    if ($iniciales === '') {
        $iniciales = 'SA';
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS TEMPORALES VISUALES
    |--------------------------------------------------------------------------
    | Tu compañero luego los cambia por datos reales desde controller o API.
    */
    $datosEstados = [
        'Aprobados'  => 64,
        'Pendientes' => 38,
        'Revisión'   => 21,
        'Rechazados' => 14,
    ];

    $carreras = [
        'Economía',
        'Informática Administrativa',
        'Administración Aduanera',
        'Administración y Generación de Empresas',
        'Comercio Internacional',
    ];

    $datosCarreras = [42, 35, 28, 31, 26];

    $clasesMasCanceladas = [
        'Matemática Financiera' => 31,
        'Contabilidad II'       => 24,
        'Estadística I'         => 19,
    ];

    $tramitesGlobales = array_sum($datosCarreras);
    $aprobados = $datosEstados['Aprobados'];
    $pendientes = $datosEstados['Pendientes'];
    $revision = $datosEstados['Revisión'];
    $rechazados = $datosEstados['Rechazados'];

    $resumenCarreras = [
        [
            'carrera' => 'Economía',
            'total' => 42,
            'aprobados' => 18,
            'pendientes' => 12,
            'revision' => 7,
            'rechazados' => 5,
        ],
        [
            'carrera' => 'Informática Administrativa',
            'total' => 35,
            'aprobados' => 16,
            'pendientes' => 9,
            'revision' => 6,
            'rechazados' => 4,
        ],
        [
            'carrera' => 'Administración Aduanera',
            'total' => 28,
            'aprobados' => 11,
            'pendientes' => 8,
            'revision' => 5,
            'rechazados' => 4,
        ],
        [
            'carrera' => 'Administración y Generación de Empresas',
            'total' => 31,
            'aprobados' => 12,
            'pendientes' => 10,
            'revision' => 5,
            'rechazados' => 4,
        ],
        [
            'carrera' => 'Comercio Internacional',
            'total' => 26,
            'aprobados' => 7,
            'pendientes' => 9,
            'revision' => 4,
            'rechazados' => 6,
        ],
    ];
@endphp

<script>
    window.secretariaAcademicaCharts = {
        estadosLabels: @json(array_keys($datosEstados)),
        estadosValores: @json(array_values($datosEstados)),
        carrerasLabels: @json($carreras),
        carrerasValores: @json($datosCarreras),
        clasesLabels: @json(array_keys($clasesMasCanceladas)),
        clasesValores: @json(array_values($clasesMasCanceladas)),
    };
</script>

<div class="sa-view">

    {{-- BANNER PRINCIPAL --}}
    <div class="hero-banner">
        <div class="hero-banner-bg"></div>
        <div class="hero-wave wave-one"></div>
        <div class="hero-wave wave-two"></div>
        <div class="hero-gold-ribbon"></div>

        <div class="hero-photo" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>

        <div class="hero-content">
            <div class="hero-top-title">Portal de Secretaría Académica UNAH</div>

            <div class="hero-breadcrumb">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
                <i class="fas fa-angle-right sep"></i>
                <span>Gestión Académica Facultad</span>
            </div>

            <div class="hero-faculty-title">
                FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                ADMINISTRATIVAS Y CONTABLES
    @vite(['resources/css/graficas_secretarias.css', 'resources/js/graficas_secretarias.js'])

    @php
        $authUser = auth()->user();

        $userName = $userName ?? optional($authUser->persona)->nombre_persona ?? 'Secretaría Académica';

        $correoInstitucional =
            $authUser->email
            ?? $authUser->correo_institucional
            ?? optional($authUser->persona)->correo_institucional
            ?? 'secretaria.academica@unah.hn';

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

        $departamentoLabel = 'Todos los departamentos';

        if (!empty($idDepartamentoSeleccionado) && isset($departamentos)) {
            foreach ($departamentos as $dep) {
                $depId = is_array($dep)
                    ? ($dep['id_departamento'] ?? null)
                    : ($dep->id_departamento ?? null);

                $depNombre = is_array($dep)
                    ? ($dep['nombre_departamento'] ?? null)
                    : ($dep->nombre_departamento ?? null);

                if ((string) $depId === (string) $idDepartamentoSeleccionado) {
                    $departamentoLabel = $depNombre ?: 'Departamento seleccionado';
                    break;
                }
            }
        }
    @endphp

    {{-- ══ TOPBAR ══════════════════════════════════════════ --}}
    <div class="student-topbar">

        {{-- Izquierda --}}
        <div class="student-topbar-left">
            <div class="topbar-left-copy">
                <div class="topbar-breadcrumb">
                    <i class="fas fa-house"></i>
                    <span>Inicio</span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="topbar-breadcrumb-active">Secretaría Académica</span>
                </div>
                <h1 class="topbar-page-title">Panel de control académico</h1>
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
                            <div class="topbar-dropdown-icon blue">
                                <i class="fas fa-chart-column"></i>
                            </div>
                            <div class="topbar-dropdown-text">
                                <strong>Reporte actualizado</strong>
                                <span>Se regeneraron las métricas globales de la facultad.</span>
                                <small>Hace 10 min</small>
                            </div>
                        </li>

                        <li class="topbar-dropdown-item unread">
                            <div class="topbar-dropdown-icon gold">
                                <i class="fas fa-building-columns"></i>
                            </div>
                            <div class="topbar-dropdown-text">
                                <strong>Filtro aplicado</strong>
                                <span>Hay información disponible para el año {{ $anioSeleccionado }}.</span>
                                <small>Hace 1 hora</small>
                            </div>
                        </li>

                        <li class="topbar-dropdown-item">
                            <div class="topbar-dropdown-icon green">
                                <i class="fas fa-circle-check"></i>
                            </div>
                            <div class="topbar-dropdown-text">
                                <strong>Panel listo</strong>
                                <span>La vista de Secretaría Académica está disponible.</span>
                                <small>Hoy</small>
                            </div>
                        </li>
                    </ul>

                    <div class="topbar-dropdown-footer">
                        <a href="#">Ver todas las notificaciones</a>
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
            </div>

            {{-- FILA SUPERIOR --}}
            <div class="top-search-row">
                <div class="tsr-input-wrap">
                    <input type="text" placeholder="Buscar expediente en toda la facultad..." id="top-search">
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
                            <div class="topbar-dropdown-avatar">DG</div>
                            <div class="topbar-dropdown-text">
                                <strong>Dirección académica</strong>
                                <span>Revisa el comportamiento global de los trámites del período actual.</span>
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
                    <div class="student-user-chip-avatar">{{ $iniciales }}</div>
                    <div class="student-user-chip-info">
                        <span class="student-user-chip-name">{{ $userName }}</span>
                        <span class="student-user-chip-role">Secretaría Académica</span>
                    </div>
                    <i class="fas fa-chevron-down student-user-chip-arrow"></i>
                </button>

                <div class="topbar-dropdown align-right" id="dropUser">
                    <div class="topbar-user-header">
                        <div class="topbar-user-header-avatar">{{ $iniciales }}</div>
                        <div>
                            <strong>{{ $userName }}</strong>
                            <span>{{ $correoInstitucional }}</span>
                        </div>
                    </div>

                    <ul class="topbar-dropdown-list">
                        <li class="topbar-dropdown-item sm">
                            <div class="topbar-dropdown-icon blue sm">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="topbar-dropdown-text">
                                <span>Mi perfil</span>
                            </div>
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

    {{-- ══ BANNER ESTILO ESTUDIANTE ═══════════════════════ --}}
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
                    <span>Vista: <strong>Facultad completa</strong></span>
                </div>

                <div class="hero-stat-divider"></div>

                <div class="hero-stat">
                    <i class="fas fa-calendar-days"></i>
                    <span>Año: <strong>{{ $anioSeleccionado }}</strong></span>
                </div>

                <div class="hero-stat-divider"></div>

                <div class="hero-stat">
                    <i class="fas fa-sitemap"></i>
                    <span>Departamento: <strong>{{ $departamentoLabel }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- TÍTULO DE SECCIÓN --}}
    <div class="student-intro-strip" style="margin-top: 26px;">
    <div class="student-intro-text">
        <h2>Resumen gráfico de trámites</h2>
        <p>
            Visualiza el comportamiento global de cancelaciones excepcionales y cambios de carrera
            de toda la facultad.
        </p>
    </div>

    <div class="student-user-chip intro-user-chip">
        <div class="student-user-chip-avatar">{{ $iniciales }}</div>
        <div class="student-user-chip-name intro-user-name">{{ $userName }}</div>
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
