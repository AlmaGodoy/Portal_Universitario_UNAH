@php
    use Illuminate\Support\Facades\Route;

    $usuario = auth()->user();
    $idRolActual = (int) ($usuario->id_rol ?? 0);

    $layout = $layout ?? match ($idRolActual) {
        1 => 'layouts.app-secretaria-academica',
        4 => 'layouts.app-coordinador',
        5 => 'layouts.app-secretaria',
        default => 'layouts.app-estudiantes',
    };

    $dashboardRoute = $dashboardRoute ?? match ($idRolActual) {
        1, 4, 5 => Route::has('empleado.dashboard')
            ? route('empleado.dashboard')
            : url('/empleado/dashboard'),

        default => Route::has('dashboard')
            ? route('dashboard')
            : url('/dashboard'),
    };

    $backupGenerarUrl = Route::has('backup.generar')
        ? route('backup.generar')
        : url('/respaldos/generar');

    $backupProbarUrl = Route::has('backup.probar')
        ? route('backup.probar')
        : url('/respaldos/probar');

    $historialRespaldos = collect($historial ?? []);
    $totalRespaldos = $historialRespaldos->count();
    $ultimoRespaldo = optional($historialRespaldos->first())->nombre_archivo ?? 'Sin registros';
@endphp

@extends($layout)

@section('title', 'Gestión de Respaldos')
@section('titulo', 'Gestión de Respaldos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/backup.css') }}">
@endpush

@section('content')

<div id="backupPage" class="container-fluid backup-page">

    {{-- Alertas --}}
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

    @if(session('warning'))
        <div class="backup-alert backup-alert-warning">
            <i class="fas fa-triangle-exclamation"></i>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    {{-- Encabezado --}}
    <section class="backup-header">
        <div class="backup-header-copy">
            <span class="backup-header-label">
                <i class="fas fa-database"></i>
                Administración del sistema
            </span>

            <h1>Gestión de Respaldos</h1>

            <p>
                Genera, consulta y administra las copias de seguridad del sistema
                de manera ordenada, visual y segura.
            </p>
        </div>

        <div class="backup-header-actions">
            <a href="{{ $dashboardRoute }}" class="backup-btn backup-btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver al dashboard
            </a>

            <span class="backup-status-pill">
                <i class="fas fa-shield-halved"></i>
                Sistema seguro
            </span>
        </div>
    </section>

    {{-- Resumen y acción principal --}}
    <div class="backup-grid-top">

        <div class="backup-card backup-action-card">
            <div class="backup-card-header">
                <div>
                    <h2>
                        <i class="fas fa-bolt"></i>
                        Acción principal
                    </h2>
                    <p>
                        Crea una copia de seguridad actualizada para proteger la información registrada.
                    </p>
                </div>
            </div>

            <div class="backup-card-body">
                <div class="backup-main-action">
                    <div class="backup-main-action-text">
                        <h4>Generar nuevo respaldo del sistema</h4>
                        <p>
                            Se recomienda generar un respaldo antes de realizar cambios importantes
                            o como parte del control periódico de seguridad.
                        </p>
                    </div>

                    <form action="{{ $backupGenerarUrl }}"
                          method="POST"
                          class="backup-inline-form"
                          id="backup-generate-form">
                        @csrf

                        <button type="submit"
                                class="backup-btn backup-btn-primary"
                                id="backup-generate-btn">
                            <i class="fas fa-download"></i>
                            <span>Realizar respaldo</span>
                        </button>
                    </form>
                </div>

                <div class="backup-test-action">
                    <form action="{{ $backupProbarUrl }}"
                          method="POST"
                          class="backup-inline-form"
                          id="backup-test-form">
                        @csrf

                        <button type="submit"
                                class="backup-btn backup-btn-secondary"
                                id="backup-test-btn">
                            <i class="fas fa-plug-circle-check"></i>
                            Probar conexión
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="backup-card backup-summary-card">
            <div class="backup-card-header">
                <div>
                    <h2>
                        <i class="fas fa-chart-simple"></i>
                        Resumen rápido
                    </h2>
                    <p>
                        Estado general del historial de respaldos.
                    </p>
                </div>
            </div>

            <div class="backup-card-body">
                <div class="backup-summary-list">

                    <div class="backup-stat-box">
                        <div class="backup-stat-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>

                        <div>
                            <span class="backup-stat-label">Respaldos registrados</span>
                            <span class="backup-stat-value" id="backup-count-top">
                                {{ $totalRespaldos }}
                            </span>
                        </div>
                    </div>

                    <div class="backup-stat-box">
                        <div class="backup-stat-icon backup-stat-icon-gold">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>

                        <div>
                            <span class="backup-stat-label">Último respaldo visible</span>
                            <span class="backup-stat-value backup-stat-value-sm">
                                {{ $ultimoRespaldo }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- Tabla --}}
    <div class="backup-card backup-table-card">
        <div class="backup-table-header">
            <div class="backup-table-header-left">
                <h2 class="backup-table-title">
                    <i class="fas fa-history"></i>
                    Historial de Respaldos
                    <span class="table-count" id="backup-count">{{ $totalRespaldos }}</span>
                </h2>

                <p class="backup-table-subtitle">
                    Consulta las copias de seguridad generadas dentro del sistema.
                </p>
            </div>

            <div class="table-search">
                <i class="fas fa-search"></i>
                <input type="text"
                       placeholder="Buscar archivo..."
                       id="search-input"
                       autocomplete="off">
            </div>
        </div>

        <div class="backup-table-wrap">
            <table class="backup-table" id="backup-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-file-archive"></i> Nombre del archivo</th>
                        <th><i class="fas fa-weight-hanging"></i> Tamaño</th>
                        <th><i class="fas fa-user"></i> Usuario</th>
                        <th><i class="fas fa-calendar"></i> Registro</th>
                    </tr>
                </thead>

                <tbody id="backup-tbody">
                    @forelse($historialRespaldos as $log)
                        @php
                            $usuarioRegistro = $log->usuario ?? 'Usuario';
                            $inicialesUsuario = strtoupper(substr(trim((string) $usuarioRegistro), 0, 2));
                        @endphp

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
                                        {{ $inicialesUsuario }}
                                    </div>

                                    <span>{{ $usuarioRegistro }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="date-cell">
                                    <i class="fas fa-clock"></i>
                                    <span>#{{ $log->id ?? 'Sin registro' }}</span>
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

    {{-- Recomendaciones --}}
    <div class="backup-info-card">
        <div class="backup-info-header">
            <h2>
                <i class="fas fa-circle-info"></i>
                Recomendaciones de uso
            </h2>
            <p>
                Buenas prácticas para mantener un control adecuado de las copias de seguridad.
            </p>
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
                    Revisa el historial para confirmar que los respaldos se están generando correctamente.
                </p>
            </div>

            <div class="backup-info-item">
                <h5>Búsqueda rápida</h5>
                <p>
                    Usa el buscador para localizar archivos concretos por nombre y mantener mejor control.
                </p>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/backup.js') }}"></script>
@endpush