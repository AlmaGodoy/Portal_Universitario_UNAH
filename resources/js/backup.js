document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const tbody = document.getElementById('backup-tbody');

    const countElement = document.getElementById('backup-count'); // contador de tabla
    const countTopElement = document.getElementById('backup-count-top'); // total real

    const backupForm = document.getElementById('backup-generate-form');
    const generateButton = document.getElementById('backup-generate-btn');

    const testForm = document.getElementById('backup-test-form');
    const testButton = document.getElementById('backup-test-btn');

    function obtenerFilasReales() {
        if (!tbody) return [];

        return Array.from(tbody.querySelectorAll('tr')).filter((row) => {
            return !row.classList.contains('backup-empty-row') && row.id !== 'backup-empty-search-row';
        });
    }

    function obtenerFilasVisibles() {
        return obtenerFilasReales().filter((row) => row.style.display !== 'none');
    }

    function actualizarConteos() {
        const totalReales = obtenerFilasReales().length;
        const totalVisibles = obtenerFilasVisibles().length;

        // Este contador sí cambia con la búsqueda
        if (countElement) {
            countElement.textContent = totalVisibles;
        }

        // Este contador representa el total de respaldos registrados
        if (countTopElement) {
            countTopElement.textContent = totalReales;
        }
    }

    function crearFilaVaciaBusqueda() {
        if (!tbody) return null;

        let emptySearchRow = document.getElementById('backup-empty-search-row');

        if (!emptySearchRow) {
            emptySearchRow = document.createElement('tr');
            emptySearchRow.id = 'backup-empty-search-row';
            emptySearchRow.style.display = 'none';
            emptySearchRow.innerHTML = `
                <td colspan="4">
                    <div class="backup-empty">
                        <i class="fas fa-search"></i>
                        <h4>No se encontraron resultados</h4>
                        <p>No hay respaldos que coincidan con tu búsqueda actual.</p>
                    </div>
                </td>
            `;
            tbody.appendChild(emptySearchRow);
        }

        return emptySearchRow;
    }

    function mostrarEstadoVacioBusqueda() {
        const emptySearchRow = crearFilaVaciaBusqueda();
        if (!emptySearchRow) return;

        const hayResultadosVisibles = obtenerFilasVisibles().length > 0;
        const hayFilasReales = obtenerFilasReales().length > 0;

        if (hayFilasReales && !hayResultadosVisibles) {
            emptySearchRow.style.display = '';
        } else {
            emptySearchRow.style.display = 'none';
        }
    }

    function filtrarTabla() {
        if (!searchInput || !tbody) return;

        const texto = searchInput.value.toLowerCase().trim();
        const rows = obtenerFilasReales();

        rows.forEach((row) => {
            const contenido = row.textContent.toLowerCase();
            row.style.display = contenido.includes(texto) ? '' : 'none';
        });

        actualizarConteos();
        mostrarEstadoVacioBusqueda();
    }

    function inicializarBusqueda() {
        if (!searchInput) return;
        searchInput.addEventListener('input', filtrarTabla);
    }

    function inicializarBotonGenerar() {
        if (!backupForm || !generateButton) return;

        backupForm.addEventListener('submit', function () {
            generateButton.disabled = true;
            generateButton.classList.add('is-loading');

            generateButton.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <span>Generando respaldo...</span>
            `;
        });
    }

    function inicializarBotonProbar() {
        if (!testForm || !testButton) return;

        testForm.addEventListener('submit', function () {
            testButton.disabled = true;
            testButton.classList.add('is-loading');

            testButton.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <span>Probando conexión...</span>
            `;
        });
    }

    function inicializarTooltips() {
        const rows = obtenerFilasReales();

        rows.forEach((row) => {
            const fileName = row.querySelector('.file-main-text strong');

            if (fileName) {
                fileName.setAttribute('title', fileName.textContent.trim());
            }
        });
    }

    function inicializar() {
        actualizarConteos();
        mostrarEstadoVacioBusqueda();
        inicializarBusqueda();
        inicializarBotonGenerar();
        inicializarBotonProbar();
        inicializarTooltips();
    }

    inicializar();
});
