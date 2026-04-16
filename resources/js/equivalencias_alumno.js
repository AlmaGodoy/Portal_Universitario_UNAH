function renderAsignaturas(items) {
    if (!tablaAsignaturasPlanViejo) return;

    console.log('Asignaturas recibidas desde API:', items);

    if (!Array.isArray(items) || items.length === 0) {
        tablaAsignaturasPlanViejo.innerHTML = `
            <tr>
                <td colspan="4" class="eq-empty-row">No se encontraron asignaturas para este plan.</td>
            </tr>
        `;
        return;
    }

    tablaAsignaturasPlanViejo.innerHTML = items.map((item, index) => {
        const codigo = String(item.codigo_asignatura || '').trim();
        const nombre = String(item.nombre_asignatura || '').trim();
        const uv = item.uv ?? item.unidades_valorativas ?? '-';

        return `
            <tr>
                <td>
                    <input
                        type="checkbox"
                        class="eq-check-asignatura"
                        id="asignatura_${index}"
                        data-codigo="${escapeHtml(codigo)}"
                    >
                </td>
                <td>${escapeHtml(codigo)}</td>
                <td>${escapeHtml(nombre)}</td>
                <td>${escapeHtml(uv)}</td>
            </tr>
        `;
    }).join('');
}