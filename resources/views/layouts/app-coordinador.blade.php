@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();

    $displayName = 'Coordinadora';
    $displayRole = 'Coordinadora';

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

    $correoInstitucional =
        $user->email
        ?? $user->correo_institucional
        ?? optional($user->persona)->correo_institucional
        ?? 'coordinacion@unah.hn';

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

    $cancelacionRouteName = null;

    if (Route::has('cancelacion.coordinadora.index')) {
        $cancelacionRouteName = 'cancelacion.coordinadora.index';
    } elseif (Route::has('resolucion.cancelacion.vista')) {
        $cancelacionRouteName = 'resolucion.cancelacion.vista';
    }

    $cancelacionUrl = $cancelacionRouteName
        ? route($cancelacionRouteName)
        : 'javascript:void(0)';

    $backupRouteName = Route::has('backup.index') ? 'backup.index' : null;

    $backupUrl = $backupRouteName
        ? route($backupRouteName)
        : 'javascript:void(0)';

    $dashboardActive     = request()->routeIs('empleado.dashboard') || request()->is('empleado/dashboard*');
    $cambioCarreraActive = request()->routeIs('coordinador.cambio-carrera.*');
    $cancelacionActive   = request()->routeIs('cancelacion.coordinadora.*') || request()->routeIs('resolucion.cancelacion.*');
    $backupActive        = request()->routeIs('backup.*') || request()->is('respaldos*');
    $tramitesMenuOpen    = $cambioCarreraActive || $cancelacionActive;

    $pageTitle = trim($__env->yieldContent('title', 'Panel de Coordinación'));
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PumaGestión – @yield('title', 'Panel de Coordinación')</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        .coordinator-topbar {
            position: sticky;
            top: 0;
            z-index: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 64px;
            padding: 0 20px;
            margin: 0 18px 0;
            background: linear-gradient(90deg, #102b67 0%, #163880 18%, #1d4f9f 42%, #1a4899 100%);
            border-bottom: 4px solid #f1be1a;
            box-shadow: 0 6px 24px rgba(10,28,75,.30), 0 2px 6px rgba(10,28,75,.18);
            border-radius: 0;
        }

        .coordinator-topbar::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                115deg,
                rgba(255,255,255,.07) 0%,
                rgba(255,255,255,.02) 40%,
                rgba(255,255,255,0) 60%
            );
            pointer-events: none;
        }

        .coordinator-topbar::after {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 4px;
            width: 4px;
            background: linear-gradient(180deg, #ffe566 0%, #f1be1a 50%, #e0aa00 100%);
        }

        .coordinator-topbar-left,
        .coordinator-topbar-right {
            display: flex;
            align-items: center;
            min-width: 0;
            position: relative;
            z-index: 2;
        }

        .coordinator-topbar-right {
            gap: 6px;
            margin-left: auto;
        }

        .coord-breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,.86);
            font-size: .98rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-left: 8px;
        }

        .coord-breadcrumb i {
            font-size: .86rem;
        }

        .coord-breadcrumb .active {
            color: #ffd34d;
            font-weight: 800;
        }

        .coord-action-group {
            position: relative;
        }

        .coord-icon-btn {
            position: relative;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.10);
            color: rgba(255,255,255,.88);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .22s ease;
            font-size: .9rem;
        }

        .coord-icon-btn:hover {
            background: rgba(255,255,255,.20);
            border-color: rgba(255,255,255,.30);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,.18);
        }

        .coord-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            color: #fff;
            font-size: .6rem;
            font-weight: 800;
            border: 2px solid #1a4899;
            box-shadow: 0 4px 10px rgba(229,57,53,.30);
        }

        .coord-badge.gold {
            background: linear-gradient(135deg, #efbe1a 0%, #dca600 100%);
            color: #16315d;
            border-color: #1a4899;
            box-shadow: 0 4px 10px rgba(239,190,26,.28);
        }

        .coord-divider {
            width: 1px;
            height: 32px;
            background: linear-gradient(
                180deg,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,.22) 50%,
                rgba(255,255,255,0) 100%
            );
            margin: 0 4px;
            flex-shrink: 0;
        }

        .coord-user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 6px 12px 6px 6px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.18);
            background: rgba(255,255,255,.12);
            color: #fff;
            cursor: pointer;
            transition: all .22s ease;
            min-width: unset;
            max-width: unset;
        }

        .coord-user-chip:hover {
            background: rgba(255,255,255,.20);
            border-color: rgba(255,255,255,.32);
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,.18);
            color: #fff;
        }

        .coord-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #ffe08a 0%, #f1be1a 100%);
            color: #163a78;
            font-weight: 900;
            font-size: .82rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(239,190,26,.30);
        }

        .coord-user-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
            flex: 1;
            line-height: 1.1;
        }

        .coord-user-name {
            font-size: .83rem;
            font-weight: 900;
            color: #ffffff;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .coord-user-role {
            font-size: .68rem;
            color: rgba(255,255,255,.60);
            font-weight: 700;
            line-height: 1;
            margin-top: 2px;
        }

        .coord-user-arrow {
            font-size: .62rem;
            color: rgba(255,255,255,.50);
            margin-left: 2px;
        }

        .coord-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            left: 0;
            width: 340px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 32, 68, .22);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all .18s ease;
            z-index: 999;
        }

        .coord-dropdown.align-right {
            left: auto;
            right: 0;
        }

        .coord-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .coord-dropdown-header {
            padding: 14px 16px;
            background: #f5f8ff;
            border-bottom: 1px solid #e8eefb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .coord-dropdown-header span {
            color: #17346c;
            font-weight: 800;
            font-size: .95rem;
        }

        .coord-dropdown-header a {
            color: #2956ac;
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
        }

        .coord-dropdown-list {
            list-style: none;
            margin: 0;
            padding: 0;
            max-height: 320px;
            overflow-y: auto;
        }

        .coord-dropdown-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2fb;
            background: #fff;
        }

        .coord-dropdown-item.unread {
            background: #f8fbff;
        }

        .coord-dropdown-item.sm {
            align-items: center;
        }

        .coord-dropdown-icon,
        .coord-dropdown-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 800;
        }

        .coord-dropdown-icon.blue {
            background: rgba(41,86,172,.12);
            color: #2956ac;
        }

        .coord-dropdown-icon.gold {
            background: rgba(241,190,26,.18);
            color: #9b7300;
        }

        .coord-dropdown-icon.green {
            background: rgba(31,163,98,.12);
            color: #138a50;
        }

        .coord-dropdown-icon.sm {
            width: 38px;
            height: 38px;
            border-radius: 10px;
        }

        .coord-dropdown-avatar {
            background: linear-gradient(135deg, #2956ac 0%, #17346c 100%);
            color: #fff;
        }

        .coord-dropdown-text {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .coord-dropdown-text strong {
            color: #17346c;
            font-size: .92rem;
            font-weight: 800;
        }

        .coord-dropdown-text span {
            color: #53627f;
            font-size: .85rem;
            line-height: 1.35;
        }

        .coord-dropdown-text small {
            color: #7f8ca6;
            font-size: .77rem;
            font-weight: 700;
        }

        .coord-dropdown-footer {
            padding: 12px 16px;
            background: #fff;
            border-top: 1px solid #eef2fb;
        }

        .coord-dropdown-footer a {
            color: #2956ac;
            font-size: .85rem;
            font-weight: 800;
            text-decoration: none;
        }

        .coord-dropdown-footer.danger {
            background: #fff8f8;
        }

        .coord-dropdown-footer.danger form {
            margin: 0;
        }

        .coord-dropdown-footer.danger button {
            width: 100%;
            border: none;
            background: transparent;
            color: #c73737;
            font-size: .9rem;
            font-weight: 800;
            text-align: left;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .coord-user-header {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #f5f8ff 0%, #eef3ff 100%);
            border-bottom: 1px solid #e8eefb;
        }

        .coord-user-header-avatar {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #ffd34d 0%, #f1be1a 100%);
            color: #17346c;
            font-size: 1rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .coord-user-header strong {
            display: block;
            color: #17346c;
            font-size: .96rem;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .coord-user-header span {
            color: #62708b;
            font-size: .84rem;
            word-break: break-word;
        }

        .content-wrapper {
            min-height: 100vh;
            padding-top: 0 !important;
        }

        .dashboard-shell {
            padding-top: 0 !important;
        }

        .session-timeout-modal .modal-content {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(17, 43, 94, .28);
        }

        .session-timeout-header {
            background: linear-gradient(135deg, #17346c 0%, #1d4f9f 100%);
            color: #fff;
            padding: 18px 22px;
            border-bottom: none;
        }

        .session-timeout-header .modal-title {
            font-weight: 800;
            font-size: 1.15rem;
            margin: 0;
        }

        .session-timeout-body {
            padding: 24px 22px 18px;
            color: #24324b;
        }

        .session-timeout-body p {
            margin-bottom: 10px;
            font-size: .98rem;
        }

        .session-timeout-note {
            color: #6f7b92;
            font-size: .92rem;
        }

        .session-timeout-countdown {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(239,190,26,.16);
            color: #7a5b00;
            font-weight: 800;
            font-size: .92rem;
        }

        .session-timeout-footer {
            padding: 0 22px 22px;
            border-top: none;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-session-continue {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            border: none;
            background: linear-gradient(135deg, #f1be1a 0%, #e0aa00 100%);
            color: #17346c;
        }

        .btn-session-continue:hover {
            color: #17346c;
            filter: brightness(.98);
        }

        .btn-session-logout {
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
        }

        @media (max-width: 1199.98px) {
            .coord-user-chip {
                min-width: unset;
                max-width: 340px;
            }
        }

        @media (max-width: 991.98px) {
            .coordinator-topbar {
                display: none;
            }
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
                </div>
            </div>

            <div id="dashboardSidebarScroll" class="dashboard-sidebar-scroll">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column dashboard-menu"
                        data-widget="treeview"
                        role="menu"
                        data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ route('empleado.dashboard') }}"
                               class="nav-link {{ $dashboardActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview {{ $tramitesMenuOpen ? 'menu-open' : '' }}">
                            <a href="javascript:void(0)"
                               class="nav-link {{ $tramitesMenuOpen ? 'active' : '' }}">
                                <i class="nav-icon fas fa-folder-open"></i>
                                <p>
                                    Trámites
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('coordinador.cambio-carrera.index') }}"
                                       class="nav-link {{ $cambioCarreraActive ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cambio de carrera</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ $cancelacionUrl }}"
                                       class="nav-link {{ $cancelacionActive ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cancelación</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $backupUrl }}"
                               class="nav-link {{ $backupActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Respaldo</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('seguridad.index') }}"
                               class="nav-link {{ request()->is('seguridad*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-halved"></i>
                                <p>Seguridad</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('reporte.tramites.vista') }}"
                               class="nav-link {{ request()->routeIs('reporte.tramites.vista') || request()->is('reporte-tramites*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reportes</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('auditoria') }}"
                               class="nav-link {{ request()->routeIs('auditoria') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-magnifying-glass-chart"></i>
                                <p>Auditoría</p>
                            </a>
                        </li>

                        @if (Route::has('bitacora.index'))
                            <li class="nav-item">
                                <a href="{{ route('bitacora.index') }}"
                                   class="nav-link {{ request()->routeIs('bitacora.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-book"></i>
                                    <p>Bitácora</p>
                                </a>
                            </li>
                        @endif

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

    <button id="sidebarToggleBtn"
            class="sidebar-float-toggle d-none d-lg-flex"
            type="button"
            title="Colapsar/Expandir menú">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="content-wrapper">

        <div class="coordinator-topbar">
            <div class="coordinator-topbar-left">
                <div class="coord-breadcrumb">
                    <i class="fas fa-house"></i>
                    <span>Inicio</span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="active">{{ $pageTitle }}</span>
                </div>
            </div>

            <div class="coordinator-topbar-right">

                <div class="coord-action-group">
                    <button class="coord-icon-btn" id="btnCoordNotif" title="Notificaciones">
                        <i class="fas fa-bell"></i>
                        <span class="coord-badge">3</span>
                    </button>

                    <div class="coord-dropdown" id="dropCoordNotif">
                        <div class="coord-dropdown-header">
                            <span>Notificaciones</span>
                            <a href="#">Marcar todas</a>
                        </div>

                        <ul class="coord-dropdown-list">
                            <li class="coord-dropdown-item unread">
                                <div class="coord-dropdown-icon blue">
                                    <i class="fas fa-file-circle-check"></i>
                                </div>
                                <div class="coord-dropdown-text">
                                    <strong>Nuevo trámite recibido</strong>
                                    <span>Hay una solicitud pendiente de revisión en coordinación.</span>
                                    <small>Hace 5 min</small>
                                </div>
                            </li>

                            <li class="coord-dropdown-item unread">
                                <div class="coord-dropdown-icon gold">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="coord-dropdown-text">
                                    <strong>Seguimiento requerido</strong>
                                    <span>Una solicitud continúa en estado de revisión.</span>
                                    <small>Hace 1 hora</small>
                                </div>
                            </li>

                            <li class="coord-dropdown-item">
                                <div class="coord-dropdown-icon green">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                                <div class="coord-dropdown-text">
                                    <strong>Dictamen emitido</strong>
                                    <span>Se registró correctamente una resolución reciente.</span>
                                    <small>Ayer</small>
                                </div>
                            </li>
                        </ul>

                        <div class="coord-dropdown-footer">
                            <a href="#">Ver todas las notificaciones</a>
                        </div>
                    </div>
                </div>

                <div class="coord-action-group">
                    <button class="coord-icon-btn" id="btnCoordMsg" title="Mensajes">
                        <i class="fas fa-envelope"></i>
                        <span class="coord-badge gold">1</span>
                    </button>

                    <div class="coord-dropdown" id="dropCoordMsg">
                        <div class="coord-dropdown-header">
                            <span>Mensajes</span>
                            <a href="#">Ver todos</a>
                        </div>

                        <ul class="coord-dropdown-list">
                            <li class="coord-dropdown-item unread">
                                <div class="coord-dropdown-avatar">SA</div>
                                <div class="coord-dropdown-text">
                                    <strong>Secretaría Académica</strong>
                                    <span>Se actualizó un trámite que requiere validación de coordinación.</span>
                                    <small>Hace 30 min</small>
                                </div>
                            </li>
                        </ul>

                        <div class="coord-dropdown-footer">
                            <a href="#">Ir a mensajes</a>
                        </div>
                    </div>
                </div>

                <div class="coord-divider"></div>

                <div class="coord-action-group">
                    <button class="coord-user-chip" id="btnCoordUser" title="Mi perfil">
                        <div class="coord-user-avatar">{{ $initials }}</div>
                        <div class="coord-user-info">
                            <span class="coord-user-name">{{ $displayName }}</span>
                            <span class="coord-user-role">{{ $displayRole }}</span>
                        </div>
                        <i class="fas fa-chevron-down coord-user-arrow"></i>
                    </button>

                    <div class="coord-dropdown align-right" id="dropCoordUser">
                        <div class="coord-user-header">
                            <div class="coord-user-header-avatar">{{ $initials }}</div>
                            <div>
                                <strong>{{ $displayName }}</strong>
                                <span>{{ $correoInstitucional }}</span>
                            </div>
                        </div>

                        <ul class="coord-dropdown-list">
                            <li class="coord-dropdown-item sm">
                                <div class="coord-dropdown-icon blue sm">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="coord-dropdown-text">
                                    <span>Mi perfil</span>
                                </div>
                            </li>
                        </ul>

                        <div class="coord-dropdown-footer danger">
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

        <section class="content dashboard-shell">
            @yield('content')
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2026 PumaGestión – UNAH</strong>
    </footer>
</div>

<div class="modal fade session-timeout-modal"
     id="sessionTimeoutModal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="sessionTimeoutModalLabel"
     aria-hidden="true"
     data-backdrop="static"
     data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="session-timeout-header">
                <h5 class="modal-title" id="sessionTimeoutModalLabel">¿Sigues ahí?</h5>
            </div>

            <div class="session-timeout-body">
                <p>Tu sesión está por expirar por inactividad.</p>
                <p class="session-timeout-note">Presiona <strong>“Continuar sesión”</strong> para seguir trabajando.</p>

                <div class="session-timeout-countdown">
                    <i class="fas fa-hourglass-half"></i>
                    <span>Se cerrará en <span id="sessionCountdownText">180</span> segundos</span>
                </div>
            </div>

            <div class="session-timeout-footer">
                <button type="button" class="btn btn-outline-secondary btn-session-logout" id="sessionLogoutNowBtn">
                    Cerrar sesión
                </button>

                <button type="button" class="btn btn-session-continue" id="sessionContinueBtn">
                    Continuar sesión
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnNotif = document.getElementById('btnCoordNotif');
    const btnMsg   = document.getElementById('btnCoordMsg');
    const btnUser  = document.getElementById('btnCoordUser');

    const dropNotif = document.getElementById('dropCoordNotif');
    const dropMsg   = document.getElementById('dropCoordMsg');
    const dropUser  = document.getElementById('dropCoordUser');

    const allDrops = [dropNotif, dropMsg, dropUser];

    function closeAll(except = null) {
        allDrops.forEach(drop => {
            if (!drop) return;
            if (drop !== except) {
                drop.classList.remove('show');
            }
        });
    }

    function toggleDropdown(button, dropdown) {
        if (!button || !dropdown) return;

        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const willOpen = !dropdown.classList.contains('show');
            closeAll();
            if (willOpen) {
                dropdown.classList.add('show');
            }
        });
    }

    toggleDropdown(btnNotif, dropNotif);
    toggleDropdown(btnMsg, dropMsg);
    toggleDropdown(btnUser, dropUser);

    document.addEventListener('click', function (e) {
        const groups = document.querySelectorAll('.coord-action-group');
        let clickedInside = false;

        groups.forEach(group => {
            if (group.contains(e.target)) {
                clickedInside = true;
            }
        });

        if (!clickedInside) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAll();
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const WARNING_TIME_MS = 28 * 60 * 1000;
    const LOGOUT_TIME_MS  = 31 * 60 * 1000;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const modalElement = $('#sessionTimeoutModal');
    const continueBtn = document.getElementById('sessionContinueBtn');
    const logoutNowBtn = document.getElementById('sessionLogoutNowBtn');
    const countdownText = document.getElementById('sessionCountdownText');

    let warningTimer = null;
    let logoutTimer = null;
    let countdownInterval = null;
    let modalVisible = false;
    let secondsLeft = Math.floor((LOGOUT_TIME_MS - WARNING_TIME_MS) / 1000);

    function clearAllTimers() {
        if (warningTimer) clearTimeout(warningTimer);
        if (logoutTimer) clearTimeout(logoutTimer);
        if (countdownInterval) clearInterval(countdownInterval);
    }

    function updateCountdownText() {
        if (countdownText) {
            countdownText.textContent = String(secondsLeft);
        }
    }

    function startCountdown() {
        secondsLeft = Math.floor((LOGOUT_TIME_MS - WARNING_TIME_MS) / 1000);
        updateCountdownText();

        countdownInterval = setInterval(() => {
            secondsLeft--;
            updateCountdownText();

            if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    function showWarningModal() {
        modalVisible = true;
        startCountdown();
        modalElement.modal('show');
    }

    function hideWarningModal() {
        modalVisible = false;
        if (countdownInterval) clearInterval(countdownInterval);
        modalElement.modal('hide');
    }

    function resetSessionTimers() {
        clearAllTimers();

        warningTimer = setTimeout(() => {
            showWarningModal();
        }, WARNING_TIME_MS);

        logoutTimer = setTimeout(() => {
            forceLogoutByInactivity();
        }, LOGOUT_TIME_MS);
    }

    async function continueSession() {
        continueBtn.disabled = true;

        try {
            const response = await fetch('{{ route('session.keepalive') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            if (!response.ok) {
                throw new Error('No se pudo renovar la sesión.');
            }

            hideWarningModal();
            resetSessionTimers();
        } catch (error) {
            await forceLogoutByInactivity();
        } finally {
            continueBtn.disabled = false;
        }
    }

    async function forceLogoutByInactivity() {
        clearAllTimers();

        try {
            const response = await fetch('{{ route('session.logout.inactive') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            if (!response.ok) {
                throw new Error('No se pudo cerrar la sesión automáticamente.');
            }

            const data = await response.json();
            window.location.href = data.redirect || '{{ url('/portal') }}';
        } catch (error) {
            window.location.href = '{{ url('/portal') }}';
        }
    }

    const activityEvents = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'];

    activityEvents.forEach((eventName) => {
        window.addEventListener(eventName, function () {
            if (modalVisible) return;
            resetSessionTimers();
        }, { passive: true });
    });

    if (continueBtn) {
        continueBtn.addEventListener('click', function () {
            continueSession();
        });
    }

    if (logoutNowBtn) {
        logoutNowBtn.addEventListener('click', function () {
            forceLogoutByInactivity();
        });
    }

    modalElement.on('hidden.bs.modal', function () {
        if (!modalVisible && countdownInterval) {
            clearInterval(countdownInterval);
        }
    });

    resetSessionTimers();
});
</script>
</body>
</html>
