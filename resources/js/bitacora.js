document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("form-bitacora");
    if (!form) return;

    const fechaInicio = form.querySelector('input[name="fecha_inicio"]');
    const fechaFin = form.querySelector('input[name="fecha_fin"]');

    form.addEventListener("submit", function (e) {
        if (!fechaInicio || !fechaFin) return;

        const valorInicio = (fechaInicio.value || "").trim();
        const valorFin = (fechaFin.value || "").trim();

        if (!valorInicio || !valorFin) {
            alert("Debe seleccionar ambas fechas para realizar la búsqueda.");
            e.preventDefault();
            return;
        }

        if (valorInicio > valorFin) {
            alert("La fecha inicio no puede ser mayor que la fecha fin.");
            e.preventDefault();
        }
    });
});
