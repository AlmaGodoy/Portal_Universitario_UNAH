<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PumaGestión – UNAH</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

  {{-- Vite: incluye app.css (que importa dashboard.css) y app.js (que importa dashboard.js) --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<!-- ══ NAVBAR ══ -->
<nav class="main-header navbar navbar-expand navbar-dark">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>
  <a class="navbar-brand d-flex align-items-center" href="#">
    <div class="nav-logo-circle">
      <img src="{{ asset('images/logo_puma.jpeg') }}" alt="PumaGestión Logo">
    </div>
    <span class="nav-brand-title">PumaGestión</span>
  </a>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-search fa-fw"></i></a></li>
    <li class="nav-item" style="position:relative;">
      <a class="nav-link" href="#"><i class="far fa-bell fa-fw"></i>
        <span class="navbar-badge-custom" id="notif-count">0</span>
      </a>
    </li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="far fa-envelope fa-fw"></i></a></li>
  </ul>
</nav>

<!-- ══ SIDEBAR ══ -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="#" class="brand-link">
    <div class="sidebar-brand-logo">
      <img src="{{ asset('images/logo_puma.jpeg') }}" alt="PumaGestión Logo">
    </div>
    <span class="brand-text">PumaGestión</span>
  </a>
  <div class="sidebar">
    <div class="sidebar-user-top">
      <div class="su-icon" id="sidebar-initials">AP</div>
      <div>
        <div class="su-name" id="sidebar-username">Alexander Pierce</div>
        <div class="su-s" id="sidebar-role">Administrador</div>
      </div>
    </div>
    <div class="su-search-row">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Buscar..." id="sidebar-search">
    </div>
    <nav>
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
        <li class="nav-item">
          <a href="#" class="nav-link active">
            <i class="nav-icon fas fa-building"></i><p>Panel Institucional</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-exchange-alt"></i><p>Cambio de Carrera</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('cancelacion.index') }}" class="nav-link">
            <i class="nav-icon fas fa-ban"></i>
            <p style="font-size:.8rem;line-height:1.2;white-space:normal;">Cancelación Excepcional de Clases</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-clipboard-list"></i><p>Gestión de Trámites</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-search-dollar"></i><p>Auditoría</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-chart-bar"></i><p>Reportes</p>
          </a>
        </li>
        <li class="nav-item">
    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
      @csrf
      <button type="submit" class="nav-link btn btn-link text-left w-100" style="border:none;background:none;">
        <i class="nav-icon fas fa-sign-out-alt"></i>
        <p>Cerrar sesión</p>
      </button>
    </form>
  </li>
      </ul>
    </nav>
  </div>
</aside>

<!-- ══ CONTENT WRAPPER ══ -->
<div class="content-wrapper">
  <div style="padding:8px 18px 0;">
    <div class="breadcrumb-wrap">
      <i class="fas fa-home" style="color:var(--blue2);"></i>
      <a href="#">Inicio</a>
      <span style="color:#bbb;"><i class="fas fa-chevron-right" style="font-size:.65rem;"></i></span>
      <span>Panel Institucional</span>
    </div>
  </div>

  <section class="content" style="padding:0 18px 80px;">

    <!-- FACULTY BANNER -->
    <div class="faculty-banner">
      <div class="fb-bg"></div>
      <div class="fb-photo-right"></div>
      <div class="fb-content">
        <div class="fb-logo-wrap">
          <div class="fb-logo-glow"></div>
          <div class="fb-logo-circle">
            <img src="{{ asset('images/logo_puma.jpeg') }}" alt="PumaGestión Logo">
          </div>
        </div>
        <div>
          <div class="fb-title-main">Facultad de Ciencias Económicas,<br>Administrativas y Contables</div>
          <div class="fb-subtitle">FCEAC · UNAH · PumaGestión</div>
        </div>
      </div>
    </div>

    <!-- SEARCH BAR + USER -->
    <div class="top-search-row">
      <div class="tsr-input-wrap">
        <input type="text" placeholder="Buscar..." id="top-search">
      </div>
      <div class="tsr-user">
        <div class="tsr-avatar" id="top-initials">AP</div>
        <span class="tsr-name" id="top-username">Alexander Pierce</span>
        <div style="display:flex;align-items:center;gap:6px;">
          <i class="fas fa-chevron-down" style="color:#aaa;font-size:.78rem;"></i>
          <button class="tsr-btn"><i class="fas fa-th-large"></i></button>
          <i class="fas fa-chevron-down" style="color:#aaa;font-size:.78rem;"></i>
        </div>
      </div>
    </div>

    <!-- STAT CARDS -->
    <div class="info-boxes-row">
      <div class="info-box-custom ibc-gold">
        <i class="fas fa-user-graduate ibc-icon-bg"></i>
        <div class="ibc-top"><i class="fas fa-user-graduate ibc-icon-front"></i>
          <div><div class="ibc-num" id="cnt-estudiantes">0</div><div class="ibc-label">Estudiantes Inscritos</div></div>
        </div>
        <div class="ibc-footer" id="footer-estudiantes">Sin registros aún</div>
      </div>
      <div class="info-box-custom ibc-blue">
        <i class="fas fa-user-tie ibc-icon-bg"></i>
        <div class="ibc-top"><i class="fas fa-user-tie ibc-icon-front"></i>
          <div><div class="ibc-num" id="cnt-secretarios">0</div><div class="ibc-label">Secretarios Registrados</div></div>
        </div>
        <div class="ibc-footer" id="footer-secretarios">Sin registros aún</div>
      </div>
      <div class="info-box-custom ibc-green">
        <i class="fas fa-file-alt ibc-icon-bg"></i>
        <div class="ibc-top"><i class="fas fa-file-alt ibc-icon-front"></i>
          <div><div class="ibc-num" id="cnt-tramites">0</div><div class="ibc-label">Trámites en Proceso</div></div>
        </div>
        <div class="ibc-footer" id="footer-tramites">Sin trámites aún</div>
      </div>
      <div class="info-box-custom ibc-cyan">
        <i class="fas fa-clock ibc-icon-bg"></i>
        <div class="ibc-top"><i class="fas fa-clock ibc-icon-front"></i>
          <div><div class="ibc-num" id="cnt-pendientes">0</div><div class="ibc-label">Solicitudes Pendientes</div></div>
        </div>
        <div class="ibc-footer" id="footer-pendientes">Sin solicitudes aún</div>
      </div>
      <div class="info-box-custom ibc-dark">
        <i class="fas fa-calendar-check ibc-icon-bg"></i>
        <div class="ibc-top"><i class="fas fa-calendar-check ibc-icon-front"></i>
          <div><div class="ibc-num" id="cnt-aprobados">0</div><div class="ibc-label">Trámites Aprobados</div></div>
        </div>
        <div class="ibc-footer" id="footer-aprobados">Sin aprobados aún</div>
      </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row mb-3">
      <div class="col-lg-5 mb-3">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <span>Trámites Ingresados por Mes</span>
            <div class="dch-tools"><button class="btn" title="Exportar" onclick="exportarDatos()"><i class="fas fa-download"></i></button></div>
          </div>
          <div class="card-body" style="padding:12px 14px;">
            <div class="chart-legend mb-2">
              <span><span class="cl-dot" style="background:var(--blue3);"></span>Ingresados</span>
              <span><span class="cl-dot" style="background:var(--gold);"></span>Pendientes</span>
              <span><span class="cl-dot" style="background:var(--green);"></span>Aprobados</span>
            </div>
            <div style="height:200px;" id="bar-wrap"><canvas id="barChart"></canvas></div>
            <div id="bar-empty" class="empty-state" style="display:none;"><i class="fas fa-chart-bar"></i><p>La gráfica se llenará cuando se ingresen trámites</p></div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 mb-3">
        <div class="dash-card h-100">
          <div class="dash-card-header"><span>Estado de Trámites</span></div>
          <div class="card-body d-flex flex-column align-items-center justify-content-center" style="padding:14px;">
            <div style="position:relative;width:190px;height:190px;" id="pie-wrap"><canvas id="pieChart"></canvas></div>
            <div id="pie-empty" class="empty-state" style="display:none;"><i class="fas fa-chart-pie"></i><p>Sin datos aún</p></div>
            <div class="pie-leg">
              <div class="pl-item"><div class="pl-dot" style="background:var(--gold);"></div><span id="pl-pend">Pendiente: 0</span></div>
              <div class="pl-item"><div class="pl-dot" style="background:var(--blue2);"></div><span id="pl-rev">Revisión: 0</span></div>
              <div class="pl-item"><div class="pl-dot" style="background:var(--green);"></div><span id="pl-apr">Aprobado: 0</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
          <div class="dash-card-header"><span>Trámites por Tipo</span></div>
          <div class="card-body" style="padding:12px 14px;">
            <div style="height:200px;" id="line-wrap"><canvas id="lineChart"></canvas></div>
            <div id="line-empty" class="empty-state" style="display:none;"><i class="fas fa-chart-line"></i><p>Sin datos para mostrar</p></div>
            <div class="chart-legend mt-2" style="flex-wrap:wrap;gap:6px;">
              <span><span class="cl-dot cl-circle" style="background:#0e2348;"></span>Cambio Carrera</span>
              <span><span class="cl-dot cl-circle" style="background:var(--cyan);"></span>Cancelación</span>
              <span><span class="cl-dot cl-circle" style="background:var(--green);"></span>Otros</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLE + EVENTS -->
    <div class="row">
      <div class="col-lg-8 mb-3">
        <div class="dash-card">
          <div class="dash-card-header">
            <span>Seguimiento de Trámites</span>
            <div class="dch-tools">
              <button class="btn" onclick="abrirModal()"><i class="fas fa-plus" style="color:var(--green);"></i> Nuevo</button>
              <button class="btn" onclick="limpiarTodo()"><i class="fas fa-trash" style="color:#e55;"></i></button>
            </div>
          </div>
          <div id="tabla-wrap"></div>
        </div>
      </div>
      <div class="col-lg-4 mb-3">
        <div class="dash-card">
          <div class="dash-card-header">
            <span>Próximos Eventos <span style="color:var(--blue2);">Académicos</span></span>
            <a href="#" style="font-size:.75rem;color:var(--blue2);font-weight:700;text-decoration:none;" onclick="abrirModalEvento();return false;">+ Agregar</a>
          </div>
          <div id="eventos-wrap"></div>
        </div>
      </div>
    </div>

  </section>
</div>

<footer class="main-footer">
  <strong>© 2024 PumaGestión – UNAH</strong> – Facultad de Ciencias Económicas, Administrativas y Contables. Todos los derechos reservados.
</footer>

<button class="btn-add-tramite" onclick="abrirModal()" title="Nuevo trámite">
  <i class="fas fa-plus"></i>
</button>

</div><!-- /wrapper -->

<!-- ══ MODAL TRÁMITE ══ -->
<div class="modal fade" id="modalTramite" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Nuevo Trámite</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Tipo de Trámite *</label>
            <select class="form-control" id="f-tipo">
              <option value="">— Seleccionar —</option>
              <option value="Cambio de Carrera">Cambio de Carrera</option>
              <option value="Cancelación Excepcional">Cancelación Excepcional de Clases</option>
              <option value="Gestión de Trámites">Gestión de Trámites</option>
              <option value="Auditoría">Auditoría</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Carrera / Facultad *</label>
            <select class="form-control" id="f-carrera">
              <option value="">— Seleccionar —</option>
              <option>Economía</option><option>Administración</option>
              <option>Contabilidad</option><option>Finanzas</option><option>Otra</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Descripción del Trámite *</label>
            <input type="text" class="form-control" id="f-descripcion" placeholder="Ej: Solicitud de cambio a Administración">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Estado *</label>
            <select class="form-control" id="f-estado">
              <option value="Pendiente">Pendiente</option>
              <option value="Revisión">En Revisión</option>
              <option value="Aprobado">Aprobado</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Nombre del Solicitante *</label>
            <input type="text" class="form-control" id="f-solicitante" placeholder="Nombre completo">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Rol del Solicitante *</label>
            <select class="form-control" id="f-rol">
              <option value="Estudiante">Estudiante</option>
              <option value="Docente">Docente</option>
              <option value="Secretario">Secretario</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" class="form-control" id="f-fecha">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Número de Cuenta (opcional)</label>
            <input type="text" class="form-control" id="f-cuenta" placeholder="Ej: 20211234567">
          </div>
        </div>
        <div id="modal-error" class="alert alert-danger" style="display:none;font-size:.84rem;"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button class="btn btn-gold" onclick="guardarTramite()"><i class="fas fa-save mr-1"></i>Guardar Trámite</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ MODAL EVENTO ══ -->
<div class="modal fade" id="modalEvento" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-calendar-plus mr-2"></i>Nuevo Evento Académico</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre del Evento *</label>
          <input type="text" class="form-control" id="ev-titulo" placeholder="Ej: Taller de Contabilidad">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3"><label class="form-label">Hora *</label><input type="time" class="form-control" id="ev-hora"></div>
          <div class="col-md-6 mb-3"><label class="form-label">Fecha *</label><input type="date" class="form-control" id="ev-fecha"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Lugar</label>
          <input type="text" class="form-control" id="ev-lugar" placeholder="Ej: Aula 204 – Edificio B">
        </div>
        <div class="mb-3">
          <label class="form-label">Color</label>
          <select class="form-control" id="ev-color">
            <option value="#1a3a6b">Azul (predeterminado)</option>
            <option value="#28a745">Verde</option>
            <option value="#e8b800">Dorado</option>
            <option value="#17a2b8">Cyan</option>
            <option value="#6c757d">Gris</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button class="btn btn-gold" onclick="guardarEvento()"><i class="fas fa-save mr-1"></i>Guardar Evento</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts CDN (antes del cierre de body) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

</body>
</html>
