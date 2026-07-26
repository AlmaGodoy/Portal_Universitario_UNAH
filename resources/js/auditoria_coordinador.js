document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById(
        'formAuditoriaCoordinador'
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

    const idUsuario = document.getElementById(
        'id_usuario'
    );

    const idTramite = document.getElementById(
        'id_tramite'
    );

    const errorFechaInicio = document.getElementById(
        'errorFechaInicioAuditoriaCoordinador'
    );

    const errorFechaFin = document.getElementById(
        'errorFechaFinAuditoriaCoordinador'
    );

    const errorUsuario = document.getElementById(
        'errorUsuarioAuditoriaCoordinador'
    );

    const errorTramite = document.getElementById(
        'errorTramiteAuditoriaCoordinador'
    );

    const botonBuscar = document.getElementById(
        'btnBuscarAuditoriaCoordinador'
    );

    const camposOpcionales = formulario.querySelectorAll(
        '[data-coord-aud-optional]'
    );

    const contenidoOriginalBoton = botonBuscar
        ? botonBuscar.innerHTML
        : '';

    const mostrarError = (
        campo,
        elementoError,
        mostrar
    ) => {
        campo?.classList.toggle(
            'coord-aud-invalid',
            mostrar
        );

        elementoError?.classList.toggle(
            'coord-aud-error--visible',
            mostrar
        );
    };

    const actualizarFechaMinima = () => {
        if (!fechaInicio || !fechaFin) {
            return;
        }

        fechaFin.min = fechaInicio.value || '';
    };

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
            fechaFin.classList.add(
                'coord-aud-invalid'
            );

            errorFechaFin?.classList.remove(
                'coord-aud-error--visible'
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
            errorFechaFin,
            !rangoValido
        );

        return rangoValido;
    };

    const validarEnteroOpcional = (
        campo,
        elementoError
    ) => {
        if (!campo) {
            return true;
        }

        const valor = campo.value.trim();

        if (valor === '') {
            mostrarError(
                campo,
                elementoError,
                false
            );

            return true;
        }

        const numero = Number(valor);

        const valido =
            Number.isInteger(numero)
            && numero > 0;

        mostrarError(
            campo,
            elementoError,
            !valido
        );

        return valido;
    };

    const limpiarTextos = () => {
        formulario
            .querySelectorAll('input[type="text"]')
            .forEach((campo) => {
                campo.value = campo.value.trim();
            });
    };

    const deshabilitarOpcionalesVacios = () => {
        camposOpcionales.forEach((campo) => {
            const valor = String(
                campo.value ?? ''
            ).trim();

            if (valor === '') {
                campo.disabled = true;
            }
        });
    };

    const habilitarOpcionales = () => {
        camposOpcionales.forEach((campo) => {
            campo.disabled = false;
        });
    };

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

    const restaurarFormulario = () => {
        habilitarOpcionales();

        if (!botonBuscar) {
            return;
        }

        botonBuscar.disabled = false;

        botonBuscar.innerHTML =
            contenidoOriginalBoton;
    };

    actualizarFechaMinima();

    fechaInicio?.addEventListener(
        'input',
        () => {
            actualizarFechaMinima();
            validarFechas();
        }
    );

    fechaInicio?.addEventListener(
        'change',
        () => {
            actualizarFechaMinima();
            validarFechas();
        }
    );

    fechaFin?.addEventListener(
        'input',
        validarFechas
    );

    fechaFin?.addEventListener(
        'change',
        validarFechas
    );

    idUsuario?.addEventListener(
        'input',
        () => {
            validarEnteroOpcional(
                idUsuario,
                errorUsuario
            );
        }
    );

    idTramite?.addEventListener(
        'input',
        () => {
            validarEnteroOpcional(
                idTramite,
                errorTramite
            );
        }
    );

    formulario.addEventListener(
        'submit',
        (evento) => {
            limpiarTextos();

            const fechasValidas =
                validarFechas();

            const usuarioValido =
                validarEnteroOpcional(
                    idUsuario,
                    errorUsuario
                );

            const tramiteValido =
                validarEnteroOpcional(
                    idTramite,
                    errorTramite
                );

            if (
                !fechasValidas
                || !usuarioValido
                || !tramiteValido
            ) {
                evento.preventDefault();
                evento.stopPropagation();

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

                if (!usuarioValido) {
                    idUsuario?.focus();
                    return;
                }

                idTramite?.focus();
                return;
            }

            deshabilitarOpcionalesVacios();
            mostrarCarga();
        }
    );

    window.addEventListener(
        'pageshow',
        restaurarFormulario
    );

    document
        .querySelectorAll(
            '[data-coord-aud-auto-close]'
        )
        .forEach((alerta) => {
            window.setTimeout(() => {
                alerta.style.transition =
                    'opacity 0.3s ease, '
                    + 'transform 0.3s ease';

                alerta.style.opacity = '0';

                alerta.style.transform =
                    'translateY(-6px)';

                window.setTimeout(() => {
                    alerta.remove();
                }, 300);
            }, 4500);
        });
});