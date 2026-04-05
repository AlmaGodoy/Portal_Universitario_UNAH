document.addEventListener('DOMContentLoaded', function () {
    function iniciarGrafica() {
        const canvas = document.getElementById('graficaTramites');

        if (!canvas) return;
        if (typeof window.Chart === 'undefined') {
            setTimeout(iniciarGrafica, 300);
            return;
        }

        const aprobados = parseInt(canvas.dataset.aprobados || 0, 10);
        const rechazados = parseInt(canvas.dataset.rechazados || 0, 10);
        const pendientes = parseInt(canvas.dataset.pendientes || 0, 10);
        const revision = parseInt(canvas.dataset.revision || 0, 10);

        const ctx = canvas.getContext('2d');

        const gradienteAprobado = ctx.createLinearGradient(0, 0, 0, 320);
        gradienteAprobado.addColorStop(0, 'rgba(34, 197, 94, 0.95)');
        gradienteAprobado.addColorStop(1, 'rgba(22, 163, 74, 0.68)');

        const gradienteRechazado = ctx.createLinearGradient(0, 0, 0, 320);
        gradienteRechazado.addColorStop(0, 'rgba(239, 68, 68, 0.95)');
        gradienteRechazado.addColorStop(1, 'rgba(220, 38, 38, 0.68)');

        const gradientePendiente = ctx.createLinearGradient(0, 0, 0, 320);
        gradientePendiente.addColorStop(0, 'rgba(245, 158, 11, 0.95)');
        gradientePendiente.addColorStop(1, 'rgba(217, 119, 6, 0.68)');

        const gradienteRevision = ctx.createLinearGradient(0, 0, 0, 320);
        gradienteRevision.addColorStop(0, 'rgba(59, 130, 246, 0.95)');
        gradienteRevision.addColorStop(1, 'rgba(29, 78, 216, 0.68)');

        const labelsPlugin = {
            id: 'labelsPlugin',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;

                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);

                    meta.data.forEach((bar, index) => {
                        const value = dataset.data[index];

                        ctx.save();
                        ctx.font = '700 13px Arial';
                        ctx.fillStyle = '#1e293b';
                        ctx.textAlign = 'center';
                        ctx.fillText(value, bar.x, bar.y - 10);
                        ctx.restore();
                    });
                });
            }
        };

        new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Aprobados', 'Rechazados', 'Pendientes', 'Revisión'],
                datasets: [{
                    label: 'Cantidad de trámites',
                    data: [aprobados, rechazados, pendientes, revision],
                    backgroundColor: [
                        gradienteAprobado,
                        gradienteRechazado,
                        gradientePendiente,
                        gradienteRevision
                    ],
                    borderColor: [
                        '#15803d',
                        '#b91c1c',
                        '#b45309',
                        '#1d4ed8'
                    ],
                    borderWidth: 2,
                    borderRadius: 14,
                    borderSkipped: false,
                    maxBarThickness: 120,
                    hoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 26,
                        right: 16,
                        bottom: 8,
                        left: 8
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Distribución de trámites según su estado',
                        color: '#475569',
                        font: {
                            size: 16,
                            weight: '600'
                        },
                        padding: {
                            bottom: 14
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return ` ${context.raw} trámite(s)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#475569',
                            font: {
                                size: 13,
                                weight: '600'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1,
                            color: '#64748b'
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                            drawBorder: false
                        }
                    }
                }
            },
            plugins: [labelsPlugin]
        });
    }

    iniciarGrafica();
});
