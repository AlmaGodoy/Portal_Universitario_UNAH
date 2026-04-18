document.addEventListener('DOMContentLoaded', function () {
    const btnEnviar = document.getElementById('btnEnviarSoporte');
    const form = document.getElementById('studentSupportForm');
    const message = document.getElementById('supportMessage');

    const inputAsunto = document.getElementById('supportAsunto');
    const inputTipo = document.getElementById('supportTipo');
    const inputPrioridad = document.getElementById('supportPrioridad');
    const inputModulo = document.getElementById('supportModulo');
    const inputDescripcion = document.getElementById('supportDescripcion');

    if (
        !btnEnviar ||
        !form ||
        !message ||
        !inputAsunto ||
        !inputTipo ||
        !inputPrioridad ||
        !inputModulo ||
        !inputDescripcion
    ) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function limpiarEstadoMensaje() {
        message.classList.remove('is-success', 'is-error');
    }

    function mostrarMensaje(texto, tipo = 'success') {
        limpiarEstadoMensaje();

        if (tipo === 'error') {
            message.classList.add('is-error');
        } else {
            message.classList.add('is-success');
        }

        message.textContent = texto;
        message.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function obtenerPayload() {
        return {
            asunto: inputAsunto.value.trim(),
            tipo: inputTipo.value.trim(),
            prioridad: inputPrioridad.value.trim(),
            modulo: inputModulo.value.trim(),
            descripcion: inputDescripcion.value.trim(),
            canal: 'Portal estudiantil'
        };
    }

    function validarCampos(payload) {
        if (!payload.asunto || !payload.tipo || !payload.prioridad || !payload.modulo || !payload.descripcion) {
            mostrarMensaje('Completa todos los campos antes de enviar la solicitud.', 'error');
            return false;
        }

        return true;
    }

    async function enviarSoporte() {
        const payload = obtenerPayload();

        if (!validarCampos(payload)) {
            return;
        }

        btnEnviar.disabled = true;
        const textoOriginal = btnEnviar.innerHTML;
        btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';

        try {
            const response = await fetch('/api/soporte/crear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || data.ok === false) {
                let mensajeError = data.message || 'No fue posible enviar la solicitud de soporte.';

                if (data.errors) {
                    const primerosErrores = Object.values(data.errors)
                        .flat()
                        .filter(Boolean);

                    if (primerosErrores.length > 0) {
                        mensajeError = primerosErrores[0];
                    }
                }

                throw new Error(mensajeError);
            }

            mostrarMensaje(
                data.message || 'Solicitud de soporte enviada correctamente.',
                'success'
            );

            form.reset();
        } catch (error) {
            mostrarMensaje(
                error.message || 'Ocurrió un error al enviar la solicitud.',
                'error'
            );
        } finally {
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = textoOriginal;
        }
    }

    btnEnviar.addEventListener('click', function () {
        enviarSoporte();
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        enviarSoporte();
    });

    form.addEventListener('reset', function () {
        setTimeout(() => {
            limpiarEstadoMensaje();
            message.textContent = 'Tu solicitud de soporte fue enviada correctamente y será revisada por Secretaría.';
        }, 50);
    });
});
