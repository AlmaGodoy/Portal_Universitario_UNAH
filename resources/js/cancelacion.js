/**
 * PumaGestión — Cancelación Excepcional
 * FCEAC · UNAH
 * JS completo para:
 * - Mostrar paso 1 / paso 2
 * - Popover legal
 * - Validación visual de formulario
 * - Ayuda dinámica para tipo de solicitud
 * - Toast / mensajes flotantes desde arriba
 */

document.addEventListener('DOMContentLoaded', () => {
    initStartProcess();
    initLegalPopover();
    initFormValidation();
    initTipoSolicitudHelper();
});

/* =========================================================
   CONFIG TOAST
========================================================= */
const TOAST_TOP = 138;              // Más abajo
const SIDEBAR_OFFSET = 72;          // Mitad aprox. del sidebar visible
const TOAST_SHOW_TIME = 1050;       // Se queda poco tiempo
const TOAST_EXIT_TIME = 160;        // Desaparece fugazmente

/* =========================================================
   1. CAMBIO DE INTRO A FORMULARIO
========================================================= */
function initStartProcess() {
    window.startProcess = function () {
        const intro = document.getElementById('step-intro');
        const form = document.getElementById('step-form');

        if (intro) {
            intro.style.display = 'none';
        }

        if (form) {
            form.style.display = 'block';
            form.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    };
}

/* =========================================================
   2. POPOVER / FLOTANTE DE SUSTENTO LEGAL
========================================================= */
function initLegalPopover() {
    const popover = document.getElementById('legalPopover');
    const legalCard = document.querySelector('.info-item--legal');

    window.toggleLegalPopover = function (event) {
        if (event) {
            event.stopPropagation();
        }

        if (!popover) return;

        popover.classList.toggle('is-open');
    };

    document.addEventListener('click', function (event) {
        if (!popover || !legalCard) return;

        if (!legalCard.contains(event.target)) {
            popover.classList.remove('is-open');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && popover) {
            popover.classList.remove('is-open');
        }
    });
}

/* =========================================================
   3. VALIDACIÓN DEL FORMULARIO
========================================================= */
function initFormValidation() {
    const form = document.querySelector('#step-form form');

    if (!form) return;

    const requiredFields = form.querySelectorAll(
        '.puma-input[required], .puma-select[required], .puma-textarea[required]'
    );

    requiredFields.forEach((field) => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('change', () => validateField(field));
        field.addEventListener('input', () => clearFieldState(field));
    });

    form.addEventListener('submit', function (event) {
        let isValid = true;

        requiredFields.forEach((field) => {
            const valid = validateField(field);
            if (!valid) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault();
            showToast('Por favor, complete los campos obligatorios.', 'error');

            const firstInvalidField = form.querySelector('.field-invalid');
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                setTimeout(() => {
                    firstInvalidField.focus();
                }, 250);
            }
        }
    });
}

/* =========================================================
   4. VALIDACIÓN INDIVIDUAL
========================================================= */
function validateField(field) {
    if (!field) return true;

    const value = field.value ? field.value.trim() : '';
    const isValid = value !== '';

    if (!isValid) {
        field.classList.add('field-invalid');
        field.classList.remove('field-valid');
        field.style.borderColor = '#c94a4a';
        field.style.boxShadow = '0 0 0 4px rgba(201, 74, 74, 0.12)';
    } else {
        field.classList.remove('field-invalid');
        field.classList.add('field-valid');
        field.style.borderColor = '#d8dfeb';
        field.style.boxShadow = 'none';
    }

    return isValid;
}

function clearFieldState(field) {
    if (!field) return;

    const value = field.value ? field.value.trim() : '';

    if (value !== '') {
        field.classList.remove('field-invalid');
        field.classList.remove('field-valid');
        field.style.borderColor = '#d8dfeb';
        field.style.boxShadow = 'none';
    }
}

/* =========================================================
   5. AYUDA DINÁMICA PARA TIPO DE SOLICITUD
========================================================= */
function initTipoSolicitudHelper() {
    const tipoSelect = document.getElementById('tipo');
    const tipoInfo = document.querySelector('.puma-tipo-info');

    if (!tipoSelect || !tipoInfo) return;

    const items = tipoInfo.querySelectorAll('.puma-tipo-info__item');

    const resetHighlight = () => {
        items.forEach((item) => {
            const icon = item.querySelector('.puma-tipo-info__icon');
            const title = item.querySelector('.puma-tipo-info__content strong');
            const text = item.querySelector('.puma-tipo-info__content p');

            item.style.borderLeftColor = '#e4b220';
            item.style.borderLeftWidth = '4px';
            item.style.background = '#ffffff';
            item.style.boxShadow = '0 8px 18px rgba(15, 28, 63, 0.05)';
            item.style.transform = 'scale(1)';
            item.style.opacity = '0.72';
            item.style.filter = 'grayscale(0.05)';

            if (icon) {
                icon.style.background = 'rgba(16, 42, 92, 0.08)';
                icon.style.color = '#102a5c';
                icon.style.transform = 'scale(1)';
                icon.style.boxShadow = 'none';
            }

            if (title) {
                title.style.color = '#102a5c';
            }

            if (text) {
                text.style.color = '#667085';
            }
        });
    };

    const highlightItem = (selectedItem) => {
        if (!selectedItem) return;

        const icon = selectedItem.querySelector('.puma-tipo-info__icon');
        const title = selectedItem.querySelector('.puma-tipo-info__content strong');
        const text = selectedItem.querySelector('.puma-tipo-info__content p');

        selectedItem.style.borderLeftColor = '#102a5c';
        selectedItem.style.borderLeftWidth = '6px';
        selectedItem.style.background = 'linear-gradient(180deg, #fffdf3 0%, #f8fbff 100%)';
        selectedItem.style.boxShadow = '0 16px 32px rgba(16, 42, 92, 0.18)';
        selectedItem.style.transform = 'scale(1.02)';
        selectedItem.style.opacity = '1';
        selectedItem.style.filter = 'none';

        if (icon) {
            icon.style.background = 'linear-gradient(180deg, #102a5c 0%, #16356f 100%)';
            icon.style.color = '#ffffff';
            icon.style.transform = 'scale(1.08)';
            icon.style.boxShadow = '0 10px 20px rgba(16, 42, 92, 0.22)';
        }

        if (title) {
            title.style.color = '#0b1f47';
        }

        if (text) {
            text.style.color = '#42526b';
        }
    };

    const updateTipoInfo = (showToastMessage = false) => {
        const value = tipoSelect.value;

        resetHighlight();

        if (value === 'Parcial') {
            const parcialCard = tipoInfo.querySelector('[data-tipo-card="Parcial"]');
            highlightItem(parcialCard);

            if (showToastMessage) {
                showToast('Seleccionó Cancelación Parcial.', 'success');
            }
        } else if (value === 'Total') {
            const totalCard = tipoInfo.querySelector('[data-tipo-card="Total"]');
            highlightItem(totalCard);

            if (showToastMessage) {
                showToast('Seleccionó Cancelación Total del período.', 'success');
            }
        }
    };

    tipoSelect.addEventListener('change', () => {
        updateTipoInfo(true);
    });

    updateTipoInfo(false);
}

/* =========================================================
   6. TOAST / MENSAJES FLOTANTES DESDE ARRIBA
========================================================= */
function showToast(message, type = 'error') {
    const existingToast = document.getElementById('puma-toast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'puma-toast';

    const isSuccess = type === 'success';
    const background = isSuccess ? '#1f8f55' : '#c94a4a';

    toast.style.cssText = `
        position: fixed;
        left: calc(50% + ${SIDEBAR_OFFSET}px);
        top: ${TOAST_TOP}px;
        transform: translateX(-50%) translateY(-56px);
        background: ${background};
        color: #ffffff;
        padding: 14px 24px;
        border-radius: 14px;
        font-family: 'Nunito', sans-serif;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.4;
        box-shadow: 0 16px 34px rgba(0,0,0,0.20);
        z-index: 99999;
        opacity: 0;
        transition: transform 0.34s ease, opacity 0.18s ease;
        width: max-content;
        max-width: min(420px, calc(100vw - 32px));
        text-align: center;
        pointer-events: none;
    `;

    toast.textContent = message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(-28px)';

        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, TOAST_EXIT_TIME);
    }, TOAST_SHOW_TIME);
}