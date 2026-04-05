document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('top-search');

    // 1. Lógica de búsqueda (Simulada para el PM)
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            let valor = e.target.value.toLowerCase();
            console.log("Buscando trámite o estudiante: ", valor);
            // Aquí puedes agregar la lógica para filtrar tablas o hacer peticiones AJAX
        });
    }

    // 2. Efecto visual al entrar
    const boxes = document.querySelectorAll('.info-box-custom');
    boxes.forEach((box, index) => {
        setTimeout(() => {
            box.style.opacity = '1';
            box.style.transform = 'translateY(0)';
        }, index * 150);
    });
});
