document.addEventListener('DOMContentLoaded', () => {
    const inputPersona = document.getElementById('id_persona');
    const estadoTramite = document.getElementById('estadoTramite');

    function claseEstado(estado) {
        const valor = (estado || '').toLowerCase().trim();

        if (valor.includes('aprob')) return 'aprobada';
        if (valor.includes('rechaz')) return 'rechazada';
        if (valor.includes('pend')) return 'pendiente';
        if (valor.includes('revision') || valor.includes('revisión')) return 'revision';

        return 'pendiente';
    }

    function pasoActivo(estado) {
        const valor = (estado || '').toLowerCase().trim();

        if (valor.includes('aprob') || valor.includes('rechaz')) return 3;
        if (valor.includes('revision') || valor.includes('revisión')) return 2;
        return 1;
    }

   
    function seleccionarTramiteMasReciente(tramites) {
        if (!Array.isArray(tramites) || tramites.length === 0) {
            return null;
        }

        const copia = [...tramites];

        copia.sort((a, b) => {
            const estadoA = (a.estado_tramite || '').toLowerCase().trim();
            const estadoB = (b.estado_tramite || '').toLowerCase().trim();

            const idA = parseInt(a.id_tramite || 0, 10);
            const idB = parseInt(b.id_tramite || 0, 10);

            const prioridadEstado = (estado) => {
                if (estado.includes('revision') || estado.includes('revisión')) return 1;
                if (estado.includes('pend')) return 2;
                if (estado.includes('aprob')) return 3;
                if (estado.includes('rechaz')) return 4;
                return 5;
            };

            const prioridadA = prioridadEstado(estadoA);
            const prioridadB = prioridadEstado(estadoB);

            if (prioridadA !== prioridadB) {
                return prioridadA - prioridadB;
            }

            return idB - idA;
        });

        return copia[0];
    }

    async function cargarEstadoTramite() {
        if (!inputPersona || !estadoTramite) return;

        try {
            const idPersona = parseInt(inputPersona.value, 10);

            if (!idPersona || isNaN(idPersona)) {
                estadoTramite.innerHTML = `
                    <div class="estado-empty">
                        <p>No se pudo identificar al estudiante autenticado.</p>
                    </div>
                `;
                return;
            }

            const res = await fetch(`/api/cambio-carrera/ver/${idPersona}`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
                estadoTramite.innerHTML = `
                    <div class="estado-empty">
                        <p>No tienes trámites registrados.</p>
                    </div>
                `;
                return;
            }

            const tramite = seleccionarTramiteMasReciente(data);

            if (!tramite) {
                estadoTramite.innerHTML = `
                    <div class="estado-empty">
                        <p>No se encontró un trámite válido para mostrar.</p>
                    </div>
                `;
                return;
            }

            const estadoTexto = tramite.estado_tramite ?? 'pendiente';
            const estadoCss = claseEstado(estadoTexto);
            const paso = pasoActivo(estadoTexto);

            estadoTramite.innerHTML = `
                <div class="seguimiento-wrap fade-in">
                    <div class="timeline">
                        <div class="timeline-step ${paso >= 1 ? 'active' : ''}">
                            <div class="timeline-dot">1</div>
                            <span>Solicitado</span>
                        </div>

                        <div class="timeline-line ${paso >= 2 ? 'active' : ''}"></div>

                        <div class="timeline-step ${paso >= 2 ? 'active' : ''}">
                            <div class="timeline-dot">2</div>
                            <span>En revisión</span>
                        </div>

                        <div class="timeline-line ${paso >= 3 ? 'active' : ''}"></div>

                        <div class="timeline-step ${paso >= 3 ? 'active' : ''}">
                            <div class="timeline-dot">3</div>
                            <span>Resuelto</span>
                        </div>
                    </div>

                    <div class="estado-resumen">
                        <div class="estado-principal">
                            <span class="estado-badge ${estadoCss}">${estadoTexto}</span>
                            <h4>Estado actual del trámite</h4>
                            <p>Consulta aquí el avance más reciente de tu solicitud académica.</p>
                        </div>

                        <div class="estado-grid">
                            <div class="estado-item">
                                <strong>ID Trámite</strong>
                                <span>${tramite.id_tramite ?? ''}</span>
                            </div>

                            <div class="estado-item">
                                <strong>Fecha</strong>
                                <span>${tramite.fecha_solicitud ?? ''}</span>
                            </div>

                            <div class="estado-item">
                                <strong>Carrera Destino</strong>
                                <span>${tramite.carrera_destino ?? ''}</span>
                            </div>

                            <div class="estado-item">
                                <strong>Estado</strong>
                                <span class="estado-badge ${estadoCss}">${estadoTexto}</span>
                            </div>

                            <div class="estado-item full">
                                <strong>Dirección / Justificación</strong>
                                <span>${tramite.direccion ?? ''}</span>
                            </div>

                            <div class="estado-item full">
                                <strong>Dictamen / Observación</strong>
                                <span>${tramite.dictamen ?? tramite.observacion ?? tramite.observacion_dictamen ?? 'Aún no hay dictamen registrado.'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } catch (error) {
            console.error('Error al cargar estado:', error);
            estadoTramite.innerHTML = `
                <div class="estado-empty">
                    <p>Error al cargar el estado del trámite.</p>
                </div>
            `;
        }
    }

    cargarEstadoTramite();
});