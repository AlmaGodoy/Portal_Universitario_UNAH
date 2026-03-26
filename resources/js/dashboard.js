document.addEventListener('DOMContentLoaded', function () {
    const commonGrid = { color: 'rgba(30, 56, 102, 0.10)', drawBorder: false };
    const commonTicks = { color: '#58657d', font: { size: 11 } };

    // ─── SIDEBAR TOGGLE (desktop) — bypass total de AdminLTE ────────────────────
    (function () {
        function applyCollapse(collapsed) {
            if (collapsed) {
                document.body.classList.add('sidebar-collapse');
            } else {
                document.body.classList.remove('sidebar-collapse');
            }
        }

        if (window.innerWidth >= 992) {
            applyCollapse(localStorage.getItem('pgSidebarCollapsed') === '1');
        }

        function bindToggle() {
            const btn = document.getElementById('sidebarToggleBtn');
            if (!btn) return;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const collapsed = !document.body.classList.contains('sidebar-collapse');
                applyCollapse(collapsed);
                localStorage.setItem('pgSidebarCollapsed', collapsed ? '1' : '0');
                setTimeout(() => {
                    ajustarSidebarScroll();
                    window.dispatchEvent(new Event('resize'));
                }, 320);
            }, true); // capture:true corre antes que AdminLTE
        }

        bindToggle();
        window.addEventListener('load', bindToggle);
    })();
    // ────────────────────────────────────────────────────────────────────────────

    ajustarSidebarScroll();
    fetchDashboardData();

    async function fetchDashboardData() {
        try {
            const response = await fetch('/dashboard/data');
            const data = await response.json();

            if (data.status === 'success') {
                updateStatsCards(data.cards);
                renderMainCharts(data.charts);
                updateRecentActivity(data.recentActivity);
            }
        } catch (error) {
            console.error('Error cargando el dashboard:', error);
        }
    }

    function updateStatsCards(cards) {
        const grid = document.querySelector('.stats-grid');
        if (!grid || !cards) return;

        const estNum = grid.querySelector('.stat-gold .stat-number');
        if (estNum && cards.estudiantes) {
            estNum.innerText = Number(cards.estudiantes.total || 0).toLocaleString();
        }

        const docNum = grid.querySelector('.stat-blue .stat-number');
        if (docNum && cards.docentes) {
            docNum.innerText = Number(cards.docentes.total || 0).toLocaleString();
        }

        const traNum = grid.querySelector('.stat-green .stat-number');
        if (traNum && cards.tramites) {
            traNum.innerText = Number(cards.tramites.total || 0).toLocaleString();
        }
    }

    function renderMainCharts(charts) {
        if (!charts) return;

        const incomeCtx = document.getElementById('incomeExpensesChart');
        if (incomeCtx && charts.distribucionTramites) {
            new Chart(incomeCtx, {
                type: 'bar',
                data: {
                    labels: charts.distribucionTramites.map(item => item.tipo),
                    datasets: [{
                        label: 'Total de Trámites',
                        data: charts.distribucionTramites.map(item => item.total),
                        backgroundColor: '#1c4ea0',
                        borderRadius: 5,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: commonTicks },
                        y: { beginAtZero: true, grid: commonGrid, ticks: commonTicks }
                    }
                }
            });
        }

        const distCtx = document.getElementById('distributionChart');
        if (distCtx && charts.distribucionTramites) {
            new Chart(distCtx, {
                type: 'pie',
                data: {
                    labels: charts.distribucionTramites.map(item => item.tipo),
                    datasets: [{
                        data: charts.distribucionTramites.map(item => item.total),
                        backgroundColor: ['#163f8f', '#4aa3ff', '#f0c11e', '#78b94e', '#295ab2', '#7db6ff'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, font: { size: 10 } }
                        }
                    }
                }
            });
        }

        const enrollCtx = document.getElementById('enrollmentChart');
        if (enrollCtx) {
            new Chart(enrollCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Tendencia General',
                        data: [400, 450, 520, 480, 600, 700],
                        borderColor: '#193f96',
                        backgroundColor: 'rgba(25, 63, 150, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: commonGrid,
                            ticks: commonTicks
                        },
                        x: {
                            ticks: commonTicks
                        }
                    }
                }
            });
        }
    }

    function updateRecentActivity(activities) {
        const tbody = document.querySelector('.custom-table tbody');
        if (!tbody || !activities) return;

        tbody.innerHTML = '';

        activities.forEach(item => {
            const statusClass = getStatusClass(item.estado || '');
            const row = `
                <tr>
                    <td><i class="fas fa-file-signature type-icon"></i></td>
                    <td>${item.tipo ?? ''}</td>
                    <td><span class="badge-status ${statusClass}">${item.estado ?? ''}</span></td>
                    <td>${item.created_at ? new Date(item.created_at).toLocaleDateString('es-HN') : ''}</td>
                    <td>${item.user ? item.user.name : 'Sistema'}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function getStatusClass(status) {
        const s = String(status).toLowerCase();
        if (s.includes('aprobado')) return 'approved';
        if (s.includes('pendiente')) return 'pending';
        return 'review';
    }

    function ajustarSidebarScroll() {
        const aside = document.querySelector('.main-sidebar');
        const brand = document.querySelector('.main-sidebar .brand-link');
        const scrollArea = document.getElementById('dashboardSidebarScroll');

        if (!aside || !brand || !scrollArea) return;

        const viewportHeight = window.innerHeight;
        const brandHeight = brand.offsetHeight;
        const disponible = viewportHeight - brandHeight;

        scrollArea.style.height = `${Math.max(disponible, 120)}px`;
        scrollArea.style.maxHeight = `${Math.max(disponible, 120)}px`;
    }

    window.addEventListener('resize', ajustarSidebarScroll);
    window.addEventListener('load', ajustarSidebarScroll);

    // AdminLTE pushmenu (móvil)
    const pushMenu = document.querySelector('[data-widget="pushmenu"]');
    if (pushMenu) {
        pushMenu.addEventListener('click', function () {
            setTimeout(() => {
                ajustarSidebarScroll();
                window.dispatchEvent(new Event('resize'));
            }, 350);
        });
    }

    const bodyObserver = new MutationObserver(() => {
        setTimeout(ajustarSidebarScroll, 50);
    });

    bodyObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
});
