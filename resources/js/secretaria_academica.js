document.addEventListener('DOMContentLoaded', function() {
    console.log("PumaGestión: Dashboard de Empleado cargado.");

    const searchInput = document.getElementById('top-search');

    // 1. Lógica de Búsqueda (Filtro visual por ahora)
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            let query = e.target.value.toLowerCase();
            console.log("Filtrando expedientes por: " + query);

            // Aquí podrás llamar a tu API en el futuro:
            // fetch(`/api/empleados/buscar?q=${query}`).then(...)
        });
    }

    // 2. Animación de entrada para las Info Boxes
    const cards = document.querySelectorAll('.info-box-custom');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });

    // 3. Simulación de notificaciones
    // Se puede conectar a la ruta api.empleados.notificaciones definida en tus rutas
    function chequearNotificaciones() {
        // Lógica para el punto rojo en el avatar si hay trámites pendientes
    }
});
