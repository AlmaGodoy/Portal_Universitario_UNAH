document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');
    if (!form) return;

    function mostrar(elemento) {
        if (elemento) elemento.classList.remove('hidden-field');
    }

    function ocultar(elemento) {
        if (elemento) elemento.classList.add('hidden-field');
    }

    function soloNumerosMax(input, max) {
        if (!input) return;

        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, max);
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const texto = (e.clipboardData.getData('text') || '')
                .replace(/\D/g, '')
                .slice(0, max);
            this.value = texto;
        });
    }

    function aplicarSoloLetras(idCampo) {
        const input = document.getElementById(idCampo);
        if (!input) return;

        const re = /[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g;

        input.addEventListener('input', function () {
            this.value = this.value.replace(re, '').replace(/\s{2,}/g, ' ');
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            this.value = (e.clipboardData.getData('text') || '')
                .replace(re, '')
                .replace(/\s{2,}/g, ' ');
        });
    }

    function capitalizarTexto(input) {
        if (!input) return;

        input.addEventListener('blur', function () {
            this.value = this.value
                .toLowerCase()
                .replace(/\b\w/g, function (letra) {
                    return letra.toUpperCase();
                });
        });
    }

    aplicarSoloLetras('primer_nombre');
    aplicarSoloLetras('segundo_nombre');
    aplicarSoloLetras('primer_apellido');
    aplicarSoloLetras('segundo_apellido');

    capitalizarTexto(document.getElementById('primer_nombre'));
    capitalizarTexto(document.getElementById('segundo_nombre'));
    capitalizarTexto(document.getElementById('primer_apellido'));
    capitalizarTexto(document.getElementById('segundo_apellido'));

    soloNumerosMax(document.getElementById('numero_cuenta'), 11);

    function togglePassword(inputId, btnId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(btnId);
        if (!input || !button) return;

        button.addEventListener('click', () => {
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            button.textContent = esPassword ? '🔒' : '🔓';
        });
    }

    togglePassword('contrasena', 'btn_toggle_pass');
    togglePassword('contrasena_confirmation', 'btn_toggle_pass2');

    const pass1 = document.getElementById('contrasena');
    const pass2 = document.getElementById('contrasena_confirmation');
    const mismatch = document.getElementById('pass_mismatch');

    function validarMatch() {
        if (!pass1 || !pass2 || !mismatch) return;

        const v1 = pass1.value || '';
        const v2 = pass2.value || '';

        if (v1.length > 0 && v2.length > 0 && v1 !== v2) {
            mostrar(mismatch);
        } else {
            ocultar(mismatch);
        }
    }

    pass1?.addEventListener('input', validarMatch);
    pass2?.addEventListener('input', validarMatch);

    const tipoSel = document.getElementById('tipo_usuario');
    const rolManualSel = document.getElementById('id_rol_manual');
    const tipoEmp = document.getElementById('tipo_empleado');
    const correoHint = document.getElementById('correo_hint');
    const correoInput = document.getElementById('correo');
    const carSel = document.getElementById('id_carrera');

    const carreras = JSON.parse(form.dataset.carreras || '[]');
    const oldCarrera = form.dataset.oldCarrera || '';

    function getTipoActual() {
        return (tipoSel?.value || '').toLowerCase().trim();
    }

    function setCorreoRegla(tipo) {
        if (!correoHint) return;

        if (tipo === 'estudiante') {
            correoHint.textContent = 'Estudiante: solo correos @unah.hn';
        } else if (tipo === 'empleado') {
            correoHint.textContent = 'Empleado: solo correos @unah.edu.hn';
        } else {
            correoHint.textContent = '';
        }
    }

    function validarCorreoDominio(tipo) {
        if (!correoInput) return true;

        const valor = (correoInput.value || '').trim().toLowerCase();
        if (!valor) return true;

        if (tipo === 'estudiante') return valor.endsWith('@unah.hn');
        if (tipo === 'empleado') return valor.endsWith('@unah.edu.hn');

        return true;
    }

    function cargarCarrerasTodas() {
        if (!carSel) return;

        carSel.innerHTML = '<option value="">Seleccione...</option>';

        carreras.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id_carrera;
            opt.textContent = c.nombre_carrera;

            if (oldCarrera && String(oldCarrera) === String(c.id_carrera)) {
                opt.selected = true;
            }

            carSel.appendChild(opt);
        });
    }

    function syncTipoEmpleado() {
    if (!rolManualSel || !tipoEmp) return;

    const rol = String(rolManualSel.value || '');
    const departamentoInput = document.getElementById('id_departamento');
    const departamentoCol = departamentoInput ? departamentoInput.closest('.col-md-6') : null;

    if (rol === '1') {
        tipoEmp.value = 'secretaria_general';

        if (departamentoInput) {
            departamentoInput.value = '';
            departamentoInput.required = false;
        }

        if (departamentoCol) {
            departamentoCol.classList.add('hidden-field');
        }

    } else if (rol === '4') {
        tipoEmp.value = 'coordinador';

        if (departamentoInput) {
            departamentoInput.required = true;
        }

        if (departamentoCol) {
            departamentoCol.classList.remove('hidden-field');
        }

    } else if (rol === '5') {
        tipoEmp.value = 'secretario';

        if (departamentoInput) {
            departamentoInput.required = true;
        }

        if (departamentoCol) {
            departamentoCol.classList.remove('hidden-field');
        }

    } else {
        tipoEmp.value = '';

        if (departamentoInput) {
            departamentoInput.required = false;
        }

        if (departamentoCol) {
            departamentoCol.classList.remove('hidden-field');
        }
    }
}

    const tipoActual = getTipoActual();
    setCorreoRegla(tipoActual);

    if (tipoActual === 'estudiante') {
        cargarCarrerasTodas();
    }

    syncTipoEmpleado();

    rolManualSel?.addEventListener('change', syncTipoEmpleado);

    correoInput?.addEventListener('input', () => {
        const tipo = getTipoActual();
        correoInput.setCustomValidity(
            validarCorreoDominio(tipo) ? '' : 'Dominio inválido para este tipo.'
        );
    });

    validarMatch();
});
