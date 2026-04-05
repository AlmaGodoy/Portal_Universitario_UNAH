/*valida que fecha_inicial no sea mayor que fecha_final
filtra visualmente en la tabla mientras escribe
filtra por acción sin recargar
muestra contador de registros visibles
muestra mensaje si no hay coincidencias en la página actual
cambia el texto del botón a “Filtrando...”
*/


document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action*="auditoria/estudiantes"]') || document.querySelector('form');
    const fechaInicial = document.querySelector('input[name="fecha_inicial"]');
    const fechaFinal = document.querySelector('input[name="fecha_final"]');
    const accion = document.querySelector('select[name="accion"]');
    const texto = document.querySelector('input[name="texto"]');
    const btnFiltrar = form?.querySelector('button[type="submit"]');
    const tabla = document.querySelector('.auditoria-table');
    const tbody = tabla?.querySelector('tbody');

    if (!form || !tabla || !tbody) return;

    let alerta = null;
    let contador = null;

    function normalizar(valor) {
        return (valor || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function crearAlerta() {
        if (alerta) return alerta;

        alerta = document.createElement('div');
        alerta.className = 'alert alert-danger d-none';
        alerta.style.marginBottom = '16px';

        const cardBody = form.closest('.card-body');
        if (cardBody) {
            cardBody.insertBefore(alerta, form);
        }

        return alerta;
    }

    function mostrarAlerta(mensaje, tipo = 'danger') {
        const box = crearAlerta();
        box.className = `alert alert-${tipo}`;
        box.textContent = mensaje;
        box.classList.remove('d-none');
    }

    function ocultarAlerta() {
        if (!alerta) return;
        alerta.classList.add('d-none');
        alerta.textContent = '';
    }

    function validarFechas() {
        ocultarAlerta();

        const inicio = fechaInicial?.value || '';
        const fin = fechaFinal?.value || '';

        if (inicio && fin && inicio > fin) {
            mostrarAlerta('La fecha inicial no puede ser mayor que la fecha final.');
            fechaFinal.focus();
            return false;
        }

        return true;
    }

    function actualizarMinMaxFechas() {
        if (!fechaInicial || !fechaFinal) return;

        if (fechaInicial.value) {
            fechaFinal.min = fechaInicial.value;
        } else {
            fechaFinal.removeAttribute('min');
        }

        if (fechaFinal.value) {
            fechaInicial.max = fechaFinal.value;
        } else {
            fechaInicial.removeAttribute('max');
        }
    }

    function obtenerFilasReales() {
        return Array.from(tbody.querySelectorAll('tr')).filter(row => {
            const td = row.querySelector('td[colspan]');
            return !td;
        });
    }

    function crearContador() {
        if (contador) return contador;

        contador = document.createElement('div');
        contador.className = 'mb-3 text-muted fw-semibold';
        contador.id = 'contadorAuditoriaEstudiantes';

        const tableResponsive = tabla.closest('.table-responsive');
        const cardBody = tabla.closest('.card-body');

        if (cardBody && tableResponsive) {
            cardBody.insertBefore(contador, tableResponsive);
        }

        return contador;
    }

    function actualizarContador(visibles, total) {
        const box = crearContador();
        box.textContent = `Mostrando ${visibles} de ${total} registros en esta página.`;
    }

    function crearFilaSinResultados() {
        let fila = document.getElementById('sinResultadosAuditoria');

        if (!fila) {
            fila = document.createElement('tr');
            fila.id = 'sinResultadosAuditoria';
            fila.innerHTML = `
                <td colspan="4" class="text-center text-muted">
                    No hay coincidencias con los filtros aplicados en esta página.
                </td>
            `;
            fila.style.display = 'none';
            tbody.appendChild(fila);
        }

        return fila;
    }

    function obtenerTextoAccionSeleccionada() {
        const value = accion?.value || '';
        return normalizar(value.replaceAll('_', ' '));
    }

    function aplicarFiltroVisual() {
        const filas = obtenerFilasReales();
        const filtroTexto = normalizar(texto?.value || '');
        const filtroAccion = obtenerTextoAccionSeleccionada();

        let visibles = 0;

        filas.forEach(row => {
            const contenido = normalizar(row.innerText);
            const badge = row.querySelector('.badge');
            const textoBadge = normalizar(badge?.innerText || '');

            const coincideTexto = !filtroTexto || contenido.includes(filtroTexto);
            const coincideAccion = !filtroAccion || textoBadge.includes(filtroAccion);

            if (coincideTexto && coincideAccion) {
                row.style.display = '';
                visibles++;
            } else {
                row.style.display = 'none';
            }
        });

        const filaSinResultados = crearFilaSinResultados();
        filaSinResultados.style.display = visibles === 0 ? '' : 'none';

        actualizarContador(visibles, filas.length);
    }

    function prepararBotonSubmit() {
        if (!btnFiltrar) return;

        btnFiltrar.disabled = true;
        btnFiltrar.dataset.originalText = btnFiltrar.textContent;
        btnFiltrar.textContent = 'Filtrando...';
    }

    function restaurarBotonSubmit() {
        if (!btnFiltrar) return;

        btnFiltrar.disabled = false;
        if (btnFiltrar.dataset.originalText) {
            btnFiltrar.textContent = btnFiltrar.dataset.originalText;
        }
    }

    function limpiarTextoBusqueda() {
        if (!texto) return;
        texto.value = texto.value.trim().replace(/\s+/g, ' ');
    }

    function resaltarFilasPorAccion() {
        const filas = obtenerFilasReales();

        filas.forEach(row => {
            row.classList.remove(
                'fila-registro',
                'fila-actualizacion',
                'fila-eliminacion',
                'fila-estado'
            );

            const badge = row.querySelector('.badge');
            const accionTexto = normalizar(badge?.innerText || '');

            if (accionTexto.includes('registro')) {
                row.classList.add('fila-registro');
            } else if (accionTexto.includes('actualizacion')) {
                row.classList.add('fila-actualizacion');
            } else if (accionTexto.includes('eliminacion')) {
                row.classList.add('fila-eliminacion');
            } else if (accionTexto.includes('estado')) {
                row.classList.add('fila-estado');
            }
        });
    }

    function enlazarEventos() {
        fechaInicial?.addEventListener('change', () => {
            actualizarMinMaxFechas();
            validarFechas();
        });

        fechaFinal?.addEventListener('change', () => {
            actualizarMinMaxFechas();
            validarFechas();
        });

        accion?.addEventListener('change', aplicarFiltroVisual);
        texto?.addEventListener('input', aplicarFiltroVisual);

        texto?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();

                limpiarTextoBusqueda();

                if (!validarFechas()) return;

                prepararBotonSubmit();
                form.submit();
            }
        });

        form.addEventListener('submit', (e) => {
            limpiarTextoBusqueda();

            if (!validarFechas()) {
                e.preventDefault();
                restaurarBotonSubmit();
                return;
            }

            prepararBotonSubmit();
        });

        window.addEventListener('pageshow', () => {
            restaurarBotonSubmit();
        });
    }

    function init() {
        actualizarMinMaxFechas();
        resaltarFilasPorAccion();
        aplicarFiltroVisual();
        enlazarEventos();
    }

    init();
});
