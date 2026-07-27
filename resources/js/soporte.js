document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('studentSupportForm');
    const message = document.getElementById('supportMessage');
    const submitButton = document.getElementById('btnEnviarSoporte');

    if (!form || !message || !submitButton) {
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        submitButton.disabled = true;

        const originalText = submitButton.innerHTML;

        submitButton.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            Enviando...
        `;

        window.setTimeout(function () {
            message.classList.add('is-success');

            submitButton.disabled = false;
            submitButton.innerHTML = originalText;

            form.reset();

            window.setTimeout(function () {
                message.classList.remove('is-success');
            }, 5000);
        }, 700);
    });
});