document.addEventListener('DOMContentLoaded', () => {
    /*
    =====================================================
    1) ELEMENTOS DE LA VISTA
    =====================================================
    */
    const inputTramite = document.getElementById('id_tramite');
    const msg = document.getElementById('msg');
    const formRevision = document.getElementById('formRevisionSecretaria');

    // Protección para que este script solo corra en esta vista
    if (!inputTramite || !formRevision) return;

    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    function limpiarMsg() {
        if (!msg) return;
        msg.textContent = '';
        msg.className = 'msg';
    }

    const idTramite = parseInt(inputTramite.value, 10);

    if (!idTramite) {
        setMsg('No se encontró el id del trámite.', false);
        return;
    }

    /*
    =====================================================
    2) CAMPOS INFORMATIVOS DEL TRÁMITE
    =====================================================
    */
    const datoIdTramite = document.getElementById('dato-id-tramite');
    const datoFecha = document.getElementById('dato-fecha');
    const datoEstudiante = document.getElementById('dato-estudiante');
    const datoCarrera = document.getElementById('dato-carrera');
    const datoJustificacion = document.getElementById('dato-justificacion');
    const datoEstadoTramite = document.getElementById('dato-estado-tramite');

    const docHistorial = document.getElementById('doc-historial');

    /*
    =====================================================
    3) CAMPOS DEL FORMULARIO
    =====================================================
    */
    const inputIndicePeriodo = document.getElementById('indice_periodo');
    const inputIndiceGlobal = document.getElementById('indice_global');
    const inputClasesAprobadas = document.getElementById('clases_aprobadas');
    const inputObservaciones = document.getElementById('observaciones_secretaria');
    const btnSubmit = formRevision.querySelector('button[type="submit"]');

    /*
    =====================================================
    4) TOKEN CSRF
    =====================================================
    */
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    let enviando = false;

    function bloquearFormulario(bloquear = true) {
        if (inputIndicePeriodo) inputIndicePeriodo.disabled = bloquear;
        if (inputIndiceGlobal) inputIndiceGlobal.disabled = bloquear;
        if (inputClasesAprobadas) inputClasesAprobadas.disabled = bloquear;
        if (inputObservaciones) inputObservaciones.disabled = bloquear;
        if (btnSubmit) {
            btnSubmit.disabled = bloquear;
            btnSubmit.textContent = bloquear ? 'Guardando revisión...' : 'Guardar revisión de Secretaría';
        }
    }

    function obtenerMensajeError(data, fallback = 'Ocurrió un error.') {
        if (!data) return fallback;
        if (typeof data === 'string') return data;
        return data.mensaje || data.message || fallback;
    }

    /*
    =====================================================
    5) CARGAR DETALLE DEL TRÁMITE
    =====================================================
    */
    async function cargarDetalleTramite() {
        try {
            const res = await fetch(`/api/cambio-carrera/secretaria/detalle/${inputTramite.value}`, {
                headers: { 'Accept': 'application/json' }
            });

            const raw = await res.text();
            let t = null;

            try {
                t = raw ? JSON.parse(raw) : null;
            } catch (parseError) {
                console.error('Respuesta no JSON en detalle:', raw);
                setMsg('El servidor devolvió una respuesta inválida al cargar el trámite.', false);
                return;
            }

            if (!res.ok || !t || t.resultado === 'ERROR') {
                setMsg(obtenerMensajeError(t, 'No se encontró información del trámite.'), false);
                return;
            }

            if (datoIdTramite) datoIdTramite.textContent = t.id_tramite ?? '';
            if (datoFecha) datoFecha.textContent = t.fecha_solicitud ?? '';
            if (datoEstudiante) datoEstudiante.textContent = t.estudiante ?? '';
            if (datoCarrera) datoCarrera.textContent = t.carrera_destino ?? '';
            if (datoJustificacion) datoJustificacion.textContent = t.direccion ?? '';
            if (datoEstadoTramite) datoEstadoTramite.textContent = t.estado_tramite ?? '';

            if (inputIndicePeriodo) inputIndicePeriodo.value = t.indice_periodo ?? '';
            if (inputIndiceGlobal) inputIndiceGlobal.value = t.indice_global ?? '';
            if (inputClasesAprobadas) inputClasesAprobadas.value = t.cantidad_clases_aprobadas ?? '';
            if (inputObservaciones) inputObservaciones.value = t.observaciones_secretaria ?? '';

        } catch (error) {
            console.error('Error cargando detalle del trámite:', error);
            setMsg('Error al cargar la información del trámite.', false);
        }
    }

    /*
    =====================================================
    6) CARGAR DOCUMENTOS DEL TRÁMITE
    =====================================================
    */
    async function cargarDocumentosTramite() {
        try {
            const res = await fetch(`/api/documentos/ver/${inputTramite.value}`, {
                headers: { 'Accept': 'application/json' }
            });

            const raw = await res.text();
            let docs = [];

            try {
                docs = raw ? JSON.parse(raw) : [];
            } catch (parseError) {
                console.error('Respuesta no JSON en documentos:', raw);
                if (docHistorial) {
                    docHistorial.innerHTML = '<span class="sin-documento">Respuesta inválida</span>';
                }
                return;
            }

            if (!Array.isArray(docs) || docs.length === 0) {
                if (docHistorial) {
                    docHistorial.innerHTML = '<span class="sin-documento">No subido</span>';
                }
                return;
            }

            const historial = docs.find(d => d.tipo_documento === 'historial_academico');

            if (docHistorial) {
                if (historial && historial.ruta_archivo) {
                    docHistorial.innerHTML = `
                        <a href="/empleado/cambio-carrera/documento/${inputTramite.value}" target="_blank" class="btn-ver-doc">
                            Ver historial
                        </a>
                    `;
                } else {
                    docHistorial.innerHTML = '<span class="sin-documento">No subido</span>';
                }
            }

        } catch (error) {
            console.error('Error cargando documentos del trámite:', error);
            if (docHistorial) {
                docHistorial.innerHTML = '<span class="sin-documento">Error al cargar</span>';
            }
        }
    }

    /*
    =====================================================
    7) GUARDAR REVISIÓN DE SECRETARÍA
    =====================================================
    */
    formRevision.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (enviando) return;

        const payload = {
            id_tramite: inputTramite ? inputTramite.value : '',
            indice_periodo: inputIndicePeriodo ? inputIndicePeriodo.value : '',
            indice_global: inputIndiceGlobal ? inputIndiceGlobal.value : '',
            clases_aprobadas: inputClasesAprobadas ? inputClasesAprobadas.value : '',
            observaciones_secretaria: inputObservaciones ? inputObservaciones.value : ''
        };

        enviando = true;
        limpiarMsg();
        bloquearFormulario(true);

        try {
            const res = await fetch('/api/cambio-carrera/secretaria/guardar-revision', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const raw = await res.text();
            let data = null;

            try {
                data = raw ? JSON.parse(raw) : null;
            } catch (parseError) {
                console.error('Respuesta no JSON al guardar revisión:', raw);
                bloquearFormulario(false);
                enviando = false;
                setMsg('El servidor devolvió una respuesta inválida al guardar la revisión.', false);
                return;
            }

            if (!res.ok || data?.resultado === 'ERROR') {
                bloquearFormulario(false);
                enviando = false;
                setMsg(obtenerMensajeError(data, 'No se pudo guardar la revisión.'), false);
                return;
            }

            setMsg(data?.mensaje || 'Revisión de Secretaría guardada correctamente.', true);
            await cargarDetalleTramite();

        } catch (error) {
            console.error('Error guardando revisión:', error);
            bloquearFormulario(false);
            setMsg('Error al guardar la revisión.', false);
        } finally {
            enviando = false;
            bloquearFormulario(false);
        }
    });

    cargarDetalleTramite();
    cargarDocumentosTramite();
});