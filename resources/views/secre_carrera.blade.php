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
    @endphp

    <style>
        /* =========================================================
           BANNER SECRETARÍA DE CARRERA
           Igual al banner del estudiante, con textos del rol
        ========================================================= */

        .hero-banner {
            position: relative !important;
            min-height: 225px !important;
            margin: 8px 10px 18px !important;
            border: 8px solid #ffffff !important;
            border-radius: 18px !important;
            overflow: hidden !important;
            background: #163f86 !important;
            box-shadow: 0 10px 24px rgba(8, 35, 78, 0.18) !important;
        }

        .hero-banner-bg {
            position: absolute !important;
            inset: 0 !important;
            z-index: 1 !important;
            background:
                radial-gradient(circle at 48% 44%, rgba(255, 255, 255, 0.10), transparent 25%),
                linear-gradient(90deg, #123674 0%, #1c4f9d 48%, #174487 100%) !important;
        }

        .hero-banner-bg::before {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            background:
                linear-gradient(135deg, transparent 0 44%, rgba(9, 43, 105, 0.26) 44% 56%, transparent 56%),
                linear-gradient(135deg, transparent 0 52%, rgba(255,255,255,0.04) 52% 53%, transparent 53%) !important;
            pointer-events: none !important;
        }

        .hero-banner::before {
            content: "" !important;
            position: absolute !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 250px !important;
            height: 110px !important;
            z-index: 2 !important;
            opacity: 0.15 !important;
            background-image: radial-gradient(rgba(255,255,255,0.85) 1px, transparent 1px) !important;
            background-size: 10px 10px !important;
            pointer-events: none !important;
        }

        .hero-banner::after {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: 2 !important;
            background:
                radial-gradient(circle at 48% 42%, rgba(255,255,255,0.08), transparent 25%),
                linear-gradient(120deg, transparent 0%, transparent 42%, rgba(255,255,255,0.07) 52%, transparent 65%) !important;
            pointer-events: none !important;
        }

        .hero-wave {
            display: none !important;
        }

        .hero-gold-ribbon {
            position: absolute !important;
            top: -10% !important;
            right: 300px !important;
            width: 74px !important;
            height: 120% !important;
            z-index: 6 !important;
            background: linear-gradient(180deg, #ffd21f 0%, #f2bd12 55%, #dfa600 100%) !important;
            transform: skewX(-10deg) !important;
            box-shadow:
                -12px 0 0 rgba(9, 44, 105, 0.55),
                7px 0 0 rgba(255, 255, 255, 0.16) !important;
        }

        .hero-gold-ribbon::before {
            content: "" !important;
            position: absolute !important;
            left: -16px !important;
            top: 0 !important;
            width: 5px !important;
            height: 100% !important;
            background: rgba(7, 37, 92, 0.72) !important;
            border-radius: 999px !important;
        }

        .hero-photo {
            position: absolute !important;
            top: 0 !important;
            right: 0 !important;
            width: 370px !important;
            height: 100% !important;
            z-index: 4 !important;
            overflow: hidden !important;
            clip-path: polygon(10% 0, 100% 0, 100% 100%, 0% 100%) !important;
        }

        .hero-photo::after {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            z-index: 2 !important;
            background: linear-gradient(90deg, rgba(18, 54, 116, 0.18), transparent 34%) !important;
            pointer-events: none !important;
        }

        .hero-photo-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            display: block !important;
        }

        .hero-content {
            position: relative !important;
            z-index: 8 !important;
            width: calc(100% - 365px) !important;
            min-height: 225px !important;
            padding: 38px 46px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: flex-start !important;
        }

        .hero-faculty-title {
            margin: 0 0 20px 0 !important;
            color: #ffd21f !important;
            font-size: clamp(26px, 2.1vw, 36px) !important;
            line-height: 1.13 !important;
            font-weight: 900 !important;
            letter-spacing: 0.2px !important;
            text-transform: uppercase !important;
            text-shadow:
                0 3px 7px rgba(0, 0, 0, 0.24),
                0 0 8px rgba(255, 210, 31, 0.08) !important;
        }

        .hero-service-strip {
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            width: fit-content !important;
            max-width: 100% !important;
        }

        .hero-service-pill {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 8px 13px !important;
            border-radius: 12px !important;
            color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            white-space: nowrap !important;
            background: rgba(255, 255, 255, 0.11) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.12),
                0 5px 12px rgba(0, 0, 0, 0.10) !important;
        }

        .hero-service-pill i {
            color: #ffd21f !important;
            font-size: 13px !important;
        }

        .hero-service-pill.main-pill {
            background: rgba(255, 210, 31, 0.16) !important;
            border-color: rgba(255, 210, 31, 0.38) !important;
        }

        @media (max-width: 1200px) {
            .hero-photo {
                width: 330px !important;
            }

            .hero-gold-ribbon {
                right: 268px !important;
                width: 66px !important;
            }

            .hero-content {
                width: calc(100% - 320px) !important;
                padding: 34px 38px !important;
            }

            .hero-faculty-title {
                font-size: 28px !important;
            }

            .hero-service-pill {
                font-size: 12px !important;
                padding: 7px 11px !important;
            }
        }

        @media (max-width: 900px) {
            .hero-banner {
                min-height: 255px !important;
                border-width: 6px !important;
                margin: 8px 8px 16px !important;
            }

            .hero-photo {
                width: 100% !important;
                opacity: 0.20 !important;
                clip-path: none !important;
            }

            .hero-gold-ribbon {
                right: 35px !important;
                width: 56px !important;
                opacity: 0.90 !important;
            }

            .hero-content {
                width: 100% !important;
                padding: 32px 26px !important;
            }

            .hero-service-strip {
                gap: 8px !important;
            }

            .hero-service-pill {
                font-size: 12px !important;
            }
        }
    </style>

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

            <div class="hero-service-strip">
                <div class="hero-service-pill main-pill">
                    <i class="fas fa-briefcase"></i>
                    <span>Gestión por carrera</span>
                </div>

                <div class="hero-service-pill">
                    <i class="fas fa-file-circle-check"></i>
                    <span>Revisión documental</span>
                </div>

                <div class="hero-service-pill">
                    <i class="fas fa-headset"></i>
                    <span>Atención a estudiantes</span>
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
                        <p>Identifica cuáles solicitudes están activas, en revisión o pendientes de atención.</p>
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