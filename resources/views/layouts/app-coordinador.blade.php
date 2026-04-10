@php
    use Illuminate\Support\Facades\DB;

    $user = auth()->user();

    $displayName = 'Coordinador';
    $displayRole = 'Coordinador';

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
        $initials = 'C';
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PumaGestión – @yield('titulo', 'Panel de Coordinación')</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
                </div>
            </div>

            <div id="dashboardSidebarScroll" class="dashboard-sidebar-scroll">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column dashboard-menu" data-widget="treeview" role="menu" data-accordion="false">

                        {{-- Dashboard --}}
                        <li class="nav-item">
                           <a href="{{ url('/empleado/dashboard') }}"
                               class="nav-link {{ request()->routeIs('dashboard') || request()->is('dashboard*') ? 'active' : '' }}">
                            <a href="{{ route('empleado.dashboard') }}"
                               class="nav-link {{ request()->routeIs('empleado.dashboard') || request()->is('empleado/dashboard*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        {{-- Trámites --}}
                        <li class="nav-item has-treeview {{ request()->routeIs('coordinador.cambio-carrera.*', 'coordinador.cancelacion.*') ? 'menu-open' : '' }}">
    <a href="javascript:void(0)"
       class="nav-link {{ request()->routeIs('coordinador.cambio-carrera.*', 'coordinador.cancelacion.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-folder-open"></i>
        <p>
            Trámites
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>

    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('coordinador.cambio-carrera.index') }}"
               class="nav-link {{ request()->routeIs('coordinador.cambio-carrera.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Cambio de carrera</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('coordinador.cancelacion.index') }}"
               class="nav-link {{ request()->routeIs('coordinador.cancelacion.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Cancelación</p>
            </a>
        </li>
    </ul>
</li>

                        {{-- Seguridad: SOLO BOTÓN DIRECTO --}}
                        <li class="nav-item">
                            <a href="{{ route('seguridad.index') }}"
                               class="nav-link {{ request()->is('seguridad*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-halved"></i>
                                <p>Seguridad</p>
                            </a>
                        </li>
{{-- Reportes --}}
<li class="nav-item">
    <a href="{{ route('reporte.tramites.vista') }}"
       class="nav-link {{ request()->routeIs('reporte.tramites.vista') || request()->is('reporte-tramites*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>Reportes</p>
    </a>
</li>

                        {{-- Auditoría --}}
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="nav-icon fas fa-magnifying-glass-chart"></i>
                                <p>Auditoría</p>
                            </a>
                        </li>

                        {{-- Bitácora --}}
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Bitácora</p>
                            </a>
                        </li>

                        {{-- Configuración --}}
                        <li class="nav-item">
                            <a href="{{ route('configuracion.index') }}"
                               class="nav-link {{ request()->routeIs('configuracion.index') || request()->is('configuracion') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gear"></i>
                                <p>Configuración</p>
                            </a>
                        </li>

                        {{-- Cerrar sesión --}}
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
