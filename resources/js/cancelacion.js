/**
 * PumaGestión — Lógica de Cancelación Digital
 * FCEAC · UNAH
 * Desarrollado por: Alma Patricia Godoy
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Contador de Caracteres Dinámico ───────────────────────────
    const textarea  = document.getElementById('observacion');
    const charCount = document.getElementById('charCount');
    const MAX_CHARS = 500;

    if (textarea && charCount) {
        const updateCount = () => {
            const len = textarea.value.length;
            charCount.textContent = `${len} / ${MAX_CHARS}`;

            // Gestión de estados visuales del contador
            charCount.classList.remove('warn', 'over');
            if (len >= MAX_CHARS) {
                charCount.classList.add('over');
                charCount.style.color = '#e24b4a'; // Rojo error
            } else if (len >= MAX_CHARS * 0.8) {
                charCount.classList.add('warn');
                charCount.style.color = '#f5a623'; // Dorado advertencia
            } else {
                charCount.style.color = 'rgba(255, 255, 255, 0.3)';
            }
        };
        textarea.addEventListener('input', updateCount);
        updateCount();
    }

    // ── 2. Validación y Envío del Formulario (AJAX) ──────────────────
    const form = document.getElementById('formCancelacion');
    const btn  = document.getElementById('btnSubmit');

    if (form && btn) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validación previa antes de intentar el envío
            if (!validateForm()) {
                showToast('Por favor, complete los campos obligatorios.', 'error');
                return;
            }

            // Estado de carga (UX)
            setLoading(true);

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('Solicitud enviada con éxito.', 'success');
                    // Redirección controlada por el backend
                    setTimeout(() => {
                        window.location.href = data.redirect || '/dashboard';
                    }, 1500);
                } else {
                    // Manejo de errores de validación de Laravel (422)
                    const errorMsg = data.message || 'Error en la validación de datos.';
                    showToast(errorMsg, 'error');
                    setLoading(false);
                }
            } catch (error) {
                console.error('Error PumaGestión:', error);
                showToast('Error de conexión con el servidor.', 'error');
                setLoading(false);
            }
        });
    }

    // ── 3. Validación de Campos en Tiempo Real ──────────────────────
    const requiredFields = document.querySelectorAll('.puma-input[required], .puma-select[required], .puma-textarea[required]');

    requiredFields.forEach(field => {
        ['blur', 'change'].forEach(evt => {
            field.addEventListener(evt, () => validateField(field));
        });
    });

    // ── 4. Funciones Auxiliares (Helpers) ────────────────────────────

    function validateForm() {
        let isValid = true;
        requiredFields.forEach(field => {
            if (!validateField(field)) isValid = false;
        });
        return isValid;
    }

    function validateField(field) {
        const val = field.value.trim();
        const isValid = val !== '';

        if (!isValid) {
            field.style.borderColor = '#e24b4a'; // Rojo UNAH Error
            field.style.boxShadow = '0 0 0 3px rgba(226, 75, 74, 0.15)';
        } else {
            field.style.borderColor = 'rgba(245, 166, 35, 0.5)'; // Dorado UNAH
            field.style.boxShadow = 'none';
        }
        return isValid;
    }

    function setLoading(isLoading) {
        if (isLoading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="puma-spinner"></span> Procesando...';
            btn.style.opacity = '0.7';
        } else {
            btn.disabled = false;
            btn.innerHTML = 'Enviar Solicitud <i class="puma-btn__arrow">→</i>';
            btn.style.opacity = '1';
        }
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // ── 5. Sistema de Notificaciones (Toast) ─────────────────────────
    function showToast(message, type = 'error') {
        const existing = document.getElementById('puma-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'puma-toast';
        const bgColor = type === 'success' ? '#2d6a4f' : '#a32d2d';

        toast.style.cssText = `
            position: fixed; bottom: 30px; left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: ${bgColor}; color: white;
            padding: 12px 25px; border-radius: 12px;
            font-family: 'Nunito', sans-serif; font-size: 14px;
            font-weight: 700; z-index: 10000; opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        `;

        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
        }, 100);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    }
});
