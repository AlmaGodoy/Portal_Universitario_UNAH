@extends('layouts.app-coordinador')

@section('title', 'Coordinadora - Dictamen de Cancelaciones')

@section('content')
@php
    $estadoActual = strtoupper(trim((string) ($estado ?? '')));

    $badgeClass = function ($value) {
        return match (strtoupper(trim((string) $value))) {
            'LISTO_COORDINADORA' => 'bg-primary',
            'APROBADA'           => 'bg-success',
            'RECHAZADA'          => 'bg-danger',
            default              => 'bg-secondary',
        };
    };

    $estadoLegible = function ($value) {
        return match (strtoupper(trim((string) $value))) {
            'LISTO_COORDINADORA' => 'Listo para dictamen',
            'APROBADA'           => 'Aprobada',
            'RECHAZADA'          => 'Rechazada',
            default              => $value ?: 'Sin estado',
        };
    };

    $formatearFecha = function ($fecha) {
        return !empty($fecha)
            ? \Carbon\Carbon::parse($fecha)->format('d/m/Y h:i A')
            : 'Sin fecha registrada';
    };
@endphp

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Coordinadora</h2>
            <p class="text-muted mb-0">Bandeja de dictamen para trámites de cancelación</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cancelacion.coordinadora.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Buscar</label>
                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        value="{{ $buscar ?? '' }}"
                        placeholder="Buscar por número de trámite o nombre del estudiante">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($estados as $itemEstado)
                            <option value="{{ $itemEstado }}" {{ $estadoActual === strtoupper($itemEstado) ? 'selected' : '' }}>
                                {{ $estadoLegible($itemEstado) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Filtrar
                    </button>

                    <a href="{{ route('cancelacion.coordinadora.index') }}" class="btn btn-outline-secondary w-100">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Trámites para dictamen</h5>
            <span class="badge bg-dark">
                Total: {{ $tramites->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            @if ($tramites->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center"># Trámite</th>
                                <th>Estudiante</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tramites as $tramite)
                                @php
                                    $estadoFila = strtoupper(trim((string) ($tramite->resolucion_de_tramite_academico ?? 'LISTO_COORDINADORA')));
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold">{{ $tramite->id_tramite }}</td>
                                    <td>{{ $tramite->nombre_estudiante }}</td>
                                    <td class="text-center">
                                        {{ $formatearFecha($tramite->fecha_solicitud ?? null) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass($estadoFila) }}">
                                            {{ $estadoLegible($estadoFila) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a
                                            href="{{ route('cancelacion.coordinadora.detalle', ['id_tramite' => $tramite->id_tramite]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Revisar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $tramites->links() }}
                </div>
            @else
                <div class="p-4 text-center text-muted">
                    No se encontraron trámites de cancelación para dictamen.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection