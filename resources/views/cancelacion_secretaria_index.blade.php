@extends('layouts.app-secretaria')

@section('title', 'Secretaría de Carrera - Revisión de Cancelaciones')
@section('titulo', 'Secretaría de Carrera - Revisión de Cancelaciones')

@section('content')
@php
    $estadoActual = strtoupper(trim((string) ($estado ?? '')));

    $badgeClass = function ($value) {
        return match (strtoupper(trim((string) $value))) {
            'REVISION'           => 'sc-status sc-status-review',
            'DEVUELTO'           => 'sc-status sc-status-returned',
            'LISTO_COORDINADORA' => 'sc-status sc-status-ready',
            default              => 'sc-status sc-status-neutral',
        };
    };

    $estadoLegible = function ($value) {
        return match (strtoupper(trim((string) $value))) {
            'REVISION'           => 'En revisión',
            'DEVUELTO'           => 'Devuelto a estudiante',
            'LISTO_COORDINADORA' => 'Listo para coordinadora',
            default              => $value ?: 'Sin estado',
        };
    };

    $formatearFecha = function ($fecha) {
        return !empty($fecha)
            ? \Carbon\Carbon::parse($fecha)->format('d/m/Y h:i A')
            : 'Sin fecha registrada';
    };
@endphp

<div class="sc-page">

    <section class="sc-top-banner">
        <div class="sc-top-banner-copy">
            <h1>Secretaría de Carrera</h1>
            <p>Bandeja de revisión documental para trámites de cancelación de clases.</p>
        </div>

        <div class="sc-top-banner-badge">
            <i class="fas fa-folder-open"></i>
            <span>Total: {{ $tramites->total() }}</span>
        </div>
    </section>

    @if (session('success'))
        <div class="sc-alert sc-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="sc-alert sc-alert-danger">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <section class="sc-card sc-filter-card">
        <div class="sc-card-head">
            <div>
                <h3><i class="fas fa-filter"></i> Filtros de búsqueda</h3>
                <p>Busca por trámite, estudiante o filtra por estado actual.</p>
            </div>
        </div>

        <div class="sc-card-body">
            <form method="GET" action="{{ route('cancelacion.secretaria.index') }}" class="sc-filter-form">
                <div class="sc-field sc-field-search">
                    <label for="buscar">Buscar</label>
                    <input
                        id="buscar"
                        type="text"
                        name="buscar"
                        value="{{ $buscar ?? '' }}"
                        placeholder="Buscar por número de trámite o nombre del estudiante">
                </div>

                <div class="sc-field sc-field-state">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        @foreach ($estados as $itemEstado)
                            <option value="{{ $itemEstado }}" {{ $estadoActual === strtoupper($itemEstado) ? 'selected' : '' }}>
                                {{ $estadoLegible($itemEstado) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sc-filter-actions">
                    <button type="submit" class="sc-btn sc-btn-primary">
                        <i class="fas fa-magnifying-glass"></i>
                        Filtrar
                    </button>

                    <a href="{{ route('cancelacion.secretaria.index') }}" class="sc-btn sc-btn-secondary">
                        <i class="fas fa-rotate-left"></i>
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </section>

    <section class="sc-card sc-table-card">
        <div class="sc-card-head">
            <div>
                <h3><i class="fas fa-list-check"></i> Trámites de cancelación</h3>
                <p>Listado general de solicitudes pendientes de revisión documental.</p>
            </div>

            <div class="sc-card-head-side">
                <span class="sc-counter-badge">Total: {{ $tramites->total() }}</span>
            </div>
        </div>

        <div class="sc-card-body sc-card-body-table">
            @if ($tramites->count() > 0)
                <div class="sc-table-wrap">
                    <table class="sc-table">
                        <thead>
                            <tr>
                                <th class="sc-text-center"># Trámite</th>
                                <th>Estudiante</th>
                                <th class="sc-text-center">Fecha</th>
                                <th class="sc-text-center">Estado</th>
                                <th class="sc-text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tramites as $tramite)
                                @php
                                    $estadoFila = strtoupper(trim((string) ($tramite->resolucion_de_tramite_academico ?? 'REVISION')));
                                @endphp
                                <tr>
                                    <td class="sc-text-center">
                                        <span class="sc-id-badge">#{{ $tramite->id_tramite }}</span>
                                    </td>

                                    <td>
                                        <div class="sc-student-cell">
                                            <strong>{{ $tramite->nombre_estudiante }}</strong>
                                            <small>Trámite académico de cancelación</small>
                                        </div>
                                    </td>

                                    <td class="sc-text-center">
                                        <div class="sc-date-cell">
                                            <strong>{{ $formatearFecha($tramite->fecha_solicitud ?? null) }}</strong>
                                        </div>
                                    </td>

                                    <td class="sc-text-center">
                                        <span class="{{ $badgeClass($estadoFila) }}">
                                            {{ $estadoLegible($estadoFila) }}
                                        </span>
                                    </td>

                                    <td class="sc-text-center">
                                        <a
                                            href="{{ route('cancelacion.secretaria.detalle', ['id_tramite' => $tramite->id_tramite]) }}"
                                            class="sc-btn sc-btn-outline sc-btn-sm">
                                            <i class="fas fa-eye"></i>
                                            Revisar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sc-pagination-wrap">
                    {{ $tramites->links() }}
                </div>
            @else
                <div class="sc-empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h4>No se encontraron trámites</h4>
                    <p>No hay trámites de cancelación disponibles para revisión con los filtros seleccionados.</p>
                </div>
            @endif
        </div>
    </section>

</div>
@endsection