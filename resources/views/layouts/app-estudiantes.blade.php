@php
    use Illuminate\Support\Facades\DB;

    $user = auth()->user();

    $displayName = 'Alumno';
    $displayRole = 'Alumno';

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

    $parts    = preg_split('/\s+/', trim($displayName));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        if (!empty($part)) $initials .= strtoupper(mb_substr($part, 0, 1));
    }
    if ($initials === '') $initials = 'A';
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PumaGestión – @yield('titulo', 'Portal Estudiantil')</title>

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
        <a href="{{ route('dashboard') }}" class="brand-link">
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
                            <a href="{{ route('dashboard') }}"
                               class="nav-link {{ request()->routeIs('dashboard') || request()->is('dashboard*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Panel institucional</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('/equivalencias') }}"
                               class="nav-link {{ request()->is('equivalencias') || request()->is('equivalencias*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shuffle"></i>
                                <p>Equivalencias</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('mis.tramites') }}"
                               class="nav-link {{ request()->routeIs('mis.tramites') || request()->is('mis-tramites') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-folder-open"></i>
                                <p>Mis trámites</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('configuracion.index') }}"
                               class="nav-link {{ request()->routeIs('configuracion.index') || request()->is('configuracion') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gear"></i>
                                <p>Configuración</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('/soporte') }}"
                               class="nav-link {{ request()->is('soporte') || request()->is('soporte*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-headset"></i>
                                <p>Soporte</p>
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
            @yield('content')
        </section>
    </div>

    <footer class="main-footer">
        <strong>© 2026 PumaGestión – UNAH</strong>
    </footer>

</div>

{{-- ── SCRIPTS ──────────────────────────────────────────────── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>

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
</body>
</html>