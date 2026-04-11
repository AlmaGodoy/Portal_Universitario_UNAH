document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('graficasDashboard');
    if (!root) return;

    const apiUrl = root.dataset.apiUrl || '';
    const scopeLabel = root.dataset.scopeLabel || 'módulo';
    const fallbackScopeNote = root.dataset.scopeNote || 'Mostrando estadísticas.';
    const breakdownLabel = root.dataset.breakdownLabel || 'grupo';

    const anioSelect = document.getElementById('anioSelectGraficas');
    const mesSelect = document.getElementById('mesSelectGraficas');
    const filtroDepartamento = document.getElementById('filtroDepartamento');
    const filtroCarrera = document.getElementById('filtroCarrera');

    const estadoCarga = document.getElementById('estadoCargaGraficas');
    const scopeNote = document.getElementById('scopeNoteGraficas');

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '';
    }

    function escapeHtml(text) {
        return String(text ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function normalizeSpecialLabel(label) {
        const txt = String(label ?? '').trim();

        if (/^sin carrera asignada$/i.test(txt)) {
            return 'Sin carrera relacionada';
        }

        if (/^sin departamento asignado$/i.test(txt)) {
            return 'Sin departamento relacionado';
        }

        return txt;
    }

    function construirQueryString() {
        const params = new URLSearchParams();

        if (anioSelect?.value) {
            params.set('anio', anioSelect.value);
        }

        if (mesSelect?.value) {
            params.set('mes', mesSelect.value);
        }

        if (filtroDepartamento?.value) {
            params.set('id_departamento', filtroDepartamento.value);
        }

        if (filtroCarrera?.value) {
            params.set('id_carrera', filtroCarrera.value);
        }

        return params.toString();
    }

    function buildDonutColors(total) {
        const colors = [
            '#0b4ea2',
            '#f1c40f',
            '#2aa1b3',
            '#d93025',
            '#6f42c1',
            '#16a085',
            '#f39c12',
            '#795548',
            '#1abc9c',
            '#8e44ad',
            '#ff7043',
            '#5c6bc0',
            '#26c6da',
            '#9ccc65',
            '#ec407a'
        ];

        return Array.from({ length: total }, (_, index) => colors[index % colors.length]);
    }

    function renderSvgDonut({ hostId, emptyId, payload, typeName }) {
        const currentHost = document.getElementById(hostId);
        const emptyBox = document.getElementById(emptyId);

        if (!currentHost) return;

        const wrap = currentHost.parentElement;
        if (!wrap) return;

        const rawLabels = payload?.labels ?? [];
        const data = (payload?.data ?? []).map(item => Number(item ?? 0));
        const labels = rawLabels.map(label => normalizeSpecialLabel(label));

        const items = labels.map((label, index) => ({
            label,
            value: data[index] ?? 0
        }));

        const total = data.reduce((sum, value) => sum + value, 0);
        const hasData = labels.length > 0 && total > 0;

        if (!hasData) {
            wrap.innerHTML = `<div id="${hostId}" class="donut-render-host donut-render-empty"></div>`;

            if (emptyBox) {
                emptyBox.style.display = 'block';
                emptyBox.textContent = `No hay datos de ${typeName} por ${breakdownLabel} para el filtro seleccionado.`;
            }
            return;
        }

        if (emptyBox) {
            emptyBox.style.display = 'none';
        }

        const size = 280;
        const strokeWidth = 54;
        const radius = (size - strokeWidth) / 2;
        const circumference = 2 * Math.PI * radius;
        const colors = buildDonutColors(labels.length);

        let accumulatedFraction = 0;

        const segments = items.map((item, index) => {
            const value = item.value;
            const fraction = total > 0 ? value / total : 0;
            const dash = fraction * circumference;
            const gap = circumference - dash;
            const offset = -accumulatedFraction * circumference;
            accumulatedFraction += fraction;

            return `
                <circle
                    class="donut-segment"
                    cx="${size / 2}"
                    cy="${size / 2}"
                    r="${radius}"
                    fill="none"
                    stroke="${colors[index]}"
                    stroke-width="${strokeWidth}"
                    stroke-dasharray="${dash} ${gap}"
                    stroke-dashoffset="${offset}"
                    stroke-linecap="butt"
                    transform="rotate(-90 ${size / 2} ${size / 2})"
                    data-label="${escapeHtml(item.label)}"
                    data-value="${value}"
                    data-color="${colors[index]}"
                ></circle>
            `;
        }).join('');

        const legend = items.map((item, index) => `
            <div class="donut-legend-item">
                <span class="donut-legend-color" style="background:${colors[index]}"></span>
                <span class="donut-legend-text">${escapeHtml(item.label)}</span>
            </div>
        `).join('');

        wrap.innerHTML = `
            <div class="donut-render-host modern-donut-layout" id="${hostId}">
                <div class="modern-donut-graphic">
                    <svg viewBox="0 0 ${size} ${size}" class="donut-svg" aria-label="Gráfico donut de ${typeName}">
                        <circle
                            cx="${size / 2}"
                            cy="${size / 2}"
                            r="${radius}"
                            fill="none"
                            stroke="#eef3f9"
                            stroke-width="${strokeWidth}">
                        </circle>
                        ${segments}
                        <circle
                            cx="${size / 2}"
                            cy="${size / 2}"
                            r="${radius - strokeWidth / 2 + 8}"
                            fill="#ffffff">
                        </circle>
                        <text x="50%" y="47%" text-anchor="middle" class="donut-total-value">${total}</text>
                        <text x="50%" y="60%" text-anchor="middle" class="donut-total-label">Total</text>
                    </svg>
                    <div class="donut-tooltip" id="${hostId}Tooltip"></div>
                </div>

                <div class="donut-legend modern-donut-legend">
                    ${legend}
                </div>
            </div>
        `;

        const host = document.getElementById(hostId);
        const tooltip = document.getElementById(`${hostId}Tooltip`);
        const hostSegments = host?.querySelectorAll('.donut-segment') ?? [];

        hostSegments.forEach(segment => {
            segment.addEventListener('mousemove', (event) => {
                if (!tooltip || !host) return;

                const label = segment.getAttribute('data-label') || '';
                const value = segment.getAttribute('data-value') || '0';
                const color = segment.getAttribute('data-color') || '#0b4ea2';

                const hostRect = host.getBoundingClientRect();
                const left = event.clientX - hostRect.left + 12;
                const top = event.clientY - hostRect.top - 12;

                tooltip.innerHTML = `
                    <div class="donut-tooltip-title">${label}</div>
                    <div class="donut-tooltip-value">
                        <span class="donut-tooltip-color" style="background:${color}"></span>
                        <span>${value}</span>
                    </div>
                `;

                tooltip.style.display = 'block';
                tooltip.style.left = `${left}px`;
                tooltip.style.top = `${top}px`;
            });

            segment.addEventListener('mouseleave', () => {
                if (tooltip) {
                    tooltip.style.display = 'none';
                }
            });
        });
    }

    function renderHtmlBars(containerId, noteId, payload) {
        const container = document.getElementById(containerId);
        const note = document.getElementById(noteId);

        if (!container) return;

        const labels = payload?.labels ?? [];
        const data = (payload?.data ?? []).map(item => Number(item ?? 0));

        if (!labels.length || !data.length) {
            container.classList.remove('bar-chart-host');
            container.classList.add('bar-chart-placeholder');
            container.innerHTML = `<div>No hay datos disponibles para el período seleccionado.</div>`;

            if (note) {
                note.textContent = 'Sin información para el período seleccionado.';
            }
            return;
        }

        const maxValue = Math.max(...data, 1);

        const gridLines = [100, 75, 50, 25, 0].map(percent => {
            const top = `${100 - percent}%`;
            return `<span class="bar-grid-line" style="top:${top}"></span>`;
        }).join('');

        const bars = labels.map((label, index) => {
            const value = Number(data[index] ?? 0);
            const height = Math.max((value / maxValue) * 190, value > 0 ? 18 : 0);

            return `
                <div class="bar-modern-item">
                    <div class="bar-modern-value">${value}</div>
                    <div class="bar-modern-track">
                        <div class="bar-modern-fill" style="height:${height}px;"></div>
                    </div>
                    <div class="bar-modern-label">${escapeHtml(label)}</div>
                </div>
            `;
        }).join('');

        container.classList.remove('bar-chart-placeholder');
        container.classList.add('bar-chart-host');

        container.innerHTML = `
            <div class="bar-modern-surface">
                <div class="bar-modern-gridlines">${gridLines}</div>
                <div class="bar-modern-columns">
                    ${bars}
                </div>
            </div>
        `;

        if (note) {
            note.textContent = `Total acumulado: ${payload?.total_anual ?? 0}`;
        }
    }

    async function cargarGraficas() {
        const queryString = construirQueryString();
        estadoCarga.textContent = `Cargando estadísticas de ${scopeLabel}.`;

        try {
            const response = await fetch(`${apiUrl}?${queryString}`, {
                headers: { Accept: 'application/json' }
            });

            if (!response.ok) {
                throw new Error('No se pudo obtener la información.');
            }

            const result = await response.json();

            setText('totalCancelaciones', result.cancelaciones?.total_anual ?? 0);
            setText('totalCambios', result.cambio_carrera?.total_anual ?? 0);

            renderHtmlBars('chartCancelaciones', 'noteCancelaciones', result.cancelaciones);
            renderHtmlBars('chartCambios', 'noteCambios', result.cambio_carrera);

            renderSvgDonut({
                hostId: 'donutCancelaciones',
                emptyId: 'donutEmptyCancelaciones',
                payload: result.distribucion_cancelaciones,
                typeName: 'cancelaciones'
            });

            renderSvgDonut({
                hostId: 'donutCambios',
                emptyId: 'donutEmptyCambios',
                payload: result.distribucion_cambios,
                typeName: 'cambios de carrera'
            });

            scopeNote.textContent = result.nota || fallbackScopeNote || `Mostrando estadísticas de ${scopeLabel}.`;

            const partesEstado = [`Mostrando estadísticas de ${scopeLabel}`];

            if (anioSelect?.value) {
                partesEstado.push(`año ${anioSelect.value}`);
            }

            if (mesSelect?.value) {
                const labelMes = mesSelect.options[mesSelect.selectedIndex]?.text || mesSelect.value;
                partesEstado.push(`mes ${labelMes}`);
            }

            estadoCarga.textContent = partesEstado.join(' · ');

            const nuevaUrl = queryString
                ? `${window.location.pathname}?${queryString}`
                : window.location.pathname;

            window.history.replaceState({}, '', nuevaUrl);
        } catch (error) {
            estadoCarga.textContent = 'Error al cargar estadísticas.';
            setText('noteCancelaciones', error.message);
            setText('noteCambios', error.message);

            ['chartCancelaciones', 'chartCambios'].forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.classList.remove('bar-chart-host');
                    container.classList.add('bar-chart-placeholder');
                    container.innerHTML = `<div>${error.message}</div>`;
                }
            });

            const donutEmptyCancel = document.getElementById('donutEmptyCancelaciones');
            const donutEmptyCambio = document.getElementById('donutEmptyCambios');

            if (donutEmptyCancel) {
                donutEmptyCancel.style.display = 'block';
                donutEmptyCancel.textContent = error.message;
            }

            if (donutEmptyCambio) {
                donutEmptyCambio.style.display = 'block';
                donutEmptyCambio.textContent = error.message;
            }
        }
    }

    anioSelect?.addEventListener('change', cargarGraficas);
    mesSelect?.addEventListener('change', cargarGraficas);
    filtroDepartamento?.addEventListener('change', cargarGraficas);
    filtroCarrera?.addEventListener('change', cargarGraficas);

    cargarGraficas();
});
