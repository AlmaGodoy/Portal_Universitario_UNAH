document.addEventListener('DOMContentLoaded', () => {
    const botonesCopiar = document.querySelectorAll('[data-copy]');
    const toast = document.getElementById('perfilSecretariaToast');

    let temporizadorToast = null;

    botonesCopiar.forEach((boton) => {
        boton.addEventListener('click', async () => {
            const valor = boton.dataset.copy?.trim();
            const etiqueta = boton.dataset.label || 'dato';
            const contenidoOriginal = boton.innerHTML;

            if (!valor || valor === 'No disponible') {
                mostrarToast(`El ${etiqueta} no está disponible.`, true);
                return;
            }

            try {
                await copiarTexto(valor);

                boton.innerHTML = '<i class="fas fa-check"></i> Copiado';
                boton.disabled = true;

                mostrarToast(`El ${etiqueta} fue copiado correctamente.`);

                setTimeout(() => {
                    boton.innerHTML = contenidoOriginal;
                    boton.disabled = false;
                }, 1800);
            } catch (error) {
                console.error(`Error al copiar el ${etiqueta}:`, error);

                mostrarToast(
                    `No se pudo copiar el ${etiqueta}.`,
                    true
                );
            }
        });
    });

    async function copiarTexto(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(texto);
            return;
        }

        copiarConElementoTemporal(texto);
    }

    function copiarConElementoTemporal(texto) {
        const textarea = document.createElement('textarea');

        textarea.value = texto;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '-9999px';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';

        document.body.appendChild(textarea);

        textarea.focus();
        textarea.select();

        const copiado = document.execCommand('copy');

        document.body.removeChild(textarea);

        if (!copiado) {
            throw new Error('El navegador no permitió copiar el texto.');
        }
    }

    function mostrarToast(mensaje, esError = false) {
        if (!toast) {
            return;
        }

        if (temporizadorToast) {
            clearTimeout(temporizadorToast);
        }

        toast.textContent = mensaje;
        toast.classList.toggle('error', esError);
        toast.classList.add('visible');

        temporizadorToast = setTimeout(() => {
            toast.classList.remove('visible');
            toast.classList.remove('error');
        }, 2500);
    }
});