@extends('layouts.app-estudiantes')

@section('titulo', 'Equivalencias')

@section('content')
<link rel="stylesheet" href="{{ asset('css/equivalencias.css') }}">

<div class="eq-page">
    <div class="eq-hero">
        <div>
            <span class="eq-kicker">Módulo Académico</span>
            <h1>Solicitud de Equivalencias</h1>
            <p>
                Sube tu historial, selecciona tu plan viejo y marca las asignaturas aprobadas.
                El sistema calculará equivalencias preliminares y después un revisor validará la solicitud.
            </p>
        </div>
        <div class="eq-hero-badge">
            <i class="fas fa-file-lines"></i>
            <span>Trámite guiado</span>
        </div>
    </div>

    <div id="eqAlumnoAlert" class="eq-alert d-none"></div>

    <div class="eq-grid eq-grid-main">
        <section class="eq-card">
            <div class="eq-card-head">
                <h3><i class="fas fa-upload"></i> Nueva solicitud</h3>
                <p>Sube el historial y crea tu solicitud.</p>
            </div>

            <form id="formCrearSolicitud" class="eq-form" enctype="multipart/form-data">
                @csrf

                <div class="eq-form-grid">
                    <div class="eq-field">
                        <label for="version_plan_viejo">Plan viejo</label>
                        <select id="version_plan_viejo" name="version_plan_viejo" required>
                            <option value="">Seleccione una opción</option>
                            <option value="19">Plan 2019</option>
                            <option value="2022">Plan 2022</option>
                        </select>
                    </div>

                    <div class="eq-field">
                        <label for="version_plan_nuevo">Plan nuevo</label>
                        <input id="version_plan_nuevo" name="version_plan_nuevo" type="number" value="2025" readonly>
                    </div>

                    <div class="eq-field eq-field-full">
                        <label for="documento">Historial académico (PDF/JPG/PNG)</label>
                        <input id="documento" name="documento" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>

                    <div class="eq-field eq-field-full">
                        <label for="observacion_alumno">Observación</label>
                        <textarea id="observacion_alumno" name="observacion_alumno" rows="3" placeholder="Opcional"></textarea>
                    </div>
                </div>

                <div class="eq-actions">
                    <button type="submit" class="eq-btn eq-btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Crear solicitud
                    </button>
                </div>
            </form>
        </section>

        <aside class="eq-card">
            <div class="eq-card-head">
                <h3><i class="fas fa-folder-open"></i> Mis solicitudes</h3>
                <p>Estado general de tus solicitudes.</p>
            </div>

            <div id="misSolicitudesWrap" class="eq-list-wrap">
                <div class="eq-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Aún no hay solicitudes registradas.</span>
                </div>
            </div>
        </aside>
    </div>

    <section id="bloqueMaterias" class="eq-card d-none">
        <div class="eq-card-head">
            <div>
                <h3><i class="fas fa-list-check"></i> Materias aprobadas del plan viejo</h3>
                <p>Marca solo las materias que aparecen aprobadas en tu historial.</p>
            </div>

            <div class="eq-pill-wrap">
                <span class="eq-pill">
                    Solicitud ID: <strong id="currentSolicitudLabel">-</strong>
                </span>
                <span class="eq-pill">
                    Plan viejo: <strong id="currentPlanViejoLabel">-</strong>
                </span>
            </div>
        </div>

        <div class="eq-table-wrap">
            <table class="eq-table">
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>Código</th>
                        <th>Asignatura</th>
                        <th>UV</th>
                        <th>Nota final</th>
                    </tr>
                </thead>
                <tbody id="tablaAsignaturasPlanViejo">
                    <tr>
                        <td colspan="5" class="eq-empty-row">Primero crea una solicitud.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="eq-actions">
            <button id="btnGuardarDetalle" type="button" class="eq-btn eq-btn-primary">
                <i class="fas fa-floppy-disk"></i>
                Guardar materias
            </button>

            <button id="btnVerPreliminares" type="button" class="eq-btn eq-btn-secondary">
                <i class="fas fa-wand-magic-sparkles"></i>
                Ver equivalencias preliminares
            </button>
        </div>
    </section>

    <section id="bloquePreliminares" class="eq-card d-none">
        <div class="eq-card-head">
            <h3><i class="fas fa-diagram-project"></i> Equivalencias preliminares</h3>
            <p>Estas equivalencias son preliminares hasta que el revisor valide la solicitud.</p>
        </div>

        <div class="eq-table-wrap">
            <table class="eq-table">
                <thead>
                    <tr>
                        <th>Código viejo</th>
                        <th>Asignatura vieja</th>
                        <th>Nota</th>
                        <th>Código nuevo</th>
                        <th>Asignatura nueva</th>
                        <th>Situación</th>
                    </tr>
                </thead>
                <tbody id="tablaPreliminares">
                    <tr>
                        <td colspan="6" class="eq-empty-row">Aún no se han calculado equivalencias.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '{{ csrf_token() }}';

    const routes = {
        crearSolicitud: '{{ route('api.equivalencias.crear') }}',
        misSolicitudes: '{{ route('api.equivalencias.mis') }}',
        guardarDetalle: '{{ route('api.equivalencias.detalle.guardar') }}',
        planViejoAsignaturas: '{{ route('api.equivalencias.planViejo.asignaturas', ['versionPlanViejo' => '__VERSION__']) }}',
        preliminares: '{{ route('api.equivalencias.solicitud.preliminares', ['idSolicitud' => '__ID__']) }}',
        documento: '{{ route('api.equivalencias.solicitud.documento', ['idSolicitud' => '__ID__']) }}',
    };

    let currentSolicitudId = null;
    let currentVersionPlanViejo = null;
    let currentPlanViejoTexto = null;

    const alertBox = document.getElementById('eqAlumnoAlert');
    const formCrearSolicitud = document.getElementById('formCrearSolicitud');
    const bloqueMaterias = document.getElementById('bloqueMaterias');
    const bloquePreliminares = document.getElementById('bloquePreliminares');
    const currentSolicitudLabel = document.getElementById('currentSolicitudLabel');
    const currentPlanViejoLabel = document.getElementById('currentPlanViejoLabel');
    const tablaAsignaturasPlanViejo = document.getElementById('tablaAsignaturasPlanViejo');
    const tablaPreliminares = document.getElementById('tablaPreliminares');
    const misSolicitudesWrap = document.getElementById('misSolicitudesWrap');

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
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function cargarMisSolicitudes() {
        try {
            const response = await fetch(routes.misSolicitudes, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!data.ok || !data.data || data.data.length === 0) {
                misSolicitudesWrap.innerHTML = `
                    <div class="eq-empty">
                        <i class="fas fa-inbox"></i>
                        <span>Aún no hay solicitudes registradas.</span>
                    </div>
                `;
                return;
            }

            misSolicitudesWrap.innerHTML = data.data.map(item => `
                <article class="eq-request-item">
                    <div class="eq-request-top">
                        <div>
                            <h4>Solicitud #${item.id_solicitud_equivalencia}</h4>
                            <p>Plan viejo: ${escapeHtml(item.version_plan_viejo)} · Plan nuevo: ${escapeHtml(item.version_plan_nuevo)}</p>
                        </div>
                        <span class="eq-status eq-status-${String(item.estado_solicitud).toLowerCase()}">
                            ${escapeHtml(item.estado_solicitud)}
                        </span>
                    </div>
                    <div class="eq-request-meta">
                        <span><i class="fas fa-calendar-days"></i> ${escapeHtml(item.fecha_solicitud ?? '-')}</span>
                        <span><i class="fas fa-list-check"></i> ${escapeHtml(item.total_materias_marcadas ?? 0)} materias</span>
                    </div>
                    <div class="eq-request-actions">
                        <a class="eq-link-btn" target="_blank"
                           href="${routes.documento.replace('__ID__', item.id_solicitud_equivalencia)}">
                            <i class="fas fa-file-arrow-down"></i> Documento
                        </a>
                    </div>
                </article>
            `).join('');
        } catch (error) {
            misSolicitudesWrap.innerHTML = `
                <div class="eq-empty">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>Error al cargar solicitudes.</span>
                </div>
            `;
        }
    }

    async function cargarAsignaturasPlanViejo(version) {
        const url = routes.planViejoAsignaturas.replace('__VERSION__', version);

        tablaAsignaturasPlanViejo.innerHTML = `
            <tr>
                <td colspan="5" class="eq-empty-row">Cargando materias...</td>
            </tr>
        `;

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!data.ok || !data.data) {
                tablaAsignaturasPlanViejo.innerHTML = `
                    <tr>
                        <td colspan="5" class="eq-empty-row">No fue posible cargar las materias.</td>
                    </tr>
                `;
                return;
            }

            tablaAsignaturasPlanViejo.innerHTML = data.data.map(item => `
                <tr>
                    <td>
                        <label class="eq-check">
                            <input type="checkbox" class="js-asig-check" data-codigo="${escapeHtml(item.codigo_asignatura)}">
                            <span></span>
                        </label>
                    </td>
                    <td>${escapeHtml(item.codigo_asignatura)}</td>
                    <td>${escapeHtml(item.nombre_asignatura)}</td>
                    <td>${escapeHtml(item.uv ?? '-')}</td>
                    <td>
                        <input type="number"
                               step="0.01"
                               min="0"
                               max="100"
                               class="eq-note-input js-asig-nota"
                               data-codigo="${escapeHtml(item.codigo_asignatura)}"
                               placeholder="Opcional">
                    </td>
                </tr>
            `).join('');

            bloqueMaterias.classList.remove('d-none');
        } catch (error) {
            tablaAsignaturasPlanViejo.innerHTML = `
                <tr>
                    <td colspan="5" class="eq-empty-row">Error al cargar las materias.</td>
                </tr>
            `;
        }
    }

    function obtenerMateriasSeleccionadas() {
        const checks = document.querySelectorAll('.js-asig-check');
        const materias = [];

        checks.forEach(check => {
            if (!check.checked) return;

            const codigo = check.dataset.codigo;
            const notaInput = document.querySelector(`.js-asig-nota[data-codigo="${CSS.escape(codigo)}"]`);

            materias.push({
                codigo_asignatura_viejo: codigo,
                nota_final: notaInput ? notaInput.value : '',
                seleccionada_alumno: 1,
            });
        });

        return materias;
    }

    async function guardarDetalle() {
        if (!currentSolicitudId || !currentVersionPlanViejo) {
            mostrarAlerta('Primero crea una solicitud.', 'error');
            return;
        }

        const materias = obtenerMateriasSeleccionadas();

        if (materias.length === 0) {
            mostrarAlerta('Debes marcar al menos una materia.', 'error');
            return;
        }

        try {
            const response = await fetch(routes.guardarDetalle, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    id_solicitud_equivalencia: currentSolicitudId,
                    version_plan_viejo: currentVersionPlanViejo,
                    asignaturas: materias,
                })
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No fue posible guardar el detalle.');
            }

            mostrarAlerta('Materias guardadas correctamente.', 'success');
            await cargarMisSolicitudes();
        } catch (error) {
            mostrarAlerta(error.message || 'Error al guardar materias.', 'error');
        }
    }

    async function verPreliminares() {
        if (!currentSolicitudId) {
            mostrarAlerta('Primero crea una solicitud.', 'error');
            return;
        }

        const url = routes.preliminares.replace('__ID__', currentSolicitudId);

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!data.ok || !data.data) {
                throw new Error(data.message || 'No fue posible calcular equivalencias.');
            }

            if (data.data.length === 0) {
                tablaPreliminares.innerHTML = `
                    <tr>
                        <td colspan="6" class="eq-empty-row">No hay equivalencias para mostrar.</td>
                    </tr>
                `;
            } else {
                tablaPreliminares.innerHTML = data.data.map(item => `
                    <tr>
                        <td>${escapeHtml(item.codigo_viejo ?? '-')}</td>
                        <td>${escapeHtml(item.asignatura_vieja ?? '-')}</td>
                        <td>${escapeHtml(item.nota_final ?? '-')}</td>
                        <td>${escapeHtml(item.codigo_nuevo ?? '-')}</td>
                        <td>${escapeHtml(item.asignatura_nueva ?? '-')}</td>
                        <td>
                            <span class="eq-mini-status ${item.situacion_equivalencia === 'CON_EQUIVALENCIA' ? 'ok' : 'no'}">
                                ${escapeHtml(item.situacion_equivalencia ?? '-')}
                            </span>
                        </td>
                    </tr>
                `).join('');
            }

            bloquePreliminares.classList.remove('d-none');
        } catch (error) {
            mostrarAlerta(error.message || 'Error al calcular equivalencias.', 'error');
        }
    }

    formCrearSolicitud.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formCrearSolicitud);
        const selectPlanViejo = document.getElementById('version_plan_viejo');

        try {
            const response = await fetch(routes.crearSolicitud, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No fue posible crear la solicitud.');
            }

            currentSolicitudId = data.id_solicitud_equivalencia;
            currentVersionPlanViejo = selectPlanViejo.value;
            currentPlanViejoTexto = selectPlanViejo.options[selectPlanViejo.selectedIndex].text;

            currentSolicitudLabel.textContent = currentSolicitudId;
            currentPlanViejoLabel.textContent = currentPlanViejoTexto;

            mostrarAlerta('Solicitud creada correctamente.', 'success');

            await cargarAsignaturasPlanViejo(currentVersionPlanViejo);
            await cargarMisSolicitudes();
        } catch (error) {
            mostrarAlerta(error.message || 'Error al crear la solicitud.', 'error');
        }
    });

    document.getElementById('btnGuardarDetalle').addEventListener('click', guardarDetalle);
    document.getElementById('btnVerPreliminares').addEventListener('click', verPreliminares);

    cargarMisSolicitudes();
});
</script>
@endsection