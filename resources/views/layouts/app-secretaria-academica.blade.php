@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();

    $displayName = 'Secretaría Académica';
    $displayRole = 'Secretaría Académica';

    if ($user) {
        if (isset($user->persona) && $user->persona && !empty($user->persona->nombre_persona)) {
            $displayName = trim($user->persona->nombre_persona);
        } elseif (!empty($user->nombre_persona)) {
            $displayName = trim($user->nombre_persona);
        } elseif (!empty($user->name)) {
            $displayName = trim($user->name);
        } elseif (!empty($user->id_persona)) {
            $persona = DB::table('tbl_persona')
                ->where('id_persona', $user->id_persona)
                ->first();

            if ($persona && !empty($persona->nombre_persona)) {
                $displayName = trim($persona->nombre_persona);
            }
        } elseif (!empty($user->email)) {
            $displayName = trim($user->email);
        }
    }

    $parts = preg_split('/\s+/', trim($displayName));
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }
    }

    if ($initials === '') {
        $initials = 'SA';
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PumaGestión – @yield('titulo', 'Secretaría Académica')</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        .sidebar-user-role {
            color: #d4af37 !important;
        }

        .nav-header-custom {
            color: rgba(255,255,255,.55);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 12px 18px 8px;
        }

        .academic-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(212, 175, 55, 0.16);
            color: #f3d46b;
            font-size: .78rem;
            font-weight: 700;
            border: 1px solid rgba(212, 175, 55, 0.28);
        }

        .content-wrapper {
            min-height: 100vh;
        }
    </style>
</head>
<body class="hold-transition dashboard-body">
<div class="wrapper">

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
                    <div class="sidebar-user-role">{{ $displayRole }}</div>
                    <div class="academic-badge">
                        <i class="fas fa-building-columns"></i>
                        Supervisión Global
                    </div>
                </div>
            </div>

            <div id="dashboardSidebarScroll" class="dashboard-sidebar-scroll">
                <nav class="mt-2">
                    <div class="nav-header-custom">Panel académico global</div>

                    <ul class="nav nav-pills nav-sidebar flex-column dashboard-menu" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ route('empleado.dashboard') }}"
                               class="nav-link {{ request()->routeIs('empleado.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="{{ route('reporte.tramites.secretaria_general.vista') }}"
                               class="nav-link {{ request()->routeIs('reporte.tramites.secretaria_general.vista') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-column"></i>
                                <p>Reportes Generales</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Gráficas Globales</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="nav-icon fas fa-building-columns"></i>
                                <p>Resumen por Carreras</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('configuracion.index') }}"
                               class="nav-link {{ request()->routeIs('configuracion.index') || request()->is('configuracion') ? 'active' : '' }}">
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

    <button id="sidebarToggleBtn" class="sidebar-float-toggle d-none d-lg-flex" type="button" title="Colapsar/Expandir menú">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="content-wrapper">
        <section class="content dashboard-shell">
            @yield('content')
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
