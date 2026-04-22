@extends('layouts.app-secretaria')

@section('titulo', 'Revisión de Equivalencias')

@section('content')
<link rel="stylesheet" href="{{ asset('css/equivalencias.css') }}">

<div class="eq-page">
    <div class="eq-hero">
        <div>
            <span class="eq-kicker">Panel de revisión</span>
            <h1>Revisión de solicitudes de equivalencia</h1>
            <p>
                Revisa las solicitudes pendientes, valida las materias seleccionadas
                y actualiza el estado general del trámite.
            </p>
        </div>
        <div class="eq-hero-badge">
            <i class="fas fa-user-check"></i>
            <span>Revisor</span>
        </div>
    </div>

    <div id="eqRevisorAlert" class="eq-alert d-none"></div>

    <div class="eq-review-grid">
        <aside class="eq-card">
            <div class="eq-card-head">
                <h3><i class="fas fa-hourglass-half"></i> Solicitudes pendientes</h3>
                <p>Selecciona una solicitud para revisarla.</p>
            </div>

            <div id="panelPendientes" class="eq-list-wrap">
                <div class="eq-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Cargando solicitudes...</span>
                </div>
            </div>
        </aside>

        <section class="eq-review-main">
            <section class="eq-card">
                <div class="eq-card-head">
                    <h3><i class="fas fa-id-card"></i> Cabecera de la solicitud</h3>
                    <p>Información general y documento soporte.</p>
                </div>

                <div id="cabeceraSolicitud" class="eq-empty">
                    <i class="fas fa-arrow-left"></i>
                    <span>Selecciona una solicitud para comenzar.</span>
                </div>
            </section>

            <section class="eq-card">
                <div class="eq-card-head">
                    <h3><i class="fas fa-list-ul"></i> Detalle de materias</h3>
                    <p>Valida materia por materia.</p>
                </div>

                <div class="eq-table-wrap">
                    <table class="eq-table">
                        <thead>
                            <tr>
                                <th>Código viejo</th>
                                <th>Asignatura vieja</th>
                                <th>Nota</th>
                                <th>Equivalencias nuevo plan</th>
                                <th>Validación</th>
                                <th>Observación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDetalleSolicitud">
                            <tr>
                                <td colspan="7" class="eq-empty-row">Sin detalle cargado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="eq-card">
                <div class="eq-card-head">
                    <h3><i class="fas fa-stamp"></i> Validación general</h3>
                    <p>Actualiza el estado final de la solicitud.</p>
                </div>

                <div class="eq-form-grid">
                    <div class="eq-field">
                        <label for="estado_solicitud_revisor">Estado</label>
                        <select id="estado_solicitud_revisor">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="EN_REVISION">EN REVISION</option>
                            <option value="APROBADA"></option>
                            <option value="RECHAZADA"></option>
                        </select>
                    </div>

                    <div class="eq-field eq-field-full">
                        <label for="observacion_revisor">Observación del revisor</label>
                        <textarea id="observacion_revisor" rows="3" placeholder="Observación general"></textarea>
                    </div>
                </div>

                <div class="eq-actions">
                    <button id="btnGuardarEstadoSolicitud" type="button" class="eq-btn eq-btn-primary">
                        <i class="fas fa-check"></i>
                        Guardar estado
                    </button>
                </div>
            </section>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '{{ csrf_token() }}';

    const routes = {
        pendientes: '{{ route('api.equivalencias.pendientes') }}',
        cabecera: '{{ route('api.equivalencias.solicitud.cabecera', ['idSolicitud' => '__ID__']) }}',
        detalle: '{{ route('api.equivalencias.solicitud.detalle', ['idSolicitud' => '__ID__']) }}',
        documento: '{{ route('api.equivalencias.solicitud.documento', ['idSolicitud' => '__ID__']) }}',
        validarDetalle: '{{ route('api.equivalencias.detalle.validar') }}',
        validarSolicitud: '{{ route('api.equivalencias.validar') }}',
    };

    let solicitudActual = null;

    const alertBox = document.getElementById('eqRevisorAlert');
    const panelPendientes = document.getElementById('panelPendientes');
    const cabeceraSolicitud = document.getElementById('cabeceraSolicitud');
    const tablaDetalleSolicitud = document.getElementById('tablaDetalleSolicitud');
    const estadoSolicitudInput = document.getElementById('estado_solicitud_revisor');
    const observacionRevisorInput = document.getElementById('observacion_revisor');
    const btnGuardarEstadoSolicitud = document.getElementById('btnGuardarEstadoSolicitud');

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function mostrarAlerta(message, type = 'success') {
        alertBox.className = 'eq-alert';
        alertBox.classList.add(type === 'success' ? 'eq-alert-success' : 'eq-alert-error');
        alertBox.classList.remove('d-none');
        alertBox.textContent = message;
    }

    async function cargarPendientes() {
        try {
            const response = await fetch(routes.pendientes, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!data.ok || !data.data || data.data.length === 0) {
                panelPendientes.innerHTML = `
                    <div class="eq-empty">
                        <i class="fas fa-inbox"></i>
                        <span>No hay solicitudes pendientes.</span>
                    </div>
                `;
                return;
            }

            panelPendientes.innerHTML = data.data.map(item => `
                <article class="eq-request-item js-pendiente-item" data-id="${item.id_solicitud_equivalencia}">
                    <div class="eq-request-top">
                        <div>
                            <h4>Solicitud #${item.id_solicitud_equivalencia}</h4>
                            <p>Persona ID: ${item.id_persona}</p>
                        </div>
                        <span class="eq-status eq-status-${String(item.estado_solicitud).toLowerCase()}">
                            ${escapeHtml(item.estado_solicitud)}
                        </span>
                    </div>
                    <div class="eq-request-meta">
                        <span><i class="fas fa-calendar-days"></i> ${escapeHtml(item.fecha_solicitud ?? '-')}</span>
                        <span><i class="fas fa-list-check"></i> ${escapeHtml(item.total_materias_marcadas ?? 0)} materias</span>
                    </div>
                </article>
            `).join('');

            document.querySelectorAll('.js-pendiente-item').forEach(item => {
                item.addEventListener('click', () => cargarSolicitudCompleta(item.dataset.id));
            });
        } catch (error) {
            panelPendientes.innerHTML = `
                <div class="eq-empty">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>Error al cargar solicitudes.</span>
                </div>
            `;
        }
    }

    async function cargarCabecera(idSolicitud) {
        const response = await fetch(routes.cabecera.replace('__ID__', idSolicitud), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (!data.ok || !data.data) {
            throw new Error('No fue posible cargar la cabecera.');
        }

        solicitudActual = data.data;

        cabeceraSolicitud.innerHTML = `
            <div class="eq-head-summary">
                <div class="eq-summary-grid">
                    <div class="eq-summary-item">
                        <span>ID Solicitud</span>
                        <strong>#${escapeHtml(data.data.id_solicitud_equivalencia)}</strong>
                    </div>
                    <div class="eq-summary-item">
                        <span>ID Persona</span>
                        <strong>${escapeHtml(data.data.id_persona)}</strong>
                    </div>
                    <div class="eq-summary-item">
                        <span>Plan viejo</span>
                        <strong>${escapeHtml(data.data.version_plan_viejo)}</strong>
                    </div>
                    <div class="eq-summary-item">
                        <span>Plan nuevo</span>
                        <strong>${escapeHtml(data.data.version_plan_nuevo)}</strong>
                    </div>
                    <div class="eq-summary-item">
                        <span>Estado</span>
                        <strong>${escapeHtml(data.data.estado_solicitud)}</strong>
                    </div>
                    <div class="eq-summary-item">
                        <span>Documento</span>
                        <a class="eq-link-btn" target="_blank"
                           href="${routes.documento.replace('__ID__', data.data.id_solicitud_equivalencia)}">
                            <i class="fas fa-file-arrow-down"></i> Descargar
                        </a>
                    </div>
                </div>

                <div class="eq-summary-note">
                    <strong>Observación del alumno:</strong>
                    <p>${escapeHtml(data.data.observacion_alumno || 'Sin observación.')}</p>
                </div>
            </div>
        `;

        estadoSolicitudInput.value = data.data.estado_solicitud || 'PENDIENTE';
        observacionRevisorInput.value = data.data.observacion_revisor || '';
    }

    async function cargarDetalle(idSolicitud) {
        tablaDetalleSolicitud.innerHTML = `
            <tr>
                <td colspan="7" class="eq-empty-row">Cargando detalle...</td>
            </tr>
        `;

        const response = await fetch(routes.detalle.replace('__ID__', idSolicitud), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (!data.ok || !data.data || data.data.length === 0) {
            tablaDetalleSolicitud.innerHTML = `
                <tr>
                    <td colspan="7" class="eq-empty-row">No hay detalle disponible.</td>
                </tr>
            `;
            return;
        }

        tablaDetalleSolicitud.innerHTML = data.data.map(item => `
            <tr>
                <td>${escapeHtml(item.codigo_viejo)}</td>
                <td>${escapeHtml(item.asignatura_vieja)}</td>
                <td>${escapeHtml(item.nota_final ?? '-')}</td>
                <td>${escapeHtml(item.equivalencias_plan_nuevo || 'Sin equivalencia')}</td>
                <td>
                    <select class="eq-validate-select" data-codigo="${escapeHtml(item.codigo_viejo)}">
                        <option value="">Seleccione</option>
                        <option value="1" ${item.validada_revisor === 1 || item.validada_revisor === '1' ? 'selected' : ''}>Validada</option>
                        <option value="0" ${item.validada_revisor === 0 || item.validada_revisor === '0' ? 'selected' : ''}>Rechazada</option>
                    </select>
                </td>
                <td>
                    <textarea class="eq-validate-text" data-codigo="${escapeHtml(item.codigo_viejo)}" rows="2">${escapeHtml(item.observacion_revision || '')}</textarea>
                </td>
                <td>
                    <button type="button"
                            class="eq-btn eq-btn-secondary eq-btn-sm js-save-detail"
                            data-codigo="${escapeHtml(item.codigo_viejo)}">
                        Guardar
                    </button>
                </td>
            </tr>
        `).join('');

        document.querySelectorAll('.js-save-detail').forEach(btn => {
            btn.addEventListener('click', () => guardarValidacionDetalle(btn.dataset.codigo));
        });
    }

    async function guardarValidacionDetalle(codigo) {
        if (!solicitudActual) {
            mostrarAlerta('Selecciona una solicitud primero.', 'error');
            return;
        }

        const select = document.querySelector(`.eq-validate-select[data-codigo="${CSS.escape(codigo)}"]`);
        const textarea = document.querySelector(`.eq-validate-text[data-codigo="${CSS.escape(codigo)}"]`);

        if (!select || select.value === '') {
            mostrarAlerta(`Debes seleccionar una validación para ${codigo}.`, 'error');
            return;
        }

        try {
            const response = await fetch(routes.validarDetalle, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    id_solicitud_equivalencia: solicitudActual.id_solicitud_equivalencia,
                    version_plan_viejo: solicitudActual.version_plan_viejo,
                    codigo_asignatura_viejo: codigo,
                    validada_revisor: Number(select.value),
                    observacion_revision: textarea ? textarea.value : '',
                })
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No fue posible validar el detalle.');
            }

            mostrarAlerta(`Detalle ${codigo} guardado correctamente.`, 'success');
        } catch (error) {
            mostrarAlerta(error.message || 'Error al validar detalle.', 'error');
        }
    }

    async function guardarEstadoSolicitud() {
        if (!solicitudActual) {
            mostrarAlerta('Selecciona una solicitud primero.', 'error');
            return;
        }

        try {
            const response = await fetch(routes.validarSolicitud, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    id_solicitud_equivalencia: solicitudActual.id_solicitud_equivalencia,
                    estado_solicitud: estadoSolicitudInput.value,
                    observacion_revisor: observacionRevisorInput.value,
                })
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No fue posible actualizar la solicitud.');
            }

            mostrarAlerta('Estado de la solicitud actualizado correctamente.', 'success');
            await cargarPendientes();
            await cargarSolicitudCompleta(solicitudActual.id_solicitud_equivalencia);
        } catch (error) {
            mostrarAlerta(error.message || 'Error al guardar el estado.', 'error');
        }
    }

    async function cargarSolicitudCompleta(idSolicitud) {
        await cargarCabecera(idSolicitud);
        await cargarDetalle(idSolicitud);
    }

    btnGuardarEstadoSolicitud.addEventListener('click', guardarEstadoSolicitud);
    cargarPendientes();
});
</script>
@endsection
