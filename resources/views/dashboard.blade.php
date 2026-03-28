@php
    $user = auth()->user();
    $displayName = 'Alumno';

    if ($user) {
        if (isset($user->persona) && $user->persona && $user->persona->nombre_persona) {
            $displayName = $user->persona->nombre_persona;
        } elseif (isset($user->id_persona) && $user->id_persona) {
            $persona = \DB::table('tbl_persona')
                ->where('id_persona', $user->id_persona)
                ->first();

            if ($persona && $persona->nombre_persona) {
                $displayName = $persona->nombre_persona;
            }
        } elseif (isset($user->name) && $user->name) {
            $displayName = $user->name;
        }
    }

    $parts = preg_split('/\s+/', trim($displayName));
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(mb_substr($part, 0, 1));
    }

    if ($initials === '') {
        $initials = 'A';
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PumaGestión – Panel del Alumno</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="hold-transition dashboard-body">
<div class="wrapper">

    {{-- NAVBAR MÓVIL --}}
    <nav class="main-header navbar navbar-expand navbar-dark d-lg-none">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link mobile-menu-btn" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <span class="mobile-title">PumaGestión</span>
    </nav>

    {{-- SIDEBAR --}}
    <aside class="main-sidebar elevation-4">
        <div class="sidebar-bg" style="background-image: url('{{ asset('images/Edificio2.jpeg') }}');"></div>
        <div class="sidebar-overlay"></div>

        <a href="javascript:void(0)" class="brand-link">
            <div class="brand-top-glow"></div>

            <div class="brand-logo-wrap">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo PumaGestión" class="brand-logo-img">
            </div>

            <div class="brand-name">
                <span class="brand-name-white">Puma</span><span class="brand-name-gold">Gestión</span>
            </div>
            <div class="brand-subtitle">FCEAC · UNAH</div>
        </a>

        <div class="sidebar">
            <div class="sidebar-user-card">
                <div class="sidebar-user-avatar">{{ $initials }}</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $displayName }}</div>
                    <div class="sidebar-user-role">Alumno</div>
                </div>
            </div>

            <div id="dashboardSidebarScroll" class="dashboard-sidebar-scroll">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column dashboard-menu" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ url()->current() }}" class="nav-link active">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('backup.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Respaldo</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="nav-icon fas fa-gear"></i>
                                <p>Configuración</p>
                            </a>
                        </li>

                        <li class="nav-item nav-item-logout">
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="nav-link logout-btn">
                                    <i class="nav-icon fas fa-right-from-bracket"></i>
                                    <p>Cerrar sesión</p>
                                </button>
                            </form>
                        </li>

                    </ul>
                </nav>
            </div>
        </div>
    </aside>

    {{-- BOTÓN TOGGLE SIDEBAR --}}
    <button id="sidebarToggleBtn" class="sidebar-float-toggle d-none d-lg-flex" type="button" title="Colapsar/Expandir menú">
        <i class="fas fa-chevron-left"></i>
    </button>

    {{-- CONTENIDO --}}
    <div class="content-wrapper">
        <section class="content dashboard-shell">

            {{-- BANNER --}}
            <div class="hero-banner">
                <div class="hero-banner-bg"></div>
                <div class="hero-wave wave-one"></div>
                <div class="hero-wave wave-two"></div>
                <div class="hero-gold-ribbon"></div>

                <div class="hero-photo" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>

                <div class="hero-content">
                    <div class="hero-top-title">Portal Estudiantil UNAH</div>

                    <div class="hero-breadcrumb">
                        <i class="fas fa-house"></i>
                        <span>Inicio</span>
                        <i class="fas fa-angle-right sep"></i>
                        <span>Dashboard del Alumno</span>
                    </div>

                    <div class="hero-faculty-title">
                        FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                        ADMINISTRATIVAS Y CONTABLES
                    </div>
                </div>
            </div>

            {{-- FRANJA SIMPLE --}}
            <div class="student-intro-strip">
                <div class="student-intro-text">
                    <h2>Panel estudiantil</h2>
                    <p>Selecciona el trámite académico que deseas gestionar dentro del sistema.</p>
                </div>

                <div class="student-user-chip">
                    <div class="student-user-chip-avatar">{{ $initials }}</div>
                    <div class="student-user-chip-name">{{ $displayName }}</div>
                </div>
            </div>

            {{-- INFORMACIÓN PRIMERO --}}
            <div class="student-info-grid">
                <div class="info-panel">
                    <div class="info-panel-header">
                        <i class="fas fa-circle-info"></i>
                        <h3>Recomendaciones</h3>
                    </div>

                    <div class="info-panel-body">
                        <ul class="student-tips">
                            <li>Verifica que tus datos personales estén correctos antes de enviar cualquier solicitud.</li>
                            <li>Ten listos tus documentos en formato PDF si el trámite los requiere.</li>
                            <li>Revisa frecuentemente el estado de tus trámites para no perder observaciones o respuestas.</li>
                            <li>Utiliza este panel únicamente para procesos académicos autorizados para estudiantes.</li>
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
                                <strong>Selecciona el trámite</strong>
                                <p>Elige entre Cambio de Carrera o Cancelación de Clases.</p>
                            </div>
                        </div>

                        <div class="info-step">
                            <span class="step-number">2</span>
                            <div>
                                <strong>Completa tu solicitud</strong>
                                <p>Llena los datos requeridos y adjunta los documentos necesarios.</p>
                            </div>
                        </div>

                        <div class="info-step">
                            <span class="step-number">3</span>
                            <div>
                                <strong>Da seguimiento</strong>
                                <p>Consulta el avance y la respuesta de tu trámite dentro del sistema.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACCESOS PRINCIPALES --}}
            <div class="student-section-title">
                <h3>Trámites disponibles</h3>
                <p>Accede directamente a los módulos académicos habilitados para el alumno.</p>
            </div>

            <div class="student-main-grid">
                <div class="student-module-card">
                    <div class="module-badge">Trámite Académico</div>
                    <div class="module-icon">
                        <i class="fas fa-right-left"></i>
                    </div>

                    <h2>Cambio de Carrera</h2>

                    <p>
                        Inicia tu solicitud de cambio de carrera, adjunta la documentación necesaria
                        y consulta el estado del proceso desde el módulo correspondiente.
                    </p>

                    <div class="module-actions">
                        <a href="{{ route('cambio-carrera.index') }}" class="module-btn primary">
                            <i class="fas fa-arrow-right"></i>
                            Ir al módulo
                        </a>
                    </div>
                </div>

                <div class="student-module-card">
                    <div class="module-badge">Trámite Académico</div>
                    <div class="module-icon gold">
                        <i class="fas fa-ban"></i>
                    </div>

                    <h2>Cancelación de Clases</h2>

                    <p>
                        Accede al proceso de cancelación de clases para registrar tu solicitud
                        y dar seguimiento a la resolución correspondiente.
                    </p>

                    <div class="module-actions">
                        <a href="{{ route('cancelacion.index') }}" class="module-btn secondary">
                            <i class="fas fa-arrow-right"></i>
                            Ir al módulo
                        </a>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2026 PumaGestión – UNAH</strong>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
