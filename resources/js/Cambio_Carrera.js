document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const selectCarreras = document.getElementById('id_carrera_destino');
    const inputCalendario = document.getElementById('id_calendario');
    const inputPersona = document.getElementById('id_persona');

    const formCambio = document.getElementById('formCambioCarrera');
    const formHistorial = document.getElementById('formHistorial');

    const seccionHistorial = document.getElementById('seccionHistorial');
    const inputTramite = document.getElementById('id_tramite');

    const msg = document.getElementById('msg');

    function setMsg(text, ok = false) {
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    async function cargarCalendarioVigente() {
        const res = await fetch('/api/cambio-carrera/calendario-vigente');
        const data = await res.json();

        if (!data || data.resultado === 'ERROR') {
            setMsg('No hay calendario vigente para cambio de carrera.', false);
            inputCalendario.value = '';
            return;
        }

        inputCalendario.value = data.id_calendario_academico;
    }

    async function cargarCarreras() {
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
    }

    formCambio.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!inputCalendario.value) {
            setMsg('No hay calendario vigente. No se puede crear el trámite.', false);
            return;
        }

        const payload = {
            id_persona: parseInt(inputPersona.value, 10),             // temporal
            id_calendario: parseInt(inputCalendario.value, 10),
            id_carrera_destino: parseInt(selectCarreras.value, 10),
            direccion: document.getElementById('direccion').value
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

            // tu SP devuelve id_tramite
            const idTramite = data.id_tramite || (Array.isArray(data) ? data[0]?.id_tramite : null);

            if (!idTramite) {
                setMsg('Trámite creado pero no se recibió id_tramite.', false);
                return;
            }

            inputTramite.value = idTramite;
            seccionHistorial.style.display = 'block';

            setMsg(`Trámite creado (#${idTramite}). Ahora sube tu Historial Académico (PDF).`, true);

        } catch (err) {
            setMsg('Error de conexión al crear trámite.', false);
        }
    });

    formHistorial.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fileInput = document.getElementById('archivo');
        if (!fileInput.files.length) {
            setMsg('Selecciona un PDF.', false);
            return;
        }

        const formData = new FormData();
        formData.append('id_tramite', inputTramite.value);
        formData.append('tipo_documento', 'historial_academico'); // <--- CLAVE: aquí va el tipo real
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
            setMsg('Error de conexión al subir PDF.', false);
        }
    });

    // carga inicial
    cargarCalendarioVigente();
    cargarCarreras();
    cargarMisTramites();

    async function cargarMisTramites() {
    try {
        const idPersona = parseInt(inputPersona.value, 10);

        const res = await fetch(`/api/cambio-carrera/ver/${idPersona}`, {
            headers: { 'Accept': 'application/json' }
        });

        const data = await res.json();

        const tbody = document.getElementById('tbodyTramites');

        // si viene vacío
        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5">No tienes trámites registrados.</td></tr>`;
            return;
        }

        // llenar tabla
        tbody.innerHTML = '';
        data.forEach(t => {
            tbody.innerHTML += `
                <tr>
                    <td>${t.id_tramite}</td>
                    <td>${t.fecha_solicitud ?? ''}</td>
                    <td>${t.carrera_destino ?? ''}</td>
                    <td>${t.estado_tramite ?? ''}</td>
                    <td>${t.direccion ?? ''}</td>
                </tr>
            `;
        });

    } catch (err) {
        console.error(err);
        const tbody = document.getElementById('tbodyTramites');
        tbody.innerHTML = `<tr><td colspan="5">Error cargando trámites.</td></tr>`;
    }
}
});