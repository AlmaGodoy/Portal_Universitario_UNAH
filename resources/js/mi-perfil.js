document.addEventListener('DOMContentLoaded', () => {
    const btnCopiarCorreo = document.getElementById('btnCopiarCorreo');
    const perfilCorreo = document.getElementById('perfilCorreo');

    if (btnCopiarCorreo && perfilCorreo) {
        btnCopiarCorreo.addEventListener('click', async () => {
            const correo = perfilCorreo.textContent.trim();

            if (!correo || correo === 'No disponible') {
                mostrarMensajeBoton(
                    btnCopiarCorreo,
                    'Correo no disponible',
                    'fas fa-exclamation-circle',
                    'btn-error'
                );
                return;
            }

            try {
                await copiarTexto(correo);

                mostrarMensajeBoton(
                    btnCopiarCorreo,
                    'Correo copiado',
                    'fas fa-check',
                    'btn-copiado'
                );
            } catch (error) {
                mostrarMensajeBoton(
                    btnCopiarCorreo,
                    'No se pudo copiar',
                    'fas fa-times',
                    'btn-error'
                );
            }
        });
    }

    function copiarTexto(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(texto);
        }

        return new Promise((resolve, reject) => {
            const textarea = document.createElement('textarea');

            textarea.value = texto;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '-9999px';

            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                const copiado = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (copiado) {
                    resolve();
                } else {
                    reject(new Error('No se pudo copiar el texto.'));
                }
            } catch (error) {
                document.body.removeChild(textarea);
                reject(error);
            }
        });
    }

    function mostrarMensajeBoton(boton, texto, icono, claseTemporal) {
        const contenidoOriginal = boton.innerHTML;

        boton.innerHTML = `<i class="${icono}"></i> ${texto}`;
        boton.disabled = true;

        if (claseTemporal) {
            boton.classList.add(claseTemporal);
        }

        setTimeout(() => {
            boton.innerHTML = contenidoOriginal;
            boton.disabled = false;

            if (claseTemporal) {
                boton.classList.remove(claseTemporal);
            }
        }, 1800);
    }
});