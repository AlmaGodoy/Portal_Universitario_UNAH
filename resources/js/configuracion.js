document.addEventListener('DOMContentLoaded', () => {
    const toggleButtons = document.querySelectorAll('.btn-toggle-password');
    const passwordNueva = document.getElementById('password_nueva');
    const passwordConfirm = document.getElementById('password_nueva_confirmation');
    const formCambiarPassword = document.getElementById('formCambiarPassword');

    const ruleLength = document.getElementById('ruleLength');
    const ruleUpper = document.getElementById('ruleUpper');
    const ruleNumber = document.getElementById('ruleNumber');
    const ruleSymbol = document.getElementById('ruleSymbol');
    const passwordMatchMessage = document.getElementById('passwordMatchMessage');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');

    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = button.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    function validatePasswordRules(password) {
        const validations = {
            length: password.length >= 8,
            upper: /[A-ZÁÉÍÓÚÑ]/.test(password),
            number: /\d/.test(password),
            symbol: /[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9]/.test(password),
        };

        ruleLength.classList.toggle('valid', validations.length);
        ruleLength.classList.toggle('invalid', password.length > 0 && !validations.length);

        ruleUpper.classList.toggle('valid', validations.upper);
        ruleUpper.classList.toggle('invalid', password.length > 0 && !validations.upper);

        ruleNumber.classList.toggle('valid', validations.number);
        ruleNumber.classList.toggle('invalid', password.length > 0 && !validations.number);

        ruleSymbol.classList.toggle('valid', validations.symbol);
        ruleSymbol.classList.toggle('invalid', password.length > 0 && !validations.symbol);

        const score = Object.values(validations).filter(Boolean).length;

        passwordStrengthBar.classList.remove('low', 'medium', 'high');

        if (password.length === 0) {
            passwordStrengthBar.style.width = '0';
            passwordStrengthText.textContent = 'Seguridad de contraseña: pendiente';
        } else if (score <= 2) {
            passwordStrengthBar.classList.add('low');
            passwordStrengthText.textContent = 'Seguridad de contraseña: baja';
        } else if (score === 3) {
            passwordStrengthBar.classList.add('medium');
            passwordStrengthText.textContent = 'Seguridad de contraseña: media';
        } else {
            passwordStrengthBar.classList.add('high');
            passwordStrengthText.textContent = 'Seguridad de contraseña: alta';
        }
    }

    function validatePasswordMatch() {
        const nueva = passwordNueva?.value || '';
        const confirmacion = passwordConfirm?.value || '';

        if (!confirmacion.length) {
            passwordMatchMessage.textContent = '• Confirma la contraseña nueva';
            passwordMatchMessage.classList.remove('valid', 'invalid');
            return;
        }

        if (nueva === confirmacion) {
            passwordMatchMessage.textContent = '• Las contraseñas coinciden';
            passwordMatchMessage.classList.add('valid');
            passwordMatchMessage.classList.remove('invalid');
        } else {
            passwordMatchMessage.textContent = '• Las contraseñas no coinciden';
            passwordMatchMessage.classList.add('invalid');
            passwordMatchMessage.classList.remove('valid');
        }
    }

    if (passwordNueva) {
        passwordNueva.addEventListener('input', () => {
            validatePasswordRules(passwordNueva.value);
            validatePasswordMatch();
        });
    }

    if (passwordConfirm) {
        passwordConfirm.addEventListener('input', validatePasswordMatch);
    }

    if (formCambiarPassword) {
        formCambiarPassword.addEventListener('submit', (e) => {
            const actual = document.getElementById('password_actual')?.value.trim() || '';
            const nueva = passwordNueva?.value.trim() || '';
            const confirmacion = passwordConfirm?.value.trim() || '';

            const cumpleMinimo = nueva.length >= 8;
            const coincide = nueva === confirmacion;

            if (!actual || !nueva || !confirmacion) {
                e.preventDefault();
                alert('Debes completar todos los campos para cambiar la contraseña.');
                return;
            }

            if (!cumpleMinimo) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 8 caracteres.');
                return;
            }

            if (!coincide) {
                e.preventDefault();
                alert('La confirmación de la contraseña no coincide.');
            }
        });
    }
});