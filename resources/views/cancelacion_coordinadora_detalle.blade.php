@extends('layouts.app-coordinador')

@section('title', 'Coordinadora - Detalle de Cancelación')

@section('content')
@php
    $estadoActual = strtoupper(trim((string) ($estado ?? 'LISTO_COORDINADORA')));

    $badgeClass = match ($estadoActual) {
        'LISTO_COORDINADORA' => 'bg-primary',
        'APROBADA'           => 'bg-success',
        'RECHAZADA'          => 'bg-danger',
        default              => 'bg-secondary',
    };

    $estadoLegible = match ($estadoActual) {
        'LISTO_COORDINADORA' => 'Listo para dictamen',
        'APROBADA'           => 'Aprobada',
        'RECHAZADA'          => 'Rechazada',
        default              => $estadoActual ?: 'Sin estado',
    };

    $fechaSolicitud = !empty($tramite->fecha_solicitud)
        ? \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('d/m/Y h:i A')
        : 'Sin fecha registrada';

    $hayDocumentos = isset($documentos) && $documentos->count() > 0;
@endphp

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-1">Detalle del trámite</h2>
            <p class="text-muted mb-0">Dictamen final por Coordinadora</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('cancelacion.coordinadora.index') }}" class="btn btn-outline-secondary">
                Volver
            </a>
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

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold">Información del trámite</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Número de trámite</small>
                        <span class="fw-bold fs-5">#{{ $tramite->id_tramite }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Estudiante</small>
                        <span class="fw-semibold">{{ $tramite->nombre_estudiante }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Fecha de solicitud</small>
                        <span>{{ $fechaSolicitud }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Estado actual</small>
                        <span class="badge {{ $badgeClass }} fs-6">
                            {{ $estadoLegible }}
                        </span>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted d-block">Observación actual</small>
                        <div class="border rounded p-2 bg-light">
                            {{ !empty($tramite->descripcion) ? $tramite->descripcion : 'Sin observación registrada.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0 fw-bold">Documentos adjuntos</h5>
                </div>

                <div class="card-body">
                    @if ($hayDocumentos)
                        <div class="row g-3">
                            @foreach ($documentos as $documento)
                                @php
                                    $fechaCarga = !empty($documento->fecha_carga)
                                        ? \Carbon\Carbon::parse($documento->fecha_carga)->format('d/m/Y h:i A')
                                        : 'Sin fecha registrada';
                                @endphp

                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $documento->nombre_legible }}</h6>
                                                <div class="small text-muted">{{ $documento->nombre_documento }}</div>
                                            </div>
                                        </div>

                                        <div class="small text-muted mb-3">
                                            Cargado: {{ $fechaCarga }}
                                        </div>

                                        <a
                                            href="{{ route('cancelacion.coordinadora.documento', ['id_documento' => $documento->id_documento]) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            Ver documento
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted">
                            No hay documentos adjuntos para este trámite.
                        </div>
                    @endif
                </div>
            </div>

            @if ($estadoActual === 'LISTO_COORDINADORA')
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 fw-bold text-success">Aprobar trámite</h5>
                            </div>
                            <div class="card-body">
                                <form
                                    method="POST"
                                    action="{{ route('cancelacion.coordinadora.aprobar', ['id_tramite' => $tramite->id_tramite]) }}"
                                    onsubmit="return confirm('¿Desea aprobar este trámite?');">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Observación (opcional)</label>
                                        <textarea
                                            name="observacion"
                                            class="form-control"
                                            rows="5"
                                            placeholder="Puede dejar una observación interna del dictamen...">{{ old('observacion') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success w-100">
                                        Aprobar trámite
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0 fw-bold text-danger">Rechazar trámite</h5>
                            </div>
                            <div class="card-body">
                                <form
                                    method="POST"
                                    action="{{ route('cancelacion.coordinadora.rechazar', ['id_tramite' => $tramite->id_tramite]) }}"
                                    onsubmit="return confirm('¿Desea rechazar este trámite?');">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Observación del rechazo</label>
                                        <textarea
                                            name="observacion"
                                            class="form-control"
                                            rows="5"
                                            placeholder="Explique la razón del rechazo..."
                                            required>{{ old('observacion') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-danger w-100">
                                        Rechazar trámite
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            Este trámite ya tiene un estado final y no puede volver a dictaminarse desde esta vista.
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <div class="small text-muted">
                        <strong>Regla del proceso:</strong> Coordinadora es la única encargada de emitir el dictamen final del trámite de cancelación.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection