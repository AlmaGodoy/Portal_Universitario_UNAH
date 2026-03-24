document.addEventListener('DOMContentLoaded', () => {
    /*
        =====================================================
        1) ELEMENTOS DE LA VISTA
        =====================================================
      
    */
    const inputTramite = document.getElementById('id_tramite');
    const msg = document.getElementById('msg');

    // Campos informativos del trámite
    const datoIdTramite = document.getElementById('dato-id-tramite');
    const datoFecha = document.getElementById('dato-fecha');
    const datoEstudiante = document.getElementById('dato-estudiante');
    const datoCarrera = document.getElementById('dato-carrera');
    const datoJustificacion = document.getElementById('dato-justificacion');
    const datoEstadoTramite = document.getElementById('dato-estado-tramite');
    const datoEstadoPago = document.getElementById('dato-estado-pago');

    // Espacios donde se mostrarán los documentos
    const docHistorial = document.getElementById('doc-historial');
    const docPago = document.getElementById('doc-pago');

    // Campos del formulario de Secretaría
    const inputIndicePeriodo = document.getElementById('indice_periodo');
    const inputIndiceGlobal = document.getElementById('indice_global');
    const inputClasesAprobadas = document.getElementById('clases_aprobadas');
    const selectPagoValidado = document.getElementById('pago_validado');
    const inputObservaciones = document.getElementById('observaciones_secretaria');

    const formRevision = document.getElementById('formRevisionSecretaria');

    /*
        =====================================================
        2) TOKEN CSRF
        =====================================================
      
    */
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    /*
        =====================================================
        3) FUNCIÓN DE MENSAJES
        =====================================================
       
    */
    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    /*
        =====================================================
        4) CARGAR DETALLE DEL TRÁMITE
        =====================================================
       
    */
    async function cargarDetalleTramite() {
        if (!inputTramite || !inputTramite.value) {
            setMsg('No se encontró el id del trámite.', false);
            return;
        }

        try {
            const res = await fetch(`/api/cambio-carrera/secretaria/detalle/${inputTramite.value}`, {
                headers: { 'Accept': 'application/json' }
            });

            const t = await res.json();

            if (!res.ok || !t || t.resultado === 'ERROR') {
                setMsg(t?.mensaje || 'No se encontró información del trámite.', false);
                return;
            }

            /*
                LLENADO DE LA SECCIÓN INFORMATIVA
                Aquí solo mostramos datos en pantalla.
            */
            if (datoIdTramite) datoIdTramite.textContent = t.id_tramite ?? '';
            if (datoFecha) datoFecha.textContent = t.fecha_solicitud ?? '';
            if (datoEstudiante) datoEstudiante.textContent = t.estudiante ?? '';
            if (datoCarrera) datoCarrera.textContent = t.carrera_destino ?? '';
            if (datoJustificacion) datoJustificacion.textContent = t.direccion ?? '';
            if (datoEstadoTramite) datoEstadoTramite.textContent = t.estado_tramite ?? '';
            if (datoEstadoPago) datoEstadoPago.textContent = t.estado_pago ?? 'Sin registro';

            if (inputIndicePeriodo) inputIndicePeriodo.value = t.indice_periodo ?? '';
            if (inputIndiceGlobal) inputIndiceGlobal.value = t.indice_global ?? '';
            if (inputClasesAprobadas) inputClasesAprobadas.value = t.cantidad_clases_aprobadas ?? '';

        
            if (selectPagoValidado && t.estado_pago) {
                selectPagoValidado.value = t.estado_pago;
            }

            /*
                Si ya existen observaciones del pago en tbl_pago,
                también se cargan en el textarea.
            */
            if (inputObservaciones && t.observaciones_pago) {
                inputObservaciones.value = t.observaciones_pago;
            }

        } catch (error) {
            console.error('Error cargando detalle del trámite:', error);
            setMsg('Error al cargar la información del trámite.', false);
        }
    }

    /*
        =====================================================
        5) CARGAR DOCUMENTOS DEL TRÁMITE
        =====================================================
    */
    async function cargarDocumentosTramite() {
        if (!inputTramite || !inputTramite.value) return;

        try {
            const res = await fetch(`/api/documentos/ver/${inputTramite.value}`, {
                headers: { 'Accept': 'application/json' }
            });

            const docs = await res.json();

            if (!Array.isArray(docs) || docs.length === 0) {
                if (docHistorial) docHistorial.innerHTML = '<span class="sin-documento">No subido</span>';
                if (docPago) docPago.innerHTML = '<span class="sin-documento">No subido</span>';
                return;
            }

           
            const historial = docs.find(d => d.tipo_documento === 'historial_academico');

        
            const comprobantePago = docs.find(d => d.tipo_documento === 'comprobante_pago');

            if (docHistorial) {
                if (historial && historial.ruta_archivo) {
                    docHistorial.innerHTML = `
                        <a href="/storage/${historial.ruta_archivo}" target="_blank" class="btn-ver-doc">
                            Ver historial
                        </a>
                    `;
                } else {
                    docHistorial.innerHTML = '<span class="sin-documento">No subido</span>';
                }
            }

            if (docPago) {
                if (comprobantePago && comprobantePago.ruta_archivo) {
                    docPago.innerHTML = `
                        <a href="/storage/${comprobantePago.ruta_archivo}" target="_blank" class="btn-ver-doc">
                            Ver comprobante
                        </a>
                    `;
                } else {
                    docPago.innerHTML = '<span class="sin-documento">No subido</span>';
                }
            }

        } catch (error) {
            console.error('Error cargando documentos del trámite:', error);

            if (docHistorial) docHistorial.innerHTML = '<span class="sin-documento">Error al cargar</span>';
            if (docPago) docPago.innerHTML = '<span class="sin-documento">Error al cargar</span>';
        }
    }

    /*
        =====================================================
        6) GUARDAR REVISIÓN DE SECRETARÍA
        =====================================================
    */
    if (formRevision) {
        formRevision.addEventListener('submit', async (e) => {
            e.preventDefault();

          
            if (!selectPagoValidado || !selectPagoValidado.value) {
                setMsg('Debes seleccionar el estado del pago.', false);
                return;
            }

            const payload = {
                id_tramite: inputTramite ? inputTramite.value : '',
                indice_periodo: inputIndicePeriodo ? inputIndicePeriodo.value : '',
                indice_global: inputIndiceGlobal ? inputIndiceGlobal.value : '',
                clases_aprobadas: inputClasesAprobadas ? inputClasesAprobadas.value : '',
                estado_pago: selectPagoValidado ? selectPagoValidado.value : '',
                observaciones_pago: inputObservaciones ? inputObservaciones.value : ''
            };

            try {
                const res = await fetch('/api/cambio-carrera/secretaria/guardar-revision', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || data.resultado === 'ERROR') {
                    setMsg(data.mensaje || 'No se pudo guardar la revisión.', false);
                    return;
                }

                setMsg('Revisión de Secretaría guardada correctamente.', true);

             
                cargarDetalleTramite();

            } catch (error) {
                console.error('Error guardando revisión:', error);
                setMsg('Error al guardar la revisión.', false);
            }
        });
    }

    cargarDetalleTramite();
    cargarDocumentosTramite();
});