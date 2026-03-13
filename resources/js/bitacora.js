document.addEventListener("DOMContentLoaded", function () {

    const fechaInicial = document.querySelector('input[name="fecha_inicial"]');
    const fechaFinal = document.querySelector('input[name="fecha_final"]');

    // Validación simple de fechas
    document.querySelector("form").addEventListener("submit", function (e) {

        if (!fechaInicial.value || !fechaFinal.value) {
            alert("Debe seleccionar ambas fechas para realizar la búsqueda.");
            e.preventDefault();
            return;
        }

        if (fechaInicial.value > fechaFinal.value) {
            alert("La fecha inicial no puede ser mayor que la fecha final.");
            e.preventDefault();
        }

    });

});
