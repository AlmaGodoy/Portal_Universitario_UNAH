document.addEventListener('DOMContentLoaded', () => {
    const formSolicitud = document.getElementById('formSolicitudEquivalencia');
    const btnCrearSolicitud = document.getElementById('btnCrearSolicitud');
    const selectPlanViejo = document.getElementById('version_plan_viejo');
    const inputPlanNuevo = document.getElementById('version_plan_nuevo');
    const inputDocumento = document.getElementById('documento');
    const textareaObservacion = document.getElementById('observacion_alumno');

    const contenedorMisSolicitudes = document.getElementById('equivalenciasMisSolicitudes');

    const bloqueAsignaturas = document.getElementById('bloqueAsignaturas');
    const tablaAsignaturasPlanViejo = document.getElementById('tablaAsignaturasPlanViejo');
    const btnGuardarMaterias = document.getElementById('btnGuardarMaterias');

    const bloquePreliminares = document.getElementById('bloquePreliminares');
    const tablaEquivalenciasPreliminares = document.getElementById('tablaEquivalenciasPreliminares');
    const btnVerPreliminares = document.getElementById('btnVerPreliminares');

    const alertWrapper = document.getElementById('eqAlertWrapper');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let solicitudActualId = null;
    let versionPlanViejoActual = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function mostrarAlerta(mensaje, tipo = 'success') {
        if (!alertWrapper) return;

        alertWrapper.innerHTML = `
            <div class="alert alert-${tipo} shadow-sm border-0">
                ${escapeHtml(mensaje)}
            </div>
        `;

        setTimeout(() => {
            alertWrapper.innerHTML = '';
        }, 5000);
    }

    function obtenerBadgeEstado(estado) {
        const valor = String(estado ?? '').toLowerCase();

        if (valor.includes('aprob')) return 'bg-success';
        if (valor.includes('rechaz')) return 'bg-danger';
        if (valor.includes('revision')) return 'bg-warning text-dark';
        if (valor.includes('pend')) return 'bg-secondary';

        return 'bg-primary';
    }

    async function cargarMisSolicitudes() {
        if (!contenedorMisSolicitudes) return;

        contenedorMisSolicitudes.innerHTML = `
            <div class="text-center py-5 border rounded-4 text-muted">
                <i class="fas fa-spinner fa-spin mb-3 fa-lg"></i>
                <p class="mb-0">Cargando solicitudes...</p>
            </div>
        `;

        try {
            const response = await fetch('/equivalencias/api/mis-solicitudes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const data = await response.json();
            console.log('MIS SOLICITUDES =>', data);

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No se pudieron cargar las solicitudes.');
            }

            const solicitudes = Array.isArray(data.data) ? data.data : [];

            if (!solicitudes.length) {
                contenedorMisSolicitudes.innerHTML = `
                    <div class="text-center py-5 border rounded-4 text-muted">
                        <i class="fas fa-folder-open mb-3 fa-lg"></i>
                        <p class="mb-0">Aún no hay solicitudes registradas.</p>
                    </div>
                `;
                return;
            }

            contenedorMisSolicitudes.innerHTML = solicitudes.map(item => {
                const id = item.id_solicitud_equivalencia ?? '-';
                const estado = item.estado_solicitud ?? 'PENDIENTE';
                const planViejo = item.version_plan_viejo ?? '-';
                const planNuevo = item.version_plan_nuevo ?? '2026';
                const observacion = item.observacion_alumno ?? 'Sin observación';
                const fecha = item.fecha_solicitud ?? '';

                return `
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h6 class="mb-1 fw-bold">Solicitud #${escapeHtml(id)}</h6>
                                    <small class="text-muted d-block">Plan viejo: ${escapeHtml(planViejo)}</small>
                                    <small class="text-muted d-block">Plan nuevo: ${escapeHtml(planNuevo)}</small>
                                    <small class="text-muted d-block">Fecha: ${escapeHtml(fecha)}</small>
                                </div>

                                <span class="badge ${obtenerBadgeEstado(estado)} rounded-pill px-3 py-2">
                                    ${escapeHtml(estado)}
                                </span>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted d-block">
                                    Observación: ${escapeHtml(observacion)}
                                </small>
                            </div>

                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill btn-ver-detalle"
                                    data-id="${escapeHtml(id)}"
                                    data-plan="${escapeHtml(planViejo)}"
                                >
                                    Ver / continuar materias
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-info rounded-pill btn-ver-preliminares-item"
                                    data-id="${escapeHtml(id)}"
                                >
                                    Ver preliminares
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            contenedorMisSolicitudes.querySelectorAll('.btn-ver-detalle').forEach(btn => {
                btn.addEventListener('click', async () => {
                    solicitudActualId = Number(btn.dataset.id);
                    versionPlanViejoActual = Number(btn.dataset.plan);

                    await cargarAsignaturasPlanViejo(versionPlanViejoActual);

                    if (bloqueAsignaturas) {
                        bloqueAsignaturas.classList.remove('d-none');
                        bloqueAsignaturas.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            contenedorMisSolicitudes.querySelectorAll('.btn-ver-preliminares-item').forEach(btn => {
                btn.addEventListener('click', async () => {
                    solicitudActualId = Number(btn.dataset.id);

                    await cargarEquivalenciasPreliminares(solicitudActualId);

                    if (bloquePreliminares) {
                        bloquePreliminares.classList.remove('d-none');
                        bloquePreliminares.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

        } catch (error) {
            console.error('Error cargando solicitudes:', error);

            contenedorMisSolicitudes.innerHTML = `
                <div class="alert alert-danger mb-0">
                    ${escapeHtml(error.message || 'Error al cargar solicitudes.')}
                </div>
            `;
        }
    }

    async function crearSolicitud() {
        try {
            if (!selectPlanViejo || !inputPlanNuevo || !inputDocumento) {
                throw new Error('Faltan elementos del formulario.');
            }

            const versionPlanViejo = selectPlanViejo.value.trim();
            const versionPlanNuevo = inputPlanNuevo.value.trim();
            const documento = inputDocumento.files[0];
            const observacion = textareaObservacion ? textareaObservacion.value.trim() : '';

            if (!versionPlanViejo) throw new Error('Debes seleccionar el plan viejo.');
            if (!versionPlanNuevo) throw new Error('Debes indicar el plan nuevo.');
            if (!documento) throw new Error('Debes adjuntar el historial académico.');

            const formData = new FormData();
            formData.append('version_plan_viejo', versionPlanViejo);
            formData.append('version_plan_nuevo', versionPlanNuevo);
            formData.append('documento', documento);
            formData.append('observacion_alumno', observacion);

            if (btnCrearSolicitud) {
                btnCrearSolicitud.disabled = true;
                btnCrearSolicitud.innerHTML = 'Guardando...';
            }

            const response = await fetch('/equivalencias/api/solicitud', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            });

            const data = await response.json();
            console.log('CREAR SOLICITUD =>', data);

            if (!response.ok || !data.ok) {
                let mensaje = data.message || 'No fue posible crear la solicitud.';

                if (data.errors) {
                    const errores = Object.values(data.errors).flat().join('\n');
                    if (errores) mensaje = errores;
                }

                throw new Error(mensaje);
            }

            solicitudActualId = Number(data.id_solicitud_equivalencia || 0);
            versionPlanViejoActual = Number(versionPlanViejo);

            mostrarAlerta('Solicitud creada correctamente.', 'success');

            if (formSolicitud) formSolicitud.reset();
            if (inputPlanNuevo) inputPlanNuevo.value = '2026';

            await cargarMisSolicitudes();

            if (bloqueAsignaturas) {
                bloqueAsignaturas.classList.remove('d-none');
                await cargarAsignaturasPlanViejo(versionPlanViejoActual);
            }

        } catch (error) {
            console.error('Error creando solicitud:', error);
            mostrarAlerta(error.message || 'Ocurrió un error al crear la solicitud.', 'danger');
        } finally {
            if (btnCrearSolicitud) {
                btnCrearSolicitud.disabled = false;
                btnCrearSolicitud.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Crear solicitud';
            }
        }
    }

    async function cargarAsignaturasPlanViejo(versionPlanViejo) {
        if (!tablaAsignaturasPlanViejo) return;

        tablaAsignaturasPlanViejo.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    Cargando asignaturas...
                </td>
            </tr>
        `;

        try {
            const response = await fetch(`/equivalencias/api/plan-viejo/${versionPlanViejo}/asignaturas`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const data = await response.json();
            console.log('ASIGNATURAS PLAN VIEJO =>', data);

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No se pudieron cargar las asignaturas.');
            }

            renderAsignaturas(Array.isArray(data.data) ? data.data : []);
        } catch (error) {
            console.error('Error cargando asignaturas:', error);

            tablaAsignaturasPlanViejo.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger py-4">
                        ${escapeHtml(error.message || 'Error al cargar asignaturas.')}
                    </td>
                </tr>
            `;
        }
    }

    function renderAsignaturas(items) {
        if (!tablaAsignaturasPlanViejo) return;

        if (!Array.isArray(items) || !items.length) {
            tablaAsignaturasPlanViejo.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        No se encontraron asignaturas para este plan.
                    </td>
                </tr>
            `;
            return;
        }

        tablaAsignaturasPlanViejo.innerHTML = items.map((item, index) => {
            const codigo = String(item.codigo_asignatura || '').trim();
            const nombre = String(item.nombre_asignatura || '').trim();
            const uv = item.uv ?? item.unidades_valorativas ?? '-';

            return `
                <tr>
                    <td>
                        <input
                            type="checkbox"
                            class="eq-check-asignatura"
                            id="asignatura_${index}"
                            data-codigo="${escapeHtml(codigo)}"
                        >
                    </td>
                    <td>${escapeHtml(codigo)}</td>
                    <td>${escapeHtml(nombre)}</td>
                    <td>${escapeHtml(uv)}</td>
                </tr>
            `;
        }).join('');
    }

    async function guardarMateriasSeleccionadas() {
        try {
            if (!solicitudActualId) throw new Error('Primero debes crear o seleccionar una solicitud.');
            if (!versionPlanViejoActual) throw new Error('No se pudo identificar el plan viejo.');

            const checks = Array.from(document.querySelectorAll('.eq-check-asignatura:checked'));

            if (!checks.length) throw new Error('Debes seleccionar al menos una materia.');

            const asignaturas = checks.map(check => ({
                codigo_asignatura_viejo: check.dataset.codigo,
                seleccionada_alumno: true
            }));

            if (btnGuardarMaterias) {
                btnGuardarMaterias.disabled = true;
                btnGuardarMaterias.innerHTML = 'Guardando...';
            }

            const response = await fetch('/equivalencias/api/solicitud/detalle', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    id_solicitud_equivalencia: solicitudActualId,
                    version_plan_viejo: versionPlanViejoActual,
                    asignaturas
                }),
                credentials: 'same-origin'
            });

            const data = await response.json();
            console.log('GUARDAR MATERIAS =>', data);

            if (!response.ok || !data.ok) {
                let mensaje = data.message || 'No fue posible guardar las materias.';

                if (data.errors) {
                    const errores = Object.values(data.errors).flat().join('\n');
                    if (errores) mensaje = errores;
                }

                throw new Error(mensaje);
            }

            mostrarAlerta('Materias guardadas correctamente.', 'success');
            await cargarMisSolicitudes();
            await cargarEquivalenciasPreliminares(solicitudActualId);

            if (bloquePreliminares) {
                bloquePreliminares.classList.remove('d-none');
            }

        } catch (error) {
            console.error('Error guardando materias:', error);
            mostrarAlerta(error.message || 'Error al guardar materias.', 'danger');
        } finally {
            if (btnGuardarMaterias) {
                btnGuardarMaterias.disabled = false;
                btnGuardarMaterias.innerHTML = '<i class="fas fa-save me-2"></i>Guardar materias';
            }
        }
    }

    async function cargarEquivalenciasPreliminares(idSolicitud) {
        if (!tablaEquivalenciasPreliminares) return;

        tablaEquivalenciasPreliminares.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    Cargando equivalencias preliminares...
                </td>
            </tr>
        `;

        try {
            const response = await fetch(`/equivalencias/api/solicitud/${idSolicitud}/preliminares`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const data = await response.json();
            console.log('PRELIMINARES =>', data);

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No se pudieron cargar las equivalencias preliminares.');
            }

            const preliminares = Array.isArray(data.data) ? data.data : [];
            renderEquivalenciasPreliminares(preliminares);

            if (bloquePreliminares) {
                bloquePreliminares.classList.remove('d-none');
            }

        } catch (error) {
            console.error('Error cargando equivalencias preliminares:', error);

            tablaEquivalenciasPreliminares.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger py-4">
                        ${escapeHtml(error.message || 'Error al cargar equivalencias preliminares.')}
                    </td>
                </tr>
            `;
        }
    }

    function renderEquivalenciasPreliminares(items) {
        if (!tablaEquivalenciasPreliminares) return;

        if (!Array.isArray(items) || !items.length) {
            tablaEquivalenciasPreliminares.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        No se encontraron equivalencias preliminares.
                    </td>
                </tr>
            `;
            return;
        }

        tablaEquivalenciasPreliminares.innerHTML = items.map(item => {
            const codigoViejo = item.codigo_asignatura_viejo ?? item.codigo_viejo ?? '-';
            const nombreViejo = item.nombre_asignatura_viejo ?? item.asignatura_vieja ?? '-';
            const codigoNuevo = item.codigo_asignatura_nuevo ?? item.codigo_nuevo ?? '-';
            const nombreNuevo = item.nombre_asignatura_nuevo ?? item.asignatura_nueva ?? '-';
            const estado = item.estado_equivalencia ?? item.estado ?? 'Preliminar';

            return `
                <tr>
                    <td>${escapeHtml(codigoViejo)}</td>
                    <td>${escapeHtml(nombreViejo)}</td>
                    <td>${escapeHtml(codigoNuevo)}</td>
                    <td>${escapeHtml(nombreNuevo)}</td>
                    <td>
                        <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                            ${escapeHtml(estado)}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    if (formSolicitud) {
        formSolicitud.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopPropagation();
            crearSolicitud();
        });
    }

    if (btnGuardarMaterias) {
        btnGuardarMaterias.addEventListener('click', (e) => {
            e.preventDefault();
            guardarMateriasSeleccionadas();
        });
    }

    if (btnVerPreliminares) {
        btnVerPreliminares.addEventListener('click', async (e) => {
            e.preventDefault();

            if (!solicitudActualId) {
                mostrarAlerta('Primero debes crear o seleccionar una solicitud.', 'warning');
                return;
            }

            await cargarEquivalenciasPreliminares(solicitudActualId);

            if (bloquePreliminares) {
                bloquePreliminares.classList.remove('d-none');
                bloquePreliminares.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    cargarMisSolicitudes();
});