<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Respaldos — UNAH</title>
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="top-bar">
  <a href="#" class="top-bar-brand">
    <div class="top-bar-bee">🐝</div>
    <span class="top-bar-title">Portal Administrativo UNAH</span>
  </a>
  <div class="top-bar-right">
    <a href="#"><i class="fas fa-home"></i> Inicio</a>
    <span class="divider">|</span>
    <a href="#"><i class="fas fa-user-circle"></i> Mi cuenta</a>
    <span class="divider">|</span>
    <a href="#"><i class="fas fa-sign-out-alt"></i> Salir</a>
  </div>
</nav>

<div class="breadcrumb-bar">
  <i class="fas fa-home"></i>
  <a href="#">Inicio</a>
  <span class="sep"><i class="fas fa-chevron-right"></i></span>
  <a href="#">Panel Institucional</a>
  <span class="sep"><i class="fas fa-chevron-right"></i></span>
  <span class="current">Respaldos</span>
</div>

<div class="page-wrapper">

  <div class="page-header">
    <div class="page-header-left">
      <div class="header-icon">
        <i class="fas fa-database"></i>
      </div>
      <div>
        <div class="page-title">Gestión de Respaldos</div>
        <div class="page-subtitle">Administración y control de copias de seguridad del sistema</div>
      </div>
    </div>
    <div class="page-badge"><i class="fas fa-shield-alt"></i> Sistema seguro</div>
  </div>

  @if(session('error'))
  <div class="alert-error">
    <i class="fas fa-exclamation-circle"></i>
    {{ session('error') }}
  </div>
  @endif

  <div class="action-card">
    <div class="action-card-title">
      <i class="fas fa-bolt"></i> Acciones disponibles
    </div>
    <div class="action-buttons">
      <form action="{{ route('backup.probar') }}" method="POST">
        @csrf
        <button type="submit" class="btn-backup btn-backup-primary">
          <i class="fas fa-plug"></i> Probar Conexión
        </button>
      </form>
      <form action="{{ route('backup.generar') }}" method="POST">
        @csrf
        <button type="submit" class="btn-backup btn-backup-success">
          <i class="fas fa-download"></i> Realizar Respaldo del Sistema
        </button>
      </form>
    </div>
  </div>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-card-header-left">
        <span class="table-card-title">
          <i class="fas fa-history"></i> Historial de Respaldos
        </span>
        <span class="table-count" id="backup-count">0</span>
      </div>
      <div class="table-search">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Buscar archivo..." id="search-input" onkeyup="filtrarTabla()">
      </div>
    </div>

    <table class="backup-table" id="backup-table">
      <thead>
        <tr>
          <th><i class="fas fa-file-archive"></i> Nombre del Archivo</th>
          <th><i class="fas fa-weight"></i> Tamaño</th>
          <th><i class="fas fa-user"></i> Usuario</th>
          <th><i class="fas fa-calendar"></i> Fecha de Creación</th>
        </tr>
      </thead>
      <tbody id="backup-tbody">
        @forelse($historial as $log)
        <tr>
          <td>
            <div class="file-name">
              <div class="file-icon"><i class="fas fa-file-archive"></i></div>
              {{ $log->nombre_archivo }}
            </div>
          </td>
          <td>
            <span class="size-badge">
              <i class="fas fa-hdd"></i> {{ $log->tamano }}
            </span>
          </td>
          <td>
            <div class="user-cell">
              <div class="user-avatar">{{ strtoupper(substr($log->usuario, 0, 2)) }}</div>
              {{ $log->usuario }}
            </div>
          </td>
          <td>
            <div class="date-cell">
              <i class="fas fa-clock"></i> {{ $log->created_at }}
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4">
            <div class="backup-empty">
              <i class="fas fa-inbox"></i>
              <p>No hay respaldos registrados aún.</p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>

<footer class="backup-footer">
  <strong>© 2026 UNAH</strong> — Facultad de Ciencias Económicas, Administrativas y Contables. Todos los derechos reservados.
</footer>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('#backup-tbody tr');
    document.getElementById('backup-count').textContent = rows.length;
  });

  function filtrarTabla() {
    const input = document.getElementById('search-input').value.toLowerCase();
    const rows  = document.querySelectorAll('#backup-tbody tr');
    rows.forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
    });
  }
</script>

</body>
</html>
