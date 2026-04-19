@extends('layouts.app-secretaria')

@section('titulo', 'Soporte')

@section('content')
<link rel="stylesheet" href="{{ asset('css/soporte_estudiante.css') }}">

<style>
    .sec-support-page {
        padding: 24px;
    }

    .sec-support-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 28px 28px 24px;
        margin-bottom: 24px;
        background: linear-gradient(135deg, #17346c 0%, #1d4f9f 55%, #245fbc 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(17, 43, 94, .22);
    }

    .sec-support-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,.16), transparent 28%),
            radial-gradient(circle at bottom left, rgba(241,190,26,.16), transparent 26%);
        pointer-events: none;
    }

    .sec-support-kicker {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,.14);
        color: #ffe082;
        font-size: .86rem;
        font-weight: 800;
        letter-spacing: .02em;
        margin-bottom: 14px;
    }

    .sec-support-hero h1 {
        position: relative;
        z-index: 1;
        margin: 0 0 10px;
        font-size: 2rem;
        font-weight: 800;
    }

    .sec-support-hero p {
        position: relative;
        z-index: 1;
        margin: 0;
        max-width: 860px;
        color: rgba(255,255,255,.92);
        font-size: 1rem;
        line-height: 1.65;
    }

    .sec-support-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .sec-summary-card {
        background: #fff;
        border-radius: 20px;
        padding: 18px 18px;
        box-shadow: 0 12px 28px rgba(15, 32, 68, .08);
        border: 1px solid rgba(23, 52, 108, .08);
    }

    .sec-summary-label {
        display: block;
        color: #667085;
        font-size: .86rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .sec-summary-value {
        color: #17346c;
        font-size: 1.9rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .sec-summary-note {
        color: #7b8798;
        font-size: .82rem;
        font-weight: 600;
    }

    .sec-support-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, .9fr);
        gap: 24px;
    }

    .sec-support-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 14px 34px rgba(15, 32, 68, .08);
        border: 1px solid rgba(23, 52, 108, .08);
        overflow: hidden;
    }

    .sec-support-card-header {
        padding: 22px 22px 14px;
        border-bottom: 1px solid #eef2f7;
    }

    .sec-support-card-header h2 {
        margin: 0 0 6px;
        color: #17346c;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .sec-support-card-header p {
        margin: 0;
        color: #6b7280;
        font-size: .93rem;
        line-height: 1.55;
    }

    .sec-support-card-body {
        padding: 22px;
    }

    .sec-support-toolbar {
        display: grid;
        grid-template-columns: 1fr 190px;
        gap: 14px;
        margin-bottom: 18px;
    }

    .sec-support-input,
    .sec-support-select {
        width: 100%;
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid #d7dfeb;
        background: #fff;
        padding: 0 14px;
        font-size: .95rem;
        color: #24324b;
        outline: none;
        transition: all .2s ease;
    }

    .sec-support-input:focus,
    .sec-support-select:focus {
        border-color: #2956ac;
        box-shadow: 0 0 0 4px rgba(41, 86, 172, .10);
    }

    .sec-ticket-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .sec-ticket-item {
        border: 1px solid #e8edf5;
        border-radius: 18px;
        padding: 16px;
        background: #fff;
        transition: all .2s ease;
        cursor: pointer;
    }

    .sec-ticket-item:hover {
        border-color: rgba(41, 86, 172, .28);
        box-shadow: 0 10px 24px rgba(41, 86, 172, .08);
        transform: translateY(-1px);
    }

    .sec-ticket-item.active {
        border-color: #2956ac;
        background: #f8fbff;
        box-shadow: 0 12px 26px rgba(41, 86, 172, .10);
    }

    .sec-ticket-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .sec-ticket-code {
        color: #17346c;
        font-size: .95rem;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .sec-ticket-subject {
        color: #24324b;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .sec-ticket-meta {
        color: #7a8699;
        font-size: .84rem;
        line-height: 1.5;
    }

    .sec-ticket-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .sec-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
    }

    .sec-badge-estado-pendiente {
        background: rgba(241,190,26,.18);
        color: #946c00;
    }

    .sec-badge-estado-proceso {
        background: rgba(41,86,172,.14);
        color: #2956ac;
    }

    .sec-badge-estado-resuelto {
        background: rgba(28,163,98,.14);
        color: #138a50;
    }

    .sec-badge-prioridad-alta {
        background: rgba(226,59,53,.14);
        color: #c73737;
    }

    .sec-badge-prioridad-media {
        background: rgba(245,158,11,.14);
        color: #b76b00;
    }

    .sec-badge-prioridad-baja {
        background: rgba(107,114,128,.14);
        color: #5b6472;
    }

    .sec-empty-state {
        border: 1px dashed #d4dcec;
        border-radius: 18px;
        padding: 28px 18px;
        text-align: center;
        color: #667085;
        background: #fafcff;
    }

    .sec-detail-shell {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .sec-detail-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .sec-detail-title {
        color: #17346c;
        font-size: 1.08rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .sec-detail-subtitle {
        color: #7a8699;
        font-size: .86rem;
        line-height: 1.5;
    }

    .sec-detail-block {
        border: 1px solid #e9eef6;
        border-radius: 18px;
        padding: 16px;
        background: #fff;
    }

    .sec-detail-block h3 {
        color: #17346c;
        font-size: .96rem;
        font-weight: 800;
        margin: 0 0 12px;
    }

    .sec-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .sec-detail-field {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 12px;
    }

    .sec-detail-field span {
        display: block;
        color: #7a8699;
        font-size: .78rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .sec-detail-field strong {
        color: #24324b;
        font-size: .93rem;
        font-weight: 700;
        word-break: break-word;
    }

    .sec-detail-description {
        color: #374151;
        font-size: .95rem;
        line-height: 1.7;
        white-space: pre-line;
    }

    .sec-detail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .sec-btn {
        border: none;
        border-radius: 14px;
        min-height: 44px;
        padding: 0 16px;
        font-weight: 800;
        font-size: .92rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all .2s ease;
    }

    .sec-btn-primary {
        background: linear-gradient(135deg, #17346c 0%, #2956ac 100%);
        color: #fff;
    }

    .sec-btn-primary:hover {
        filter: brightness(.98);
        transform: translateY(-1px);
    }

    .sec-btn-success {
        background: linear-gradient(135deg, #159957 0%, #138a50 100%);
        color: #fff;
    }

    .sec-btn-success:hover {
        filter: brightness(.98);
        transform: translateY(-1px);
    }

    .sec-btn-light {
        background: #eef3fb;
        color: #17346c;
    }

    .sec-btn-light:hover {
        background: #e7eef9;
    }

    .sec-support-message {
        display: none;
        margin-top: 14px;
        border-radius: 14px;
        padding: 14px 16px;
        font-weight: 700;
        font-size: .92rem;
    }

    .sec-support-message.is-success {
        display: block;
        background: rgba(21,153,87,.10);
        color: #138a50;
        border: 1px solid rgba(21,153,87,.20);
    }

    .sec-support-message.is-error {
        display: block;
        background: rgba(226,59,53,.10);
        color: #c73737;
        border: 1px solid rgba(226,59,53,.20);
    }

    @media (max-width: 1199px) {
        .sec-support-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sec-support-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .sec-support-page {
            padding: 16px;
        }

        .sec-support-summary {
            grid-template-columns: 1fr;
        }

        .sec-support-toolbar,
        .sec-detail-grid {
            grid-template-columns: 1fr;
        }

        .sec-detail-header {
            flex-direction: column;
        }
    }
</style>

<div class="sec-support-page">
    <section class="sec-support-hero">
        <span class="sec-support-kicker">
            <i class="fas fa-headset"></i>
            Bandeja de soporte
        </span>

        <h1>Gestión de soporte para Secretaría</h1>
        <p>
            Desde esta vista puedes revisar las solicitudes enviadas por los estudiantes,
            consultar el detalle de cada caso y actualizar su estado a <strong>En proceso</strong>
            o <strong>Resuelto</strong>.
        </p>
    </section>

    <div class="sec-support-summary">
        <div class="sec-summary-card">
            <span class="sec-summary-label">Total de casos</span>
            <div class="sec-summary-value" id="supportTotal">0</div>
            <div class="sec-summary-note">Solicitudes visibles en bandeja</div>
        </div>

        <div class="sec-summary-card">
            <span class="sec-summary-label">Pendientes</span>
            <div class="sec-summary-value" id="supportPendientes">0</div>
            <div class="sec-summary-note">Casos nuevos por atender</div>
        </div>

        <div class="sec-summary-card">
            <span class="sec-summary-label">En proceso</span>
            <div class="sec-summary-value" id="supportProceso">0</div>
            <div class="sec-summary-note">Casos tomados por secretaría</div>
        </div>

        <div class="sec-summary-card">
            <span class="sec-summary-label">Resueltos</span>
            <div class="sec-summary-value" id="supportResueltos">0</div>
            <div class="sec-summary-note">Casos finalizados</div>
        </div>
    </div>

    <div class="sec-support-grid">
        <div class="sec-support-card">
            <div class="sec-support-card-header">
                <h2>Bandeja de solicitudes</h2>
                <p>
                    Consulta las incidencias registradas por los estudiantes y filtra rápidamente
                    por texto o por estado.
                </p>
            </div>

            <div class="sec-support-card-body">
                <div class="sec-support-toolbar">
                    <input
                        type="text"
                        id="supportSearch"
                        class="sec-support-input"
                        placeholder="Buscar por código, asunto, estudiante, correo o módulo"
                    >

                    <select id="supportFilterEstado" class="sec-support-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="resuelto">Resuelto</option>
                    </select>
                </div>

                <div id="supportTicketList" class="sec-ticket-list">
                    <div class="sec-empty-state">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cargando solicitudes de soporte...
                    </div>
                </div>

                <div class="sec-support-message" id="supportActionMessage"></div>
            </div>
        </div>

        <div class="sec-support-card">
            <div class="sec-support-card-header">
                <h2>Detalle del caso</h2>
                <p>
                    Selecciona una solicitud de la bandeja para ver toda su información.
                </p>
            </div>

            <div class="sec-support-card-body">
                <div id="supportDetail" class="sec-detail-shell">
                    <div class="sec-empty-state">
                        <i class="fas fa-circle-info mr-2"></i>
                        Selecciona un caso para ver su detalle.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoints = {
        bandeja: "{{ route('api.soporte.secretaria.bandeja') }}",
        verBase: "{{ url('api/soporte/secretaria/ver') }}",
        tomarBase: "{{ url('api/soporte/secretaria/tomar') }}",
        resolverBase: "{{ url('api/soporte/secretaria/resolver') }}"
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const supportTotal = document.getElementById('supportTotal');
    const supportPendientes = document.getElementById('supportPendientes');
    const supportProceso = document.getElementById('supportProceso');
    const supportResueltos = document.getElementById('supportResueltos');

    const supportSearch = document.getElementById('supportSearch');
    const supportFilterEstado = document.getElementById('supportFilterEstado');
    const supportTicketList = document.getElementById('supportTicketList');
    const supportDetail = document.getElementById('supportDetail');
    const supportActionMessage = document.getElementById('supportActionMessage');

    let tickets = [];
    let selectedId = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function estadoBadgeClass(estadoKey) {
        if (estadoKey === 'pendiente') return 'sec-badge-estado-pendiente';
        if (estadoKey === 'en_proceso') return 'sec-badge-estado-proceso';
        if (estadoKey === 'resuelto') return 'sec-badge-estado-resuelto';
        return 'sec-badge-estado-pendiente';
    }

    function prioridadBadgeClass(prioridadKey) {
        if (prioridadKey === 'alta') return 'sec-badge-prioridad-alta';
        if (prioridadKey === 'media') return 'sec-badge-prioridad-media';
        if (prioridadKey === 'baja') return 'sec-badge-prioridad-baja';
        return 'sec-badge-prioridad-media';
    }

    function showMessage(text, type = 'success') {
        supportActionMessage.className = 'sec-support-message';
        supportActionMessage.classList.add(type === 'error' ? 'is-error' : 'is-success');
        supportActionMessage.textContent = text;
    }

    function clearMessage() {
        supportActionMessage.className = 'sec-support-message';
        supportActionMessage.textContent = '';
    }

    function setResumen(resumen) {
        supportTotal.textContent = resumen?.total ?? 0;
        supportPendientes.textContent = resumen?.pendientes ?? 0;
        supportProceso.textContent = resumen?.en_proceso ?? 0;
        supportResueltos.textContent = resumen?.resueltos ?? 0;
    }

    function getFilteredTickets() {
        const text = (supportSearch.value || '').trim().toLowerCase();
        const estado = supportFilterEstado.value;

        return tickets.filter(function (ticket) {
            const matchEstado = !estado || (ticket.estado_key === estado);

            const target = [
                ticket.codigo,
                ticket.asunto,
                ticket.usuario,
                ticket.correo,
                ticket.modulo,
                ticket.tipo,
                ticket.carrera
            ].join(' ').toLowerCase();

            const matchText = !text || target.includes(text);

            return matchEstado && matchText;
        });
    }

    function renderList() {
        const filtered = getFilteredTickets();

        if (!filtered.length) {
            supportTicketList.innerHTML = `
                <div class="sec-empty-state">
                    <i class="fas fa-inbox mr-2"></i>
                    No hay solicitudes que coincidan con el filtro aplicado.
                </div>
            `;
            return;
        }

        supportTicketList.innerHTML = filtered.map(function (ticket) {
            return `
                <div class="sec-ticket-item ${String(ticket.id_soporte) === String(selectedId) ? 'active' : ''}" data-id="${escapeHtml(ticket.id_soporte)}">
                    <div class="sec-ticket-top">
                        <div>
                            <div class="sec-ticket-code">${escapeHtml(ticket.codigo || ('ST-' + ticket.id_soporte))}</div>
                            <div class="sec-ticket-subject">${escapeHtml(ticket.asunto || 'Sin asunto')}</div>
                            <div class="sec-ticket-meta">
                                <strong>${escapeHtml(ticket.usuario || 'Sin usuario')}</strong><br>
                                ${escapeHtml(ticket.correo || 'Sin correo')} · ${escapeHtml(ticket.fecha || 'Sin fecha')}
                            </div>
                        </div>
                    </div>

                    <div class="sec-ticket-badges">
                        <span class="sec-badge-pill ${estadoBadgeClass(ticket.estado_key)}">
                            <i class="fas fa-circle"></i>
                            ${escapeHtml(ticket.estado || 'Pendiente')}
                        </span>

                        <span class="sec-badge-pill ${prioridadBadgeClass(ticket.prioridad_key)}">
                            <i class="fas fa-flag"></i>
                            ${escapeHtml(ticket.prioridad || 'Media')}
                        </span>

                        <span class="sec-badge-pill sec-badge-estado-proceso">
                            <i class="fas fa-layer-group"></i>
                            ${escapeHtml(ticket.modulo || 'Sin módulo')}
                        </span>
                    </div>
                </div>
            `;
        }).join('');

        supportTicketList.querySelectorAll('.sec-ticket-item').forEach(function (item) {
            item.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                loadDetail(id);
            });
        });
    }

    function renderDetail(ticket) {
        if (!ticket) {
            supportDetail.innerHTML = `
                <div class="sec-empty-state">
                    <i class="fas fa-circle-info mr-2"></i>
                    Selecciona un caso para ver su detalle.
                </div>
            `;
            return;
        }

        const isPendiente = ticket.estado_key === 'pendiente';
        const isProceso = ticket.estado_key === 'en_proceso';
        const isResuelto = ticket.estado_key === 'resuelto';

        supportDetail.innerHTML = `
            <div class="sec-detail-header">
                <div>
                    <div class="sec-detail-title">${escapeHtml(ticket.asunto || 'Solicitud de soporte')}</div>
                    <div class="sec-detail-subtitle">
                        Código ${escapeHtml(ticket.codigo || ticket.id_soporte)} ·
                        Estado actual: <strong>${escapeHtml(ticket.estado || 'Pendiente')}</strong>
                    </div>
                </div>

                <div class="sec-ticket-badges">
                    <span class="sec-badge-pill ${estadoBadgeClass(ticket.estado_key)}">
                        <i class="fas fa-circle"></i>
                        ${escapeHtml(ticket.estado || 'Pendiente')}
                    </span>
                    <span class="sec-badge-pill ${prioridadBadgeClass(ticket.prioridad_key)}">
                        <i class="fas fa-flag"></i>
                        ${escapeHtml(ticket.prioridad || 'Media')}
                    </span>
                </div>
            </div>

            <div class="sec-detail-block">
                <h3>Información general</h3>
                <div class="sec-detail-grid">
                    <div class="sec-detail-field">
                        <span>Estudiante</span>
                        <strong>${escapeHtml(ticket.usuario || 'No disponible')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Correo</span>
                        <strong>${escapeHtml(ticket.correo || 'No disponible')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Carrera</span>
                        <strong>${escapeHtml(ticket.carrera || 'Sin carrera relacionada')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Módulo</span>
                        <strong>${escapeHtml(ticket.modulo || 'No disponible')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Tipo de incidencia</span>
                        <strong>${escapeHtml(ticket.tipo || 'No disponible')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Canal</span>
                        <strong>${escapeHtml(ticket.canal || 'Portal estudiantil')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Fecha</span>
                        <strong>${escapeHtml(ticket.fecha || 'No disponible')}</strong>
                    </div>
                    <div class="sec-detail-field">
                        <span>Código</span>
                        <strong>${escapeHtml(ticket.codigo || ticket.id_soporte)}</strong>
                    </div>
                </div>
            </div>

            <div class="sec-detail-block">
                <h3>Descripción del caso</h3>
                <div class="sec-detail-description">${escapeHtml(ticket.descripcion || 'Sin descripción registrada.')}</div>
            </div>

            <div class="sec-detail-block">
                <h3>Acciones de secretaría</h3>
                <div class="sec-detail-actions">
                    ${!isResuelto ? `
                        <button type="button" class="sec-btn sec-btn-primary" id="btnTomarCaso" ${isProceso ? 'disabled' : ''}>
                            <i class="fas fa-hand-paper"></i>
                            ${isProceso ? 'Caso en proceso' : 'Tomar caso'}
                        </button>
                    ` : ''}

                    ${!isResuelto ? `
                        <button type="button" class="sec-btn sec-btn-success" id="btnResolverCaso">
                            <i class="fas fa-circle-check"></i>
                            Marcar como resuelto
                        </button>
                    ` : ''}

                    <button type="button" class="sec-btn sec-btn-light" id="btnRecargarDetalle">
                        <i class="fas fa-rotate-right"></i>
                        Actualizar detalle
                    </button>
                </div>
            </div>
        `;

        const btnTomarCaso = document.getElementById('btnTomarCaso');
        const btnResolverCaso = document.getElementById('btnResolverCaso');
        const btnRecargarDetalle = document.getElementById('btnRecargarDetalle');

        if (btnTomarCaso && !isProceso) {
            btnTomarCaso.addEventListener('click', function () {
                updateEstado(ticket.id_soporte, 'tomar');
            });
        }

        if (btnResolverCaso) {
            btnResolverCaso.addEventListener('click', function () {
                updateEstado(ticket.id_soporte, 'resolver');
            });
        }

        if (btnRecargarDetalle) {
            btnRecargarDetalle.addEventListener('click', function () {
                loadDetail(ticket.id_soporte);
            });
        }
    }

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, options);
        const data = await response.json();

        if (!response.ok || data.ok === false) {
            throw new Error(data.message || 'Ocurrió un error al procesar la solicitud.');
        }

        return data;
    }

    async function loadBandeja(preserveSelection = true) {
        try {
            clearMessage();

            const data = await fetchJson(endpoints.bandeja, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            tickets = Array.isArray(data.data) ? data.data : [];
            setResumen(data.resumen || {});

            if (!preserveSelection || !tickets.some(t => String(t.id_soporte) === String(selectedId))) {
                selectedId = tickets.length ? tickets[0].id_soporte : null;
            }

            renderList();

            if (selectedId) {
                await loadDetail(selectedId, false);
            } else {
                renderDetail(null);
            }
        } catch (error) {
            supportTicketList.innerHTML = `
                <div class="sec-empty-state">
                    <i class="fas fa-triangle-exclamation mr-2"></i>
                    ${escapeHtml(error.message || 'No fue posible cargar la bandeja de soporte.')}
                </div>
            `;
            renderDetail(null);
        }
    }

    async function loadDetail(idSoporte, rerenderList = true) {
        try {
            selectedId = idSoporte;

            if (rerenderList) {
                renderList();
            }

            const data = await fetchJson(`${endpoints.verBase}/${idSoporte}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            renderDetail(data.data || null);
        } catch (error) {
            supportDetail.innerHTML = `
                <div class="sec-empty-state">
                    <i class="fas fa-triangle-exclamation mr-2"></i>
                    ${escapeHtml(error.message || 'No fue posible cargar el detalle del caso.')}
                </div>
            `;
        }
    }

    async function updateEstado(idSoporte, action) {
        try {
            clearMessage();

            const url = action === 'resolver'
                ? `${endpoints.resolverBase}/${idSoporte}`
                : `${endpoints.tomarBase}/${idSoporte}`;

            const data = await fetchJson(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            showMessage(data.message || 'Estado actualizado correctamente.', 'success');
            await loadBandeja(true);
        } catch (error) {
            showMessage(error.message || 'No fue posible actualizar el estado del caso.', 'error');
        }
    }

    supportSearch.addEventListener('input', function () {
        renderList();
    });

    supportFilterEstado.addEventListener('change', function () {
        renderList();
    });

    loadBandeja();
});
</script>
@endsection
