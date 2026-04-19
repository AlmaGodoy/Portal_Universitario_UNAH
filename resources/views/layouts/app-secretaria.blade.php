@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;

    $user = Auth::user();

    $displayName = 'Secretaría';
    $displayRole = 'Secretaría de Carrera';

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

    $correoInstitucional = 'secretaria@unah.hn';

    if ($user && !empty($user->email)) {
        $correoInstitucional = $user->email;
    } elseif ($user && !empty($user->correo_institucional)) {
        $correoInstitucional = $user->correo_institucional;
    } elseif ($user && optional($user->persona)->correo_institucional) {
        $correoInstitucional = optional($user->persona)->correo_institucional;
    }

    $parts = preg_split('/\s+/', trim($displayName));
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }
    }

    if ($initials === '') {
        $initials = 'SC';
    }

    /*
    |--------------------------------------------------------------------------
    | RUTAS SEGURAS
    |--------------------------------------------------------------------------
    */
    $dashboardUrl = Route::has('empleado.dashboard')
        ? route('empleado.dashboard')
        : 'javascript:void(0)';

    $cambioCarreraUrl = Route::has('cambio-carrera.secretaria')
        ? route('cambio-carrera.secretaria')
        : 'javascript:void(0)';

    $cancelacionUrl = Route::has('cancelacion.secretaria.index')
        ? route('cancelacion.secretaria.index')
        : 'javascript:void(0)';

    $equivalenciasUrl = Route::has('equivalencias.revisor')
        ? route('equivalencias.revisor')
        : 'javascript:void(0)';

    $fechasUrl = Route::has('cambio-carrera.secretaria.calendarios')
        ? route('cambio-carrera.secretaria.calendarios')
        : 'javascript:void(0)';

    $respaldoUrl = Route::has('backup.index')
        ? route('backup.index')
        : 'javascript:void(0)';

    $auditoriaUrl = Route::has('auditoria')
        ? route('auditoria')
        : 'javascript:void(0)';

    $bitacoraUrl = Route::has('bitacora.index')
        ? route('bitacora.index')
        : 'javascript:void(0)';

    $soporteUrl = Route::has('soporte.vista')
        ? route('soporte.vista')
        : 'javascript:void(0)';

    $configuracionUrl = Route::has('configuracion.index')
        ? route('configuracion.index')
        : 'javascript:void(0)';

    /*
    |--------------------------------------------------------------------------
    | ACTIVOS DEL MENÚ
    |--------------------------------------------------------------------------
    */
    $dashboardActive = request()->routeIs('empleado.dashboard') || request()->is('empleado/dashboard*');

    $menuRevisionOpen = request()->routeIs(
        'cambio-carrera.secretaria',
        'cambio-carrera.secretaria.revisar',
        'cancelacion.secretaria.*',
        'equivalencias.revisor'
    ) || request()->is('equivalencias/revision*') || request()->is('equivalencias/api/*');

    $menuCambioCarreraActive = request()->routeIs(
        'cambio-carrera.secretaria',
        'cambio-carrera.secretaria.revisar'
    );

    $menuCancelacionActive = request()->routeIs('cancelacion.secretaria.*');

    $menuEquivalenciasActive = request()->routeIs('equivalencias.revisor')
        || request()->is('equivalencias/revision*')
        || request()->is('equivalencias/api/*');

    $fechasActive = request()->routeIs('cambio-carrera.secretaria.calendarios');

    $respaldoActive = request()->routeIs('backup.*') || request()->is('respaldos*');

    $auditoriaActive = request()->routeIs('auditoria') || request()->routeIs('auditoria.*');

    $bitacoraActive = request()->routeIs('bitacora.*');

    $soporteActive = request()->routeIs('soporte.vista')
        || request()->is('soporte')
        || request()->is('api/soporte*');

    $configuracionActive = request()->routeIs('configuracion.index')
        || request()->is('configuracion')
        || request()->is('configuracion*');

    $pageTitle = trim($__env->yieldContent('titulo', $__env->yieldContent('title', 'Secretaría de Carrera')));
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PumaGestión – {{ $pageTitle }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <style>
        .content-wrapper {
            min-height: 100vh;
            padding-top: 0 !important;
        }

        .dashboard-shell {
            padding-top: 0 !important;
        }

        .secretaria-topbar {
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
        }

        .secretaria-topbar::before {
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

        .secretaria-topbar-left,
        .secretaria-topbar-right {
            display: flex;
            align-items: center;
            min-width: 0;
            position: relative;
            z-index: 2;
        }

        .secretaria-topbar-right {
            gap: 12px;
            margin-left: auto;
        }

        .sec-breadcrumb {
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

        .sec-breadcrumb .active {
            color: #ffd34d;
            font-weight: 800;
        }

        .sec-action-group {
            position: relative;
        }

        .sec-icon-btn {
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

        .sec-icon-btn:hover {
            background: rgba(255,255,255,.20);
            border-color: rgba(255,255,255,.30);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,.18);
        }

        .sec-badge {
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

        .sec-badge.gold {
            background: #f1be1a;
            color: #17346c;
            box-shadow: 0 4px 10px rgba(241,190,26,.35);
        }

        .sec-divider {
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

        .sec-user-chip {
            min-width: 300px;
            max-width: 360px;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 18px;
            background: rgba(255,255,255,.10);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 14px;
            backdrop-filter: blur(8px);
            transition: all .18s ease;
        }

        .sec-user-chip:hover {
            background: rgba(255,255,255,.16);
            color: #fff;
        }

        .sec-user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffd34d 0%, #f1be1a 100%);
            color: #17346c;
            font-weight: 900;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(239,190,26,.30);
        }

        .sec-user-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
            flex: 1;
            line-height: 1.1;
        }

        .sec-user-name {
            font-size: 1.02rem;
            font-weight: 800;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sec-user-role {
            font-size: .74rem;
            color: rgba(255,255,255,.70);
            font-weight: 700;
            margin-top: 2px;
        }

        .sec-user-arrow {
            color: rgba(255,255,255,.85);
            font-size: .9rem;
        }

        .sec-dropdown {
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

        .sec-dropdown.align-right {
            left: auto;
            right: 0;
        }

        .sec-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .sec-dropdown-header {
            padding: 14px 16px;
            background: #f5f8ff;
            border-bottom: 1px solid #e8eefb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .sec-dropdown-header span {
            color: #17346c;
            font-weight: 800;
            font-size: .95rem;
        }

        .sec-dropdown-header a {
            color: #2956ac;
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
        }

        .sec-dropdown-list {
            list-style: none;
            margin: 0;
            padding: 0;
            max-height: 320px;
            overflow-y: auto;
        }

        .sec-dropdown-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #eef2fb;
            background: #fff;
        }

        .sec-dropdown-item.unread {
            background: #f8fbff;
        }

        .sec-dropdown-item.sm {
            align-items: center;
        }

        .sec-dropdown-icon,
        .sec-dropdown-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 800;
        }

        .sec-dropdown-icon.blue {
            background: rgba(41,86,172,.12);
            color: #2956ac;
        }

        .sec-dropdown-icon.gold {
            background: rgba(241,190,26,.18);
            color: #9b7300;
        }

        .sec-dropdown-icon.green {
            background: rgba(31,163,98,.12);
            color: #138a50;
        }

        .sec-dropdown-icon.sm {
            width: 38px;
            height: 38px;
            border-radius: 10px;
        }

        .sec-dropdown-avatar {
            background: linear-gradient(135deg, #2956ac 0%, #17346c 100%);
            color: #fff;
        }

        .sec-dropdown-text {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .sec-dropdown-text strong {
            color: #17346c;
            font-size: .92rem;
            font-weight: 800;
        }

        .sec-dropdown-text span {
            color: #53627f;
            font-size: .85rem;
            line-height: 1.35;
        }

        .sec-dropdown-text small {
            color: #7f8ca6;
            font-size: .77rem;
            font-weight: 700;
        }

        .sec-dropdown-footer {
            padding: 12px 16px;
            background: #fff;
            border-top: 1px solid #eef2fb;
        }

        .sec-dropdown-footer a {
            color: #2956ac;
            font-size: .85rem;
            font-weight: 800;
            text-decoration: none;
        }

        .sec-dropdown-footer.danger {
            background: #fff8f8;
        }

        .sec-dropdown-footer.danger form {
            margin: 0;
        }

        .sec-dropdown-footer.danger button {
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

        .session-timeout-modal .modal-content {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(17,43,94,.28);
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

        @media (max-width: 991.98px) {
            .secretaria-topbar {
                display: none;
            }

            .sec-user-chip {
                min-width: unset;
                max-width: unset;
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed dashboard-body">
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

                        <li class="nav-item">
                            <a href="{{ $dashboardUrl }}" class="nav-link {{ $dashboardActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview {{ $menuRevisionOpen ? 'menu-open' : '' }}">
                            <a href="javascript:void(0)" class="nav-link {{ $menuRevisionOpen ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-circle-check"></i>
                                <p>
                                    Revisión de documentos
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ $cambioCarreraUrl }}" class="nav-link {{ $menuCambioCarreraActive ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cambio de carrera</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ $cancelacionUrl }}" class="nav-link {{ $menuCancelacionActive ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Cancelación</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ $equivalenciasUrl }}" class="nav-link {{ $menuEquivalenciasActive ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Equivalencias</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $fechasUrl }}" class="nav-link {{ $fechasActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-days"></i>
                                <p>Fechas</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $respaldoUrl }}" class="nav-link {{ $respaldoActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Respaldo</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $auditoriaUrl }}" class="nav-link {{ $auditoriaActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-magnifying-glass-chart"></i>
                                <p>Auditoría</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $bitacoraUrl }}" class="nav-link {{ $bitacoraActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-book"></i>
                                <p>Bitácora</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $soporteUrl }}" class="nav-link {{ $soporteActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-headset"></i>
                                <p>Soporte</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $configuracionUrl }}" class="nav-link {{ $configuracionActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gear"></i>
                                <p>Configuración</p>
                            </a>
                        </li>


                    </ul>
                </nav>
            </div>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="student-topbar secretaria-topbar-sa">

    <div class="student-topbar-left">
        <div class="topbar-left-copy">
            <div class="topbar-breadcrumb">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
                <i class="fas fa-chevron-right"></i>
                <span class="topbar-breadcrumb-active">{{ $pageTitle }}</span>
            </div>
            <h1 class="topbar-page-title">Panel de secretaría de carrera</h1>
        </div>
    </div>

    <div class="student-topbar-right">
        <div class="topbar-action-group">
            <button class="topbar-icon-btn" id="btnSecNotif" title="Notificaciones">
                <i class="fas fa-bell"></i>
                <span class="topbar-badge">3</span>
            </button>

            <div class="topbar-dropdown" id="dropSecNotif">
                <div class="topbar-dropdown-header">
                    <span>Notificaciones</span>
                    <a href="#" class="topbar-dropdown-mark">Marcar todas</a>
                </div>

                <ul class="topbar-dropdown-list">
                    <li class="topbar-dropdown-item unread">
                        <div class="topbar-dropdown-icon blue">
                            <i class="fas fa-file-circle-check"></i>
                        </div>
                        <div class="topbar-dropdown-text">
                            <strong>Nuevo documento recibido</strong>
                            <span>Hay un trámite pendiente por revisión en secretaría.</span>
                            <small>Hace 5 min</small>
                        </div>
                    </li>

                    <li class="topbar-dropdown-item unread">
                        <div class="topbar-dropdown-icon gold">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="topbar-dropdown-text">
                            <strong>Seguimiento requerido</strong>
                            <span>Un expediente sigue pendiente de validación.</span>
                            <small>Hace 1 hora</small>
                        </div>
                    </li>

                    <li class="topbar-dropdown-item">
                        <div class="topbar-dropdown-icon green">
                            <i class="fas fa-circle-check"></i>
                        </div>
                        <div class="topbar-dropdown-text">
                            <strong>Revisión completada</strong>
                            <span>Se procesó correctamente una solicitud reciente.</span>
                            <small>Ayer</small>
                        </div>
                    </li>
                </ul>

                <div class="topbar-dropdown-footer">
                    <a href="#">Ver todas las notificaciones</a>
                </div>
            </div>
        </div>

        <div class="topbar-action-group">
            <button class="topbar-icon-btn" id="btnSecMsg" title="Mensajes">
                <i class="fas fa-envelope"></i>
                <span class="topbar-badge gold">1</span>
            </button>

            <div class="topbar-dropdown" id="dropSecMsg">
                <div class="topbar-dropdown-header">
                    <span>Mensajes</span>
                    <a href="#" class="topbar-dropdown-mark">Ver todos</a>
                </div>

                <ul class="topbar-dropdown-list">
                    <li class="topbar-dropdown-item unread">
                        <div class="topbar-dropdown-avatar">CO</div>
                        <div class="topbar-dropdown-text">
                            <strong>Coordinación</strong>
                            <span>Se requiere seguimiento sobre un trámite remitido.</span>
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

        <div class="topbar-action-group">
            <button class="student-user-chip" id="btnSecUser" title="Mi perfil">
                <div class="student-user-chip-avatar">{{ $initials }}</div>
                <div class="student-user-chip-info">
                    <span class="student-user-chip-name">{{ $displayName }}</span>
                    <span class="student-user-chip-role">{{ $displayRole }}</span>
                </div>
                <i class="fas fa-chevron-down student-user-chip-arrow"></i>
            </button>

            <div class="topbar-dropdown align-right" id="dropSecUser">
                <div class="topbar-user-header">
                    <div class="topbar-user-header-avatar">{{ $initials }}</div>
                    <div>
                        <strong>{{ $displayName }}</strong>
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
    const btnNotif = document.getElementById('btnSecNotif');
    const btnMsg = document.getElementById('btnSecMsg');
    const btnUser = document.getElementById('btnSecUser');

    const dropNotif = document.getElementById('dropSecNotif');
    const dropMsg = document.getElementById('dropSecMsg');
    const dropUser = document.getElementById('dropSecUser');

    const allDrops = [dropNotif, dropMsg, dropUser];

    function closeAll(except = null) {
        allDrops.forEach(function (drop) {
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
        const groups = document.querySelectorAll('.sec-action-group');
        let clickedInside = false;

        groups.forEach(function (group) {
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
    const LOGOUT_TIME_MS = 31 * 60 * 1000;

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

        countdownInterval = setInterval(function () {
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

        warningTimer = setTimeout(function () {
            showWarningModal();
        }, WARNING_TIME_MS);

        logoutTimer = setTimeout(function () {
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

    activityEvents.forEach(function (eventName) {
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


