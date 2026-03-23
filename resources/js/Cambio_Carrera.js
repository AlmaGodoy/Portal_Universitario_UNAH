document.addEventListener('DOMContentLoaded', () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const selectCarreras = document.getElementById('id_carrera_destino');
    const inputCalendario = document.getElementById('id_calendario');
    const inputPersona = document.getElementById('id_persona');

    const formCambio = document.getElementById('formCambioCarrera');
    const formHistorial = document.getElementById('formHistorial');
    const formPago = document.getElementById('formPago');

    const seccionHistorial = document.getElementById('seccionHistorial');
    const seccionPago = document.getElementById('seccionPago'); 
    const inputTramite = document.getElementById('id_tramite');
    const inputTramitePago = document.getElementById('id_tramite_pago');

    const msg = document.getElementById('msg');
    const tbody = document.getElementById('tbodyTramites');

    
    // Estas variables son para la vista previa del archivo antes de subirlo.
    const fileInput = document.getElementById('archivo');
    const previewArchivo = document.getElementById('previewArchivo');
    const nombreArchivo = document.getElementById('nombreArchivo');
    const tamanoArchivo = document.getElementById('tamanoArchivo');
    const btnVerArchivo = document.getElementById('btnVerArchivo');
    const btnQuitarArchivo = document.getElementById('btnQuitarArchivo');

    const selectBanco = document.getElementById('id_banco');
    const inputFechaPago = document.getElementById('fecha_pago');
    const inputReferenciaBanco = document.getElementById('referencia_banco');
    const inputObservacionesPago = document.getElementById('observaciones_pago');
    const inputComprobantePago = document.getElementById('comprobante_pago');


      const previewComprobante = document.getElementById('previewComprobante');
    const nombreComprobante = document.getElementById('nombreComprobante');
    const tamanoComprobante = document.getElementById('tamanoComprobante');
    const btnVerComprobante = document.getElementById('btnVerComprobante');
    const btnQuitarComprobante = document.getElementById('btnQuitarComprobante');

    
    // Guarda una URL temporal del archivo para poder abrirlo antes de subirlo.
    let archivoURLTemporal = null;
      let comprobanteURLTemporal = null;

 
    // Esta función sigue mostrando mensajes en la vista.
    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

   
    // Sigue cargando el calendario vigente desde el backend.
    async function cargarCalendarioVigente() {
        if (!inputCalendario) return;

        try {
            const res = await fetch('/api/cambio-carrera/calendario-vigente');
            const data = await res.json();

            if (!data || data.resultado === 'ERROR') {
                setMsg('No hay calendario vigente para cambio de carrera.', false);
                inputCalendario.value = '';
                return;
            }

            inputCalendario.value = data.id_calendario_academico;
        } catch (error) {
            setMsg('Error al cargar calendario vigente.', false);
            inputCalendario.value = '';
        }
    }


    //  cargando las carreras en el select.
    async function cargarCarreras() {
        if (!selectCarreras) return;

        try {
            const res = await fetch('/api/cambio-carrera/carreras');
            const data = await res.json();

            selectCarreras.innerHTML = '<option value="">Seleccione...</option>';

            if (!Array.isArray(data)) {
                setMsg('No se pudo cargar catálogo de carreras.', false);
                return;
            }

            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_carrera;
                opt.textContent = c.nombre_carrera;
                selectCarreras.appendChild(opt);
            });
        } catch (error) {
            setMsg('Error al cargar carreras.', false);
        }
    }

    async function cargarBancos() {
        if (!selectBanco) return;

        try {
            const res = await fetch('/api/bancos', {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            selectBanco.innerHTML = '<option value="">Seleccione un banco</option>';

            if (!Array.isArray(data)) {
                return;
            }

            data.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id_banco;
                opt.textContent = b.nombre_banco;
                selectBanco.appendChild(opt);
            });
        } catch (error) {
            console.error('Error cargando bancos:', error);
        }
    }

 
    async function obtenerBotonDocumento(idTramite) {
        try {
            const res = await fetch(`/api/documentos/ver/${idTramite}`, {
                headers: { 'Accept': 'application/json' }
            });

            const docs = await res.json();

            if (!Array.isArray(docs) || docs.length === 0) {
                return `<span class="sin-documento">Sin documento</span>`;
            }

            const doc = docs[0];

            if (!doc.ruta_archivo) {
                return `<span class="sin-documento">Sin documento</span>`;
            }

            return `
                <a href="/storage/${doc.ruta_archivo}" target="_blank" class="btn-ver-doc">
                    Ver PDF
                </a>
            `;
        } catch (error) {
            console.error('Error obteniendo documento:', error);
            return `<span class="sin-documento">Sin documento</span>`;
        }
    }

    async function cargarMisTramites() {
        if (!tbody || !inputPersona) return;

        try {
            const idPersona = parseInt(inputPersona.value, 10);

            if (!idPersona) {
              
                tbody.innerHTML = `<tr><td colspan="6">No se encontró el id de la persona.</td></tr>`;
                return;
            }

            const res = await fetch(`/api/cambio-carrera/ver/${idPersona}`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
             
                tbody.innerHTML = `<tr><td colspan="6">No tienes trámites registrados.</td></tr>`;
                return;
            }

            tbody.innerHTML = '';

            for (const t of data) {
               
                const botonDocumento = await obtenerBotonDocumento(t.id_tramite);

                tbody.innerHTML += `
                    <tr>
                        <td>${t.id_tramite ?? ''}</td>
                        <td>${t.fecha_solicitud ?? ''}</td>
                        <td>${t.carrera_destino ?? ''}</td>
                        <td>${t.estado_tramite ?? ''}</td>
                        <td>${t.direccion ?? ''}</td>

                        <!-- SE AGREGÓ:
                             Columna nueva para ver el documento -->
                        <td>${botonDocumento}</td>
                    </tr>
                `;
            }
        } catch (err) {
            console.error('Error cargando trámites:', err);

        
            tbody.innerHTML = `<tr><td colspan="6">Error cargando trámites.</td></tr>`;
        }
    }

    if (fileInput && previewArchivo && nombreArchivo && tamanoArchivo) {
        fileInput.addEventListener('change', () => {
            const archivo = fileInput.files[0];

            if (!archivo) {
                previewArchivo.style.display = 'none';
                nombreArchivo.textContent = '';
                tamanoArchivo.textContent = '';

                if (archivoURLTemporal) {
                    URL.revokeObjectURL(archivoURLTemporal);
                    archivoURLTemporal = null;
                }
                return;
            }

            if (archivoURLTemporal) {
                URL.revokeObjectURL(archivoURLTemporal);
            }

            archivoURLTemporal = URL.createObjectURL(archivo);

            nombreArchivo.textContent = archivo.name;
            tamanoArchivo.textContent = `${(archivo.size / 1024 / 1024).toFixed(2)} MB`;
            previewArchivo.style.display = 'block';
        });
    }

    if (btnVerArchivo) {
        btnVerArchivo.addEventListener('click', () => {
            if (archivoURLTemporal) {
                window.open(archivoURLTemporal, '_blank');
            } else {
                setMsg('No hay archivo seleccionado para visualizar.', false);
            }
        });
    }

 
    if (btnQuitarArchivo && fileInput && previewArchivo) {
        btnQuitarArchivo.addEventListener('click', () => {
            fileInput.value = '';
            nombreArchivo.textContent = '';
            tamanoArchivo.textContent = '';
            previewArchivo.style.display = 'none';

            if (archivoURLTemporal) {
                URL.revokeObjectURL(archivoURLTemporal);
                archivoURLTemporal = null;
            }

            setMsg('Archivo quitado. Puedes seleccionar otro PDF.', true);
        });
    }

    if (inputComprobantePago && previewComprobante && nombreComprobante && tamanoComprobante) {
        inputComprobantePago.addEventListener('change', () => {
            const archivo = inputComprobantePago.files[0];

            if (!archivo) {
                previewComprobante.style.display = 'none';
                nombreComprobante.textContent = '';
                tamanoComprobante.textContent = '';

                if (comprobanteURLTemporal) {
                    URL.revokeObjectURL(comprobanteURLTemporal);
                    comprobanteURLTemporal = null;
                }
                return;
            }

            if (comprobanteURLTemporal) {
                URL.revokeObjectURL(comprobanteURLTemporal);
            }

            comprobanteURLTemporal = URL.createObjectURL(archivo);

            nombreComprobante.textContent = archivo.name;
            tamanoComprobante.textContent = `${(archivo.size / 1024 / 1024).toFixed(2)} MB`;
            previewComprobante.style.display = 'block';
        });
    }

      if (btnVerComprobante) {
        btnVerComprobante.addEventListener('click', () => {
            if (comprobanteURLTemporal) {
                window.open(comprobanteURLTemporal, '_blank');
            } else {
                setMsg('No hay comprobante seleccionado para visualizar.', false);
            }
        });
    }

        if (btnQuitarComprobante && inputComprobantePago && previewComprobante) {
        btnQuitarComprobante.addEventListener('click', () => {
            inputComprobantePago.value = '';
            nombreComprobante.textContent = '';
            tamanoComprobante.textContent = '';
            previewComprobante.style.display = 'none';

            if (comprobanteURLTemporal) {
                URL.revokeObjectURL(comprobanteURLTemporal);
                comprobanteURLTemporal = null;
            }

            setMsg('Comprobante quitado. Puedes seleccionar otro archivo.', true);
        });
    }

   
    if (formCambio) {
        formCambio.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!inputCalendario || !inputPersona || !selectCarreras) {
                setMsg('Faltan datos del formulario.', false);
                return;
            }

            if (!inputCalendario.value) {
                setMsg('No hay calendario vigente. No se puede crear el trámite.', false);
                return;
            }

            const direccionInput = document.getElementById('direccion');

            const payload = {
                id_persona: parseInt(inputPersona.value, 10),
                id_calendario: parseInt(inputCalendario.value, 10),
                id_carrera_destino: parseInt(selectCarreras.value, 10),
                direccion: direccionInput ? direccionInput.value : ''
            };

            try {
                const res = await fetch('/api/cambio-carrera/crear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || data.resultado === 'ERROR') {
                    setMsg(data.mensaje || 'No se pudo crear el trámite.', false);
                    return;
                }

                const idTramite = data.id_tramite || (Array.isArray(data) ? data[0]?.id_tramite : null);

                if (!idTramite) {
                    setMsg('Trámite creado pero no se recibió id_tramite.', false);
                    return;
                }

                if (inputTramite) inputTramite.value = idTramite;
                if (inputTramitePago) inputTramitePago.value = idTramite;
                if (seccionHistorial) seccionHistorial.style.display = 'block';
                if (seccionPago) seccionPago.style.display = 'block';

                setMsg(`Trámite creado (#${idTramite}). Ahora sube tu Historial Académico (PDF).`, true);

         
                cargarMisTramites();

            } catch (err) {
                console.error('Error al crear trámite:', err);
                setMsg('Error de conexión al crear trámite.', false);
            }
        });
    }

    if (formHistorial) {
        formHistorial.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fileInput = document.getElementById('archivo');

            if (!fileInput || !fileInput.files.length) {
                setMsg('Selecciona un PDF.', false);
                return;
            }

            if (!inputTramite || !inputTramite.value) {
                setMsg('No se encontró el trámite para asociar el documento.', false);
                return;
            }

            const formData = new FormData();
            formData.append('id_tramite', inputTramite.value);
            formData.append('tipo_documento', 'historial_academico');
            formData.append('archivo', fileInput.files[0]);

            try {
                const res = await fetch('/api/documentos/crear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    body: formData
                });

                const data = await res.json();

                if (!res.ok || data.resultado === 'ERROR') {
                    setMsg(data.mensaje || 'No se pudo subir el PDF.', false);
                    return;
                }

               
                // Limpiar la vista previa después de subir correctamente.
                fileInput.value = '';

                if (previewArchivo) previewArchivo.style.display = 'none';
                if (nombreArchivo) nombreArchivo.textContent = '';
                if (tamanoArchivo) tamanoArchivo.textContent = '';

                if (archivoURLTemporal) {
                    URL.revokeObjectURL(archivoURLTemporal);
                    archivoURLTemporal = null;
                }

                setMsg('PDF subido correctamente. Solicitud completada.', true);

        
                cargarMisTramites();

            } catch (err) {
                console.error('Error al subir PDF:', err);
                setMsg('Error de conexión al subir PDF.', false);
            }
        });
    }

    if (formPago) {
        formPago.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!inputTramitePago || !inputTramitePago.value) {
                setMsg('No se encontró el trámite para asociar el pago.', false);
                return;
            }

            if (!inputFechaPago || !inputFechaPago.value) {
                setMsg('Debes seleccionar la fecha de pago.', false);
                return;
            }

            if (!inputComprobantePago || !inputComprobantePago.files.length) {
                setMsg('Debes adjuntar el comprobante de pago.', false);
                return;
            }

            /*
                Se usa FormData porque además de datos de texto,
                se va a enviar un archivo.
            */
            const formData = new FormData();
            formData.append('id_tramite', inputTramitePago.value);
            formData.append('fecha_pago', inputFechaPago.value);

            /*
                El monto se manda fijo en 200.00 porque ese es el valor
                del trámite.
            */
            formData.append('monto', '200.00');

            /*
                El estado inicial del pago será "pendiente",
                para que luego secretaría o coordinación lo revise.
            */
            formData.append('estado_pago', 'pendiente');

            /*
                Estos campos son opcionales.
                Solo se envían si el usuario escribió algo.
            */
            if (selectBanco && selectBanco.value) {
                formData.append('id_banco', selectBanco.value);
            }

            if (inputReferenciaBanco && inputReferenciaBanco.value.trim() !== '') {
                formData.append('referencia_banco', inputReferenciaBanco.value.trim());
            }

            if (inputObservacionesPago && inputObservacionesPago.value.trim() !== '') {
                formData.append('observaciones_pago', inputObservacionesPago.value.trim());
            }

            /*
                Aquí se agrega el archivo del comprobante.
            */
            formData.append('comprobante_pago', inputComprobantePago.files[0]);

            try {
                /*
                    IMPORTANTE:
                    Aquí asumí una ruta como:
                    /api/pagos/crear

                    Si en tu proyecto la ruta se llama diferente,
                    solo cambias esta URL.
                */
                const res = await fetch('/api/pagos/crear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    body: formData
                });

                const data = await res.json();

                if (!res.ok || data.resultado === 'ERROR') {
                    setMsg(data.mensaje || 'No se pudo registrar el pago.', false);
                    return;
                }

                /*
                    Se limpian los campos del formulario de pago
                    después de registrar correctamente.
                */
                if (inputFechaPago) inputFechaPago.value = '';
                if (selectBanco) selectBanco.value = '';
                if (inputReferenciaBanco) inputReferenciaBanco.value = '';
                if (inputObservacionesPago) inputObservacionesPago.value = '';
                if (inputComprobantePago) inputComprobantePago.value = '';

                if (previewComprobante) previewComprobante.style.display = 'none';
                if (nombreComprobante) nombreComprobante.textContent = '';
                if (tamanoComprobante) tamanoComprobante.textContent = '';

                if (comprobanteURLTemporal) {
                    URL.revokeObjectURL(comprobanteURLTemporal);
                    comprobanteURLTemporal = null;
                }

                setMsg('Pago registrado correctamente. Queda pendiente de validación por secretaría o coordinación.', true);

                cargarMisTramites();
            } catch (err) {
                console.error('Error al registrar pago:', err);
                setMsg('Error de conexión al registrar pago.', false);
            }
        });
    }


    cargarCalendarioVigente();
    cargarCarreras();
    cargarMisTramites();
});