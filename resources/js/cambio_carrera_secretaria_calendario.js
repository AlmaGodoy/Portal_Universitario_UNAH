document.addEventListener('DOMContentLoaded', () => {

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const form = document.getElementById('formCalendario');
    const msg = document.getElementById('msg');
    const tbody = document.getElementById('tbodyCalendarios');

    const tipoInput = document.getElementById('tipo_tramite_academico');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');

    const buscarCalendarioInput = document.getElementById('buscarCalendario');

    // ===============================
    // MENSAJES
    // ===============================
    function setMsg(text, type = 'error') {
        msg.textContent = text;
        msg.className = `msg ${type}`;

        if (text) {
            setTimeout(() => {
                msg.textContent = '';
                msg.className = 'msg';
            }, 5000);
        }

    }

    // ===============================
    // FORMATEAR TEXTO
    // ===============================
    function formatearTipo(tipo) {
        if (!tipo) return 'No definido';

        const tipos = {
            cambio_carrera: 'Cambio de carrera',
            cancelacion: 'Cancelación'
        };

        return tipos[tipo] || tipo;
    }

    function formatearEstado(estado) {
        return Number(estado) === 1 ? 'Activo' : 'Inactivo';
    }

    function formatearFecha(fecha) {
        if (!fecha) return 'No disponible';

        const partes = fecha.split('T')[0].split('-');

        if (partes.length !== 3) {
            return fecha;
        }

        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    

    // ===============================
    // VALIDAR FORMULARIO
    // ===============================
    function validarFormulario() {
        const tipo = tipoInput.value;
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;

        if (!tipo) {
            setMsg('Debe seleccionar el tipo de trámite.', 'error');
            tipoInput.focus();
            return false;
        }

        if (!fechaInicio) {
            setMsg('Debe seleccionar la fecha de inicio.', 'error');
            fechaInicioInput.focus();
            return false;
        }

        if (!fechaFin) {
            setMsg('Debe seleccionar la fecha final.', 'error');
            fechaFinInput.focus();
            return false;
        }

        if (fechaInicio > fechaFin) {
            setMsg('La fecha de inicio no puede ser mayor que la fecha final.', 'error');
            fechaInicioInput.focus();
            return false;
        }

        return true;

    }

    // ===============================
    // FORMATEAR TEXTO
    // ===============================
    function formatearTipo(tipo) {
        if (!tipo) return 'No definido';

        const tipos = {
            cambio_carrera: 'Cambio de carrera',
            cancelacion: 'Cancelación'
        };

        return tipos[tipo] || tipo;
    }

    function formatearEstado(estado) {
        return Number(estado) === 1 ? 'Activo' : 'Inactivo';
    }

    function formatearFecha(fecha) {
        if (!fecha) return 'No disponible';

        const partes = fecha.split('T')[0].split('-');

        if (partes.length !== 3) {
            return fecha;
        }

        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    

    // ===============================
    // VALIDAR FORMULARIO
    // ===============================
    function validarFormulario() {
        const tipo = tipoInput.value;
        const fechaInicio = fechaInicioInput.value;
        const fechaFin = fechaFinInput.value;

        if (!tipo) {
            setMsg('Debe seleccionar el tipo de trámite.', 'error');
            tipoInput.focus();
            return false;
        }

        if (!fechaInicio) {
            setMsg('Debe seleccionar la fecha de inicio.', 'error');
            fechaInicioInput.focus();
            return false;
        }

        if (!fechaFin) {
            setMsg('Debe seleccionar la fecha final.', 'error');
            fechaFinInput.focus();
            return false;
        }

        if (fechaInicio > fechaFin) {
            setMsg('La fecha de inicio no puede ser mayor que la fecha final.', 'error');
            fechaInicioInput.focus();
            return false;
        }

        return true;
    }

    function filtrarCalendarios() {
    const texto = buscarCalendarioInput.value.toLowerCase().trim();
    const filas = tbody.querySelectorAll('tr');

    filas.forEach(fila => {
        const contenidoFila = fila.textContent.toLowerCase();

        if (contenidoFila.includes(texto)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

    // ===============================
    // CARGAR CALENDARIOS
    // ===============================
    async function cargarCalendarios() {
        try {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        Cargando calendarios...
                    </td>
                </tr>
            `;

            const res = await fetch('/api/cambio-carrera/secretaria/calendarios');
            const data = await res.json();

            if (!res.ok) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Error cargando calendarios.
                        </td>
                    </tr>
                `;

                setMsg(data.mensaje || 'Error cargando calendarios.', 'error');
                return;
            }

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            No hay calendarios registrados.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = '';

            data.forEach(c => {
                const id = c.id_calendario_academico;
                const estadoActivo = Number(c.estado) === 1;

                const estadoTexto = formatearEstado(c.estado);
                const textoBotonEstado = estadoActivo ? 'Desactivar' : 'Activar';
                const claseEstado = estadoActivo ? 'badge bg-success' : 'badge bg-secondary';

                

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center">${id}</td>

                        <td>${formatearTipo(c.tipo_tramite_academico)}</td>

                        <td class="text-center">
                            ${formatearFecha(c.fecha_inicio_calendario_academico)}
                        </td>

                        <td class="text-center">
                            ${formatearFecha(c.fecha_final_calendario_academico)}
                        </td>

                        <td class="text-center">
                            <span class="${claseEstado}">
                                ${estadoTexto}
                            </span>
                        </td>

                        <td class="text-center">
                            <button 
                                type="button"
                                onclick="toggleEstado(${id})" 
                                class="cc-btn-secondary"
                            >
                                ${textoBotonEstado}
                            </button>

                            <button 
                                type="button"
                                onclick="eliminarCalendario(${id})" 
                                class="cc-btn-danger"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                `;
            });

        } catch (error) {
            console.error(error);

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        Error de conexión al cargar calendarios.
                    </td>
                </tr>
            `;

            setMsg('Error de conexión al cargar calendarios.', 'error');
        }
    }

    // ===============================
    // CREAR CALENDARIO
    // ===============================
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!validarFormulario()) {
                return;
            }

            const payload = {
                tipo_tramite_academico: tipoInput.value,
                fecha_inicio: fechaInicioInput.value,
                fecha_fin: fechaFinInput.value
            };

            try {
                const res = await fetch('/api/cambio-carrera/secretaria/calendarios', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || data.resultado === 'ERROR') {
                    setMsg(data.mensaje || 'Error al crear calendario.', 'error');
                    return;
                }

                setMsg(
                    'Calendario creado correctamente. Los estudiantes podrán crear solicitudes dentro del período habilitado.',
                    'success'
                );

                form.reset();
                cargarCalendarios();

            } catch (error) {
                console.error(error);
                setMsg('Error de conexión al crear calendario.', 'error');
            }
        });
    }

    // ===============================
    // ACTIVAR / DESACTIVAR
    // ===============================
    window.toggleEstado = async function(id) {
        try {
            const res = await fetch(`/api/cambio-carrera/secretaria/calendarios/estado/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            if (!res.ok || data.resultado === 'ERROR') {
                setMsg(data.mensaje || 'Error al cambiar estado del calendario.', 'error');
                return;
            }

            setMsg('Estado del calendario actualizado correctamente.', 'success');
            cargarCalendarios();

        } catch (error) {
            console.error(error);
            setMsg('Error de conexión al cambiar estado del calendario.', 'error');
        }
    };

    if (buscarCalendarioInput) {
    buscarCalendarioInput.addEventListener('input', filtrarCalendarios);
}

   
    // ===============================
    // ELIMINAR LÓGICAMENTE
    // ===============================
    window.eliminarCalendario = async function(id) {
        const confirmado = confirm('¿Seguro que deseas eliminar este calendario?');

        if (!confirmado) {
            return;
        }

        try {
            const res = await fetch(`/api/cambio-carrera/secretaria/calendarios/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            if (!res.ok || data.resultado === 'ERROR') {
                setMsg(data.mensaje || 'Error al eliminar calendario.', 'error');
                return;
            }

            setMsg('Calendario eliminado correctamente.', 'success');
            cargarCalendarios();

        } catch (error) {
            console.error(error);
            setMsg('Error de conexión al eliminar calendario.', 'error');
        }
    };


    cargarCalendarios();

});