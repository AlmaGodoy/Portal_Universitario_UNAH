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
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/secre_carrera.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  {{-- ===== NAVBAR ===== --}}
  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>

    <a class="navbar-brand d-flex align-items-center" href="#">
      <div class="nav-logo-circle">
        <img src="{{ asset('images/Logo.png') }}" alt="PumaGestión Logo">
      </div>
      <span class="nav-brand-title">PumaGestión</span>
    </a>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="fas fa-search fa-fw"></i></a>
      </li>
      <li class="nav-item" style="position:relative;">
        <a class="nav-link" href="#">
          <i class="far fa-bell fa-fw"></i>
          <span class="navbar-badge-custom" id="notif-count">3</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="far fa-envelope fa-fw"></i></a>
      </li>
    </ul>
  </nav>

  {{-- ===== SIDEBAR ===== --}}
  <aside class="main-sidebar sidebar-dark-primary elevation-4">

    <div class="sidebar-edificio-bg"
         style="background-image: url('{{ asset('images/Edificio2.jpeg') }}');"></div>

    <div class="sidebar-overlay"></div>

    <a href="#" class="brand-link">
      <div class="sidebar-logo-wrap">
        <div class="logo-ring-outer"></div>
        <div class="logo-stars"></div>
        <div class="logo-circle-inner">
          <img src="{{ asset('images/Logo.png') }}" alt="PumaGestión Logo">
        </div>
      </div>
      <span class="brand-name">
        <span class="bn-puma">Puma</span><span class="bn-gestion">Gestión</span>
      </span>
      <span class="brand-sub">FCEAC · UNAH</span>
    </a>

    <div class="sidebar">

      <div class="sidebar-user-top">
        <div class="su-icon" id="sidebar-initials">AG</div>
        <div>
          <div class="su-name" id="sidebar-username">Alma Patricia Godoy</div>
          <div class="su-s" id="sidebar-role">Project Manager</div>
        </div>
      </div>

      <div class="su-search-row">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Buscar..." id="sidebar-search">
      </div>

      <nav class="mt-1">
        <ul class="nav nav-pills nav-sidebar flex-column"
            data-widget="treeview" role="menu" data-accordion="false">

          <li class="nav-item">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

        {{-- MÓDULO EMPLEADOS --}}
        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Revisión</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Configuración</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p>Calendario</p>
            </a>
        </li>

          <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
              @csrf
              <button type="submit"
                      class="nav-link btn btn-link text-left w-100"
                      style="border:none;background:none;color:rgba(255,255,255,.78);padding:9px 16px;">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                <p>Cerrar sesión</p>
              </button>
            </form>
          </li>

        </ul>
      </nav>
    </div>
  </aside>

  {{-- ===== CONTENT ===== --}}
  <div class="content-wrapper">

    <div style="padding:8px 18px 0;">
      <div class="breadcrumb-wrap">
        <i class="fas fa-home" style="color:var(--blue2);"></i>
        <a href="#">Inicio</a>
        <span><i class="fas fa-chevron-right" style="font-size:.65rem;color:#bbb;"></i></span>
        <span>Dashboard</span>
      </div>
    </div>

    <section class="content" style="padding:0 18px 80px;">

      <div class="faculty-banner">
        <div class="fb-bg"></div>
        <div class="fb-photo-right"
             style="background-image: url('{{ asset('images/FCEAC.jpeg') }}');"></div>
        <div class="fb-content">
          <div>
            <div class="fb-title-main">
              Facultad de Ciencias Económicas,<br>Administrativas y Contables
            </div>
            <div class="fb-subtitle">FCEAC · UNAH · PumaGestión</div>
          </div>
        </div>
      </div>

      <div class="top-search-row">
        <div class="tsr-input-wrap">
          <input type="text" placeholder="Buscar trámite o estudiante..." id="top-search">
        </div>
        <div class="tsr-user">
          <div class="tsr-avatar" id="top-initials">AG</div>
          <span class="tsr-name" id="top-username">Alma Patricia Godoy</span>
        </div>
      </div>

      <div class="info-boxes-row">
        <div class="info-box-custom ibc-dark">
          <i class="fas fa-calendar-check ibc-icon-bg"></i>
          <div class="ibc-top">
            <i class="fas fa-calendar-check ibc-icon-front"></i>
            <div>
              <div class="ibc-num">312</div>
              <div class="ibc-label">Trámites Aprobados</div>
            </div>
          </div>
          <div class="ibc-footer">Ciclo actual</div>
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
</body>
</html>
