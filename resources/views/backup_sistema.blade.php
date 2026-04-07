@extends('layouts.app-estudiantes')

@section('titulo', 'Gestión de Respaldos')

@section('content')
<link rel="stylesheet" href="{{ asset('css/backup.css') }}">

<div class="container-fluid backup-page">

    <div class="breadcrumb-bar mb-3">
        <i class="fas fa-home"></i>
        <a href="{{ route('dashboard') }}">Inicio</a>
        <span class="sep"><i class="fas fa-chevron-right"></i></span>
        <a href="{{ route('dashboard') }}">Panel Institucional</a>
        <span class="sep"><i class="fas fa-chevron-right"></i></span>
        <span class="current">Respaldos</span>
    </div>

    @if(session('error'))
        <div class="backup-alert backup-alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="backup-alert backup-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="backup-hero-card">
        <div class="backup-hero-wrap">
            <div class="backup-hero-left">
                <div class="backup-hero-icon">
                    <i class="fas fa-database"></i>
                </div>

                <div>
                    <h1 class="backup-hero-title">Gestión de Respaldos</h1>
                    <p class="backup-hero-subtitle">
                        Genera, consulta y administra las copias de seguridad del sistema
                        de manera ordenada, visual y segura.
                    </p>
                </div>
            </div>

            <div class="backup-status-pill">
                <i class="fas fa-shield-halved"></i>
                Sistema seguro
            </div>
        </div>
    </div>

    <div class="backup-grid-top">
        <div class="backup-action-card">
            <div class="backup-card-title">
                <i class="fas fa-bolt"></i>
                Acción principal
            </div>

            <div class="backup-main-action">
                <div class="backup-main-action-text">
                    <h4>Generar nuevo respaldo del sistema</h4>
                    <p>
                        Crea una copia de seguridad actualizada para proteger la información
                        registrada en el sistema. Se recomienda hacerlo antes de cambios importantes
                        o como parte del control periódico.
                    </p>
                </div>

                <form action="{{ route('backup.generar') }}" method="POST" style="margin: 0;" id="backup-generate-form">
                    @csrf
                    <button type="submit" class="btn-backup-main" id="backup-generate-btn">
                        <i class="fas fa-download"></i>
                        <span>Realizar Respaldo del Sistema</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="backup-summary-card">
            <div class="backup-card-title">
                <i class="fas fa-chart-simple"></i>
                Resumen rápido
            </div>

            <div class="backup-summary-list">
                <div class="backup-stat-box">
                    <div class="backup-stat-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div>
                        <span class="backup-stat-label">Respaldos registrados</span>
                        <span class="backup-stat-value" id="backup-count-top">{{ collect($historial)->count() }}</span>
                    </div>
                </div>

                <div class="backup-stat-box">
                    <div class="backup-stat-icon">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <span class="backup-stat-label">Último respaldo visible</span>
                        <span class="backup-stat-value backup-stat-value-sm">
                            {{ collect($historial)->first()->created_at ?? 'Sin registros' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="backup-table-card">
        <div class="backup-table-header">
            <div class="backup-table-header-left">
                <div class="backup-table-title-wrap">
                    <h3 class="backup-table-title">
                        <i class="fas fa-history"></i>
                        Historial de Respaldos
                    </h3>
                    <span class="table-count" id="backup-count">{{ collect($historial)->count() }}</span>
                </div>

                <p class="backup-table-subtitle">
                    Consulta las copias de seguridad generadas dentro del sistema.
                </p>
            </div>

            <div class="table-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar archivo..." id="search-input">
            </div>
        </div>

        <div class="backup-table-wrap">
            <table class="backup-table" id="backup-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-file-archive"></i> Nombre del archivo</th>
                        <th><i class="fas fa-weight-hanging"></i> Tamaño</th>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-calendar"></i> Fecha de creación</th>
                    </tr>
                </thead>
                <tbody id="backup-tbody">
                    @forelse($historial as $log)
                        <tr>
                            <td>
                                <div class="file-name">
                                    <div class="file-icon">
                                        <i class="fas fa-file-archive"></i>
                                    </div>

                                    <div class="file-main-text">
                                        <strong>{{ $log->nombre_archivo }}</strong>
                                        <small>Archivo de respaldo disponible en el historial</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="size-badge">
                                    <i class="fas fa-hard-drive"></i>
                                    {{ $log->tamano }}
                                </span>
                            </td>

                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr(trim($log->usuario), 0, 2)) }}
                                    </div>
                                    <span>{{ $log->usuario }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="date-cell">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ $log->created_at }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="backup-empty-row">
                            <td colspan="4">
                                <div class="backup-empty">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No hay respaldos registrados</h4>
                                    <p>
                                        Cuando se genere una copia de seguridad, aparecerá listada en este historial.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="backup-info-card">
        <div class="backup-card-title backup-card-title-light">
            <i class="fas fa-circle-info"></i>
            Recomendaciones de uso
        </div>

        <div class="backup-info-grid">
            <div class="backup-info-item">
                <h5>Antes de cambios importantes</h5>
                <p>
                    Genera un respaldo antes de aplicar modificaciones significativas en el sistema
                    o antes de procesos administrativos delicados.
                </p>
            </div>

            <div class="backup-info-item">
                <h5>Control periódico</h5>
                <p>
                    Revisa el historial para confirmar que los respaldos se están generando
                    correctamente y que permanecen disponibles para consulta.
                </p>
            </div>

            <div class="backup-info-item">
                <h5>Búsqueda rápida</h5>
                <p>
                    Usa el buscador para localizar archivos concretos por nombre
                    y mantener un mejor control del historial.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/backup.js') }}"></script>
@endsection