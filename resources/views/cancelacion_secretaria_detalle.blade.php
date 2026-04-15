@extends('layouts.app-secretaria')

@section('title', 'Secretaría de Carrera - Detalle de Cancelación')
@section('titulo', 'Secretaría de Carrera - Detalle de Cancelación')

@section('content')
@php
    $estadoActual = strtoupper(trim((string) ($estado ?? 'REVISION')));

    $badgeClass = match ($estadoActual) {
        'REVISION'           => 'scd-status scd-status-review',
        'DEVUELTO'           => 'scd-status scd-status-returned',
        'LISTO_COORDINADORA' => 'scd-status scd-status-ready',
        default              => 'scd-status scd-status-neutral',
    };

    $estadoLegible = match ($estadoActual) {
        'REVISION'           => 'En revisión',
        'DEVUELTO'           => 'Devuelto a estudiante',
        'LISTO_COORDINADORA' => 'Listo para coordinadora',
        default              => $estadoActual ?: 'Sin estado',
    };

    $fechaSolicitud = !empty($tramite->fecha_solicitud)
        ? \Carbon\Carbon::parse($tramite->fecha_solicitud)->format('d/m/Y h:i A')
        : 'Sin fecha registrada';

    $hayDocumentos = isset($documentos) && $documentos->count() > 0;
@endphp

<div class="scd-page">

    <section class="scd-top-banner">
        <div class="scd-top-banner-copy">
            <h1>Detalle del trámite</h1>
            <p>Revisión documental del trámite de cancelación por Secretaría de Carrera.</p>
        </div>

        <a href="{{ route('cancelacion.secretaria.index') }}" class="scd-btn scd-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
    </section>

    @if (session('success'))
        <div class="scd-alert scd-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="scd-alert scd-alert-danger">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="scd-grid">

        <aside class="scd-sidebar">
            <section class="scd-card">
                <div class="scd-card-head">
                    <div>
                        <h3><i class="fas fa-circle-info"></i> Información del trámite</h3>
                        <p>Resumen general de la solicitud académica.</p>
                    </div>
                </div>

                <div class="scd-card-body">
                    <div class="scd-info-list">
                        <div class="scd-info-item">
                            <span>Número de trámite</span>
                            <strong>#{{ $tramite->id_tramite }}</strong>
                        </div>

                        <div class="scd-info-item">
                            <span>Estudiante</span>
                            <strong>{{ $tramite->nombre_estudiante }}</strong>
                        </div>

                        <div class="scd-info-item">
                            <span>Fecha de solicitud</span>
                            <strong>{{ $fechaSolicitud }}</strong>
                        </div>

                        <div class="scd-info-item">
                            <span>Estado actual</span>
                            <div>
                                <span class="{{ $badgeClass }}">
                                    {{ $estadoLegible }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="scd-note-box">
                        <span>Observación actual</span>
                        <div class="scd-note-content">
                            {{ !empty($tramite->descripcion) ? $tramite->descripcion : 'Sin observación registrada.' }}
                        </div>
                    </div>
                </div>
            </section>
        </aside>

        <div class="scd-main">

            <section class="scd-card">
                <div class="scd-card-head">
                    <div>
                        <h3><i class="fas fa-folder-open"></i> Documentos adjuntos</h3>
                        <p>Archivos cargados por el estudiante para revisión documental.</p>
                    </div>
                </div>

                <div class="scd-card-body">
                    @if ($hayDocumentos)
                        <div class="scd-doc-grid">
                            @foreach ($documentos as $documento)
                                @php
                                    $fechaCarga = !empty($documento->fecha_carga)
                                        ? \Carbon\Carbon::parse($documento->fecha_carga)->format('d/m/Y h:i A')
                                        : 'Sin fecha registrada';
                                @endphp

                                <article class="scd-doc-card">
                                    <div class="scd-doc-head">
                                        <div>
                                            <h4>{{ $documento->nombre_legible }}</h4>
                                            <small>{{ $documento->nombre_documento }}</small>
                                        </div>
                                    </div>

                                    <div class="scd-doc-meta">
                                        <span><i class="fas fa-calendar-days"></i> Cargado: {{ $fechaCarga }}</span>
                                    </div>

                                    <div class="scd-doc-actions">
                                        <a
                                            href="{{ route('cancelacion.secretaria.documento', ['id_documento' => $documento->id_documento]) }}"
                                            target="_blank"
                                            class="scd-btn scd-btn-outline scd-btn-sm">
                                            <i class="fas fa-file-arrow-down"></i>
                                            Ver documento
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="scd-empty-message scd-empty-warning">
                            <i class="fas fa-triangle-exclamation"></i>
                            <p>Este trámite no tiene documentos adjuntos para revisión.</p>
                        </div>
                    @endif
                </div>
            </section>

            @if ($estadoActual === 'REVISION')
                @if ($hayDocumentos)
                    <div class="scd-action-grid">

                        <section class="scd-card scd-card-danger">
                            <div class="scd-card-head">
                                <div>
                                    <h3><i class="fas fa-rotate-left"></i> Devolver al estudiante</h3>
                                    <p>Solicita correcciones o nueva carga de documentos.</p>
                                </div>
                            </div>

                            <div class="scd-card-body">
                                <form
                                    method="POST"
                                    action="{{ route('cancelacion.secretaria.devolver', ['id_tramite' => $tramite->id_tramite]) }}"
                                    class="scd-form"
                                    onsubmit="return confirm('¿Está segura de devolver la documentación al estudiante?');">
                                    @csrf

                                    <div class="scd-field">
                                        <label for="observacion_devolver">Observación para devolución</label>
                                        <textarea
                                            id="observacion_devolver"
                                            name="observacion"
                                            rows="6"
                                            placeholder="Explique qué documento debe corregir o volver a cargar..."
                                            required>{{ old('observacion') }}</textarea>
                                    </div>

                                    <div class="scd-form-actions">
                                        <button type="submit" class="scd-btn scd-btn-danger">
                                            <i class="fas fa-paper-plane"></i>
                                            Devolver documentación
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </section>

                        <section class="scd-card scd-card-success">
                            <div class="scd-card-head">
                                <div>
                                    <h3><i class="fas fa-share"></i> Enviar a Coordinadora</h3>
                                    <p>Marca el trámite como listo para dictamen.</p>
                                </div>
                            </div>

                            <div class="scd-card-body">
                                <form
                                    method="POST"
                                    action="{{ route('cancelacion.secretaria.listo', ['id_tramite' => $tramite->id_tramite]) }}"
                                    class="scd-form"
                                    onsubmit="return confirm('¿Desea marcar este trámite como listo para Coordinadora?');">
                                    @csrf

                                    <div class="scd-field">
                                        <label for="observacion_listo">Observación interna (opcional)</label>
                                        <textarea
                                            id="observacion_listo"
                                            name="observacion"
                                            rows="6"
                                            placeholder="Puede dejar una nota breve para Coordinadora...">{{ old('observacion') }}</textarea>
                                    </div>

                                    <div class="scd-form-actions">
                                        <button type="submit" class="scd-btn scd-btn-success">
                                            <i class="fas fa-check"></i>
                                            Marcar listo para Coordinadora
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </section>

                    </div>
                @else
                    <section class="scd-card">
                        <div class="scd-card-body">
                            <div class="scd-empty-message scd-empty-warning">
                                <i class="fas fa-triangle-exclamation"></i>
                                <p>No se puede devolver ni enviar a Coordinadora porque este trámite no tiene documentos cargados.</p>
                            </div>
                        </div>
                    </section>
                @endif
            @elseif ($estadoActual === 'DEVUELTO')
                <section class="scd-card">
                    <div class="scd-card-body">
                        <div class="scd-empty-message scd-empty-danger">
                            <i class="fas fa-circle-xmark"></i>
                            <p>Este trámite ya fue devuelto al estudiante. Está pendiente de corrección o nueva carga de documentos.</p>
                        </div>
                    </div>
                </section>
            @elseif ($estadoActual === 'LISTO_COORDINADORA')
                <section class="scd-card">
                    <div class="scd-card-body">
                        <div class="scd-empty-message scd-empty-success">
                            <i class="fas fa-circle-check"></i>
                            <p>Este trámite ya fue revisado por Secretaría y enviado a Coordinadora para su dictamen.</p>
                        </div>
                    </div>
                </section>
            @else
                <section class="scd-card">
                    <div class="scd-card-body">
                        <div class="scd-empty-message scd-empty-neutral">
                            <i class="fas fa-circle-info"></i>
                            <p>El trámite se encuentra en un estado que no permite acciones desde Secretaría.</p>
                        </div>
                    </div>
                </section>
            @endif

            <section class="scd-card">
                <div class="scd-card-body">
                    <div class="scd-rule-box">
                        <strong>Regla del proceso:</strong>
                        Secretaría de Carrera únicamente revisa la documentación. La resolución o dictamen final solo puede emitirla Coordinadora.
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>
@endsection