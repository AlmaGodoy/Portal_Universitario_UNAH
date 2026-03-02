<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal Administrativo UNAH</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
  <style>
    :root {
      --blue1: #0e2348;
      --blue2: #1a3a6b;
      --blue3: #1e5ea6;
      --gold:  #f5c518;
      --gold2: #e8b800;
      --green: #28a745;
      --cyan:  #17a2b8;
      --sidebar: #1a2744;
    }
    * { font-family: 'Open Sans', 'Source Sans Pro', sans-serif; box-sizing: border-box; }

    /* NAVBAR */
    .main-header.navbar {
      background: linear-gradient(90deg, #0a1c3e 0%, #122658 35%, #1a3a7a 65%, #1e5ea6 85%, #2471c8 100%) !important;
      border-bottom: 0 !important; min-height: 60px; padding: 0 12px;
    }
    .navbar-brand { display: flex; align-items: center; padding: 0 8px 0 0; }
    .nav-bee-circle {
      width: 46px; height: 46px; border-radius: 50%;
      background: #e8b800; display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem; box-shadow: 0 0 0 3px rgba(232,184,0,.35);
      margin-right: 10px; flex-shrink: 0;
    }
    .nav-brand-title { font-size: 1.18rem; font-weight: 700; color: #fff; letter-spacing: .3px; white-space: nowrap; }
    .main-header .nav-link { color: rgba(255,255,255,.85) !important; }
    .main-header .nav-link:hover { color: var(--gold) !important; }
    .navbar-badge-custom {
      background: var(--gold) !important; color: #000 !important;
      font-weight: 700; font-size: .65rem; position: absolute; top: 2px; right: 2px;
      padding: 2px 5px; border-radius: 10px;
    }

    /* SIDEBAR */
    .main-sidebar { background: var(--sidebar) !important; box-shadow: 2px 0 12px rgba(0,0,0,.3); }
    .brand-link { background: #0e1e3f !important; border-bottom: 1px solid rgba(255,255,255,.1) !important; padding: 10px 15px !important; }
    .brand-link .brand-text { color: #fff !important; font-weight: 700; font-size: .95rem; }
    .sidebar-user-top { padding: 10px 15px 8px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,.07); }
    .su-search-row { padding: 8px 12px; position: relative; }
    .su-search-row input {
      width: 100%; background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.13);
      color: #fff; border-radius: 4px; padding: 5px 10px 5px 28px; font-size: .8rem; outline: none;
    }
    .su-search-row input::placeholder { color: rgba(255,255,255,.4); }
    .su-search-row .fa-search { position: absolute; left: 22px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,.35); font-size: .75rem; }
    .su-icon { width: 32px; height: 32px; border-radius: 50%; background: var(--gold2); color: var(--blue1); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
    .su-name { color: #fff; font-size: .82rem; font-weight: 600; }
    .su-s { color: rgba(255,255,255,.42); font-size: .7rem; }
    .nav-sidebar .nav-link { color: rgba(255,255,255,.75) !important; font-size: .88rem; padding: 8px 15px; border-radius: 0; margin: 0; border-left: 3px solid transparent; transition: all .15s; }
    .nav-sidebar .nav-link:hover { background: rgba(255,255,255,.07) !important; color: #fff !important; border-left-color: var(--gold); }
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active { background: var(--gold) !important; color: var(--blue1) !important; font-weight: 700; border-radius: 0; margin: 0; border-left: 3px solid var(--gold2); }
    .nav-sidebar .nav-icon { color: inherit !important; width: 1.5rem; }
    .nav-sidebar .nav-link p { white-space: normal !important; line-height: 1.25; font-size: .86rem; }

    /* CONTENT */
    .content-wrapper { background: #f4f6f9 !important; }
    .breadcrumb-wrap { background: #fff; border: 1px solid #e8e8e8; border-radius: 3px; padding: 6px 14px; font-size: .82rem; display: flex; align-items: center; gap: 6px; color: #666; margin-bottom: 14px; }
    .breadcrumb-wrap a { color: var(--blue2); text-decoration: none; }

    /* FACULTY BANNER */
    .faculty-banner { width: 100%; height: 155px; border-radius: 6px; overflow: hidden; position: relative; display: flex; margin-bottom: 14px; box-shadow: 0 3px 12px rgba(0,0,0,.2); }
    .fb-bg { position: absolute; inset: 0; background: linear-gradient(90deg, #0a1c3e 0%, #122658 30%, #1a3a7a 55%, #1e5ea6 75%, #2878cc 100%); }
    .fb-bg::before { content: ''; position: absolute; inset: 0; background: repeating-linear-gradient(-55deg, transparent, transparent 18px, rgba(255,255,255,.025) 18px, rgba(255,255,255,.025) 19px); }
    .fb-photo-right { position: absolute; right: 0; top: 0; bottom: 0; width: 42%; background: linear-gradient(135deg, #1e5ea6 0%, #2878cc 40%, #3a9ae8 70%, #4db8e8 100%); }
    .fb-photo-right::before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(30,94,166,.0) 0%, rgba(30,94,166,.0) 30%, rgba(232,184,0,.25) 30%, rgba(232,184,0,.25) 45%, rgba(30,94,166,.0) 45%, rgba(30,94,166,.0) 60%, rgba(232,184,0,.18) 60%, rgba(232,184,0,.18) 72%, rgba(30,94,166,.0) 72%); }
    .fb-photo-right::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #1a3a7a 0%, rgba(26,58,122,.3) 30%, transparent 70%); }
    .fb-content { position: relative; z-index: 2; display: flex; align-items: center; padding: 0 22px; gap: 18px; width: 100%; }
    .fb-logo-wrap { position: relative; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .fb-logo-ring { width: 92px; height: 92px; border-radius: 50%; border: 2px solid rgba(232,184,0,.5); position: absolute; flex-shrink: 0; }
    .fb-logo-circle { width: 86px; height: 86px; border-radius: 50%; background: radial-gradient(circle at 40% 35%, #2060a8 0%, #0d2248 100%); border: 3px solid rgba(255,255,255,.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 18px rgba(0,0,0,.4), inset 0 0 0 2px rgba(255,255,255,.08); }
    .fb-logo-circle .bee-emoji { font-size: 2.6rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,.4)); }
    .fb-title-main { font-size: 1.65rem; font-weight: 900; color: var(--gold); text-transform: uppercase; line-height: 1.15; letter-spacing: 1px; text-shadow: 0 2px 10px rgba(0,0,0,.5); }

    /* TOP SEARCH */
    .top-search-row { background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; padding: 8px 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    .tsr-input-wrap { position: relative; flex: 1; max-width: 420px; }
    .tsr-input-wrap input { width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 6px 12px; font-size: .88rem; color: #aaa; outline: none; }
    .tsr-input-wrap input:focus { border-color: var(--blue2); color: #333; }
    .tsr-user { display: flex; align-items: center; gap: 10px; }
    .tsr-avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--blue2); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: .88rem; }
    .tsr-name { font-weight: 600; color: #333; font-size: .88rem; }
    .tsr-btn { border: 1px solid #ddd; background: #fff; border-radius: 4px; padding: 4px 8px; color: #888; font-size: .78rem; cursor: pointer; }

    /* STAT CARDS */
    .info-boxes-row { display: flex; gap: 12px; margin-bottom: 16px; }
    .info-box-custom { flex: 1; min-width: 0; border-radius: 6px; padding: 14px 14px 12px; position: relative; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,.12); cursor: pointer; transition: transform .15s; }
    .info-box-custom:hover { transform: translateY(-2px); }
    .ibc-gold  { background: linear-gradient(135deg, #f5c518 0%, #e8ad00 100%); }
    .ibc-blue  { background: linear-gradient(135deg, #1e5ea6 0%, #2878cc 100%); color: #fff; }
    .ibc-green { background: linear-gradient(135deg, #28a745 0%, #1d8c38 100%); color: #fff; }
    .ibc-cyan  { background: linear-gradient(135deg, #17a2b8 0%, #0f8699 100%); color: #fff; }
    .ibc-dark  { background: linear-gradient(135deg, #2d3e50 0%, #3d5468 100%); color: #fff; }
    .ibc-icon-bg { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 4rem; opacity: .14; }
    .ibc-top { display: flex; align-items: flex-start; gap: 8px; }
    .ibc-icon-front { font-size: 1.5rem; opacity: .75; margin-top: 2px; }
    .ibc-num { font-size: 1.9rem; font-weight: 800; line-height: 1; }
    .ibc-label { font-size: .78rem; font-weight: 600; margin-top: 4px; opacity: .92; }
    .ibc-gold .ibc-num, .ibc-gold .ibc-label, .ibc-gold .ibc-icon-front, .ibc-gold .ibc-icon-bg { color: #7a4f00; }
    .ibc-footer { margin-top: 8px; font-size: .72rem; opacity: .75; }

    /* DASH CARDS */
    .dash-card { background: #fff; border-radius: 6px; box-shadow: 0 1px 8px rgba(0,0,0,.08); border: none; overflow: hidden; }
    .dash-card-header { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; font-size: .88rem; font-weight: 700; color: #333; display: flex; align-items: center; justify-content: space-between; background: #fff; }
    .dch-tools .btn { border: none; background: transparent; color: #ccc; padding: 2px 5px; font-size: .78rem; }
    .dch-tools .btn:hover { color: #666; background: #f5f5f5; border-radius: 3px; }
    .chart-legend { display: flex; gap: 12px; font-size: .75rem; color: #666; flex-wrap: wrap; }
    .cl-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; margin-right: 4px; }
    .cl-circle { border-radius: 50%; }

    /* EMPTY STATE */
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; color: #ccc; text-align: center; }
    .empty-state i { font-size: 2.5rem; margin-bottom: 10px; color: #ddd; }
    .empty-state p { font-size: .82rem; margin: 0; }

    /* TABLE */
    .tramites-table thead th { background: #f8f9fa; color: #888; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; border: none; padding: 8px 12px; }
    .tramites-table tbody td { font-size: .84rem; vertical-align: middle; padding: 8px 12px; border-color: #f2f2f2; color: #444; }
    .tramites-table tbody tr:hover td { background: #fafbfc; }
    .estado-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: .74rem; font-weight: 700; }
    .eb-verde    { background: rgba(40,167,69,.12);  color: #1a7a32; }
    .eb-azul     { background: rgba(30,94,166,.12);  color: #1a3a6b; }
    .eb-amarillo { background: rgba(232,184,0,.2);    color: #7a5900; }

    /* EVENTS */
    .ev-item { display: flex; gap: 12px; padding: 10px 14px; border-bottom: 1px solid #f5f5f5; align-items: flex-start; }
    .ev-item:last-child { border-bottom: none; }
    .ev-item:hover { background: #fafcff; }
    .ev-time-box { display: flex; flex-direction: column; align-items: center; background: var(--blue2); color: #fff; border-radius: 6px; padding: 5px 8px; min-width: 66px; text-align: center; flex-shrink: 0; }
    .ev-t { font-size: .82rem; font-weight: 800; }
    .ev-d { font-size: .64rem; opacity: .78; }
    .ev-ti { font-weight: 700; font-size: .84rem; color: #222; margin-bottom: 2px; }
    .ev-lo { font-size: .74rem; color: #999; }
    .ev-lo i { color: var(--gold2); }

    /* MODAL */
    .modal-header { background: var(--blue2); color: #fff; }
    .modal-header .close { color: #fff; opacity: .8; }
    .modal-title { font-weight: 700; }
    .btn-unah { background: var(--blue2); color: #fff; border: none; }
    .btn-unah:hover { background: var(--blue1); color: #fff; }
    .btn-gold { background: var(--gold); color: var(--blue1); border: none; font-weight: 700; }
    .btn-gold:hover { background: var(--gold2); color: var(--blue1); }
    .form-label { font-size: .85rem; font-weight: 600; color: #444; }

    /* ADD BTN floating */
    .btn-add-tramite {
      position: fixed; bottom: 28px; right: 28px;
      background: var(--gold); color: var(--blue1);
      border: none; border-radius: 50%; width: 52px; height: 52px;
      font-size: 1.4rem; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 16px rgba(0,0,0,.2); cursor: pointer;
      transition: transform .2s; z-index: 999;
    }
    .btn-add-tramite:hover { transform: scale(1.1); }

    /* PIE legend */
    .pie-leg { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 10px; }
    .pl-item { display: flex; align-items: center; gap: 4px; font-size: .74rem; color: #666; }
    .pl-dot  { width: 9px; height: 9px; border-radius: 50%; }

    /* FOOTER */
    .main-footer { background: var(--blue1) !important; color: rgba(255,255,255,.5) !important; border-top: 2px solid var(--gold) !important; text-align: center; font-size: .8rem; padding: 8px; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-thumb { background: var(--blue3); border-radius: 3px; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

<!-- NAVBAR -->
<nav class="main-header navbar navbar-expand navbar-dark">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
  </ul>
  <a class="navbar-brand d-flex align-items-center" href="#">
    <div class="nav-bee-circle">🐝</div>
    <span class="nav-brand-title">Portal Administrativo UNAH</span>
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

<!--  SIDEBAR -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="#" class="brand-link">
    <i class="fas fa-graduation-cap mr-2" style="color:var(--gold);"></i>
    <span class="brand-text">UNAH</span>
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
          <a href="{{ route('cancelacion.index') }}" class="nav-link" style="font-size:.8rem;">
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
      </ul>
    </nav>
  </div>
</aside>

<!-- CONTENT WRAPPER -->
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
          <div class="fb-logo-ring"></div>
          <div class="fb-logo-circle"><span class="bee-emoji">🐝</span></div>
        </div>
        <div>
          <div class="fb-title-main">Facultad de Ciencias Económicas,<br>Administrativas y Contables</div>
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
        <div class="ibc-top">
          <i class="fas fa-user-graduate ibc-icon-front"></i>
          <div>
            <div class="ibc-num" id="cnt-estudiantes">0</div>
            <div class="ibc-label">Estudiantes Inscritos</div>
          </div>
        </div>
        <div class="ibc-footer" id="footer-estudiantes">Sin registros aún</div>
      </div>
      <div class="info-box-custom ibc-blue">
        <i class="fas fa-user-tie ibc-icon-bg"></i>
        <div class="ibc-top">
          <i class="fas fa-user-tie ibc-icon-front"></i>
          <div>
            <div class="ibc-num" id="cnt-secretarios">0</div>
            <div class="ibc-label">Secretarios Registrados</div>
          </div>
        </div>
        <div class="ibc-footer" id="footer-secretarios">Sin registros aún</div>
      </div>
      <div class="info-box-custom ibc-green">
        <i class="fas fa-file-alt ibc-icon-bg"></i>
        <div class="ibc-top">
          <i class="fas fa-file-alt ibc-icon-front"></i>
          <div>
            <div class="ibc-num" id="cnt-tramites">0</div>
            <div class="ibc-label">Trámites en Proceso</div>
          </div>
        </div>
        <div class="ibc-footer" id="footer-tramites">Sin trámites aún</div>
      </div>
      <div class="info-box-custom ibc-cyan">
        <i class="fas fa-clock ibc-icon-bg"></i>
        <div class="ibc-top">
          <i class="fas fa-clock ibc-icon-front"></i>
          <div>
            <div class="ibc-num" id="cnt-pendientes">0</div>
            <div class="ibc-label">Solicitudes Pendientes</div>
          </div>
        </div>
        <div class="ibc-footer" id="footer-pendientes">Sin solicitudes aún</div>
      </div>
      <div class="info-box-custom ibc-dark">
        <i class="fas fa-calendar-check ibc-icon-bg"></i>
        <div class="ibc-top">
          <i class="fas fa-calendar-check ibc-icon-front"></i>
          <div>
            <div class="ibc-num" id="cnt-aprobados">0</div>
            <div class="ibc-label">Trámites Aprobados</div>
          </div>
        </div>
        <div class="ibc-footer" id="footer-aprobados">Sin aprobados aún</div>
      </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row mb-3">

      <!-- Bar: Trámites por mes -->
      <div class="col-lg-5 mb-3">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <span>Trámites Ingresados por Mes</span>
            <div class="dch-tools">
              <button class="btn" title="Exportar" onclick="exportarDatos()"><i class="fas fa-download"></i></button>
            </div>
          </div>
          <div class="card-body" style="padding:12px 14px;">
            <div class="chart-legend mb-2">
              <span><span class="cl-dot" style="background:var(--blue3);"></span>Ingresados</span>
              <span><span class="cl-dot" style="background:var(--gold);"></span>Pendientes</span>
              <span><span class="cl-dot" style="background:var(--green);"></span>Aprobados</span>
            </div>
            <div style="height:200px;" id="bar-wrap">
              <canvas id="barChart"></canvas>
            </div>
            <div id="bar-empty" class="empty-state" style="display:none;">
              <i class="fas fa-chart-bar"></i>
              <p>La gráfica se llenará cuando se ingresen trámites</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pie: Estado de trámites -->
      <div class="col-lg-3 mb-3">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <span>Estado de Trámites</span>
          </div>
          <div class="card-body d-flex flex-column align-items-center justify-content-center" style="padding:14px;">
            <div style="position:relative;width:190px;height:190px;" id="pie-wrap">
              <canvas id="pieChart"></canvas>
            </div>
            <div id="pie-empty" class="empty-state" style="display:none;">
              <i class="fas fa-chart-pie"></i>
              <p>Sin datos aún</p>
            </div>
            <div class="pie-leg" id="pie-legend">
              <div class="pl-item"><div class="pl-dot" style="background:var(--gold);"></div><span id="pl-pend">Pendiente: 0</span></div>
              <div class="pl-item"><div class="pl-dot" style="background:var(--blue2);"></div><span id="pl-rev">Revisión: 0</span></div>
              <div class="pl-item"><div class="pl-dot" style="background:var(--green);"></div><span id="pl-apr">Aprobado: 0</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Trámites acumulados -->
      <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
          <div class="dash-card-header">
            <span>Trámites por Tipo</span>
          </div>
          <div class="card-body" style="padding:12px 14px;">
            <div style="height:200px;" id="line-wrap">
              <canvas id="lineChart"></canvas>
            </div>
            <div id="line-empty" class="empty-state" style="display:none;">
              <i class="fas fa-chart-line"></i>
              <p>Sin datos para mostrar</p>
            </div>
            <div class="chart-legend mt-2" id="line-legend" style="flex-wrap:wrap;gap:6px;">
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

      <!-- Tabla Trámites -->
      <div class="col-lg-8 mb-3">
        <div class="dash-card">
          <div class="dash-card-header">
            <span>Seguimiento de Trámites</span>
            <div class="dch-tools">
              <button class="btn" onclick="abrirModal()"><i class="fas fa-plus" style="color:var(--green);"></i> Nuevo</button>
              <button class="btn" onclick="limpiarTodo()" title="Limpiar datos"><i class="fas fa-trash" style="color:#e55;"></i></button>
            </div>
          </div>
          <div id="tabla-wrap">
            <!-- JS -->
          </div>
        </div>
      </div>

      <!-- Eventos -->
      <div class="col-lg-4 mb-3">
        <div class="dash-card">
          <div class="dash-card-header">
            <span>Próximos Eventos <span style="color:var(--blue2);">Académicos</span></span>
            <a href="#" style="font-size:.75rem;color:var(--blue2);font-weight:700;text-decoration:none;" onclick="abrirModalEvento();return false;">+ Agregar</a>
          </div>
          <div id="eventos-wrap">
            <!-- JS -->
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<footer class="main-footer">
  <strong>© 2024 UNAH</strong> – Facultad de Ciencias Económicas, Administrativas y Contables. Todos los derechos reservados.
</footer>

<!-- ══ BOTÓN FLOTANTE ══ -->
<button class="btn-add-tramite" onclick="abrirModal()" title="Nuevo trámite">
  <i class="fas fa-plus"></i>
</button>

</div><!-- wrapper -->

<!-- ══════════════════════════════════
   NUEVO TRÁMITE
══════════════════════════════════ -->
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
              <option>Economía</option>
              <option>Administración</option>
              <option>Contabilidad</option>
              <option>Finanzas</option>
              <option>Otra</option>
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

<!-- ══ EVENTO ══ -->
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
          <div class="col-md-6 mb-3">
            <label class="form-label">Hora *</label>
            <input type="time" class="form-control" id="ev-hora">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Fecha *</label>
            <input type="date" class="form-control" id="ev-fecha">
          </div>
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

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>

function getTramites()  { return JSON.parse(localStorage.getItem('unah_tramites')  || '[]'); }
function getEventos()   { return JSON.parse(localStorage.getItem('unah_eventos')   || '[]'); }
function saveTramites(d){ localStorage.setItem('unah_tramites', JSON.stringify(d)); }
function saveEventos(d) { localStorage.setItem('unah_eventos',  JSON.stringify(d)); }


const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
let barChart, pieChart, lineChart;

function initCharts() {
  const bCtx = document.getElementById('barChart').getContext('2d');
  barChart = new Chart(bCtx, {
    type: 'bar',
    data: { labels: MESES, datasets: [
      { label:'Ingresados', data: new Array(12).fill(0), backgroundColor:'rgba(30,94,166,.82)', borderRadius:4, borderSkipped:false },
      { label:'Pendientes', data: new Array(12).fill(0), backgroundColor:'rgba(240,184,0,.85)',  borderRadius:4, borderSkipped:false },
      { label:'Aprobados',  data: new Array(12).fill(0), borderColor:'#28a745', backgroundColor:'rgba(40,167,69,.08)', type:'line', borderWidth:2, pointRadius:3, fill:true, tension:.4 }
    ]},
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font:{ size:9 }, precision:0 } }, x:{ grid:{ display:false }, ticks:{ font:{ size:9 } } } } }
  });

  const pCtx = document.getElementById('pieChart').getContext('2d');
  pieChart = new Chart(pCtx, {
    type: 'doughnut',
    data: { labels:['Pendiente','Revisión','Aprobado'], datasets:[{ data:[0,0,0], backgroundColor:['#f5c518','#1a3a6b','#28a745'], borderWidth:3, borderColor:'#fff', hoverOffset:6 }] },
    options: { responsive:false, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label: c=>' '+c.label+': '+c.parsed } } }, cutout:'60%' }
  });

  const lCtx = document.getElementById('lineChart').getContext('2d');
  lineChart = new Chart(lCtx, {
    type: 'line',
    data: { labels: MESES, datasets: [
      { label:'Cambio Carrera',  data: new Array(12).fill(0), borderColor:'#0e2348', backgroundColor:'transparent', borderWidth:2, pointRadius:3, tension:.4 },
      { label:'Cancelación',    data: new Array(12).fill(0), borderColor:'#17a2b8', backgroundColor:'transparent', borderWidth:2, pointRadius:3, tension:.4 },
      { label:'Otros',          data: new Array(12).fill(0), borderColor:'#28a745', backgroundColor:'transparent', borderWidth:2, pointRadius:3, tension:.4 }
    ]},
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font:{ size:9 }, precision:0 } }, x:{ grid:{ display:false }, ticks:{ font:{ size:9 } } } } }
  });
}

// ══════════════════════════════════════════════
//  ACTUALIZAR EL DASHBOARD
// ══════════════════════════════════════════════
function actualizarDashboard() {
  const tramites = getTramites();
  const eventos  = getEventos();

  const pendientes = tramites.filter(t => t.estado === 'Pendiente').length;
  const aprobados  = tramites.filter(t => t.estado === 'Aprobado').length;
  const revision   = tramites.filter(t => t.estado === 'Revisión').length;
  const ests = tramites.filter(t => t.rol === 'Estudiante').length;
  const secs = tramites.filter(t => t.rol === 'Secretario').length;

  document.getElementById('cnt-tramites').textContent    = tramites.length;
  document.getElementById('cnt-pendientes').textContent  = pendientes;
  document.getElementById('cnt-aprobados').textContent   = aprobados;
  document.getElementById('cnt-estudiantes').textContent = ests;
  document.getElementById('cnt-secretarios').textContent = secs;

  const hoy = new Date().toLocaleDateString('es-HN');
  document.getElementById('footer-tramites').textContent    = tramites.length ? `Último: ${tramites[tramites.length-1].fecha}` : 'Sin trámites aún';
  document.getElementById('footer-pendientes').textContent  = pendientes ? `${pendientes} esperando revisión` : 'Sin solicitudes aún';
  document.getElementById('footer-aprobados').textContent   = aprobados  ? `${aprobados} resueltos` : 'Sin aprobados aún';
  document.getElementById('footer-estudiantes').textContent = ests ? `${ests} trámites de estudiantes` : 'Sin registros aún';
  document.getElementById('footer-secretarios').textContent = secs ? `${secs} trámites de secretarios` : 'Sin registros aún';

  document.getElementById('notif-count').textContent = pendientes;

  // ── Gráficas ──
  // por mes
  const barIng  = new Array(12).fill(0);
  const barPend = new Array(12).fill(0);
  const barApr  = new Array(12).fill(0);
  const lineCam = new Array(12).fill(0);
  const lineCan = new Array(12).fill(0);
  const lineOtr = new Array(12).fill(0);

  tramites.forEach(t => {
    const d = new Date(t.fecha);
    const m = isNaN(d) ? 0 : d.getMonth();
    barIng[m]++;
    if (t.estado === 'Pendiente') barPend[m]++;
    if (t.estado === 'Aprobado')  barApr[m]++;
    if (t.tipo === 'Cambio de Carrera')       lineCam[m]++;
    else if (t.tipo === 'Cancelación Excepcional') lineCan[m]++;
    else lineOtr[m]++;
  });

  barChart.data.datasets[0].data = barIng;
  barChart.data.datasets[1].data = barPend;
  barChart.data.datasets[2].data = barApr;
  barChart.update();

  lineChart.data.datasets[0].data = lineCam;
  lineChart.data.datasets[1].data = lineCan;
  lineChart.data.datasets[2].data = lineOtr;
  lineChart.update();

  pieChart.data.datasets[0].data = [pendientes, revision, aprobados];
  pieChart.update();
  document.getElementById('pl-pend').textContent = `Pendiente: ${pendientes}`;
  document.getElementById('pl-rev').textContent  = `Revisión: ${revision}`;
  document.getElementById('pl-apr').textContent  = `Aprobado: ${aprobados}`;

  const sinDatos = tramites.length === 0;
  document.getElementById('bar-wrap').style.display  = sinDatos ? 'none' : 'block';
  document.getElementById('bar-empty').style.display = sinDatos ? 'flex' : 'none';
  document.getElementById('pie-wrap').style.display  = sinDatos ? 'none' : 'block';
  document.getElementById('pie-empty').style.display = sinDatos ? 'flex' : 'none';
  document.getElementById('line-wrap').style.display = sinDatos ? 'none' : 'block';
  document.getElementById('line-empty').style.display= sinDatos ? 'flex' : 'none';

  const wrap = document.getElementById('tabla-wrap');
  if (tramites.length === 0) {
    wrap.innerHTML = `
      <div class="empty-state" style="padding:50px 20px;">
        <i class="fas fa-inbox" style="font-size:3rem;color:#ddd;"></i>
        <p style="margin-top:10px;color:#bbb;font-size:.88rem;">No hay trámites registrados aún.<br>Haz clic en <strong>+ Nuevo</strong> para agregar el primero.</p>
      </div>`;
  } else {
    let rows = '';
    [...tramites].reverse().forEach((t, i) => {
      const idx = tramites.length - 1 - i;
      let badgeE = '', badgeA = '';
      if (t.estado === 'Revisión')  { badgeE = `<span class="estado-badge eb-verde"><i class="fas fa-sync-alt"></i> Revisión</span>`; }
      if (t.estado === 'Pendiente') { badgeE = `<span class="estado-badge eb-amarillo"><i class="fas fa-clock"></i> Pendiente</span>`; }
      if (t.estado === 'Aprobado')  { badgeE = `<span class="estado-badge eb-verde"><i class="fas fa-check-circle"></i> Revisión</span>`; }

      if (t.estado === 'Aprobado')  badgeA = `<span class="estado-badge eb-azul mr-1">APROBADO</span>`;
      if (t.estado === 'Pendiente') badgeA = `<span class="estado-badge eb-amarillo mr-1">PENDIENTE</span>`;
      if (t.estado === 'Revisión')  badgeA = `<span class="estado-badge eb-verde mr-1">REVISIÓN</span>`;

      rows += `<tr>
        <td><i class="fas fa-graduation-cap mr-1" style="color:var(--blue2);"></i>${t.carrera}</td>
        <td><i class="fas fa-arrow-up mr-1" style="color:var(--gold2);font-size:.7rem;"></i>${t.descripcion}</td>
        <td>${badgeE}</td>
        <td>${t.fecha}</td>
        <td>
          ${badgeA}
          <i class="fas fa-user-circle" style="color:var(--blue2);margin-right:3px;"></i>${t.solicitante}
          <button onclick="eliminarTramite(${idx})" class="btn btn-sm ml-1" style="color:#e55;border:none;background:transparent;padding:1px 4px;" title="Eliminar"><i class="fas fa-times"></i></button>
        </td>
      </tr>`;
    });
    wrap.innerHTML = `
      <table class="table tramites-table mb-0">
        <thead><tr>
          <th>Carrera</th><th>Trámite</th><th>Estado</th><th>Fecha</th><th>Solicitante</th>
        </tr></thead>
        <tbody>${rows}</tbody>
      </table>`;
  }

  // ── Eventos ──
  const evWrap = document.getElementById('eventos-wrap');
  if (eventos.length === 0) {
    evWrap.innerHTML = `
      <div class="empty-state" style="padding:40px 20px;">
        <i class="fas fa-calendar-times" style="font-size:2.5rem;color:#ddd;"></i>
        <p style="margin-top:10px;color:#bbb;font-size:.84rem;">No hay eventos registrados.<br>Haz clic en <strong>+ Agregar</strong>.</p>
      </div>`;
  } else {
    let evHtml = '';
    eventos.forEach((ev, i) => {
      const colorText = ev.color === '#e8b800' ? '#000' : '#fff';
      evHtml += `
        <div class="ev-item">
          <div class="ev-time-box" style="background:${ev.color};color:${colorText};">
            <div class="ev-t">${ev.hora}</div>
            <div class="ev-d">${ev.fecha}</div>
          </div>
          <div style="flex:1;">
            <div class="ev-ti">${ev.titulo}</div>
            <div class="ev-lo"><i class="fas fa-map-marker-alt mr-1"></i>${ev.lugar || 'Sin ubicación'}</div>
          </div>
          <button onclick="eliminarEvento(${i})" style="border:none;background:transparent;color:#ddd;font-size:.8rem;cursor:pointer;" title="Eliminar"><i class="fas fa-times"></i></button>
        </div>`;
    });
    evWrap.innerHTML = evHtml;
  }
}

// ══════════════════════════════════════════════
//  TRÁMITE
// ══════════════════════════════════════════════
function abrirModal() {
  ['f-tipo','f-carrera','f-descripcion','f-estado','f-solicitante','f-rol','f-cuenta'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('f-fecha').value = new Date().toISOString().split('T')[0];
  document.getElementById('modal-error').style.display = 'none';
  $('#modalTramite').modal('show');
}

function guardarTramite() {
  const tipo        = document.getElementById('f-tipo').value.trim();
  const carrera     = document.getElementById('f-carrera').value.trim();
  const descripcion = document.getElementById('f-descripcion').value.trim();
  const estado      = document.getElementById('f-estado').value;
  const solicitante = document.getElementById('f-solicitante').value.trim();
  const rol         = document.getElementById('f-rol').value;
  const fecha       = document.getElementById('f-fecha').value;
  const cuenta      = document.getElementById('f-cuenta').value.trim();

  const err = document.getElementById('modal-error');
  if (!tipo || !carrera || !descripcion || !solicitante) {
    err.style.display = 'block';
    err.textContent   = 'Por favor completa todos los campos obligatorios (*)';
    return;
  }
  err.style.display = 'none';

  const tramites = getTramites();
  tramites.push({ tipo, carrera, descripcion, estado, solicitante, rol, fecha, cuenta, creadoEn: new Date().toISOString() });
  saveTramites(tramites);
  actualizarDashboard();
  $('#modalTramite').modal('hide');
}

function eliminarTramite(idx) {
  if (!confirm('¿Eliminar este trámite?')) return;
  const tramites = getTramites();
  tramites.splice(idx, 1);
  saveTramites(tramites);
  actualizarDashboard();
}

// ══════════════════════════════════════════════
//  EVENTO
// ══════════════════════════════════════════════
function abrirModalEvento() {
  document.getElementById('ev-titulo').value = '';
  document.getElementById('ev-lugar').value  = '';
  document.getElementById('ev-hora').value   = '10:00';
  document.getElementById('ev-fecha').value  = new Date().toISOString().split('T')[0];
  document.getElementById('ev-color').value  = '#1a3a6b';
  $('#modalEvento').modal('show');
}

function guardarEvento() {
  const titulo = document.getElementById('ev-titulo').value.trim();
  const hora   = document.getElementById('ev-hora').value;
  const fecha  = document.getElementById('ev-fecha').value;
  const lugar  = document.getElementById('ev-lugar').value.trim();
  const color  = document.getElementById('ev-color').value;

  if (!titulo) { alert('El nombre del evento es obligatorio'); return; }

  // Format hora to AM/PM
  const [h, m] = hora.split(':');
  const hNum = parseInt(h);
  const ampm = hNum >= 12 ? 'PM' : 'AM';
  const h12  = hNum > 12 ? hNum - 12 : (hNum === 0 ? 12 : hNum);
  const horaFmt = `${h12}:${m} ${ampm}`;

  // Format fecha short
  const d = new Date(fecha + 'T00:00:00');
  const fechaFmt = isNaN(d) ? fecha : `${d.getDate().toString().padStart(2,'0')}.${(d.getMonth()+1).toString().padStart(2,'0')} – ${d.getFullYear().toString().slice(-2)}`;

  const eventos = getEventos();
  eventos.push({ titulo, hora: horaFmt, fecha: fechaFmt, lugar, color });
  saveEventos(eventos);
  actualizarDashboard();
  $('#modalEvento').modal('hide');
}

function eliminarEvento(idx) {
  if (!confirm('¿Eliminar este evento?')) return;
  const eventos = getEventos();
  eventos.splice(idx, 1);
  saveEventos(eventos);
  actualizarDashboard();
}

function limpiarTodo() {
  if (!confirm('⚠️ ¿Borrar TODOS los trámites? Esta acción no se puede deshacer.')) return;
  saveTramites([]);
  actualizarDashboard();
}

// ══════════════════════════════════════════════
//  EXPORTAR (CSV)
// ══════════════════════════════════════════════
function exportarDatos() {
  const tramites = getTramites();
  if (tramites.length === 0) { alert('No hay datos para exportar'); return; }
  const header = 'Tipo,Carrera,Descripción,Estado,Solicitante,Rol,Fecha,Cuenta\n';
  const rows   = tramites.map(t => `"${t.tipo}","${t.carrera}","${t.descripcion}","${t.estado}","${t.solicitante}","${t.rol}","${t.fecha}","${t.cuenta}"`).join('\n');
  const blob   = new Blob([header + rows], { type: 'text/csv;charset=utf-8;' });
  const url    = URL.createObjectURL(blob);
  const a      = document.createElement('a'); a.href = url; a.download = 'tramites_unah.csv'; a.click();
}

document.addEventListener('DOMContentLoaded', () => {
  initCharts();
  actualizarDashboard();

  document.getElementById('f-fecha').value = new Date().toISOString().split('T')[0];
});
</script>
</body>
</html>
