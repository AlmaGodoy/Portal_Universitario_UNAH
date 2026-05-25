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

    $correoInstitucional =
        $user->email
        ?? $user->correo_institucional
        ?? optional($user->persona)->correo_institucional
        ?? 'secretaria@unah.hn';

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
        :root {
            --student-sidebar-width: 350px;
            --student-sidebar-collapsed-width: 4.6rem;
        }

        /* ── ANCHO DINÁMICO ──────────────────────────────── */
        @media (min-width: 992px) {
            body:not(.sidebar-collapse) .main-sidebar {
                width: var(--student-sidebar-width) !important;
            }
            body:not(.sidebar-collapse) .content-wrapper,
            body:not(.sidebar-collapse) .main-footer,
            body:not(.sidebar-collapse) .main-header {
                margin-left: var(--student-sidebar-width) !important;
            }
            body.sidebar-collapse .main-sidebar {
                width: var(--student-sidebar-collapsed-width) !important;
            }
            body.sidebar-collapse .content-wrapper,
            body.sidebar-collapse .main-footer,
            body.sidebar-collapse .main-header {
                margin-left: var(--student-sidebar-collapsed-width) !important;
            }
        }

        /* ── BLOQUE CONTROL (donde estaba el user-card) ──── */
        .sidebar-control-block {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255,255,255,.10);
            position: relative;
            z-index: 2;
            overflow: hidden;
            min-height: 58px;
        }

        /* Botón colapsar */
        .sidebar-toggle-inner {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #ffe08a 0%, #f1be1a 100%);
            color: #17346c;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(239,190,26,.35);
            transition: background .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .sidebar-toggle-inner:hover {
            background: linear-gradient(135deg, #ffd84a 0%, #e8b20e 100%);
            transform: scale(1.08);
            box-shadow: 0 6px 18px rgba(239,190,26,.50);
        }

        .sidebar-toggle-inner i {
            transition: transform .3s ease;
            pointer-events: none;
        }

        body.sidebar-collapse .sidebar-toggle-inner i {
            transform: rotate(180deg);
        }

        /* Info de tamaño */
        .sidebar-control-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            overflow: hidden;
            transition: opacity .25s ease, max-width .25s ease;
            max-width: 999px;
        }

        body.sidebar-collapse .sidebar-control-info {
            opacity: 0;
            max-width: 0;
            pointer-events: none;
        }

        .sidebar-control-label {
            font-size: .68rem;
            font-weight: 700;
            color: rgba(255,255,255,.45);
            letter-spacing: .5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Botones +/Normal/- */
        .sidebar-size-btns {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .sidebar-size-btn {
            border: none;
            height: 26px;
            border-radius: 7px;
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.85);
            font-size: .72rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
            transition: all .18s ease;
            white-space: nowrap;
        }

        .sidebar-size-btn:hover {
            background: rgba(255,255,255,.24);
            color: #fff;
            transform: translateY(-1px);
        }

        .sidebar-size-btn i {
            font-size: .65rem;
        }

        /* ── MENÚ MÁS GRANDE Y MEJOR DISTRIBUIDO ─────────── */
        #dashboardSidebarScroll {
            display: flex;
            flex-direction: column;
        }

        #dashboardSidebarScroll nav {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 100%;
        }

        .dashboard-menu {
            display: flex !important;
            flex-direction: column;
            min-height: 100%;
            gap: 10px;
            padding: 18px 0 22px !important;
        }

        .dashboard-menu .nav-item {
            padding: 0 8px;
        }

        .dashboard-menu .nav-link {
            min-height: 56px;
            padding: 0 18px !important;
            border-radius: 16px !important;
            display: flex !important;
            align-items: center;
            font-size: 1.06rem !important;
            font-weight: 700 !important;
        }

        .dashboard-menu .nav-link p {
            margin: 0 !important;
            line-height: 1.2;
        }

        .dashboard-menu .nav-icon {
            width: 34px !important;
            margin-right: 14px !important;
            font-size: 1.18rem !important;
        }

        .dashboard-menu .nav-link.active {
            min-height: 62px;
            font-size: 1.08rem !important;
            box-shadow: 0 8px 20px rgba(0,0,0,.16);
        }

        .dashboard-menu .nav-link:hover {
            transform: translateX(2px);
        }

        /* ── RESIZE HANDLE ───────────────────────────────── */
        @media (min-width: 992px) {
            .sidebar-resize-handle {
                position: fixed;
                top: 0;
                left: calc(var(--student-sidebar-width) - 8px);
                width: 18px;
                height: 100vh;
                z-index: 2000;
                cursor: col-resize;
                background: rgba(255,255,255,.03);
                transition: left .18s ease, background .18s ease;
            }

            .sidebar-resize-handle:hover {
                background: rgba(42,119,200,.16);
            }

            .sidebar-resize-handle::before {
                content: "";
                position: absolute;
                top: 0; bottom: 0; left: 8px;
                width: 2px;
                background: rgba(255,255,255,.35);
            }

            .sidebar-resize-handle::after {
                content: "";
                position: absolute;
                top: 50%; left: 5px;
                width: 8px; height: 52px;
                transform: translateY(-50%);
                border-radius: 999px;
                background: rgba(255,255,255,.18);
                box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
            }

            body.sidebar-collapse .sidebar-resize-handle {
                display: none !important;
            }

            body.sidebar-resizing,
            body.sidebar-resizing * {
                user-select: none !important;
                cursor: col-resize !important;
            }

            body.sidebar-resizing .sidebar-resize-handle {
                background: rgba(42,119,200,.28);
            }
        }

        /* ── MÓVIL ───────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .dashboard-menu {
                gap: 6px;
                padding: 14px 0 18px !important;
            }

            .dashboard-menu .nav-item {
                padding: 0 6px;
            }

            .dashboard-menu .nav-link {
                min-height: 50px;
                padding: 0 14px !important;
                font-size: .98rem !important;
                border-radius: 14px !important;
            }

            .dashboard-menu .nav-icon {
                width: 30px !important;
                margin-right: 12px !important;
                font-size: 1.05rem !important;
            }
        }

    /* =========================================================
       TOPBAR ESTUDIANTE
       Ajuste final sutil para combinar con el banner
    ========================================================= */

    .student-topbar {
        position: relative !important;
        z-index: 40 !important;
        min-height: 52px !important;
        margin: 0 10px 10px !important;
        padding: 6px 14px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        background: linear-gradient(90deg, #123674 0%, #1c4f9d 48%, #174487 100%) !important;
        border-bottom: 2px solid #ffd21f !important;
        border-radius: 0 0 10px 10px !important;
        box-shadow: 0 3px 8px rgba(8, 35, 78, 0.10) !important;
        overflow: visible !important;
    }

    .student-topbar::before {
        content: "" !important;
        position: absolute !important;
        inset: 0 !important;
        border-radius: 0 0 10px 10px !important;
        background: linear-gradient(
            120deg,
            rgba(255,255,255,0.06),
            transparent 38%,
            rgba(255,255,255,0.03)
        ) !important;
        pointer-events: none !important;
    }

    .student-topbar-left,
    .student-topbar-right {
        position: relative !important;
        z-index: 2 !important;
    }

    .student-topbar-left {
        display: flex !important;
        align-items: center !important;
        min-width: 0 !important;
    }

    .student-topbar-right {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 7px !important;
        min-width: 0 !important;
    }

    .topbar-left-copy {
        display: flex !important;
        align-items: center !important;
    }

    .topbar-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        padding: 7px 12px !important;
        border-radius: 9px !important;
        background: rgba(255,255,255,0.08) !important;
        border: 1px solid rgba(255,255,255,0.13) !important;
        color: rgba(255,255,255,0.88) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
    }

    .topbar-breadcrumb i {
        font-size: 12px !important;
        color: rgba(255,255,255,0.82) !important;
    }

    .topbar-breadcrumb-active {
        color: #ffd21f !important;
        font-weight: 900 !important;
    }

    .topbar-action-group {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    .topbar-icon-btn {
        position: relative !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 10px !important;
        border: 1px solid rgba(255,255,255,0.15) !important;
        background: rgba(255,255,255,0.08) !important;
        color: #ffffff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
        box-shadow: none !important;
    }

    .topbar-icon-btn:hover {
        background: rgba(255,255,255,0.14) !important;
        border-color: rgba(255,210,31,0.40) !important;
        transform: translateY(-1px) !important;
    }

    .topbar-icon-btn i {
        font-size: 14px !important;
        color: #ffffff !important;
    }

    .topbar-badge {
        position: absolute !important;
        top: -6px !important;
        right: -6px !important;
        min-width: 17px !important;
        height: 17px !important;
        padding: 0 5px !important;
        border-radius: 999px !important;
        background: #e63946 !important;
        color: #ffffff !important;
        font-size: 9.5px !important;
        font-weight: 900 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 2px solid #1c4f9d !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.22) !important;
    }

    .topbar-badge.gold {
        background: #ffd21f !important;
        color: #123674 !important;
    }

    .topbar-divider {
        width: 1px !important;
        height: 27px !important;
        margin: 0 3px !important;
        background: rgba(255,255,255,0.17) !important;
    }

    .student-user-chip {
        min-height: 38px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 9px !important;
        padding: 5px 11px 5px 6px !important;
        border-radius: 12px !important;
        border: 1px solid rgba(255,255,255,0.17) !important;
        background: rgba(255,255,255,0.09) !important;
        color: #ffffff !important;
        cursor: pointer !important;
        transition: all 0.18s ease !important;
        box-shadow: none !important;
    }

    .student-user-chip:hover {
        background: rgba(255,255,255,0.14) !important;
        border-color: rgba(255,210,31,0.40) !important;
        transform: translateY(-1px) !important;
    }

    .student-user-chip-avatar {
        width: 32px !important;
        height: 32px !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #ffd21f !important;
        color: #123674 !important;
        font-weight: 900 !important;
        font-size: 13px !important;
    }

    .student-user-chip-info {
        display: flex !important;
        flex-direction: column !important;
        line-height: 1.1 !important;
    }

    .student-user-chip-name {
        font-size: 12px !important;
        font-weight: 900 !important;
        color: #ffffff !important;
        max-width: 175px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .student-user-chip-role {
        margin-top: 2px !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        color: rgba(255,255,255,0.74) !important;
    }

    .student-user-chip-arrow {
        font-size: 11px !important;
        color: rgba(255,255,255,0.76) !important;
    }

    .topbar-dropdown {
        border-radius: 14px !important;
        border: 1px solid rgba(13, 47, 104, 0.14) !important;
        box-shadow: 0 18px 42px rgba(8, 35, 78, 0.24) !important;
        overflow: hidden !important;
    }

    .topbar-dropdown-header {
        background: #f6f9ff !important;
        border-bottom: 1px solid #e3ebf7 !important;
    }

    .topbar-dropdown-header span {
        color: #123674 !important;
        font-weight: 900 !important;
    }

    .topbar-dropdown-mark,
    .topbar-dropdown-footer a {
        color: #1c4f9d !important;
        font-weight: 800 !important;
    }




        /* ── DROPDOWNS TOPBAR ─────────────────────────────── */
        .topbar-dropdown {
            position: absolute !important;
            top: calc(100% + 10px) !important;
            right: 0 !important;
            width: 330px !important;
            max-width: calc(100vw - 24px) !important;
            background: #ffffff !important;
            display: none !important;
            z-index: 5000 !important;
        }

        .topbar-dropdown.show {
            display: block !important;
        }

        .topbar-dropdown.align-right {
            right: 0 !important;
        }

        .topbar-dropdown-list {
            list-style: none !important;
            margin: 0 !important;
            padding: 8px !important;
        }

        .topbar-dropdown-header,
        .topbar-dropdown-footer {
            padding: 11px 13px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
        }

        .topbar-dropdown-item {
            display: flex !important;
            align-items: flex-start !important;
            gap: 10px !important;
            padding: 10px !important;
            border-radius: 12px !important;
            transition: background .18s ease !important;
        }

        .topbar-dropdown-item:hover,
        .topbar-dropdown-item.unread {
            background: #f4f7fc !important;
        }

        .topbar-dropdown-item.sm {
            align-items: center !important;
        }

        .topbar-dropdown-icon,
        .topbar-dropdown-avatar {
            width: 34px !important;
            height: 34px !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 34px !important;
            font-weight: 900 !important;
        }

        .topbar-dropdown-icon.sm {
            width: 30px !important;
            height: 30px !important;
            flex-basis: 30px !important;
        }

        .topbar-dropdown-icon.blue,
        .topbar-dropdown-avatar {
            background: rgba(28, 79, 157, .12) !important;
            color: #1c4f9d !important;
        }

        .topbar-dropdown-icon.gold {
            background: rgba(255, 210, 31, .18) !important;
            color: #8a6500 !important;
        }

        .topbar-dropdown-icon.green {
            background: rgba(39, 174, 96, .13) !important;
            color: #198754 !important;
        }

        .topbar-dropdown-text {
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
            color: #24324b !important;
        }

        .topbar-dropdown-text strong {
            color: #123674 !important;
            font-size: 13px !important;
            font-weight: 900 !important;
        }

        .topbar-dropdown-text span {
            color: #5d6b82 !important;
            font-size: 12px !important;
            line-height: 1.35 !important;
        }

        .topbar-dropdown-text small {
            color: #8a96a8 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
        }

        .topbar-user-header {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 13px !important;
            background: linear-gradient(90deg, #123674 0%, #1c4f9d 100%) !important;
            color: #ffffff !important;
        }

        .topbar-user-header-avatar {
            width: 38px !important;
            height: 38px !important;
            border-radius: 12px !important;
            background: #ffd21f !important;
            color: #123674 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 900 !important;
        }

        .topbar-user-header strong,
        .topbar-user-header span {
            display: block !important;
        }

        .topbar-user-header strong {
            font-size: 13px !important;
            font-weight: 900 !important;
        }

        .topbar-user-header span {
            font-size: 11px !important;
            color: rgba(255,255,255,.78) !important;
        }

        .topbar-dropdown-footer {
            border-top: 1px solid #e3ebf7 !important;
        }

        .topbar-dropdown-footer.danger button {
            width: 100% !important;
            border: none !important;
            background: transparent !important;
            color: #c62828 !important;
            font-weight: 800 !important;
            text-align: left !important;
            padding: 0 !important;
        }

        @media (max-width: 575.98px) {
            .topbar-dropdown {
                right: -8px !important;
                width: calc(100vw - 26px) !important;
            }
        }



        /* ── MENÚ DE USUARIO MEJORADO ─────────────────────── */
        .user-profile-dropdown {
            width: 315px !important;
            border-radius: 18px !important;
            border: 1px solid rgba(18, 54, 116, 0.14) !important;
            background: #ffffff !important;
            box-shadow: 0 22px 48px rgba(8, 35, 78, 0.26) !important;
            overflow: hidden !important;
        }

        .user-profile-dropdown::before {
            content: "" !important;
            position: absolute !important;
            top: -7px !important;
            right: 28px !important;
            width: 14px !important;
            height: 14px !important;
            background: #1c4f9d !important;
            transform: rotate(45deg) !important;
            border-left: 1px solid rgba(255,255,255,0.18) !important;
            border-top: 1px solid rgba(255,255,255,0.18) !important;
        }

        .user-dropdown-header {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 16px 15px !important;
            background:
                radial-gradient(circle at 92% 0%, rgba(255, 210, 31, 0.22), transparent 35%),
                linear-gradient(135deg, #123674 0%, #1c4f9d 58%, #174487 100%) !important;
            border-bottom: 3px solid #ffd21f !important;
            color: #ffffff !important;
        }

        .user-dropdown-header::after {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            background: linear-gradient(120deg, rgba(255,255,255,0.10), transparent 45%) !important;
            pointer-events: none !important;
        }

        .user-dropdown-avatar {
            position: relative !important;
            z-index: 2 !important;
            width: 46px !important;
            height: 46px !important;
            border-radius: 15px !important;
            background: #ffd21f !important;
            color: #123674 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 16px !important;
            font-weight: 900 !important;
            flex: 0 0 46px !important;
            box-shadow: 0 7px 16px rgba(0,0,0,0.20) !important;
        }

        .user-dropdown-info {
            position: relative !important;
            z-index: 2 !important;
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
        }

        .user-dropdown-info strong {
            color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            line-height: 1.2 !important;
            max-width: 220px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .user-dropdown-info span {
            color: rgba(255,255,255,0.78) !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            max-width: 220px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .user-dropdown-info small {
            margin-top: 5px !important;
            width: fit-content !important;
            padding: 3px 9px !important;
            border-radius: 999px !important;
            background: rgba(255, 210, 31, 0.18) !important;
            border: 1px solid rgba(255, 210, 31, 0.36) !important;
            color: #ffd21f !important;
            font-size: 10px !important;
            font-weight: 900 !important;
            line-height: 1 !important;
        }

        .user-dropdown-body {
            padding: 9px !important;
            background: #ffffff !important;
        }

        .user-dropdown-option {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 11px 10px !important;
            border-radius: 13px !important;
            text-decoration: none !important;
            color: #24324b !important;
            transition: all .18s ease !important;
        }

        .user-dropdown-option:hover {
            background: #f3f7ff !important;
            transform: translateX(2px) !important;
        }

        .user-dropdown-option-icon {
            width: 34px !important;
            height: 34px !important;
            border-radius: 11px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 0 0 34px !important;
            background: rgba(28, 79, 157, .12) !important;
            color: #1c4f9d !important;
        }

        .user-dropdown-option-icon.gold {
            background: rgba(255, 210, 31, .20) !important;
            color: #8a6500 !important;
        }

        .user-dropdown-option span:not(.user-dropdown-option-icon) {
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
            flex: 1 !important;
            min-width: 0 !important;
        }

        .user-dropdown-option strong {
            color: #123674 !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            line-height: 1.15 !important;
        }

        .user-dropdown-option small {
            color: #6b7890 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
        }

        .user-dropdown-option-arrow {
            color: #9aa7bb !important;
            font-size: 11px !important;
        }

        .user-dropdown-footer {
            padding: 10px 12px 12px !important;
            background: #f7f9fd !important;
            border-top: 1px solid #e3ebf7 !important;
        }

        .user-dropdown-footer form {
            margin: 0 !important;
        }

        .user-logout-btn {
            width: 100% !important;
            min-height: 38px !important;
            border: 1px solid rgba(198, 40, 40, 0.16) !important;
            border-radius: 12px !important;
            background: rgba(198, 40, 40, 0.07) !important;
            color: #c62828 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            font-size: 13px !important;
            font-weight: 900 !important;
            cursor: pointer !important;
            transition: all .18s ease !important;
        }

        .user-logout-btn:hover {
            background: #c62828 !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 8px 18px rgba(198, 40, 40, 0.22) !important;
        }



        /* ── CORRECCIÓN: EVITAR SCROLL DOBLE EN EL MENÚ ─────
           Solo #dashboardSidebarScroll debe tener scroll vertical.
           El .sidebar de AdminLTE trae su propio scroll y por eso
           aparecían dos barras una al lado de la otra.
        */
        .main-sidebar {
            overflow: hidden !important;
        }

        .main-sidebar .sidebar {
            overflow: hidden !important;
            height: auto !important;
            max-height: none !important;
        }

        #dashboardSidebarScroll {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-gutter: stable !important;
        }

        #dashboardSidebarScroll nav {
            overflow: visible !important;
        }

        #dashboardSidebarScroll::-webkit-scrollbar {
            width: 8px !important;
        }

        #dashboardSidebarScroll::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.06) !important;
            border-radius: 999px !important;
        }

        #dashboardSidebarScroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.45) !important;
            border-radius: 999px !important;
            border: 2px solid rgba(18,54,116,0.35) !important;
        }

        #dashboardSidebarScroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255,210,31,0.75) !important;
        }

        /* ── MODAL DE SESIÓN ─────────────────────────────── */
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
    
        /* ── AJUSTE SUBMENÚ SECRETARÍA ───────────────────── */
        .dashboard-menu .nav-treeview {
            padding: 4px 0 4px 0 !important;
        }

        .dashboard-menu .nav-treeview .nav-item {
            padding: 0 10px 0 18px !important;
        }

        .dashboard-menu .nav-treeview .nav-link {
            min-height: 42px !important;
            padding: 0 14px !important;
            border-radius: 12px !important;
            font-size: .94rem !important;
            font-weight: 700 !important;
        }

        .dashboard-menu .nav-treeview .nav-icon {
            width: 24px !important;
            margin-right: 10px !important;
            font-size: .78rem !important;
        }

        /* ── CORRECCIÓN FLECHA SUBMENÚ ──────────────────────
           Evita que la flecha de AdminLTE se salga o se vea rara
           cuando el menú se acerca, se reduce o se cambia el zoom.
        */
        .dashboard-menu .nav-item.has-treeview > .nav-link {
            position: relative !important;
            overflow: hidden !important;
            padding-right: 14px !important;
        }

        .dashboard-menu .nav-item.has-treeview > .nav-link .menu-parent-label {
            width: 100% !important;
            min-width: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
            margin: 0 !important;
            line-height: 1.18 !important;
        }

        .dashboard-menu .nav-item.has-treeview > .nav-link .menu-label-text {
            display: block !important;
            min-width: 0 !important;
            flex: 1 1 auto !important;
            white-space: normal !important;
            overflow-wrap: anywhere !important;
        }

        .dashboard-menu .nav-item.has-treeview > .nav-link .menu-caret,
        .dashboard-menu .nav-item.has-treeview > .nav-link .right {
            position: static !important;
            top: auto !important;
            right: auto !important;
            margin-left: 6px !important;
            transform: none !important;
            flex: 0 0 24px !important;
            width: 24px !important;
            height: 24px !important;
            border-radius: 999px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(255,255,255,.12) !important;
            border: 1px solid rgba(255,255,255,.14) !important;
            color: rgba(255,255,255,.88) !important;
            font-size: .78rem !important;
            transition: transform .22s ease, background .18s ease, color .18s ease !important;
        }

        .dashboard-menu .nav-item.has-treeview.menu-open > .nav-link .menu-caret,
        .dashboard-menu .nav-item.has-treeview.menu-open > .nav-link .right {
            transform: rotate(-90deg) !important;
            background: rgba(255,210,31,.18) !important;
            border-color: rgba(255,210,31,.30) !important;
            color: #ffd21f !important;
        }

        .dashboard-menu .nav-item.has-treeview > .nav-link:hover .menu-caret,
        .dashboard-menu .nav-item.has-treeview > .nav-link:hover .right {
            background: rgba(255,255,255,.18) !important;
            color: #ffffff !important;
        }

        body.sidebar-collapse .dashboard-menu .nav-item.has-treeview > .nav-link {
            justify-content: center !important;
            padding-right: 18px !important;
        }

        body.sidebar-collapse .dashboard-menu .nav-item.has-treeview > .nav-link .menu-parent-label {
            display: none !important;
        }


        /* =========================================================
           AJUSTE FINAL IGUAL AL ESTUDIANTE
           Topbar pegada al menú + contenido con margen interno.
        ========================================================= */

        .content-wrapper > .content.dashboard-shell {
            padding: 0 !important;
        }

        .dashboard-shell-body {
            padding: 4px 16px 0 24px !important;
        }

        .dashboard-shell-body .hero-banner {
            margin-top: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .student-topbar {
            position: relative !important;
            z-index: 80 !important;
            min-height: 68px !important;
            margin: 0 0 10px !important;
            padding: 10px 18px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 14px !important;
            overflow: visible !important;
            background:
                radial-gradient(circle at 92% 0%, rgba(255, 210, 31, 0.13), transparent 28%),
                linear-gradient(90deg, #0f3370 0%, #1c4f9d 50%, #174487 100%) !important;
            border-bottom: 3px solid #ffd21f !important;
            border-radius: 0 0 16px 0 !important;
            box-shadow:
                0 10px 24px rgba(8, 35, 78, 0.16),
                inset 0 1px 0 rgba(255,255,255,0.10) !important;
        }

        .student-topbar::before {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            border-radius: 0 0 16px 0 !important;
            background:
                linear-gradient(120deg, rgba(255,255,255,0.12), transparent 34%, rgba(255,255,255,0.04) 72%, transparent),
                linear-gradient(135deg, transparent 0 62%, rgba(9, 43, 105, 0.18) 62% 72%, transparent 72%) !important;
            pointer-events: none !important;
        }

        .student-topbar::after {
            content: "" !important;
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            bottom: -7px !important;
            height: 7px !important;
            background: linear-gradient(180deg, rgba(8,35,78,0.12), transparent) !important;
            pointer-events: none !important;
        }

        .student-topbar-left,
        .student-topbar-right {
            position: relative !important;
            z-index: 2 !important;
        }

        .student-topbar-left {
            display: flex !important;
            align-items: center !important;
            min-width: 0 !important;
        }

        .student-topbar-right {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 8px !important;
            min-width: 0 !important;
        }

        .topbar-left-copy {
            display: flex !important;
            align-items: center !important;
        }

        .topbar-breadcrumb {
            min-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 0 16px !important;
            border-radius: 14px !important;
            background: rgba(255, 255, 255, 0.10) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            color: rgba(255,255,255,0.90) !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.10) !important;
        }

        .topbar-breadcrumb i {
            font-size: 12px !important;
            color: rgba(255,255,255,0.84) !important;
        }

        .topbar-breadcrumb-active {
            color: #ffd21f !important;
            font-weight: 900 !important;
            text-shadow: 0 1px 3px rgba(0,0,0,0.16) !important;
        }

        .topbar-icon-btn {
            position: relative !important;
            width: 42px !important;
            height: 42px !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255,255,255,0.18) !important;
            background: rgba(255,255,255,0.10) !important;
            color: #ffffff !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            transition: all .18s ease !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.10) !important;
        }

        .topbar-icon-btn:hover,
        .student-user-chip:hover {
            background: rgba(255,255,255,0.17) !important;
            border-color: rgba(255,210,31,0.48) !important;
            transform: translateY(-1px) !important;
        }

        .topbar-icon-btn i {
            font-size: 15px !important;
            color: #ffffff !important;
        }

        .topbar-badge {
            position: absolute !important;
            top: -7px !important;
            right: -7px !important;
            min-width: 18px !important;
            height: 18px !important;
            padding: 0 5px !important;
            border-radius: 999px !important;
            background: #e63946 !important;
            color: #ffffff !important;
            font-size: 10px !important;
            font-weight: 900 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 2px solid #1c4f9d !important;
            box-shadow: 0 4px 9px rgba(0,0,0,0.24) !important;
        }

        .topbar-badge.gold {
            background: #ffd21f !important;
            color: #123674 !important;
        }

        .topbar-divider {
            width: 1px !important;
            height: 34px !important;
            margin: 0 5px !important;
            background: rgba(255,255,255,0.20) !important;
        }

        .student-user-chip {
            min-height: 44px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 5px 14px 5px 7px !important;
            border-radius: 15px !important;
            border: 1px solid rgba(255,255,255,0.18) !important;
            background: rgba(255,255,255,0.11) !important;
            color: #ffffff !important;
            cursor: pointer !important;
            transition: all .18s ease !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.10) !important;
        }

        .student-user-chip-avatar {
            width: 36px !important;
            height: 36px !important;
            border-radius: 13px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #ffd21f !important;
            color: #123674 !important;
            font-weight: 900 !important;
            font-size: 13px !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.18) !important;
        }

        .student-user-chip-info {
            display: flex !important;
            flex-direction: column !important;
            line-height: 1.08 !important;
            min-width: 0 !important;
        }

        .student-user-chip-name {
            max-width: 190px !important;
            color: #ffffff !important;
            font-size: 12.5px !important;
            font-weight: 900 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .student-user-chip-role {
            margin-top: 3px !important;
            color: rgba(255,255,255,0.74) !important;
            font-size: 10.5px !important;
            font-weight: 800 !important;
        }

        .student-user-chip-arrow {
            font-size: 11px !important;
            color: rgba(255,255,255,0.78) !important;
        }

        .topbar-dropdown {
            position: absolute !important;
            top: calc(100% + 12px) !important;
            right: 0 !important;
            width: 330px !important;
            max-width: calc(100vw - 24px) !important;
            background: #ffffff !important;
            display: none !important;
            z-index: 5000 !important;
            border-radius: 18px !important;
            border: 1px solid rgba(18, 54, 116, 0.14) !important;
            box-shadow: 0 22px 48px rgba(8, 35, 78, 0.26) !important;
            overflow: hidden !important;
        }

        .topbar-dropdown.show {
            display: block !important;
        }

        .user-profile-dropdown {
            width: 315px !important;
            border-radius: 18px !important;
            border: 1px solid rgba(18, 54, 116, 0.14) !important;
            background: #ffffff !important;
            box-shadow: 0 22px 48px rgba(8, 35, 78, 0.26) !important;
            overflow: hidden !important;
        }

        .user-dropdown-header {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 16px 15px !important;
            background:
                radial-gradient(circle at 92% 0%, rgba(255, 210, 31, 0.22), transparent 35%),
                linear-gradient(135deg, #123674 0%, #1c4f9d 58%, #174487 100%) !important;
            border-bottom: 3px solid #ffd21f !important;
            color: #ffffff !important;
        }

        .user-dropdown-avatar {
            position: relative !important;
            z-index: 2 !important;
            width: 46px !important;
            height: 46px !important;
            border-radius: 15px !important;
            background: #ffd21f !important;
            color: #123674 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 16px !important;
            font-weight: 900 !important;
            flex: 0 0 46px !important;
            box-shadow: 0 7px 16px rgba(0,0,0,0.20) !important;
        }

        .main-sidebar,
        .main-sidebar .sidebar,
        #dashboardSidebarScroll,
        #dashboardSidebarScroll nav {
            overflow-x: hidden !important;
        }

        #dashboardSidebarScroll {
            width: 100% !important;
        }

        .dashboard-menu,
        .dashboard-menu.nav,
        .dashboard-menu.nav-sidebar,
        .dashboard-menu.flex-column {
            width: 100% !important;
            max-width: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            justify-content: flex-start !important;
        }

        .dashboard-menu .nav-item {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 auto !important;
            display: block !important;
            float: none !important;
            clear: both !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }

        .dashboard-menu .nav-link {
            width: 100% !important;
            max-width: 100% !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            flex-wrap: nowrap !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }

        .dashboard-menu .nav-link .nav-icon {
            flex: 0 0 34px !important;
            width: 34px !important;
            min-width: 34px !important;
            max-width: 34px !important;
            margin-left: 0 !important;
            margin-right: 14px !important;
            text-align: center !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .dashboard-menu .nav-link p {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            max-width: 100% !important;
            display: block !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        body.sidebar-collapse .dashboard-menu .nav-link {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        body.sidebar-collapse .dashboard-menu .nav-link .nav-icon {
            margin-right: 0 !important;
        }

        body.sidebar-collapse .dashboard-menu .nav-link p {
            display: none !important;
        }

        @media (max-width: 768px) {
            .dashboard-shell-body {
                padding: 4px 12px 0 18px !important;
            }

            .student-topbar {
                min-height: auto !important;
                flex-direction: column !important;
                align-items: stretch !important;
                margin: 0 0 10px !important;
                padding: 10px !important;
            }

            .student-topbar-right {
                justify-content: flex-end !important;
                flex-wrap: wrap !important;
            }

            .topbar-breadcrumb {
                width: fit-content !important;
                max-width: 100% !important;
            }

            .student-user-chip-name {
                max-width: 130px !important;
            }
        }

    </style>
</head>

<body class="hold-transition dashboard-body">
<div class="wrapper">

    {{-- ── NAV MOBILE ──────────────────────────────────────── --}}
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

    {{-- ── SIDEBAR ──────────────────────────────────────────── --}}
    <aside id="studentSidebar" class="main-sidebar elevation-4">
        <div class="sidebar-bg"
             style="background-image: url('{{ asset('images/Edificio2.jpeg') }}');"></div>
        <div class="sidebar-overlay"></div>

        {{-- Logo --}}
        <a href="{{ $dashboardUrl }}" class="brand-link">
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

            {{-- ══ BLOQUE CONTROL ══ --}}
            <div class="sidebar-control-block d-none d-lg-flex">

                <button type="button"
                        id="sidebarToggleBtn"
                        class="sidebar-toggle-inner"
                        title="Colapsar / Expandir menú">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="sidebar-control-info">
                    <span class="sidebar-control-label">Tamaño del menú</span>
                    <div class="sidebar-size-btns">
                        <button type="button" id="sidebarSizeDown"
                                class="sidebar-size-btn" title="Reducir">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" id="sidebarSizeReset"
                                class="sidebar-size-btn" title="Normal">
                            Normal
                        </button>
                        <button type="button" id="sidebarSizeUp"
                                class="sidebar-size-btn" title="Ampliar">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

            </div>

            {{-- ── MENÚ ──────────────────────────────────────── --}}
            <div id="dashboardSidebarScroll" class="dashboardSidebarScroll">
                <nav class="mt-2">

                    <ul class="nav nav-pills nav-sidebar flex-column dashboard-menu"
                        data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ $dashboardUrl }}" class="nav-link {{ $dashboardActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-house"></i>
                                <p>Inicio</p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview {{ $menuRevisionOpen ? 'menu-open' : '' }}">
                            <a href="javascript:void(0)" class="nav-link {{ $menuRevisionOpen ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-circle-check"></i>
                                <p class="menu-parent-label">
                                    <span class="menu-label-text">Revisión de documentos</span>
                                    <i class="right fas fa-angle-left menu-caret"></i>
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

    {{-- ── HANDLE RESIZE ────────────────────────────────────── --}}
    <div id="sidebarResizeHandle"
         class="sidebar-resize-handle d-none d-lg-block"
         title="Arrastra para ajustar el ancho"></div>

    {{-- ── CONTENIDO ────────────────────────────────────────── --}}
    <div class="content-wrapper">
        <section class="content dashboard-shell">

    {{-- ══ TOPBAR ══════════════════════════════════════════ --}}
    <div class="student-topbar">

        <div class="student-topbar-left">
            <div class="topbar-left-copy">
                <div class="topbar-breadcrumb">
                    <i class="fas fa-house"></i>
                    <span>Inicio</span>
                    <i class="fas fa-chevron-right"></i>
                    <span class="topbar-breadcrumb-active">{{ $pageTitle }}</span>
                </div>
            </div>
        </div>

        <div class="student-topbar-right">

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
                <button class="student-user-chip" id="btnUser" title="Mi perfil">
                    <div class="student-user-chip-avatar">{{ $initials }}</div>
                    <div class="student-user-chip-info">
                        <span class="student-user-chip-name">{{ $displayName }}</span>
                        <span class="student-user-chip-role">{{ $displayRole }}</span>
                    </div>
                    <i class="fas fa-chevron-down student-user-chip-arrow"></i>
                </button>

                <div class="topbar-dropdown align-right user-profile-dropdown" id="dropUser">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">{{ $initials }}</div>
                        <div class="user-dropdown-info">
                            <strong>{{ $displayName }}</strong>
                            <span>{{ $correoInstitucional }}</span>
                            <small>{{ $displayRole }}</small>
                        </div>
                    </div>

                    <div class="user-dropdown-body">
                        <a href="#" class="user-dropdown-option">
                            <span class="user-dropdown-option-icon">
                                <i class="fas fa-user"></i>
                            </span>
                            <span>
                                <strong>Mi perfil</strong>
                                <small>Ver información personal</small>
                            </span>
                            <i class="fas fa-chevron-right user-dropdown-option-arrow"></i>
                        </a>
                    </div>

                    <div class="user-dropdown-footer">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="user-logout-btn">
                                <i class="fas fa-right-from-bracket"></i>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

            <div class="dashboard-shell-body">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2026 PumaGestión – UNAH</strong>
    </footer>

</div>

{{-- ── MODAL DE SESIÓN ───────────────────────────────────── --}}
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
                    <span>Se cerrará en <span id="sessionCountdownText">60</span> segundos</span>
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

{{-- ── SCRIPTS ──────────────────────────────────────────────── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdownPairs = [
        ['btnNotif', 'dropNotif'],
        ['btnMsg', 'dropMsg'],
        ['btnUser', 'dropUser'],
    ];

    function closeTopbarDropdowns(exceptId = null) {
        dropdownPairs.forEach(([_, dropdownId]) => {
            if (dropdownId !== exceptId) {
                document.getElementById(dropdownId)?.classList.remove('show');
            }
        });
    }

    dropdownPairs.forEach(([buttonId, dropdownId]) => {
        const button = document.getElementById(buttonId);
        const dropdown = document.getElementById(dropdownId);

        if (!button || !dropdown) return;

        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = dropdown.classList.contains('show');
            closeTopbarDropdowns(dropdownId);
            dropdown.classList.toggle('show', !isOpen);
        });

        dropdown.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', function () {
        closeTopbarDropdowns();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeTopbarDropdowns();
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const root         = document.documentElement;
    const body         = document.body;
    const toggleBtn    = document.getElementById('sidebarToggleBtn');
    const btnDown      = document.getElementById('sidebarSizeDown');
    const btnReset     = document.getElementById('sidebarSizeReset');
    const btnUp        = document.getElementById('sidebarSizeUp');
    const resizeHandle = document.getElementById('sidebarResizeHandle');

    const STORAGE_WIDTH_KEY    = 'student_sidebar_width';
    const STORAGE_COLLAPSE_KEY = 'student_sidebar_collapsed';

    const MIN_WIDTH     = 280;
    const MAX_WIDTH     = 460;
    const DEFAULT_WIDTH = 350;
    const STEP          = 20;

    let isResizing = false;

    function clamp(v, min, max) { return Math.min(Math.max(v, min), max); }

    function applyWidth(w) {
        root.style.setProperty('--student-sidebar-width', clamp(w, MIN_WIDTH, MAX_WIDTH) + 'px');
    }

    function saveWidth(w) {
        localStorage.setItem(STORAGE_WIDTH_KEY, String(clamp(w, MIN_WIDTH, MAX_WIDTH)));
    }

    function getSavedWidth() {
        const v = parseInt(localStorage.getItem(STORAGE_WIDTH_KEY), 10);
        return Number.isFinite(v) ? clamp(v, MIN_WIDTH, MAX_WIDTH) : DEFAULT_WIDTH;
    }

    function setCollapsed(collapsed) {
        body.classList.toggle('sidebar-collapse', collapsed);
        localStorage.setItem(STORAGE_COLLAPSE_KEY, collapsed ? '1' : '0');
        setTimeout(ajustarSidebarScroll, 320);
        setTimeout(() => window.dispatchEvent(new Event('resize')), 320);
    }

    function getSavedCollapsed() {
        return localStorage.getItem(STORAGE_COLLAPSE_KEY) === '1';
    }

    applyWidth(getSavedWidth());
    setCollapsed(getSavedCollapsed());

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            setCollapsed(!body.classList.contains('sidebar-collapse'));
        }, true);
    }

    if (btnDown) {
        btnDown.addEventListener('click', function () {
            const w = clamp(getSavedWidth() - STEP, MIN_WIDTH, MAX_WIDTH);
            applyWidth(w);
            saveWidth(w);
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function () {
            applyWidth(DEFAULT_WIDTH);
            saveWidth(DEFAULT_WIDTH);
        });
    }

    if (btnUp) {
        btnUp.addEventListener('click', function () {
            const w = clamp(getSavedWidth() + STEP, MIN_WIDTH, MAX_WIDTH);
            applyWidth(w);
            saveWidth(w);
        });
    }

    if (resizeHandle) {
        resizeHandle.addEventListener('mousedown', function (e) {
            if (body.classList.contains('sidebar-collapse')) return;
            isResizing = true;
            body.classList.add('sidebar-resizing');
            e.preventDefault();
        });

        document.addEventListener('mousemove', function (e) {
            if (!isResizing) return;
            applyWidth(e.clientX);
        });

        document.addEventListener('mouseup', function (e) {
            if (!isResizing) return;
            isResizing = false;
            body.classList.remove('sidebar-resizing');
            const w = clamp(e.clientX, MIN_WIDTH, MAX_WIDTH);
            applyWidth(w);
            saveWidth(w);
        });
    }

    function ajustarSidebarScroll() {
        const brand      = document.querySelector('.main-sidebar .brand-link');
        const control    = document.querySelector('.sidebar-control-block');
        const scrollArea = document.getElementById('dashboardSidebarScroll');
        if (!brand || !scrollArea) return;

        const brandH   = brand.offsetHeight;
        const controlH = control ? control.offsetHeight : 0;
        const libre    = window.innerHeight - brandH - controlH;

        scrollArea.style.height    = Math.max(libre, 120) + 'px';
        scrollArea.style.maxHeight = Math.max(libre, 120) + 'px';
    }

    ajustarSidebarScroll();
    window.addEventListener('resize', ajustarSidebarScroll);
    window.addEventListener('load', ajustarSidebarScroll);

    new MutationObserver(() => setTimeout(ajustarSidebarScroll, 50))
        .observe(body, { attributes: true, attributeFilter: ['class'] });

    const pushMenu = document.querySelector('[data-widget="pushmenu"]');
    if (pushMenu) {
        pushMenu.addEventListener('click', () => {
            setTimeout(() => {
                ajustarSidebarScroll();
                window.dispatchEvent(new Event('resize'));
            }, 350);
        });
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth < 992) return;
        applyWidth(getSavedWidth());
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | TIEMPOS
    |--------------------------------------------------------------------------
    | WARNING_TIME_MS = cuándo aparece el mensaje
    | LOGOUT_TIME_MS  = cuándo se cierra la sesión
    |--------------------------------------------------------------------------
    */
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


