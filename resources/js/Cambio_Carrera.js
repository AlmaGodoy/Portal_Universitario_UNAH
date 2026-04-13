document.addEventListener('DOMContentLoaded', () => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const selectCarreras = document.getElementById('id_carrera_destino');
    const inputCalendario = document.getElementById('id_calendario');
    const inputPersona = document.getElementById('id_persona');

    const formCambio = document.getElementById('formCambioCarrera');
    const formHistorial = document.getElementById('formHistorial');

    const seccionHistorial = document.getElementById('seccionHistorial');
    const inputTramite = document.getElementById('id_tramite');

    const msg = document.getElementById('msg');
    const tbody = document.getElementById('tbodyTramites');

    // Vista previa archivo historial
    const fileInput = document.getElementById('archivo');
    const previewArchivo = document.getElementById('previewArchivo');
    const nombreArchivo = document.getElementById('nombreArchivo');
    const tamanoArchivo = document.getElementById('tamanoArchivo');
    const btnVerArchivo = document.getElementById('btnVerArchivo');
    const btnQuitarArchivo = document.getElementById('btnQuitarArchivo');

    let archivoURLTemporal = null;

    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    function obtenerMensajeError(data, fallback = 'No se pudo crear el trámite.') {
        if (!data) return fallback;

        if (typeof data === 'string' && data.trim() !== '') {
            return data.trim();
        }

        if (typeof data.mensaje === 'string' && data.mensaje.trim() !== '') {
            return data.mensaje.trim();
        }

        if (typeof data.message === 'string' && data.message.trim() !== '') {
            return data.message.trim();
        }

        if (data.errores && typeof data.errores === 'object') {
            const lista = Object.values(data.errores).flat().filter(Boolean);
            if (lista.length) return lista.join(' | ');
        }

        if (data.errors && typeof data.errors === 'object') {
            const lista = Object.values(data.errors).flat().filter(Boolean);
            if (lista.length) return lista.join(' | ');
        }

        return fallback;
    }

    async function cargarCalendarioVigente() {
        if (!inputCalendario) return;

        try {
            const res = await fetch('/api/cambio-carrera/calendario-vigente', {
                headers: { 'Accept': 'application/json' }
            });

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

    async function cargarCarreras() {
        if (!selectCarreras) return;

        try {
            const res = await fetch('/api/cambio-carrera/carreras', {
                headers: { 'Accept': 'application/json' }
            });

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

    function obtenerBotonAccion(tramite) {
        const estadoTramite = (tramite.estado_tramite || '').toString().trim().toLowerCase();
        const estadoRegistro = Number(tramite.estado ?? 1);

        if (estadoRegistro === 1 && estadoTramite === 'pendiente') {
            return `
                <button
                    type="button"
                    class="cc-btn-danger"
                    onclick="window.cancelarTramiteCambioCarrera(${tramite.id_tramite})"
                >
                    Cancelar trámite
                </button>
            `;
        }

        return `<span class="sin-acciones">No disponible</span>`;
    }

    async function cargarMisTramites() {
        if (!tbody || !inputPersona) return;

        try {
            const idPersona = parseInt(inputPersona.value, 10);

            if (!idPersona) {
                tbody.innerHTML = `<tr><td colspan="7">No se encontró el id de la persona.</td></tr>`;
                return;
            }

            const res = await fetch(`/api/cambio-carrera/ver/${idPersona}`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7">No tienes trámites registrados.</td></tr>`;
                return;
            }

            tbody.innerHTML = '';

            for (const t of data) {
                const botonDocumento = await obtenerBotonDocumento(t.id_tramite);
                const botonAccion = obtenerBotonAccion(t);

                tbody.innerHTML += `
                    <tr>
                        <td>${t.id_tramite ?? ''}</td>
                        <td>${t.fecha_solicitud ?? ''}</td>
                        <td>${t.carrera_destino ?? ''}</td>
                        <td>${t.estado_tramite ?? ''}</td>
                        <td>${t.direccion ?? ''}</td>
                        <td>${botonDocumento}</td>
                        <td>${botonAccion}</td>
                    </tr>
                `;
            }
        } catch (err) {
            console.error('Error cargando trámites:', err);
            tbody.innerHTML = `<tr><td colspan="7">Error cargando trámites.</td></tr>`;
        }
    }

    // =========================================================
    // CORRECCIÓN 1:
    // AQUÍ ESTABA EL PROBLEMA PRINCIPAL
    //
    // En tu HTML generado llamabas:
    // window.cancelarTramiteCambioCarrera(id)
    //
    // pero esa función NO existía en tu JS.
    //
    // Por eso el botón "Cancelar trámite" no hacía nada.
    // Aquí la creamos de forma global en window para que
    // el onclick sí la encuentre.
    // =========================================================
    window.cancelarTramiteCambioCarrera = async function(idTramite) {
        if (!idTramite) {
            setMsg('No se encontró el ID del trámite.', false);
            return;
        }

        const confirmar = confirm('¿Estás seguro de cancelar este trámite?');

        if (!confirmar) {
            return;
        }

        try {
            const res = await fetch(`/api/cambio-carrera/eliminar/${idTramite}`, {
                method: 'DELETE',

                // =========================================================
                // CORRECCIÓN 2:
                // Se manda el token CSRF y cabeceras correctas
                // para que Laravel acepte la petición DELETE.
                // =========================================================
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // =========================================================
            // CORRECCIÓN 3:
            // Leemos primero como texto y luego intentamos parsear JSON,
            // así evitamos que el JS se rompa si el backend devuelve
            // algo inesperado.
            // =========================================================
            const raw = await res.text();

            let data = null;

            try {
                data = raw ? JSON.parse(raw) : null;
            } catch (parseError) {
                console.error('Respuesta no JSON al cancelar trámite:', raw);
                setMsg('El servidor devolvió una respuesta inválida al cancelar el trámite.', false);
                return;
            }

            // =========================================================
            // CORRECCIÓN 4:
            // Validación de errores del backend
            // =========================================================
            if (!res.ok || data?.resultado === 'ERROR' || data?.success === false) {
                setMsg(obtenerMensajeError(data, 'No se pudo cancelar el trámite.'), false);
                return;
            }

            setMsg(
                data?.message ||
                data?.mensaje ||
                'Trámite cancelado correctamente.',
                true
            );

            // =========================================================
            // CORRECCIÓN 5:
            // Recargamos la tabla después de cancelar para que
            // desaparezca el botón o cambie el estado.
            // =========================================================
            cargarMisTramites();

        } catch (error) {
            console.error('Error al cancelar trámite:', error);
            setMsg('Error de conexión al cancelar el trámite.', false);
        }
    };

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
                direccion: direccionInput ? direccionInput.value.trim() : ''
            };

            if (!payload.id_persona || !payload.id_calendario || !payload.id_carrera_destino) {
                setMsg('Completa los datos requeridos del formulario.', false);
                return;
            }

            if (!payload.direccion) {
                setMsg('Debes escribir el motivo o dirección de la solicitud.', false);
                return;
            }

            try {
                const res = await fetch('/api/cambio-carrera/crear', {
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
                    console.error('Respuesta no JSON al crear trámite:', raw);
                    setMsg('El servidor devolvió una respuesta inválida al crear el trámite.', false);
                    return;
                }

                if (!res.ok || data?.resultado === 'ERROR') {
                    const mensaje = obtenerMensajeError(data, 'No se pudo crear el trámite.');
                    setMsg(mensaje, false);
                    return;
                }

                const idTramite = data?.id_tramite || (Array.isArray(data) ? data[0]?.id_tramite : null);

                if (!idTramite) {
                    setMsg('Trámite creado pero no se recibió id_tramite.', false);
                    return;
                }

                if (inputTramite) inputTramite.value = idTramite;
                if (seccionHistorial) seccionHistorial.style.display = 'block';

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
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const raw = await res.text();

                let data = null;

                try {
                    data = raw ? JSON.parse(raw) : null;
                } catch (parseError) {
                    console.error('Respuesta no JSON al subir PDF:', raw);
                    setMsg('El servidor devolvió una respuesta inválida al subir el PDF.', false);
                    return;
                }

                if (!res.ok || data?.resultado === 'ERROR') {
                    setMsg(obtenerMensajeError(data, 'No se pudo subir el PDF.'), false);
                    return;
                }

                fileInput.value = '';
                previewArchivo.style.display = 'none';
                nombreArchivo.textContent = '';
                tamanoArchivo.textContent = '';

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

    cargarCalendarioVigente();
    cargarCarreras();
    cargarMisTramites();
});