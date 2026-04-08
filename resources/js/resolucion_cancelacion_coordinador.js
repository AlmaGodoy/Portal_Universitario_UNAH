document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('resolucionCancelacionApp');
    if (!app) return;

    const config = {
        listadoUrl: app.dataset.urlListado,
        detalleBaseUrl: app.dataset.urlDetalleBase,
        resolverBaseUrl: app.dataset.urlResolverBase,
        documentoBaseUrl: app.dataset.urlDocumentoBase,
        csrfToken: app.dataset.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    };

    const refs = {
        flash: document.getElementById('rcFlash'),
        totalSolicitudes: document.getElementById('rcTotalSolicitudes'),
        recargarBtn: document.getElementById('rcRecargarBtn'),
        buscarInput: document.getElementById('rcBuscarInput'),
        filtroEstado: document.getElementById('rcFiltroEstado'),
        listadoBody: document.getElementById('rcListadoBody'),

        detalleVacio: document.getElementById('rcDetalleVacio'),
        detalleContenido: document.getElementById('rcDetalleContenido'),

        datoIdTramite: document.getElementById('rcDatoIdTramite'),
        datoEstudiante: document.getElementById('rcDatoEstudiante'),
        datoMotivo: document.getElementById('rcDatoMotivo'),
        datoFechaSolicitud: document.getElementById('rcDatoFechaSolicitud'),
        datoEstadoActual: document.getElementById('rcDatoEstadoActual'),
        datoExplicacion: document.getElementById('rcDatoExplicacion'),
        badgeEstado: document.getElementById('rcBadgeEstado'),
        documentosWrap: document.getElementById('rcDocumentosWrap'),
        resolucionActual: document.getElementById('rcResolucionActual'),

        resolverForm: document.getElementById('rcResolverForm'),
        idTramite: document.getElementById('rcIdTramite'),
        decision: document.getElementById('rcDecision'),
        observaciones: document.getElementById('rcObservaciones'),
        erroresForm: document.getElementById('rcErroresForm'),
        limpiarBtn: document.getElementById('rcLimpiarBtn')
    };

    const state = {
        solicitudes: [],
        tramiteSeleccionado: null,
        detalleActual: null
    };

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeText(value) {
        return String(value ?? '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function normalizeState(value) {
        const estado = normalizeText(value);

        if (['aprobada', 'aprobado', 'aceptada', 'aceptado'].includes(estado)) {
            return 'aprobada';
        }
        if (['rechazada', 'rechazado'].includes(estado)) {
            return 'rechazada';
        }
        if (['revision', 'revisión', 'devuelto', 'devuelta'].includes(estado)) {
            return 'revision';
        }
        if (['pendiente'].includes(estado)) {
            return 'pendiente';
        }

        return estado || 'pendiente';
    }

    function stateLabel(value) {
        const estado = normalizeState(value);

        switch (estado) {
            case 'aprobada':
                return 'Aprobada';
            case 'rechazada':
                return 'Rechazada';
            case 'revision':
                return 'Revisión';
            case 'pendiente':
            default:
                return 'Pendiente';
        }
    }

    function stateClass(value) {
        const estado = normalizeState(value);

        switch (estado) {
            case 'aprobada':
                return 'rc-badge rc-badge--ok';
            case 'rechazada':
                return 'rc-badge rc-badge--bad';
            case 'revision':
                return 'rc-badge rc-badge--warn';
            case 'pendiente':
            default:
                return 'rc-badge rc-badge--pending';
        }
    }

    function formatDate(value) {
        if (!value) return '—';

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return escapeHtml(value);
        }

        return new Intl.DateTimeFormat('es-HN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    function buildApiUrl(base, id) {
        return `${base.replace(/\/$/, '')}/${id}`;
    }

    function showFlash(message, type = 'success') {
        if (!refs.flash) return;

        refs.flash.hidden = false;
        refs.flash.className = `rc-flash rc-flash--${type}`;
        refs.flash.textContent = message;

        clearTimeout(refs.flash._timer);
        refs.flash._timer = setTimeout(() => {
            refs.flash.hidden = true;
        }, 3800);
    }

    function clearFormErrors() {
        if (!refs.erroresForm) return;
        refs.erroresForm.hidden = true;
        refs.erroresForm.innerHTML = '';
    }

    function showFormErrors(errors) {
        if (!refs.erroresForm) return;

        const items = [];

        if (typeof errors === 'string') {
            items.push(errors);
        } else if (Array.isArray(errors)) {
            items.push(...errors);
        } else if (errors && typeof errors === 'object') {
            Object.values(errors).forEach(value => {
                if (Array.isArray(value)) {
                    items.push(...value);
                } else {
                    items.push(value);
                }
            });
        }

        refs.erroresForm.hidden = false;
        refs.erroresForm.innerHTML = `
            <ul>
                ${items.map(item => `<li>${escapeHtml(item)}</li>`).join('')}
            </ul>
        `;
    }

    function setListadoLoading(message = 'Cargando solicitudes...') {
        refs.listadoBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">${escapeHtml(message)}</td>
            </tr>
        `;
    }

    function showEmptyDetail() {
        refs.detalleVacio.hidden = false;
        refs.detalleContenido.hidden = true;
        refs.idTramite.value = '';
        clearFormErrors();

        if (refs.decision) refs.decision.value = '';
        if (refs.observaciones) refs.observaciones.value = '';
    }

    function setDetailLoading() {
        refs.detalleVacio.hidden = true;
        refs.detalleContenido.hidden = false;

        refs.datoIdTramite.textContent = '...';
        refs.datoEstudiante.textContent = 'Cargando...';
        refs.datoMotivo.textContent = 'Cargando...';
        refs.datoFechaSolicitud.textContent = 'Cargando...';
        refs.datoEstadoActual.textContent = 'Cargando...';
        refs.datoExplicacion.textContent = 'Cargando...';
        refs.badgeEstado.className = 'rc-badge rc-badge--pending';
        refs.badgeEstado.textContent = 'Cargando';
        refs.documentosWrap.innerHTML = '<div class="rc-doc-empty">Cargando documentos...</div>';
        refs.resolucionActual.innerHTML = 'Cargando resolución...';
    }

    async function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error('La sesión expiró o el servidor devolvió una respuesta inesperada.');
        }

        return response.json();
    }

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                ...(options.headers || {})
            },
            ...options
        });

        const data = await parseJsonResponse(response);

        if (!response.ok) {
            const error = new Error(data?.mensaje || 'Ocurrió un error en la solicitud.');
            error.status = response.status;
            error.payload = data;
            throw error;
        }

        return data;
    }

    function getFilteredSolicitudes() {
        const search = normalizeText(refs.buscarInput?.value);
        const filterState = normalizeState(refs.filtroEstado?.value);

        return state.solicitudes.filter(item => {
            const rowState = normalizeState(item.estado_actual);
            const matchesState = !filterState || rowState === filterState;

            if (!matchesState) return false;

            if (!search) return true;

            const haystack = normalizeText([
                item.id_tramite,
                item.estudiante,
                item.motivo,
                item.explicacion,
                item.estado_actual
            ].join(' '));

            return haystack.includes(search);
        });
    }

    function renderListado() {
        const filtered = getFilteredSolicitudes();
        refs.totalSolicitudes.textContent = String(filtered.length);

        if (!filtered.length) {
            refs.listadoBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        No se encontraron solicitudes con los filtros actuales.
                    </td>
                </tr>
            `;
            return;
        }

        refs.listadoBody.innerHTML = filtered.map(item => {
            const isActive = Number(state.tramiteSeleccionado) === Number(item.id_tramite);

            return `
                <tr class="${isActive ? 'is-active' : ''}">
                    <td>#${escapeHtml(item.id_tramite)}</td>
                    <td>${escapeHtml(item.estudiante ?? '—')}</td>
                    <td>${escapeHtml(item.motivo ?? '—')}</td>
                    <td>${escapeHtml(formatDate(item.fecha_solicitud))}</td>
                    <td>
                        <span class="${stateClass(item.estado_actual)}">
                            ${escapeHtml(stateLabel(item.estado_actual))}
                        </span>
                    </td>
                    <td>${escapeHtml(item.total_documentos ?? 0)}</td>
                    <td>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary rc-btn-detalle"
                            data-id="${escapeHtml(item.id_tramite)}"
                        >
                            Ver detalle
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderDocumentos(documentos) {
        if (!Array.isArray(documentos) || documentos.length === 0) {
            refs.documentosWrap.innerHTML = `
                <div class="rc-doc-empty">
                    No hay documentos cargados para esta solicitud.
                </div>
            `;
            return;
        }

        refs.documentosWrap.innerHTML = documentos.map(doc => {
            const url = buildApiUrl(config.documentoBaseUrl, doc.id_documento);

            return `
                <article class="rc-doc-card">
                    <div class="rc-doc-card-top">
                        <div>
                            <h4>${escapeHtml(doc.nombre_documento ?? 'Documento')}</h4>
                            <p class="mb-1">
                                Tipo: <strong>${escapeHtml(doc.tipo_documento ?? '—')}</strong>
                            </p>
                            <p class="mb-0">
                                Fecha: <strong>${escapeHtml(formatDate(doc.fecha_carga))}</strong>
                            </p>
                        </div>

                        <a
                            href="${escapeHtml(url)}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-sm btn-primary"
                        >
                            Ver documento
                        </a>
                    </div>
                </article>
            `;
        }).join('');
    }

    function renderResolucionActual(resolucion) {
        if (!resolucion) {
            refs.resolucionActual.innerHTML = `
                <div class="rc-res-empty">
                    Aún no se ha emitido una resolución para este trámite.
                </div>
            `;
            return;
        }

        refs.resolucionActual.innerHTML = `
            <div class="rc-res-box">
                <p><strong>Estado:</strong> ${escapeHtml(stateLabel(resolucion.estado_validacion))}</p>
                <p><strong>Fecha:</strong> ${escapeHtml(formatDate(resolucion.fecha_resolucion))}</p>
                <p><strong>Observaciones:</strong></p>
                <div class="rc-res-obs">
                    ${escapeHtml(resolucion.observaciones || 'Sin observaciones registradas.')}
                </div>
            </div>
        `;
    }

    function renderDetalle(payload) {
        const detalle = payload?.detalle || null;
        const documentos = payload?.documentos || [];
        const resolucionActual = payload?.resolucionActual || null;

        if (!detalle) {
            showEmptyDetail();
            return;
        }

        refs.detalleVacio.hidden = true;
        refs.detalleContenido.hidden = false;

        refs.datoIdTramite.textContent = detalle.id_tramite ?? '—';
        refs.datoEstudiante.textContent = detalle.estudiante ?? '—';
        refs.datoMotivo.textContent = detalle.motivo ?? '—';
        refs.datoFechaSolicitud.textContent = formatDate(detalle.fecha_solicitud);
        refs.datoEstadoActual.textContent = stateLabel(detalle.resolucion_de_tramite_academico || 'pendiente');
        refs.datoExplicacion.textContent = detalle.explicacion ?? '—';

        const estado = resolucionActual?.estado_validacion || detalle.resolucion_de_tramite_academico || 'pendiente';
        refs.badgeEstado.className = stateClass(estado);
        refs.badgeEstado.textContent = stateLabel(estado);

        refs.idTramite.value = detalle.id_tramite ?? '';

        const valorDecision = resolucionActual?.estado_validacion
            ? normalizeState(resolucionActual.estado_validacion)
            : '';

        refs.decision.value = ['aprobada', 'rechazada', 'revision'].includes(valorDecision)
            ? valorDecision
            : '';

        refs.observaciones.value = resolucionActual?.observaciones || '';

        renderDocumentos(documentos);
        renderResolucionActual(resolucionActual);

        state.detalleActual = payload;
    }

    async function cargarListado() {
        try {
            setListadoLoading('Cargando solicitudes...');

            const json = await fetchJson(config.listadoUrl, {
                method: 'GET'
            });

            state.solicitudes = Array.isArray(json.data) ? json.data : [];
            renderListado();

            if (state.tramiteSeleccionado) {
                const sigueExistiendo = state.solicitudes.some(
                    item => Number(item.id_tramite) === Number(state.tramiteSeleccionado)
                );

                if (!sigueExistiendo) {
                    state.tramiteSeleccionado = null;
                    showEmptyDetail();
                } else {
                    renderListado();
                }
            }
        } catch (error) {
            console.error(error);
            setListadoLoading(error.message || 'No se pudo cargar el listado.');
            showFlash(error.message || 'No se pudo cargar el listado.', 'error');
        }
    }

    async function cargarDetalle(idTramite) {
        try {
            state.tramiteSeleccionado = Number(idTramite);
            renderListado();
            setDetailLoading();
            clearFormErrors();

            const url = buildApiUrl(config.detalleBaseUrl, idTramite);
            const json = await fetchJson(url, {
                method: 'GET'
            });

            renderDetalle(json.data);
        } catch (error) {
            console.error(error);
            showEmptyDetail();
            showFlash(error.message || 'No se pudo cargar el detalle.', 'error');
        }
    }

    async function guardarResolucion() {
        clearFormErrors();

        const idTramite = refs.idTramite.value;
        const payload = {
            decision: refs.decision.value,
            observaciones: refs.observaciones.value.trim()
        };

        const submitBtn = refs.resolverForm.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';

        try {
            if (!idTramite) {
                throw new Error('Debe seleccionar una solicitud antes de resolver.');
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Guardando...';
            }

            const url = buildApiUrl(config.resolverBaseUrl, idTramite);

            const json = await fetchJson(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify(payload)
            });

            showFlash(json.mensaje || 'La resolución se guardó correctamente.', 'success');

            await cargarListado();
            await cargarDetalle(idTramite);
        } catch (error) {
            console.error(error);

            if (error.status === 422 && error.payload) {
                const apiErrors = error.payload.errors || error.payload.mensaje || 'Error de validación.';
                showFormErrors(apiErrors);
            } else {
                showFormErrors(error.message || 'No se pudo guardar la resolución.');
            }

            showFlash(error.message || 'No se pudo guardar la resolución.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText || 'Guardar resolución';
            }
        }
    }

    refs.recargarBtn?.addEventListener('click', () => {
        cargarListado();
    });

    refs.buscarInput?.addEventListener('input', () => {
        renderListado();
    });

    refs.filtroEstado?.addEventListener('change', () => {
        renderListado();
    });

    refs.listadoBody?.addEventListener('click', (event) => {
        const btn = event.target.closest('.rc-btn-detalle');
        if (!btn) return;

        const id = btn.dataset.id;
        if (!id) return;

        cargarDetalle(id);
    });

    refs.limpiarBtn?.addEventListener('click', () => {
        clearFormErrors();
        refs.decision.value = '';
        refs.observaciones.value = '';
    });

    refs.resolverForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        guardarResolucion();
    });

    showEmptyDetail();
    cargarListado();
});
