@php
    // El nombre está en tbl_persona.nombre_persona, relacionada por id_persona
    $user = auth()->user();
    $displayName = 'Usuario';

    if ($user) {
        // Intentar obtener desde la relación persona (si existe en el modelo)
        if ($user->persona && $user->persona->nombre_persona) {
            $displayName = $user->persona->nombre_persona;
        } elseif ($user->id_persona) {
            // Consulta directa a tbl_persona si no hay relación definida en el modelo
            $persona = \DB::table('tbl_persona')
                ->where('id_persona', $user->id_persona)
                ->first();
            if ($persona && $persona->nombre_persona) {
                $displayName = $persona->nombre_persona;
            }
        } elseif ($user->name) {
            $displayName = $user->name;
        }
    }

    // Genera iniciales a partir de las primeras 2 palabras del nombre
    $parts = preg_split('/\s+/', trim($displayName));
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= strtoupper(mb_substr($part, 0, 1));
    }

    if ($initials === '') {
        $initials = 'U';
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PumaGestión – UNAH</title>

    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="hold-transition dashboard-body">
<div class="wrapper">

    {{-- NAVBAR SOLO MÓVIL --}}
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

        <a href="#" class="brand-link">
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
            {{-- Tarjeta de usuario: muestra el nombre real del usuario registrado --}}
            <div class="sidebar-user-card">
                <div class="sidebar-user-avatar">{{ $initials }}</div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ $displayName }}</div>
                    <div class="sidebar-user-role">Administrador</div>
                </div>
            </div>

            <div id="dashboardSidebarScroll" style="overflow-y:auto;overflow-x:hidden;">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column dashboard-menu" data-widget="treeview" role="menu" data-accordion="false">

                        <li class="nav-item">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-building-columns"></i>
                                <p>Panel Institucional</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('cambio-carrera.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-right-left"></i>
                                <p>Cambio de Carrera</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('cancelacion.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-ban"></i>
                                <p>Cancelación Excepcional</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-file-signature"></i>
                                <p>Gestión de Trámites</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-graduation-cap"></i>
                                <p>Gestión Académica</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('/reporte-tramites-vista') }}" class="nav-link {{ request()->is('reporte-tramites-vista') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-column"></i>
                                <p>Reportes</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('seguridad.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Seguridad</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('backup.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Respaldo</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#" class="nav-link">
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

    {{-- BOTÓN TOGGLE SIDEBAR — fuera del aside para que no se oculte al colapsar --}}
    <button id="sidebarToggleBtn" class="sidebar-float-toggle d-none d-lg-flex" type="button" title="Colapsar/Expandir menú">
        <i class="fas fa-chevron-left"></i>
    </button>

    {{-- CONTENIDO --}}
    <div class="content-wrapper">
        <section class="content dashboard-shell">

            {{-- HERO SUPERIOR --}}
            <div class="hero-banner">
                <div class="hero-banner-bg"></div>
                <div class="hero-wave wave-one"></div>
                <div class="hero-wave wave-two"></div>
                <div class="hero-gold-ribbon"></div>

                <div class="hero-photo" style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>

                <div class="hero-content">
                    <div class="hero-top-title">Portal Administrativo UNAH</div>

                    <div class="hero-breadcrumb">
                        <i class="fas fa-house"></i>
                        <span>Inicio</span>
                        <i class="fas fa-angle-right sep"></i>
                        <span>Panel Institucional</span>
                    </div>

                    <div class="hero-faculty-title">
                        FACULTAD DE CIENCIAS ECONÓMICAS,<br>
                        ADMINISTRATIVAS Y CONTABLES
                    </div>
                </div>
            </div>

            {{-- BUSCADOR / USUARIO --}}
            <div class="toolbar-strip">
                <div class="toolbar-left">
                    <div class="toolbar-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar...">
                    </div>
                </div>

                {{-- Toolbar: muestra el nombre real del usuario registrado --}}
                <div class="toolbar-user">
                    <div class="toolbar-user-avatar">{{ $initials }}</div>
                    <div class="toolbar-user-name">{{ $displayName }}</div>
                    <button class="toolbar-circle-btn" type="button">
                        <i class="fas fa-play"></i>
                    </button>
                    <button class="toolbar-circle-btn" type="button">
                        <i class="fas fa-angle-down"></i>
                    </button>
                </div>
            </div>

            {{-- TARJETAS --}}
            <div class="stats-grid">
                <div class="stat-card stat-gold">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-main">
                        <div class="stat-number">3,250</div>
                        <div class="stat-label">Estudiantes Inscritos</div>
                    </div>
                    <div class="stat-side-icon"><i class="fas fa-user"></i></div>
                    <div class="stat-footer">Py Presidents Petra <span>▲ 1.12%</span></div>
                </div>

                <div class="stat-card stat-blue">
                    <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-main">
                        <div class="stat-number">284</div>
                        <div class="stat-label">Docentes Registrados</div>
                    </div>
                    <div class="stat-side-icon"><i class="fas fa-id-card"></i></div>
                    <div class="stat-footer">Por Presidents Petra <span>▲ 3.80%</span></div>
                </div>

                <div class="stat-card stat-green">
                    <div class="stat-icon"><i class="fas fa-file-lines"></i></div>
                    <div class="stat-main">
                        <div class="stat-number">125</div>
                        <div class="stat-label">Trámites en Proceso</div>
                    </div>
                    <div class="stat-side-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-footer">Pendientes del ciclo <span>▲ 3.80%</span></div>
                </div>

                <div class="stat-card stat-cyan">
                    <div class="stat-icon"><i class="fas fa-phone-volume"></i></div>
                    <div class="stat-main">
                        <div class="stat-number">56</div>
                        <div class="stat-label">Solicitudes Pendientes</div>
                    </div>
                    <div class="stat-side-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="stat-footer">By Navario-Perez <span>▲ 1.24%</span></div>
                </div>

                <div class="stat-card stat-deepblue">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-main">
                        <div class="stat-number">56</div>
                        <div class="stat-label">Delito y Roles Pendientes</div>
                    </div>
                    <div class="stat-side-icon"><i class="fas fa-circle-notch"></i></div>
                    <div class="stat-footer">By Ayala <span>▲ 1.24%</span></div>
                </div>
            </div>

            {{-- GRÁFICAS --}}
            <div class="analytics-grid">
                <div class="panel-card panel-large">
                    <div class="panel-header">
                        <h3>Ingresos y Gastos Mensuales</h3>
                        <div class="panel-actions">
                            <i class="far fa-square"></i>
                            <i class="far fa-window-restore"></i>
                            <i class="far fa-file-lines"></i>
                        </div>
                    </div>

                    <div class="panel-body chart-flex">
                        <div class="chart-area-lg">
                            <canvas id="incomeExpensesChart"></canvas>
                        </div>

                        <div class="metric-stack">
                            <div class="metric-item blue">
                                <div class="metric-arrow"><i class="fas fa-angle-right"></i></div>
                                <div>
                                    <div class="metric-big">3,60%</div>
                                    <div class="metric-small">Du oriente de mesidiones</div>
                                </div>
                            </div>

                            <div class="metric-item green">
                                <div class="metric-arrow"><i class="fas fa-arrow-trend-up"></i></div>
                                <div>
                                    <div class="metric-big">280%</div>
                                    <div class="metric-small">Ingresos setairenes</div>
                                </div>
                            </div>

                            <div class="metric-item gold">
                                <div class="metric-arrow"><i class="fas fa-percent"></i></div>
                                <div>
                                    <div class="metric-big">124%</div>
                                    <div class="metric-small">Ingresos adiciondies</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-card panel-pie">
                    <div class="panel-header">
                        <h3>Picaderos</h3>
                        <div class="panel-actions">
                            <i class="fas fa-up-right-and-down-left-from-center"></i>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="chart-area-sm">
                            <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="panel-card panel-line">
                    <div class="panel-header">
                        <h3>Matrícula de Estudiantes por Carrera</h3>
                    </div>
                    <div class="panel-body">
                        <div class="chart-area-md">
                            <canvas id="enrollmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA Y EVENTOS --}}
            <div class="bottom-grid">
                <div class="panel-card">
                    <div class="panel-header">
                        <h3>Seguimiento de Trámites</h3>
                        <div class="panel-actions">
                            <i class="fas fa-magnifying-glass"></i>
                            <i class="fas fa-list"></i>
                            <i class="fas fa-bars"></i>
                        </div>
                    </div>

                    <div class="panel-body no-padding">
                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Trámite</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-graduation-cap type-icon"></i></td>
                                        <td>Economía • Administración Notas</td>
                                        <td><span class="badge-status review">Revisión</span></td>
                                        <td>25/04/2024</td>
                                        <td>Alexander Pierce</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-graduation-cap type-icon"></i></td>
                                        <td>Solicitud Carta</td>
                                        <td><span class="badge-status review">Revisión</span></td>
                                        <td>25/04/2024</td>
                                        <td>Alexander Pierce</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-graduation-cap type-icon"></i></td>
                                        <td>Economía • Ajusti Carta</td>
                                        <td><span class="badge-status review">Revisión</span></td>
                                        <td>25/04/2024</td>
                                        <td>Alexander Pierce</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-graduation-cap type-icon"></i></td>
                                        <td>Cambio de Carrera</td>
                                        <td><span class="badge-status pending">Pendiente</span></td>
                                        <td>24/04/2024</td>
                                        <td>Secretaría</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-graduation-cap type-icon"></i></td>
                                        <td>Cancelación Excepcional</td>
                                        <td><span class="badge-status approved">Aprobado</span></td>
                                        <td>22/04/2024</td>
                                        <td>Coordinación</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel-card events-card">
                    <div class="panel-header">
                        <h3>Próximos Eventos Académicos</h3>
                        <a href="#" class="see-all">Ver todos</a>
                    </div>

                    <div class="panel-body events-list">
                        <div class="event-item">
                            <div class="event-time-box">
                                <i class="fas fa-calendar-days"></i>
                            </div>
                            <div class="event-text">
                                <div class="event-hour">11:00 AM</div>
                                <div class="event-title">Curso de Finanzas Personales</div>
                                <div class="event-meta">Lunes, 29 de abril</div>
                            </div>
                        </div>

                        <div class="event-item">
                            <div class="event-time-box">
                                <i class="fas fa-calendar-days"></i>
                            </div>
                            <div class="event-text">
                                <div class="event-hour">10:00 AM</div>
                                <div class="event-title">Taller de Contabilidad Tributaria</div>
                                <div class="event-meta">Jueves, 3 de mayo</div>
                            </div>
                        </div>

                        <div class="event-item">
                            <div class="event-time-box">
                                <i class="fas fa-calendar-days"></i>
                            </div>
                            <div class="event-text">
                                <div class="event-hour">08:30 AM</div>
                                <div class="event-title">Revisión de Expedientes</div>
                                <div class="event-meta">Viernes, 5 de mayo</div>
                            </div>
                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const commonGrid = {
        color: 'rgba(30, 56, 102, 0.10)',
        drawBorder: false
    };

    const commonTicks = {
        color: '#58657d',
        font: { size: 11 }
    };

    // ─── SIDEBAR TOGGLE (desktop) — bypass total de AdminLTE ────────────────────
    (function () {
        function applyCollapse(collapsed) {
            if (collapsed) {
                document.body.classList.add('sidebar-collapse');
            } else {
                document.body.classList.remove('sidebar-collapse');
            }
        }

        // Restaurar preferencia guardada al cargar
        if (window.innerWidth >= 992) {
            applyCollapse(localStorage.getItem('pgSidebarCollapsed') === '1');
        }

        function bindToggle() {
            const btn = document.getElementById('sidebarToggleBtn');
            if (!btn) return;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const collapsed = !document.body.classList.contains('sidebar-collapse');
                applyCollapse(collapsed);
                localStorage.setItem('pgSidebarCollapsed', collapsed ? '1' : '0');
                // Redibuja Chart.js tras la animación CSS
                setTimeout(() => window.dispatchEvent(new Event('resize')), 320);
            }, true); // capture:true → corre antes que los listeners de AdminLTE
        }

        // Bind inmediato + después de que AdminLTE cargue
        bindToggle();
        window.addEventListener('load', bindToggle);
    })();
    // ────────────────────────────────────────────────────────────────────────────

    const incomeCanvas = document.getElementById('incomeExpensesChart');
    if (incomeCanvas) {
        new Chart(incomeCanvas, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fab', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Ingresos',
                        data: [220, 260, 210, 190, 245, 230, 255],
                        backgroundColor: '#1c4ea0',
                        borderRadius: 4,
                        barThickness: 18
                    },
                    {
                        label: 'Gastos',
                        data: [240, 250, 235, 225, 260, 275, 295],
                        backgroundColor: '#efbe1a',
                        borderRadius: 4,
                        barThickness: 18
                    },
                    {
                        label: 'Balance',
                        data: [150, 185, 170, 145, 195, 210, 205],
                        type: 'line',
                        borderColor: '#8fa0c4',
                        backgroundColor: '#8fa0c4',
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 4,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'start',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            color: '#33415c',
                            font: { size: 12, weight: '600' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: commonTicks
                    },
                    y: {
                        beginAtZero: true,
                        grid: commonGrid,
                        ticks: commonTicks
                    }
                }
            }
        });
    }

    const distributionCanvas = document.getElementById('distributionChart');
    if (distributionCanvas) {
        new Chart(distributionCanvas, {
            type: 'pie',
            data: {
                labels: ['LTK', 'Bieestar', 'Social', 'Elvricul', 'Economía', 'Otros'],
                datasets: [{
                    data: [12, 18, 15, 35, 20, 10],
                    backgroundColor: [
                        '#163f8f',
                        '#4aa3ff',
                        '#f0c11e',
                        '#78b94e',
                        '#295ab2',
                        '#7db6ff'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            color: '#4b5872',
                            font: { size: 10 }
                        }
                    }
                }
            }
        });
    }

    const enrollmentCanvas = document.getElementById('enrollmentChart');
    if (enrollmentCanvas) {
        new Chart(enrollmentCanvas, {
            type: 'line',
            data: {
                labels: ['Jun', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Adlanta',
                        data: [360, 520, 540, 560, 590, 650, 760],
                        borderColor: '#193f96',
                        backgroundColor: '#193f96',
                        tension: 0.35,
                        pointRadius: 3
                    },
                    {
                        label: 'Economía',
                        data: [310, 430, 460, 500, 680, 720, 770],
                        borderColor: '#e0ac11',
                        backgroundColor: '#e0ac11',
                        tension: 0.35,
                        pointRadius: 3
                    },
                    {
                        label: 'Contaduría',
                        data: [190, 290, 300, 340, 390, 470, 720],
                        borderColor: '#6ea6e5',
                        backgroundColor: '#6ea6e5',
                        tension: 0.35,
                        pointRadius: 3
                    },
                    {
                        label: 'Administración',
                        data: [110, 200, 210, 220, 230, 310, 740],
                        borderColor: '#2a79cc',
                        backgroundColor: '#2a79cc',
                        tension: 0.35,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#4b5872',
                            boxWidth: 10,
                            font: { size: 11 }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(30, 56, 102, 0.08)' },
                        ticks: commonTicks
                    },
                    y: {
                        beginAtZero: true,
                        grid: commonGrid,
                        ticks: commonTicks
                    }
                }
            }
        });
    }
});
</script>
</body>
</html>
