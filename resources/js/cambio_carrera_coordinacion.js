document.addEventListener('DOMContentLoaded', () => {
    const tbodyCoordinacion = document.getElementById('tbodyCoordinacion');
    const msg = document.getElementById('msg');
    

    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    async function cargarTramitesCoordinacion() {
        if (!tbodyCoordinacion) return;

        try {
            const res = await fetch('/api/cambio-carrera/coordinacion/listado', {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (!res.ok) {
                tbodyCoordinacion.innerHTML = `
                    <tr>
                        <td colspan="7">Error cargando trámites.</td>
                    </tr>
                `;
                setMsg(data.mensaje || 'Error al cargar trámites de Coordinación.', false);
                return;
            }

            if (!Array.isArray(data) || data.length === 0) {
                tbodyCoordinacion.innerHTML = `
                    <tr>
                        <td colspan="7">No hay trámites pendientes de dictamen.</td>
                    </tr>
                `;
                return;
            }

            tbodyCoordinacion.innerHTML = '';

            data.forEach(t => {
                tbodyCoordinacion.innerHTML += `
                    <tr>
                        <td>${t.id_tramite ?? ''}</td>
                        <td>${t.fecha_solicitud ?? ''}</td>
                        <td>${t.nombre_persona ?? ''}</td>
                        <td>${t.carrera_destino ?? ''}</td>
                        <td>${t.estado_tramite ?? ''}</td>
                        <td>
                            <a href="/cambio-carrera/coordinacion/dictamen/${t.id_tramite}" class="btnLink">
                                Dictaminar
                            </a>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error('Error cargando trámites de Coordinación:', error);
            tbodyCoordinacion.innerHTML = `
                <tr>
                    <td colspan="7">Error cargando trámites.</td>
                </tr>
            `;
            setMsg('No se pudieron cargar los trámites de Coordinación.', false);
        }
    }

    cargarTramitesCoordinacion();
});