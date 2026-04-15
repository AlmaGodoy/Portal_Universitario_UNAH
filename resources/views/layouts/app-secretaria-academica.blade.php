@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Route;

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

    $correoInstitucional =
        $user->email
        ?? $user->correo_institucional
        ?? optional($user->persona)->correo_institucional
        ?? 'secretaria.academica@unah.hn';

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

    /*
    |--------------------------------------------------------------------------
    | RUTAS SEGURAS
    |--------------------------------------------------------------------------
    */
    $dashboardUrl = Route::has('empleado.dashboard')
        ? route('empleado.dashboard')
        : (Route::has('dashboard') ? route('dashboard') : 'javascript:void(0)');

    $seguridadUrl = Route::has('seguridad.index')
        ? route('seguridad.index')
        : 'javascript:void(0)';

    $auditoriaUrl = Route::has('auditoria')
        ? route('auditoria')
        : 'javascript:void(0)';

    $bitacoraUrl = Route::has('bitacora.index')
        ? route('bitacora.index')
        : 'javascript:void(0)';

    $reportesUrl = Route::has('reporte.tramites.secretaria_general.vista')
        ? route('reporte.tramites.secretaria_general.vista')
        : 'javascript:void(0)';

    $soporteUrl = Route::has('soporte.vista')
        ? route('soporte.vista')
        : 'javascript:void(0)';

    $configuracionUrl = Route::has('configuracion.index')
        ? route('configuracion.index')
        : 'javascript:void(0)';

    /*
    |--------------------------------------------------------------------------
    | MENÚS ACTIVOS
    |--------------------------------------------------------------------------
    */
    $dashboardActive = request()->routeIs('empleado.dashboard') || request()->routeIs('dashboard');
    $seguridadActive = request()->routeIs('seguridad.index') || request()->is('seguridad*');
    $auditoriaActive = request()->routeIs('auditoria') || request()->routeIs('auditoria.*');
    $bitacoraActive = request()->routeIs('bitacora.*');
    $reportesActive = request()->routeIs('reporte.tramites.secretaria_general.vista');
    $soporteActive = request()->routeIs('soporte.vista') || request()->is('soporte') || request()->is('api/soporte*');
    $configuracionActive = request()->routeIs('configuracion.index') || request()->is('configuracion') || request()->is('configuracion*');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                            <a href="{{ $dashboardUrl }}" class="nav-link {{ $dashboardActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $seguridadUrl }}" class="nav-link {{ $seguridadActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-halved"></i>
                                <p>Seguridad</p>
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
                            <a href="{{ $reportesUrl }}" class="nav-link {{ $reportesActive ? 'active' : '' }}">
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
                            <a href="{{ $soporteUrl }}" class="nav-link {{ $soporteActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Respaldo</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ $configuracionUrl }}" class="nav-link {{ $configuracionActive ? 'active' : '' }}">
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