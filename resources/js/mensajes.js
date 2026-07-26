document.addEventListener('DOMContentLoaded', function () {
    const messageForms = document.querySelectorAll('[data-message-form]');
    const deleteForms = document.querySelectorAll('[data-delete-message]');
    const closeAlertButtons = document.querySelectorAll('[data-alert-close]');
    const recipientSearch = document.querySelector('[data-recipient-search]');
    const recipientSelect = document.querySelector('[data-recipient-select]');
    const conversation = document.getElementById('mensajesConversation');

    function updateCounter(element) {
        if (!element.id) return;

        const counter = document.querySelector(
            `[data-counter-for="${element.id}"]`
        );

        if (!counter) return;

        const maximum = Number(element.getAttribute('maxlength') || 0);
        const current = element.value.length;

        counter.textContent = maximum > 0
            ? `${current}/${maximum}`
            : String(current);

        counter.classList.toggle(
            'counter-warning',
            maximum > 0 && current >= maximum * 0.9
        );
    }

    document.querySelectorAll('input[maxlength], textarea[maxlength]')
        .forEach(function (element) {
            updateCounter(element);

            element.addEventListener('input', function () {
                updateCounter(element);
            });
        });

    document.querySelectorAll('.mensaje-compose-textarea')
        .forEach(function (textarea) {
            const resizeTextarea = function () {
                textarea.style.height = 'auto';

                const newHeight = Math.min(
                    Math.max(textarea.scrollHeight, 130),
                    350
                );

                textarea.style.height = `${newHeight}px`;
            };

            resizeTextarea();
            textarea.addEventListener('input', resizeTextarea);
        });

    if (recipientSearch && recipientSelect) {
        const originalOptions = Array.from(
            recipientSelect.querySelectorAll('option')
        );

        recipientSearch.addEventListener('input', function () {
            const term = recipientSearch.value.trim().toLocaleLowerCase('es');

            originalOptions.forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const searchableText = (
                    option.dataset.search ||
                    option.textContent ||
                    ''
                ).toLocaleLowerCase('es');

                option.hidden = term !== '' &&
                    !searchableText.includes(term);
            });

            const selectedOption =
                recipientSelect.options[recipientSelect.selectedIndex];

            if (selectedOption && selectedOption.hidden) {
                recipientSelect.value = '';
            }
        });
    }

    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = window.confirm(
                '¿Deseas eliminar este mensaje de tu bandeja?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    messageForms.forEach(function (form) {
        let submitting = false;

        form.addEventListener('submit', function (event) {
            if (submitting) {
                event.preventDefault();
                return;
            }

            if (!form.checkValidity()) {
                return;
            }

            submitting = true;

            const submitButton = form.querySelector('[data-submit-message]');

            if (submitButton) {
                submitButton.disabled = true;

                const text = submitButton.querySelector('span');
                const icon = submitButton.querySelector('i');

                if (text) {
                    text.textContent = 'Enviando...';
                }

                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin';
                }
            }
        });

        const textarea = form.querySelector('textarea[name="contenido"]');

        if (textarea) {
            textarea.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Enter' &&
                    event.ctrlKey &&
                    textarea.value.trim() !== ''
                ) {
                    event.preventDefault();
                    form.requestSubmit();
                }
            });
        }
    });

    closeAlertButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const alert = button.closest('.mensajes-alert');

            if (!alert) return;

            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-5px)';

            window.setTimeout(function () {
                alert.remove();
            }, 200);
        });
    });

    if (conversation) {
        conversation.scrollTop = conversation.scrollHeight;
    }
});