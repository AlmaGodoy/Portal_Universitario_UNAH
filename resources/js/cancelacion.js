/**
 * PumaGestión — Cancelación Digital
 * FCEAC · UNAH
 * cancelacion.js
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Character counter ───────────────────────────
    const textarea  = document.getElementById('observacion')
    const charCount = document.getElementById('charCount')
    const MAX_CHARS = 500

    if (textarea && charCount) {
        const updateCount = () => {
            const len = textarea.value.length
            charCount.textContent = `${len} / ${MAX_CHARS}`
            charCount.classList.remove('warn', 'over')
            if (len >= MAX_CHARS)         charCount.classList.add('over')
            else if (len >= MAX_CHARS * 0.8) charCount.classList.add('warn')
        }
        textarea.addEventListener('input', updateCount)
        updateCount()
    }

    // ── Form validation & submit ────────────────────
    const form   = document.getElementById('formCancelacion')
    const btn    = document.getElementById('btnSubmit')

    if (!form || !btn) return

    form.addEventListener('submit', async (e) => {
        e.preventDefault()

        if (!validateForm()) return

        // Loading state
        btn.disabled = true
        btn.classList.add('loading')

        try {
            const response = await fetch(form.action || window.location.pathname, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body:    new FormData(form),
            })

            if (response.ok) {
                const data = await response.json().catch(() => ({}))
                if (data.redirect) {
                    window.location.href = data.redirect
                } else {
                    window.location.reload()
                }
            } else {
                showError('Ocurrió un error. Por favor intente de nuevo.')
                btn.disabled = false
                btn.classList.remove('loading')
            }
        } catch {
            showError('Error de conexión. Verifique su internet.')
            btn.disabled = false
            btn.classList.remove('loading')
        }
    })

    // ── Live field validation ────────────────────────
    const requiredFields = form.querySelectorAll('[required]')
    requiredFields.forEach(field => {
        field.addEventListener('change', () => validateField(field))
        field.addEventListener('blur',   () => validateField(field))
    })

    // ── Helpers ──────────────────────────────────────

    function validateForm () {
        let valid = true
        requiredFields.forEach(field => {
            if (!validateField(field)) valid = false
        })
        return valid
    }

    function validateField (field) {
        const isEmpty = field.value.trim() === '' || field.value === ''
        const wrap    = field.closest('.puma-input-wrap') || field.parentElement

        if (isEmpty) {
            field.style.borderColor = 'rgba(226, 75, 74, 0.65)'
            field.style.boxShadow   = '0 0 0 3px rgba(226, 75, 74, 0.10)'
            return false
        } else {
            field.style.borderColor = 'rgba(245, 166, 35, 0.40)'
            field.style.boxShadow   = '0 0 0 3px rgba(245, 166, 35, 0.08)'
            return true
        }
    }

    function getCsrfToken () {
        const meta = document.querySelector('meta[name="csrf-token"]')
        return meta ? meta.getAttribute('content') : ''
    }

    function showError (msg) {
        // Remove existing toast if any
        document.getElementById('puma-toast')?.remove()

        const toast = document.createElement('div')
        toast.id = 'puma-toast'
        toast.style.cssText = `
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #a32d2d;
            color: #fff;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
        `
        toast.textContent = msg
        document.body.appendChild(toast)

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity   = '1'
            toast.style.transform = 'translateX(-50%) translateY(0)'
        })

        // Auto-dismiss after 4 s
        setTimeout(() => {
            toast.style.opacity   = '0'
            toast.style.transform = 'translateX(-50%) translateY(20px)'
            setTimeout(() => toast.remove(), 350)
        }, 4000)
    }

})
