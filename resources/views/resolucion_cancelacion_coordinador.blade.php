@extends('layouts.app-coordinador')

@section('titulo', 'Resolución de Cancelación Excepcional')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div
        id="resolucionCancelacionApp"
        class="rc-page"
        data-url-listado="{{ route('api.resolucion.cancelacion.listado') }}"
        data-url-detalle-base="{{ url('/api/resolucion-cancelacion/detalle') }}"
        data-url-resolver-base="{{ url('/api/resolucion-cancelacion/resolver') }}"
        data-url-documento-base="{{ url('/api/resolucion-cancelacion/documento') }}"
        data-csrf-token="{{ csrf_token() }}"
    >
        <div class="rc-shell">
            <div class="rc-header">
                <h1 class="rc-title">Resolución de Cancelación Excepcional</h1>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger rc-alert-static">
                    {{ $errors->first() }}
                </div>
            @endif

            <div id="rcFlash" class="rc-flash" hidden></div>

            <div class="rc-grid">
                {{-- LISTADO --}}
                <section class="rc-card rc-list-card">
                    <div class="rc-card-head">
                        <div>
                            <h2>Solicitudes recibidas</h2>
                            <p>
                                Total:
                                <strong id="rcTotalSolicitudes">0</strong>
                            </p>
                        </div>

                        <button type="button" class="btn btn-outline-primary rc-refresh-btn" id="rcRecargarBtn">
                            Actualizar
                        </button>
                    </div>

                    <div class="rc-toolbar">
                        <div class="rc-field-inline">
                            <label for="rcBuscarInput">Buscar</label>
                            <input
                                type="text"
                                id="rcBuscarInput"
                                class="form-control"
                                placeholder="Buscar por estudiante, motivo o ID"
                            >
                        </div>

                        <div class="rc-field-inline">
                            <label for="rcFiltroEstado">Estado</label>
                            <select id="rcFiltroEstado" class="form-select">
                                <option value="pendiente">Pendiente</option>
                                <option value="revision">Revisión</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                            </select>
                        </div>
                    </div>

                    <div class="rc-table-wrap">
                        <table class="table rc-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Estudiante</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Docs</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="rcListadoBody">
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        Cargando solicitudes...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- DETALLE --}}
                <section class="rc-card rc-detail-card">
                    <div id="rcDetalleVacio" class="rc-empty-state">
                        <div class="rc-empty-icon">📄</div>
                        <h3>Seleccione una solicitud</h3>
                        <p>
                            Al elegir un trámite del listado podrá ver su detalle,
                            revisar la documentación y emitir la resolución.
                        </p>
                    </div>

                    <div id="rcDetalleContenido" hidden>
                        <div class="rc-detail-top">
                            <div>
                                <h2 class="rc-detail-title">
                                    Solicitud #<span id="rcDatoIdTramite">—</span>
                                </h2>
                            </div>

                            <span id="rcBadgeEstado" class="rc-badge">
                                Pendiente
                            </span>
                        </div>

                        <div class="rc-detail-grid">
                            <div class="rc-info-box">
                                <span class="rc-info-label">Estudiante</span>
                                <strong id="rcDatoEstudiante">—</strong>
                            </div>

                            <div class="rc-info-box">
                                <span class="rc-info-label">Motivo</span>
                                <strong id="rcDatoMotivo">—</strong>
                            </div>

                            <div class="rc-info-box">
                                <span class="rc-info-label">Fecha de solicitud</span>
                                <strong id="rcDatoFechaSolicitud">—</strong>
                            </div>

                            <div class="rc-info-box">
                                <span class="rc-info-label">Estado actual</span>
                                <strong id="rcDatoEstadoActual">—</strong>
                            </div>
                        </div>

                        <div class="rc-section">
                            <h3>Explicación del estudiante</h3>
                            <div id="rcDatoExplicacion" class="rc-text-box">
                                —
                            </div>
                        </div>

                        <div class="rc-section">
                            <div class="rc-section-head">
                                <h3>Documentos adjuntos</h3>
                            </div>

                            <div id="rcDocumentosWrap" class="rc-doc-list">
                                <div class="rc-doc-empty">
                                    No hay documentos cargados.
                                </div>
                            </div>
                        </div>

                        <div class="rc-section">
                            <div class="rc-section-head">
                                <h3>Resolución actual</h3>
                            </div>

                            <div id="rcResolucionActual" class="rc-text-box">
                                Aún no se ha emitido una resolución para este trámite.
                            </div>
                        </div>

                        <div class="rc-section">
                            <div class="rc-section-head">
                                <h3>Emitir resolución</h3>
                            </div>

                            <form id="rcResolverForm" class="rc-form">
                                <input type="hidden" name="id_tramite" id="rcIdTramite">

                                <div class="rc-form-grid">
                                    <div class="rc-field-block">
                                        <label for="rcDecision">Decisión</label>
                                        <select name="decision" id="rcDecision" class="form-select" required>
                                            <option value="" selected>Seleccione una opción</option>
                                            <option value="aprobada">Aprobar</option>
                                            <option value="rechazada">Rechazar</option>
                                            <option value="revision">Enviar a revisión</option>
                                        </select>
                                    </div>

                                    <div class="rc-field-block rc-field-block-full">
                                        <label for="rcObservaciones">Observaciones</label>
                                        <textarea
                                            name="observaciones"
                                            id="rcObservaciones"
                                            class="form-control"
                                            rows="5"
                                            placeholder="Escriba aquí las observaciones del dictamen..."
                                        ></textarea>
                                        <small class="rc-help-text">
                                            Las observaciones son obligatorias si rechaza o envía a revisión.
                                        </small>
                                    </div>
                                </div>

                                <div id="rcErroresForm" class="rc-form-errors" hidden></div>

                                <div class="rc-actions">
                                    <button type="submit" class="btn btn-primary">
                                        Guardar resolución
                                    </button>

                                    <button type="button" id="rcLimpiarBtn" class="btn btn-outline-secondary">
                                        Limpiar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
