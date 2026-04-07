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
            </div>
        </div>
    </div>

    {{-- FRANJA SUPERIOR --}}
    <div class="sa-header-strip">
        <div class="sa-header-text">
            <h2>Secretaría Académica</h2>
            <p>
                Visualiza primero las gráficas globales y el comportamiento general de la facultad,
                incluyendo las clases con mayor cancelación.
            </p>
        </div>

        <div class="student-user-chip">
            <div class="student-user-chip-avatar">{{ $iniciales }}</div>
            <div class="student-user-chip-name">{{ $nombreUsuario }}</div>
        </div>
    </div>

    {{-- GRAFICAS PRIMERO --}}
    <div class="sa-section-heading">
        <h3>Gráficas globales de facultad</h3>
        <p>
            Vista inicial orientada a análisis general. Los datos actuales son temporales para que luego
            solo se conecte la lógica real.
        </p>
    </div>

    <div class="sa-grid-two">
        <div class="sa-card">
            <div class="sa-card-header">
                <i class="fas fa-chart-pie"></i>
                <h3>Distribución general por estado</h3>
            </div>
            <div class="sa-card-body">
                <div class="sa-chart-box">
                    <canvas id="graficaEstadosGlobales"></canvas>
                </div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-card-header">
                <i class="fas fa-chart-column"></i>
                <h3>Trámites por carrera</h3>
            </div>
            <div class="sa-card-body">
                <div class="sa-chart-box">
                    <canvas id="graficaCarrerasGlobales"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="sa-grid-one">
        <div class="sa-card">
            <div class="sa-card-header">
                <i class="fas fa-ban"></i>
                <h3>Top 3 clases más canceladas</h3>
            </div>
            <div class="sa-card-body">
                <div class="sa-chart-box sa-chart-box-wide">
                    <canvas id="graficaClasesCanceladas"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- RESUMEN DESPUÉS --}}
    <div class="sa-section-heading">
        <h3>Resumen institucional</h3>
        <p>Indicadores rápidos para lectura general del estado actual.</p>
    </div>

    <div class="sa-metrics-grid">
        <div class="sa-metric-card">
            <div class="sa-metric-title">
                <i class="fas fa-folder-open"></i>
                <span>Trámites globales</span>
            </div>
            <div class="sa-metric-number">{{ str_pad($tramitesGlobales, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="sa-metric-text">Total consolidado de trámites registrados.</div>
        </div>

        <div class="sa-metric-card">
            <div class="sa-metric-title">
                <i class="fas fa-circle-check"></i>
                <span>Aprobados</span>
            </div>
            <div class="sa-metric-number">{{ str_pad($aprobados, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="sa-metric-text">Trámites aprobados en el consolidado general.</div>
        </div>

        <div class="sa-metric-card">
            <div class="sa-metric-title">
                <i class="fas fa-hourglass-half"></i>
                <span>Pendientes</span>
            </div>
            <div class="sa-metric-number">{{ str_pad($pendientes, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="sa-metric-text">Solicitudes aún pendientes de resolución.</div>
        </div>

        <div class="sa-metric-card">
            <div class="sa-metric-title">
                <i class="fas fa-clipboard-check"></i>
                <span>En revisión</span>
            </div>
            <div class="sa-metric-number">{{ str_pad($revision, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="sa-metric-text">Trámites en proceso de revisión.</div>
        </div>

        <div class="sa-metric-card">
            <div class="sa-metric-title">
                <i class="fas fa-circle-xmark"></i>
                <span>Rechazados</span>
            </div>
            <div class="sa-metric-number">{{ str_pad($rechazados, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="sa-metric-text">Solicitudes rechazadas en el consolidado general.</div>
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
@endsection