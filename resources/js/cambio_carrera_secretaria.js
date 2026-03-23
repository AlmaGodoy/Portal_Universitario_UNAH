document.addEventListener('DOMContentLoaded', () => {
    /*
        =====================================================
        1) ELEMENTOS DE LA VISTA
        =====================================================
        tbodySecretaria: aquí se cargarán los trámites
        msg: para mostrar mensajes de error o éxito
    */
    const tbodySecretaria = document.getElementById('tbodySecretaria');
    const msg = document.getElementById('msg');

    /*
        =====================================================
        2) FUNCIÓN PARA MOSTRAR MENSAJES
        =====================================================
    */
    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    /*
        =====================================================
        3) CARGAR TRÁMITES DE CAMBIO DE CARRERA
        =====================================================
        Aquí se consultan los trámites que Secretaría debe revisar.

        IMPORTANTE:
        Por ahora usaremos un endpoint nuevo que luego agregaremos
        en rutas/controlador para devolver los trámites de cambio de carrera.
    */
    async function cargarTramitesSecretaria() {
        if (!tbodySecretaria) return;

        try {
            const res = await fetch('/api/cambio-carrera/secretaria/listado', {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
                tbodySecretaria.innerHTML = `
                    <tr>
                        <td colspan="7">No hay trámites pendientes de revisión.</td>
                    </tr>
                `;
                return;
            }

            tbodySecretaria.innerHTML = '';

            data.forEach(t => {
                tbodySecretaria.innerHTML += `
                    <tr>
                        <td>${t.id_tramite ?? ''}</td>
                        <td>${t.fecha_solicitud ?? ''}</td>
                        <td>${t.id_persona ?? ''}</td>
                        <td>${t.carrera_destino ?? ''}</td>
                        <td>${t.estado_tramite ?? ''}</td>
                        <td>${t.estado_pago ?? 'Sin pago'}</td>
                        <td>
                            <a href="/cambio-carrera/secretaria/revisar/${t.id_tramite}" class="btnLink">
                                Revisar
                            </a>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error('Error cargando trámites de Secretaría:', error);
            tbodySecretaria.innerHTML = `
                <tr>
                    <td colspan="7">Error cargando trámites.</td>
                </tr>
            `;
            setMsg('No se pudieron cargar los trámites de Secretaría.', false);
        }
    }

    /*
        =====================================================
        4) EJECUCIÓN INICIAL
        =====================================================
    */
    cargarTramitesSecretaria();
});