@extends('layouts.app-coordinador')

@section('titulo', 'Panel de Coordinación')

@section('content')

    @vite([
        'resources/css/graficas_secretarias.css',
        'resources/js/graficas_secretarias.js'
    ])

    @php
        /*
        |--------------------------------------------------------------------------
        | Información del usuario
        |--------------------------------------------------------------------------
        */

        $authUser = auth()->user();

        $nombreUsuario =
            $userName
            ?? $authUser?->persona?->nombre_persona
            ?? $authUser?->nombre_persona
            ?? $authUser?->name
            ?? 'Coordinador';

        $partesNombre = preg_split(
            '/\s+/',
            trim((string) $nombreUsuario)
        );

        $iniciales = '';

        foreach (array_slice($partesNombre, 0, 2) as $parte) {
            if (!empty($parte)) {
                $iniciales .= strtoupper(
                    mb_substr($parte, 0, 1)
                );
            }
        }

        if ($iniciales === '') {
            $iniciales = 'C';
        }

        /*
        |--------------------------------------------------------------------------
        | Años disponibles
        |--------------------------------------------------------------------------
        */

        $aniosDisponibles = collect(
            $aniosDisponibles ?? [date('Y')]
        );

        $primerAnioDisponible = $aniosDisponibles
            ->map(function ($item) {
                return is_object($item)
                    ? ($item->anio ?? null)
                    : $item;
            })
            ->filter()
            ->first();

        $anioSeleccionado =
            $anio
            ?? request('anio')
            ?? $primerAnioDisponible
            ?? date('Y');

        /*
        |--------------------------------------------------------------------------
        | Carrera del coordinador
        |--------------------------------------------------------------------------
        */

        $carreras = collect($carreras ?? []);

        $idCarreraSeleccionada =
            $idCarreraSeleccionada
            ?? request('id_carrera')
            ?? null;

        $carreraActual = null;

        if ($idCarreraSeleccionada !== null) {
            $carreraActual = $carreras->firstWhere(
                'id_carrera',
                (int) $idCarreraSeleccionada
            );
        }

        if (!$carreraActual) {
            $carreraActual = $carreras->first();
        }

        $nombreCarrera =
            $nombreCarrera
            ?? $carreraNombre
            ?? $carreraSeleccionadaNombre
            ?? data_get($carreraActual, 'nombre_carrera')
            ?? data_get($carreraActual, 'nombre')
            ?? 'Carrera asignada';
    @endphp

    <style>
        /*
        |--------------------------------------------------------------------------
        | Ajuste general
        |--------------------------------------------------------------------------
        */

        .dashboard-shell-body {
            padding: 0 16px 20px 10px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Banner institucional
        |--------------------------------------------------------------------------
        */

        .hero-banner {
            position: relative !important;
            min-height: 225px !important;
            margin: -6px 6px 18px 0 !important;
            overflow: hidden !important;
            border: 8px solid #ffffff !important;
            border-radius: 18px !important;
            background: #174d91 !important;
            box-shadow: 0 8px 24px rgba(16, 55, 105, 0.16) !important;
        }

        .hero-banner-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background:
                linear-gradient(
                    110deg,
                    #12447f 0%,
                    #205da7 57%,
                    #174c91 100%
                );
        }

        .hero-wave {
            position: absolute;
            z-index: 1;
            width: 460px;
            height: 360px;
            border-radius: 80px;
            transform: rotate(42deg);
            background: rgba(255, 255, 255, 0.045);
        }

        .wave-one {
            top: -215px;
            right: 325px;
        }

        .wave-two {
            top: -175px;
            right: 100px;
            background: rgba(255, 255, 255, 0.03);
        }

        .hero-gold-ribbon {
            position: absolute;
            top: -12px;
            right: 245px;
            z-index: 4;
            width: 72px;
            height: 280px;
            transform: skewX(-13deg);
            background:
                linear-gradient(
                    180deg,
                    #ffd428 0%,
                    #f5b800 100%
                );
            box-shadow: 0 0 15px rgba(8, 39, 83, 0.2);
        }

        .hero-photo {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 3;
            width: 315px;
            height: 100%;
            overflow: hidden;
            clip-path: polygon(19% 0, 100% 0, 100% 100%, 0 100%);
        }

        .hero-photo::after {
            position: absolute;
            inset: 0;
            content: "";
            background:
                linear-gradient(
                    90deg,
                    rgba(23, 76, 145, 0.88) 0%,
                    rgba(23, 76, 145, 0.15) 58%,
                    rgba(23, 76, 145, 0) 100%
                );
        }

        .hero-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .hero-content {
            position: relative;
            z-index: 5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: calc(100% - 315px);
            min-height: 209px;
            padding: 34px 34px 30px;
        }

        .hero-faculty-title {
            max-width: 720px;
            margin-bottom: 26px;
            color: #ffd31c;
            font-size: clamp(24px, 2.25vw, 38px);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -0.3px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0, 34, 79, 0.2);
        }

        .hero-service-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hero-service-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 14px;
            border: 1px solid rgba(255, 255, 255, 0.34);
            border-radius: 7px;
            background: rgba(4, 40, 84, 0.48);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            backdrop-filter: blur(3px);
        }

        .hero-service-pill i {
            color: #ffd31c;
        }

        .hero-service-pill.main-pill {
            background: rgba(7, 48, 96, 0.72);
        }

        /*
        |--------------------------------------------------------------------------
        | Paneles de información
        |--------------------------------------------------------------------------
        */

        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin: 0 6px 16px 0;
        }

        .info-panel {
            overflow: hidden;
            border: 1px solid #d9e4f2;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(22, 63, 110, 0.08);
        }

        .info-panel-header {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 44px;
            padding: 11px 16px;
            border-bottom: 1px solid #e4ebf4;
            color: #173e70;
        }

        .info-panel-header i {
            color: #1e64ad;
            font-size: 14px;
        }

        .info-panel-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
        }

        .info-panel-body {
            padding: 15px 17px;
        }

        .student-tips {
            display: grid;
            gap: 9px;
            margin: 0;
            padding: 0;
            color: #617187;
            font-size: 12px;
            line-height: 1.45;
            list-style: none;
        }

        .student-tips li {
            position: relative;
            padding-left: 18px;
        }

        .student-tips li::before {
            position: absolute;
            top: 7px;
            left: 2px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #1e64ad;
            content: "";
        }

        .info-step {
            display: grid;
            grid-template-columns: 29px 1fr;
            gap: 10px;
            align-items: flex-start;
        }

        .info-step + .info-step {
            margin-top: 12px;
        }

        .step-number {
            display: grid;
            place-items: center;
            width: 27px;
            height: 27px;
            border-radius: 50%;
            background: #1761ad;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
        }

        .info-step strong {
            display: block;
            margin-bottom: 2px;
            color: #284461;
            font-size: 12px;
        }

        .info-step p {
            margin: 0;
            color: #718095;
            font-size: 11px;
            line-height: 1.4;
        }

        /*
        |--------------------------------------------------------------------------
        | Área de gráficas
        |--------------------------------------------------------------------------
        */

        #graficasDashboard {
            margin-right: 6px;
        }

        /*
        |--------------------------------------------------------------------------
        | Diseño adaptable
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1100px) {
            .hero-content {
                width: calc(100% - 250px);
            }

            .hero-photo {
                width: 260px;
            }

            .hero-gold-ribbon {
                right: 198px;
            }
        }

        @media (max-width: 900px) {
            .student-info-grid {
                grid-template-columns: 1fr;
            }

            .hero-banner {
                min-height: 255px !important;
                margin: 8px 6px 16px 0 !important;
                border-width: 6px !important;
            }

            .hero-photo {
                width: 100% !important;
                opacity: 0.2 !important;
                clip-path: none !important;
            }

            .hero-photo::after {
                background: rgba(12, 62, 121, 0.65);
            }

            .hero-gold-ribbon {
                right: 35px !important;
                width: 56px !important;
                opacity: 0.9 !important;
            }

            .hero-content {
                width: 100% !important;
                min-height: 243px;
                padding: 32px 26px !important;
            }

            .hero-service-strip {
                gap: 8px !important;
            }

            .hero-service-pill {
                font-size: 12px !important;
            }
        }

        @media (max-width: 576px) {
            .dashboard-shell-body {
                padding: 0 8px 16px !important;
            }

            .hero-banner {
                margin-right: 0 !important;
                border-radius: 12px !important;
            }

            .hero-content {
                padding: 24px 18px !important;
            }

            .hero-faculty-title {
                font-size: 23px;
            }

            .hero-service-strip {
                flex-direction: column;
                align-items: flex-start;
            }

            .student-info-grid,
            #graficasDashboard {
                margin-right: 0;
            }
        }
    </style>

    {{-- =====================================================
         BANNER INSTITUCIONAL
    ====================================================== --}}
    <div class="hero-banner">

        <div class="hero-banner-bg"></div>

        <div class="hero-wave wave-one"></div>

        <div class="hero-wave wave-two"></div>

        <div class="hero-gold-ribbon"></div>

        <div class="hero-photo">
            <img
                src="{{ asset('images/FCEAC.jpg') }}"
                alt="Edificio de la Facultad de Ciencias Económicas"
                class="hero-photo-img"
            >
        </div>

        <div class="hero-content">

            <div class="hero-faculty-title">
                Facultad de Ciencias Económicas,<br>
                Administrativas y Contables
            </div>

            <div class="hero-service-strip">

                <div class="hero-service-pill main-pill">
                    <i class="fas fa-user-tie"></i>
                    <span>Coordinación académica</span>
                </div>

                <div class="hero-service-pill">
                    <i class="fas fa-file-signature"></i>
                    <span>Dictámenes y revisiones</span>
                </div>

                <div class="hero-service-pill">
                    <i class="fas fa-chart-line"></i>
                    <span>Seguimiento por carrera</span>
                </div>

            </div>
        </div>
    </div>

    {{-- =====================================================
         INFORMACIÓN DEL PANEL
    ====================================================== --}}
    <div class="student-info-grid">

        <div class="info-panel">

            <div class="info-panel-header">
                <i class="fas fa-circle-info"></i>
                <h3>Recomendaciones</h3>
            </div>

            <div class="info-panel-body">

                <ul class="student-tips">
                    <li>
                        Revisa las gráficas para identificar el comportamiento
                        de los trámites de la carrera.
                    </li>

                    <li>
                        Da seguimiento a las solicitudes que requieren revisión,
                        validación o dictamen académico.
                    </li>

                    <li>
                        Verifica el año de gestión antes de interpretar los
                        resultados mostrados.
                    </li>

                    <li>
                        Utiliza esta vista como apoyo para la toma de decisiones
                        de coordinación.
                    </li>
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

                        <p>
                            Visualiza el comportamiento general de
                            cancelaciones y cambios de carrera.
                        </p>
                    </div>

                </div>

                <div class="info-step">

                    <span class="step-number">2</span>

                    <div>
                        <strong>Analizar los estados</strong>

                        <p>
                            Identifica trámites pendientes, en revisión o
                            finalizados dentro de la carrera asignada.
                        </p>
                    </div>

                </div>

                <div class="info-step">

                    <span class="step-number">3</span>

                    <div>
                        <strong>Apoyar decisiones</strong>

                        <p>
                            Usa la información gráfica como respaldo para la
                            gestión académica de coordinación.
                        </p>
                    </div>

                </div>

            </div>
        </div>

    </div>

    {{-- =====================================================
         GRÁFICAS DEL COORDINADOR
    ====================================================== --}}
    @include('graficas_dashboard', [
        'apiUrl' => route('api.graficas.secretaria_carrera'),

        'scopeLabel' => 'carrera',

        'scopeNote' =>
            'Mostrando estadísticas de la carrera asignada al coordinador.',

        'breakdownLabel' => 'carrera',

        'modoFiltro' => 'ninguno',

        'aniosDisponibles' => $aniosDisponibles,

        'carreras' => $carreras,

        'idCarreraSeleccionada' =>
            $idCarreraSeleccionada,

        'anio' => $anioSeleccionado,

        'rootId' => 'graficasDashboard',
    ])

@endsection