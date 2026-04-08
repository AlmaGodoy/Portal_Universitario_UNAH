document.addEventListener('DOMContentLoaded', () => {

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const form = document.getElementById('formCalendario');
    const msg = document.getElementById('msg');
    const tbody = document.getElementById('tbodyCalendarios');

    function setMsg(text, ok = false) {
        msg.textContent = text;
        msg.className = ok ? 'msg ok' : 'msg error';
    }

    // ===============================
    // CARGAR CALENDARIOS
    // ===============================
    async function cargarCalendarios() {
        try {
            const res = await fetch('/api/cambio-carrera/secretaria/calendarios');
            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6">No hay calendarios</td></tr>`;
                return;
            }

            tbody.innerHTML = '';

            data.forEach(c => {

                const estadoTexto = c.estado == 1 ? 'Activo' : 'Inactivo';

                tbody.innerHTML += `
                    <tr>
                        <td>${c.id_calendario_academico}</td>
                        <td>${c.tipo_tramite_academico}</td>
                        <td>${c.fecha_inicio_calendario_academico}</td>
                        <td>${c.fecha_final_calendario_academico}</td>
                        <td>${estadoTexto}</td>
                        <td>
                            <button onclick="toggleEstado(${c.id_calendario_academico})" class="cc-btn-secondary">
                                Activar / Desactivar
                            </button>
                        </td>
                    </tr>
                `;
            });

        } catch (error) {
            console.error(error);
            setMsg('Error cargando calendarios', false);
        }
    }

    // ===============================
    // CREAR CALENDARIO
    // ===============================
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                tipo_tramite_academico: document.getElementById('tipo_tramite_academico').value,
                fecha_inicio: document.getElementById('fecha_inicio').value,
                fecha_fin: document.getElementById('fecha_fin').value
            };

            try {
                const res = await fetch('/api/cambio-carrera/secretaria/calendarios', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || data.resultado === 'ERROR') {
                    setMsg(data.mensaje || 'Error al crear calendario', false);
                    return;
                }

                setMsg('Calendario creado correctamente', true);

                form.reset();
                cargarCalendarios();

            } catch (error) {
                console.error(error);
                setMsg('Error de conexión', false);
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
                    'X-CSRF-TOKEN': csrf
                }
            });

            const data = await res.json();

            if (!res.ok || data.resultado === 'ERROR') {
                setMsg(data.mensaje || 'Error al cambiar estado', false);
                return;
            }

            setMsg('Estado actualizado', true);
            cargarCalendarios();

        } catch (error) {
            console.error(error);
            setMsg('Error al cambiar estado', false);
        }
    }

    cargarCalendarios();

});