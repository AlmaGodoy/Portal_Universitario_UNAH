document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CONFIRMACIÓN PARA DESACTIVAR
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.js-confirm-delete').forEach(form => {

        form.addEventListener('submit', function (e) {

            const mensaje =
                this.dataset.confirm ||
                '¿Seguro que deseas desactivar este registro?';

            if (!confirm(mensaje)) {
                e.preventDefault();
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | CONFIRMACIÓN PARA GUARDAR / ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.js-confirm-submit').forEach(form => {

        form.addEventListener('submit', function (e) {

            const mensaje =
                this.dataset.confirm ||
                '¿Deseas guardar los cambios?';

            if (!confirm(mensaje)) {
                e.preventDefault();
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | OCULTAR ALERTAS AUTOMÁTICAMENTE
    |--------------------------------------------------------------------------
    */

    const alerts = document.querySelectorAll('.security-page .alert');

    if (alerts.length > 0) {

        setTimeout(() => {

            alerts.forEach(alert => {

                alert.style.transition =
                    'opacity 0.5s ease, transform 0.5s ease';

                alert.style.opacity = '0';

                alert.style.transform =
                    'translateY(-6px)';

                setTimeout(() => {
                    alert.remove();
                }, 500);

            });

        }, 4000);

    }

});
