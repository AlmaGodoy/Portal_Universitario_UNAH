document.addEventListener('DOMContentLoaded', () => {
    /*
    |--------------------------------------------------------------------------
    | PROTECCIÓN GLOBAL
    |--------------------------------------------------------------------------
    */
    if (window.__ccCoordDictamenInicializado) {
        return;
    }
    window.__ccCoordDictamenInicializado = true;

    const inputTramite = document.getElementById('id_tramite');
    const msg = document.getElementById('msg');
    const formDictamen = document.getElementById('formDictamenCoordinacion');

    if (!inputTramite || !formDictamen) return;

    const datoIdTramite = document.getElementById('dato-id-tramite');
    const datoFecha = document.getElementById('dato-fecha');
    const datoEstudiante = document.getElementById('dato-estudiante');
    const datoCarrera = document.getElementById('dato-carrera');
    const datoJustificacion = document.getElementById('dato-justificacion');
    const datoIndicePeriodo = document.getElementById('dato-indice-periodo');
    const datoIndiceGlobal = document.getElementById('dato-indice-global');
    const datoClasesAprobadas = document.getElementById('dato-clases-aprobadas');

    const docHistorial = document.getElementById('doc-historial');

    const selectDictamen = document.getElementById('dictamen_final');
    const inputObservacion = document.getElementById('observacion_dictamen');
    const btnSubmit = formDictamen.querySelector('button[type="submit"]');

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    /*
    |--------------------------------------------------------------------------
    | BANDERA GLOBAL DE ENVÍO
    |--------------------------------------------------------------------------
   
    */
    if (typeof window.__ccCoordDictamenEnviando === 'undefined') {
        window.__ccCoordDictamenEnviando = false;
    }

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

    function bloquearFormulario(bloquear = true) {
        if (selectDictamen) selectDictamen.disabled = bloquear;
        if (inputObservacion) inputObservacion.disabled = bloquear;
        if (btnSubmit) {
            btnSubmit.disabled = bloquear;
            btnSubmit.textContent = bloquear ? 'Dictamen ya registrado' : 'Guardar dictamen final';
        }
    }

    const idTramite = parseInt(inputTramite.value, 10);

    if (!idTramite) {
        setMsg('No se encontró el id del trámite.', false);
        return;
    }

    async function cargarDetalleTramite() {
        try {
            const res = await fetch(`/api/cambio-carrera/coordinacion/detalle/${inputTramite.value}`, {
                headers: { 'Accept': 'application/json' }
            });

            const response = await res.json();

            if (!res.ok || !response || response.resultado === 'ERROR' || !response.data) {
                setMsg(response?.mensaje || 'No se encontró información del trámite.', false);
                return;
            }

            const t = response.data;

            if (datoIdTramite) datoIdTramite.textContent = t.id_tramite ?? '';
            if (datoFecha) datoFecha.textContent = t.fecha_solicitud ?? '';
            if (datoEstudiante) datoEstudiante.textContent = t.estudiante ?? '';
            if (datoCarrera) datoCarrera.textContent = t.carrera_destino ?? '';
            if (datoJustificacion) datoJustificacion.textContent = t.direccion ?? '';
            if (datoIndicePeriodo) datoIndicePeriodo.textContent = t.indice_periodo ?? '';
            if (datoIndiceGlobal) datoIndiceGlobal.textContent = t.indice_global ?? '';
            if (datoClasesAprobadas) datoClasesAprobadas.textContent = t.cantidad_clases_aprobadas ?? '';

            if (t.puede_dictaminar === false) {
                bloquearFormulario(true);

                if (inputObservacion) {
                    inputObservacion.value = t.observacion_dictamen ?? '';
                }

                
            } else {
                bloquearFormulario(false);
            }

        } catch (error) {
            console.error('Error cargando detalle del trámite:', error);
            setMsg('Error al cargar la información del trámite.', false);
        }
    }

    async function cargarDocumentosTramite() {
        try {
            const res = await fetch(`/api/documentos/ver/${inputTramite.value}`, {
                headers: { 'Accept': 'application/json' }
            });

            const docs = await res.json();

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

    formDictamen.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        /*
        |--------------------------------------------------------------------------
        | EVITA DOBLE SUBMIT GLOBAL
        |--------------------------------------------------------------------------
        */
        if (window.__ccCoordDictamenEnviando) {
            return;
        }

        if (formDictamen.dataset.dictamenFinalizado === '1') {
            return;
        }

        if (!selectDictamen || !selectDictamen.value) {
            setMsg('Debes seleccionar la resolución final.', false);
            return;
        }

        window.__ccCoordDictamenEnviando = true;
        limpiarMsg();
        bloquearFormulario(true);

        const payload = {
            id_tramite: inputTramite.value,
            estado: selectDictamen.value,
            observacion_dictamen: inputObservacion ? inputObservacion.value : ''
        };

        try {
            const res = await fetch(`/api/cambio-carrera/coordinacion/dictaminar/${payload.id_tramite}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || data.resultado === 'ERROR') {
                bloquearFormulario(false);
                window.__ccCoordDictamenEnviando = false;
                setMsg(data.mensaje || 'No se pudo guardar el dictamen.', false);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ÉXITO REAL
            |--------------------------------------------------------------------------
            */
            setMsg('Dictamen final guardado correctamente.', true);

            /*
            | Marcamos el formulario como ya resuelto para no reenviar.
            */
            formDictamen.dataset.dictamenFinalizado = '1';

            await cargarDetalleTramite();
            bloquearFormulario(true);
            return;

        } catch (error) {
            console.error('Error guardando dictamen:', error);
            bloquearFormulario(false);
            setMsg('Error al guardar el dictamen.', false);
        } finally {
            window.__ccCoordDictamenEnviando = false;
        }
    });

    cargarDetalleTramite();
    cargarDocumentosTramite();
});