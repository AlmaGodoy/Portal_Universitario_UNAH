document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('backupPage');

    if (!page) {
        return;
    }

    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('backup-tbody');
    const generateForm = document.getElementById('backup-generate-form');
    const generateBtn = document.getElementById('backup-generate-btn');
    const testForm = document.getElementById('backup-test-form');
    const testBtn = document.getElementById('backup-test-btn');

    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function () {
            const search = searchInput.value.trim().toLowerCase();
            const rows = tableBody.querySelectorAll('tr:not(.backup-empty-row)');

            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });
    }

    if (generateForm && generateBtn) {
        generateForm.addEventListener('submit', function () {
            generateBtn.disabled = true;
            generateBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <span>Generando respaldo...</span>
            `;
        });
    }

    if (testForm && testBtn) {
        testForm.addEventListener('submit', function () {
            testBtn.disabled = true;
            testBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                Probando conexión...
            `;
        });
    }
});