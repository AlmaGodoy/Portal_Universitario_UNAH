document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.cancelacion-detalle-page');
    if (!page) return;

    const cards = page.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.classList.add('fade-in-up');
        if (index % 3 === 1) card.classList.add('fade-delay-1');
        if (index % 3 === 2) card.classList.add('fade-delay-2');
        if (index % 3 === 0 && index > 0) card.classList.add('fade-delay-3');
    });

    const alerts = page.querySelectorAll('.alert-success');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 350);
        }, 4500);
    });

    const textareas = page.querySelectorAll('textarea[name="observacion"]');
    textareas.forEach((textarea) => {
        const maxLength = parseInt(textarea.getAttribute('maxlength') || '1000', 10);

        const counter = document.createElement('div');
        counter.className = 'char-counter';

        const updateCounter = () => {
            const current = textarea.value.length;
            counter.textContent = `${current}/${maxLength} caracteres`;

            if (current >= maxLength - 80) {
                counter.classList.add('is-limit');
            } else {
                counter.classList.remove('is-limit');
            }
        };

        textarea.insertAdjacentElement('afterend', counter);
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    const forms = page.querySelectorAll('form');
    forms.forEach((form) => {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('button[type="submit"]');
            if (!submitButton) return;

            const originalText = submitButton.innerHTML;
            submitButton.dataset.originalText = originalText;

            submitButton.disabled = true;
            submitButton.classList.add('loading-submit');
            submitButton.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Procesando...
            `;
        });
    });

    const documentoCards = page.querySelectorAll('.bg-light-subtle');
    documentoCards.forEach((card) => {
        const fileNameEl = card.querySelector('.small.text-muted');
        if (!fileNameEl) return;

        const fileName = (fileNameEl.textContent || '').trim();
        if (!fileName) return;

        let extension = 'DOC';
        const match = fileName.match(/\.([a-zA-Z0-9]+)$/);
        if (match) {
            extension = match[1].toUpperCase();
        }

        const metaWrap = document.createElement('div');
        metaWrap.className = 'documento-meta';

        const chip = document.createElement('span');
        chip.className = 'documento-chip';
        chip.innerHTML = `<i class="fas fa-file-alt"></i> ${extension}`;

        metaWrap.appendChild(chip);
        fileNameEl.insertAdjacentElement('afterend', metaWrap);
    });

    const infoCardBody = Array.from(page.querySelectorAll('.card-body')).find((body) => {
        return body.textContent.includes('Número de trámite') && body.textContent.includes('Estado actual');
    });

    if (infoCardBody) {
        infoCardBody.classList.add('tramite-info-grid');
    }

    const finalRuleCard = Array.from(page.querySelectorAll('.card')).find((card) => {
        return card.textContent.includes('Regla del proceso');
    });

    if (finalRuleCard) {
        finalRuleCard.classList.add('rule-card');
    }
});