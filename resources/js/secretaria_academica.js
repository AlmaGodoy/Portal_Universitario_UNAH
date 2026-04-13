document.addEventListener('DOMContentLoaded', function () {
    /* =========================================================
       TOPBAR: notificaciones, mensajes y usuario
    ========================================================= */
    const btnNotif = document.getElementById('btnNotif');
    const dropNotif = document.getElementById('dropNotif');

    const btnMsg = document.getElementById('btnMsg');
    const dropMsg = document.getElementById('dropMsg');

    const btnUser = document.getElementById('btnUser');
    const dropUser = document.getElementById('dropUser');

    const dropdowns = [dropNotif, dropMsg, dropUser].filter(Boolean);

    function closeAllDropdowns() {
        dropdowns.forEach((drop) => {
            drop.style.display = 'none';
            drop.classList.remove('is-open');
        });
    }

    function toggleDropdown(drop) {
        if (!drop) return;

        const isVisible = drop.style.display === 'block';

        closeAllDropdowns();

        if (!isVisible) {
            drop.style.display = 'block';
            drop.classList.add('is-open');
        }
    }

    if (btnNotif && dropNotif) {
        dropNotif.style.display = 'none';

        btnNotif.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown(dropNotif);
        });

        dropNotif.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (btnMsg && dropMsg) {
        dropMsg.style.display = 'none';

        btnMsg.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown(dropMsg);
        });

        dropMsg.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (btnUser && dropUser) {
        dropUser.style.display = 'none';

        btnUser.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown(dropUser);
        });

        dropUser.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    document.addEventListener('click', function () {
        closeAllDropdowns();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    /* =========================================================
       GRÁFICAS
    ========================================================= */
    const data = window.secretariaAcademicaCharts || {};

    const estadosLabels = data.estadosLabels || [];
    const estadosValores = data.estadosValores || [];

    const carrerasLabels = data.carrerasLabels || [];
    const carrerasValores = data.carrerasValores || [];

    const clasesLabels = data.clasesLabels || [];
    const clasesValores = data.clasesValores || [];

    const canvasEstados = document.getElementById('graficaEstadosGlobales');
    const canvasCarreras = document.getElementById('graficaCarrerasGlobales');
    const canvasClases = document.getElementById('graficaClasesCanceladas');

    if (canvasEstados) {
        new Chart(canvasEstados, {
            type: 'doughnut',
            data: {
                labels: estadosLabels,
                datasets: [{
                    data: estadosValores,
                    backgroundColor: ['#0f4c97', '#f4b400', '#16a1b8', '#d93025'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    if (canvasCarreras) {
        new Chart(canvasCarreras, {
            type: 'bar',
            data: {
                labels: carrerasLabels,
                datasets: [{
                    label: 'Trámites',
                    data: carrerasValores,
                    backgroundColor: ['#003c71', '#1d5fbf', '#17a2b8', '#ffc107', '#6f42c1'],
                    borderRadius: 10,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
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

    if (canvasClases) {
        new Chart(canvasClases, {
            type: 'bar',
            data: {
                labels: clasesLabels,
                datasets: [{
                    label: 'Cancelaciones',
                    data: clasesValores,
                    backgroundColor: ['#d93025', '#f4b400', '#0f4c97'],
                    borderRadius: 12,
                    maxBarThickness: 40
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
