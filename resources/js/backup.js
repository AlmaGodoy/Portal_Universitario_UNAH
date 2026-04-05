document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const tbody = document.getElementById('backup-tbody');
    const countElement = document.getElementById('backup-count');
    const countTopElement = document.getElementById('backup-count-top');
    const backupForm = document.getElementById('backup-generate-form');
    const generateButton = document.getElementById('backup-generate-btn');

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
        const totalVisible = obtenerFilasVisibles().length;

        if (countElement) {
            countElement.textContent = totalVisible;
        }

        if (countTopElement) {
            countTopElement.textContent = totalVisible;
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

        const hayResultados = obtenerFilasVisibles().length > 0;
        const hayFilasReales = obtenerFilasReales().length > 0;

        if (hayFilasReales && !hayResultados) {
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
        inicializarTooltips();
    }

    inicializar();
});