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

    function setMsg(text, ok = false) {
        if (!msg) return;
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

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

    async function cargarMisTramites() {
        if (!tbody || !inputPersona) return;

        try {
            const idPersona = parseInt(inputPersona.value, 10);

            if (!idPersona) {
                tbody.innerHTML = `<tr><td colspan="5">No se encontró el id de la persona.</td></tr>`;
                return;
            }

            const res = await fetch(`/api/cambio-carrera/ver/${idPersona}`, {
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5">No tienes trámites registrados.</td></tr>`;
                return;
            }

            tbody.innerHTML = '';

            data.forEach(t => {
                tbody.innerHTML += `
                    <tr>
                        <td>${t.id_tramite ?? ''}</td>
                        <td>${t.fecha_solicitud ?? ''}</td>
                        <td>${t.carrera_destino ?? ''}</td>
                        <td>${t.estado_tramite ?? ''}</td>
                        <td>${t.direccion ?? ''}</td>
                    </tr>
                `;
            });
        } catch (err) {
            console.error('Error cargando trámites:', err);
            tbody.innerHTML = `<tr><td colspan="5">Error cargando trámites.</td></tr>`;
        }
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
                if (seccionHistorial) seccionHistorial.style.display = 'block';

                setMsg(`Trámite creado (#${idTramite}). Ahora sube tu Historial Académico (PDF).`, true);

                // Recargar tabla si existe en la misma vista
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

                setMsg('PDF subido correctamente. Solicitud completada.', true);
            } catch (err) {
                console.error('Error al subir PDF:', err);
                setMsg('Error de conexión al subir PDF.', false);
            }
        });
    }

    // Cargas iniciales según la vista
    cargarCalendarioVigente();
    cargarCarreras();
    cargarMisTramites();
});