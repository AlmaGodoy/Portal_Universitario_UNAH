document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('graficaTramites');

    if (canvas && typeof Chart !== 'undefined') {
        const aprobados = parseInt(canvas.dataset.aprobados || 0);
        const rechazados = parseInt(canvas.dataset.rechazados || 0);

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ['Aprobados', 'Rechazados'],
                datasets: [{
                    label: 'Cantidad de trámites',
                    data: [aprobados, rechazados],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.85)',
                        'rgba(220, 53, 69, 0.85)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Resumen mensual de resoluciones'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    const formFiltros = document.getElementById('formFiltrosReporte');
    const tipoTramite = document.getElementById('tipo_tramite');
    const estadoResolucion = document.getElementById('estado_resolucion');
    const mesReporte = document.getElementById('mes_reporte');

    if (formFiltros) {
        [tipoTramite, estadoResolucion, mesReporte].forEach((campo) => {
            if (campo) {
                campo.addEventListener('change', function () {
                    formFiltros.submit();
                });
            }
        });
    }
});
