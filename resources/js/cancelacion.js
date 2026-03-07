document.addEventListener('DOMContentLoaded', function() {
    const formCancelacion = document.getElementById('formCancelacion');
    const formDocumento = document.getElementById('formDocumento');
    const cardPaso1 = document.getElementById('cardPaso1');
    const cardPaso2 = document.getElementById('cardPaso2');
    const btnSiguiente = document.getElementById('btnSiguiente');


    if (!formCancelacion) return;

    // Función segura para obtener el CSRF Token
    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    // --- PASO 1: CREAR TRÁMITE ---
    formCancelacion.addEventListener('submit', function(e) {
        e.preventDefault();


        btnSiguiente.disabled = true;
        const originalText = btnSiguiente.innerText;
        btnSiguiente.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';

        const datos = {
            prioridad: document.getElementById('prioridad').value,
            observacion_inicial: document.getElementById('observacion_inicial').value
        };

        fetch('/api/cancelaciones/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(datos)
        })
        .then(response => {
            if (!response.ok) throw response;
            return response.json();
        })
        .then(data => {
            if (data.resultado === 'OK') {

                const idTramite = data.id_tramite_creado;
                const inputHidden = document.getElementById('id_tramite_hidden');
                const displayId = document.getElementById('displayIdTramite');

                if (inputHidden) inputHidden.value = idTramite;
                if (displayId) displayId.innerText = idTramite;


                if (cardPaso1) cardPaso1.classList.add('d-none');
                if (cardPaso2) cardPaso2.classList.remove('d-none');

                alert('¡Éxito! Trámite iniciado correctamente.');
            } else {
                throw new Error(data.mensaje || 'Error desconocido en el servidor');
            }
        })
        .catch(async (error) => {
            console.error('Error en Paso 1:', error);
            let msg = 'No se pudo conectar con el servidor';

            // Intentar extraer el mensaje de error de la respuesta JSON
            if (error.json) {
                try {
                    const errData = await error.json();
                    msg = errData.mensaje || msg;
                } catch (e) { /* No es JSON */ }
            } else if (error.message) {
                msg = error.message;
            }

            alert('Error: ' + msg);
            btnSiguiente.disabled = false;
            btnSiguiente.innerText = originalText;
        });
    });

    // --- PASO 2: GUARDAR DOCUMENTO PDF ---
    if (formDocumento) {
        formDocumento.addEventListener('submit', function(e) {
            e.preventDefault();

            const btnFinalizar = this.querySelector('button[type="submit"]');
            if (btnFinalizar) {
                btnFinalizar.disabled = true;
                btnFinalizar.innerText = 'Subiendo Archivo...';
            }

            const formData = new FormData(this);

            fetch('/api/cancelaciones/guardar-documento', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                    // Nota: NO se define Content-Type para enviar archivos con FormData
                }
            })
            .then(response => {
                if (!response.ok) throw response;
                return response.json();
            })
            .then(data => {
                if (data.resultado === 'OK') {
                    alert('¡Trámite finalizado correctamente!');
                    window.location.href = '/home'; // Redirección al panel
                } else {
                    throw new Error(data.mensaje || 'Error al guardar documento');
                }
            })
            .catch(async (error) => {
                console.error('Error en Paso 2:', error);
                let msg = 'Error al subir el archivo';

                if (error.json) {
                    try {
                        const errData = await error.json();
                        msg = errData.mensaje || msg;
                    } catch (e) {  }
                }

                alert(msg);
                if (btnFinalizar) {
                    btnFinalizar.disabled = false;
                    btnFinalizar.innerText = 'Finalizar Trámite';
                }
            });
        });
    }
});
