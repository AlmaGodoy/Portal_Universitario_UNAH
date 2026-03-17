document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-confirm-delete').forEach(form => {
        form.addEventListener('submit', function (e) {
            const mensaje = this.dataset.confirm || '¿Seguro que deseas eliminar este registro?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.js-confirm-submit').forEach(form => {
        form.addEventListener('submit', function (e) {
            const mensaje = this.dataset.confirm || '¿Deseas guardar los cambios?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 4000);
});
