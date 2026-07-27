document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('misTramitesPage');

    if (!page) {
        return;
    }

    const tbody = document.getElementById('tablaMisTramitesBody');
    const totalBadge = document.getElementById('totalTramitesBadge');
    const errorWrap = document.getElementById('mensajeErrorWrap');
    const sinTramitesCard = document.getElementById('sinTramitesCard');
    const tablaTramitesCard = document.getElementById('tablaTramitesCard');

    const urlMisTramites = page.dataset.urlMisTramites || '/mis-tramites/json';

    function escaparHtml(texto) {
        if (texto === null || texto === undefined) {
            return '';
        }

        return String(texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatearFecha(fecha) {
        if (!fecha) {
            return {
                fecha: '',
                hora: ''
            };
        }

        const fechaNormalizada = String(fecha).replace(' ', 'T');
        const fechaObjeto = new Date(fechaNormalizada);

        if (Number.isNaN(fechaObjeto.getTime())) {
            return {
                fecha: fecha,
                hora: ''
            };
        }

        const fechaTexto = fechaObjeto.toLocaleDateString('es-HN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        const horaTexto = fechaObjeto.toLocaleTimeString('es-HN', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        return {
            fecha: fechaTexto,
            hora: horaTexto
        };
    }

    function normalizarBadgeClase(clase) {
        const valor = String(clase || 'bg-secondary');

        if (valor.includes('bg-success')) {
            return 'bg-success';
        }

        if (valor.includes('bg-danger')) {
            return 'bg-danger';
        }

        if (valor.includes('bg-warning')) {
            return 'bg-warning';
        }

        if (valor.includes('bg-info')) {
            return 'bg-info';
        }

        if (valor.includes('bg-primary')) {
            return 'bg-primary';
        }

        return 'bg-secondary';
    }

    function renderTabla(tramites) {
        if (!tbody) {
            return;
        }

        if (!Array.isArray(tramites) || tramites.length === 0) {
            if (tablaTramitesCard) {
                tablaTramitesCard.classList.add('d-none');
            }

            if (sinTramitesCard) {
                sinTramitesCard.classList.remove('d-none');
            }

            tbody.innerHTML = '';
            return;
        }

        if (tablaTramitesCard) {
            tablaTramitesCard.classList.remove('d-none');
        }

        if (sinTramitesCard) {
            sinTramitesCard.classList.add('d-none');
        }

        tbody.innerHTML = tramites.map(function (tramite) {
            const fecha = formatearFecha(tramite.fecha_solicitud);
            const badgeClass = normalizarBadgeClase(tramite.badge_class);

            return `
                <tr>
                    <td>
                        <div class="mt-tramite-id">
                            #${escaparHtml(tramite.id_tramite)}
                        </div>
                    </td>

                    <td>
                        <div class="mt-table-strong">
                            ${escaparHtml(tramite.tipo_tramite_mostrar || 'Trámite académico')}
                        </div>
                    </td>

                    <td>
                        <div class="mt-table-strong">
                            ${escaparHtml(fecha.fecha)}
                        </div>
                        <small>
                            ${escaparHtml(fecha.hora)}
                        </small>
                    </td>

                    <td>
                        <div class="mt-table-text">
                            ${escaparHtml(tramite.detalle_clave || 'Trámite académico registrado')}
                        </div>
                    </td>

                    <td class="text-center">
                        <span class="mt-badge ${badgeClass}">
                            ${escaparHtml(tramite.estado_mostrar || 'Pendiente')}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function cargarMisTramites() {
        try {
            const response = await fetch(urlMisTramites, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                if (errorWrap) {
                    errorWrap.innerHTML = `
                        <div class="alert mt-alert mt-alert-danger shadow-sm">
                            <i class="fas fa-triangle-exclamation me-2"></i>
                            ${escaparHtml(data.message || 'No se pudieron cargar los trámites.')}
                        </div>
                    `;
                }

                return;
            }

            if (errorWrap) {
                errorWrap.innerHTML = '';
            }

            if (totalBadge) {
                totalBadge.textContent = `Total: ${data.total ?? 0}`;
            }

            renderTabla(data.tramites || []);
        } catch (error) {
            console.error('Error al actualizar mis trámites:', error);
        }
    }

    setInterval(cargarMisTramites, 10000);
});