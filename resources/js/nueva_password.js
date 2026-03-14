document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resetPasswordForm');
    if (!form) return;

    function mostrar(elemento) {
        if (elemento) elemento.classList.remove('hidden-field');
    }

    function ocultar(elemento) {
        if (elemento) elemento.classList.add('hidden-field');
    }

    function togglePassword(inputId, btnId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(btnId);

        if (!input || !button) return;

        button.addEventListener('click', () => {
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            button.textContent = esPassword ? '🔒' : '👁️';
        });
    }

    togglePassword('password', 'btn_toggle_pass');
    togglePassword('password_confirmation', 'btn_toggle_pass2');

    const pass1 = document.getElementById('password');
    const pass2 = document.getElementById('password_confirmation');
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
});
