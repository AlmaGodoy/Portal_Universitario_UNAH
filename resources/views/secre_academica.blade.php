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
=======
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

    {{-- TABLA --}}
    <div class="sa-section-heading">
        <h3>Consolidado por carrera</h3>
        <p>Comparativo general de las cinco carreras piloto.</p>
    </div>

    <div class="sa-table-card">
        <div class="sa-table-header">
            <i class="fas fa-table"></i>
            <h3>Resumen por carrera</h3>
        </div>

        <div class="sa-table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Total</th>
                        <th>Aprobados</th>
                        <th>Pendientes</th>
                        <th>Revisión</th>
                        <th>Rechazados</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumenCarreras as $fila)
                        <tr>
                            <td><strong>{{ $fila['carrera'] }}</strong></td>
                            <td>{{ $fila['total'] }}</td>
                            <td>{{ $fila['aprobados'] }}</td>
                            <td>{{ $fila['pendientes'] }}</td>
                            <td>{{ $fila['revision'] }}</td>
                            <td>{{ $fila['rechazados'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- NOTA --}}
    <div class="sa-note-card">
        <div class="sa-note-header">
            <i class="fas fa-circle-info"></i>
            <h3>Nota para integración</h3>
        </div>
        <div class="sa-note-body">
            Esta vista ya quedó enfocada en que la licenciada vea primero las gráficas.
            Los datos actuales son visuales y luego pueden ser reemplazados por datos reales
            sin modificar el diseño.
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/secre_academica.js') }}"></script>
    </section>
@endsection
=======
    </section>
@endsection
