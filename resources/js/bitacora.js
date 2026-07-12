document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById(
        'formFiltrosBitacora'
    );

    if (!formulario) {
        return;
    }

    const fechaInicio = document.getElementById(
        'fecha_inicio'
    );

    const fechaFin = document.getElementById(
        'fecha_fin'
    );

    const idTramite = document.getElementById(
        'id_tramite'
    );

    const errorFechaInicio = document.getElementById(
        'errorFechaInicioBitacora'
    );

    const errorFechas = document.getElementById(
        'errorFechasBitacora'
    );

    const errorTramite = document.getElementById(
        'errorTramiteBitacora'
    );

    const botonBuscar = document.getElementById(
        'btnBuscarBitacora'
    );

    const camposOpcionales = formulario.querySelectorAll(
        '[data-bita-optional]'
    );

    const contenidoOriginalBoton = botonBuscar
        ? botonBuscar.innerHTML
        : '';

    /**
     * Agrega o elimina el estado visual de error.
     */
    const mostrarError = (
        campo,
        elementoError,
        mostrar
    ) => {
        campo?.classList.toggle(
            'bita-invalid',
            mostrar
        );

        elementoError?.classList.toggle(
            'bita-error--visible',
            mostrar
        );
    };

    /**
     * Coloca la fecha inicial como mínimo permitido
     * para la fecha final.
     */
    const actualizarFechaMinima = () => {
        if (!fechaInicio || !fechaFin) {
            return;
        }

        fechaFin.min = fechaInicio.value || '';
    };

    /**
     * Valida que ambas fechas hayan sido seleccionadas
     * y que el rango sea correcto.
     */
    const validarFechas = () => {
        if (!fechaInicio || !fechaFin) {
            return true;
        }

        const inicioVacio =
            fechaInicio.value.trim() === '';

        const finVacio =
            fechaFin.value.trim() === '';

        mostrarError(
            fechaInicio,
            errorFechaInicio,
            inicioVacio
        );

        if (finVacio) {
            fechaFin.classList.add('bita-invalid');

            errorFechas?.classList.remove(
                'bita-error--visible'
            );

            return false;
        }

        if (inicioVacio) {
            return false;
        }

        const rangoValido =
            fechaFin.value >= fechaInicio.value;

        mostrarError(
            fechaFin,
            errorFechas,
            !rangoValido
        );

        return rangoValido;
    };

    /**
     * Valida el número de trámite solamente cuando
     * se haya ingresado un valor.
     */
    const validarTramite = () => {
        if (!idTramite) {
            return true;
        }

        const valor = idTramite.value.trim();

        if (valor === '') {
            mostrarError(
                idTramite,
                errorTramite,
                false
            );

            return true;
        }

        const numero = Number(valor);

        const esValido =
            Number.isInteger(numero)
            && numero > 0;

        mostrarError(
            idTramite,
            errorTramite,
            !esValido
        );

        return esValido;
    };

    /**
     * Elimina espacios innecesarios en los filtros
     * de tipo texto.
     */
    const limpiarCamposTexto = () => {
        formulario
            .querySelectorAll('input[type="text"]')
            .forEach((campo) => {
                campo.value = campo.value.trim();
            });
    };

    /**
     * Deshabilita temporalmente los filtros opcionales
     * vacíos para que no se envíen en la URL.
     */
    const deshabilitarCamposVacios = () => {
        camposOpcionales.forEach((campo) => {
            const valor = String(
                campo.value ?? ''
            ).trim();

            if (valor === '') {
                campo.disabled = true;
            }
        });
    };

    /**
     * Habilita nuevamente los filtros opcionales.
     */
    const habilitarCamposOpcionales = () => {
        camposOpcionales.forEach((campo) => {
            campo.disabled = false;
        });
    };

    /**
     * Coloca el botón en estado de carga.
     */
    const mostrarCarga = () => {
        if (!botonBuscar) {
            return;
        }

        botonBuscar.disabled = true;

        botonBuscar.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <span>Buscando...</span>
        `;
    };

    /**
     * Restaura el botón después de regresar mediante
     * el historial del navegador.
     */
    const restaurarBoton = () => {
        habilitarCamposOpcionales();

        if (!botonBuscar) {
            return;
        }

        botonBuscar.disabled = false;
        botonBuscar.innerHTML =
            contenidoOriginalBoton;
    };

    actualizarFechaMinima();
    validarTramite();

    fechaInicio?.addEventListener('change', () => {
        actualizarFechaMinima();
        validarFechas();
    });

    fechaInicio?.addEventListener(
        'input',
        validarFechas
    );

    fechaFin?.addEventListener(
        'change',
        validarFechas
    );

    fechaFin?.addEventListener(
        'input',
        validarFechas
    );

    idTramite?.addEventListener(
        'input',
        validarTramite
    );

    formulario.addEventListener('submit', (event) => {
        limpiarCamposTexto();

        const fechasValidas =
            validarFechas();

        const tramiteValido =
            validarTramite();

        if (
            !fechasValidas
            || !tramiteValido
        ) {
            event.preventDefault();
            event.stopPropagation();

            if (!fechasValidas) {
                if (
                    fechaInicio
                    && fechaInicio.value === ''
                ) {
                    fechaInicio.focus();
                } else {
                    fechaFin?.focus();
                }

                return;
            }

            idTramite?.focus();

            return;
        }

        deshabilitarCamposVacios();
        mostrarCarga();
    });

    window.addEventListener(
        'pageshow',
        restaurarBoton
    );

    /**
     * Oculta automáticamente las alertas de éxito.
     */
    document
        .querySelectorAll('[data-bita-auto-close]')
        .forEach((alerta) => {
            window.setTimeout(() => {
                alerta.style.transition =
                    'opacity 0.3s ease, transform 0.3s ease';

                alerta.style.opacity = '0';
                alerta.style.transform =
                    'translateY(-6px)';

                window.setTimeout(() => {
                    alerta.remove();
                }, 300);
            }, 4500);
        });
});