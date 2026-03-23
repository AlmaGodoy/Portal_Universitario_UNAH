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

    const nombreInput = document.getElementById('nombre');
    if (nombreInput) {
        const re = /[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g;

        nombreInput.addEventListener('input', function () {
            this.value = this.value.replace(re, '');
        });

        nombreInput.addEventListener('paste', function (e) {
            e.preventDefault();
            this.value = (e.clipboardData.getData('text') || '').replace(re, '');
        });
    }

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
    const boxDep = document.getElementById('box_departamento');
    const depSel = document.getElementById('id_departamento');
    const bloqueEst = document.getElementById('bloque_estudiante');
    const bloqueEmp = document.getElementById('bloque_empleado');
    const rolAutoBox = document.getElementById('rol_auto_box');
    const rolManualBox = document.getElementById('rol_manual_box');
    const rolManualSel = document.getElementById('id_rol_manual');
    const codEmp = document.getElementById('cod_empleado');
    const tipoEmp = document.getElementById('tipo_empleado');
    const correoHint = document.getElementById('correo_hint');
    const correoInput = document.getElementById('correo');
    const carSel = document.getElementById('id_carrera');

    const carreras = JSON.parse(form.dataset.carreras || '[]');
    const oldCarrera = form.dataset.oldCarrera || '';

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

    function aplicarUI() {
        const tipo = getTipoActual();

        ocultar(bloqueEst);
        ocultar(bloqueEmp);
        ocultar(boxDep);
        ocultar(rolAutoBox);
        ocultar(rolManualBox);

        if (depSel) depSel.required = false;
        if (codEmp) codEmp.required = false;
        if (tipoEmp) tipoEmp.required = false;

        setCorreoRegla(tipo);

        if (tipo === 'estudiante') {
            mostrar(bloqueEst);
            cargarCarrerasTodas();
            return;
        }

        if (tipo === 'empleado') {
            mostrar(rolManualBox);
            mostrar(boxDep);
            mostrar(bloqueEmp);

            if (depSel) depSel.required = true;
            if (codEmp) codEmp.required = true;
            if (tipoEmp) tipoEmp.required = true;

            const rolTexto = rolManualSel?.selectedOptions?.[0]?.textContent?.toLowerCase()?.trim() || '';
            if (tipoEmp) tipoEmp.value = rolTexto;
        }
    }

    rolManualSel?.addEventListener('change', aplicarUI);
    tipoSel?.addEventListener('change', aplicarUI);

    correoInput?.addEventListener('input', () => {
        const tipo = getTipoActual();
        correoInput.setCustomValidity(
            validarCorreoDominio(tipo) ? '' : 'Dominio inválido para este tipo.'
        );
    });

    aplicarUI();
});
