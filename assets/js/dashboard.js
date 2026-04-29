(function (window, $) {
    'use strict';

    var DashboardContreras = {
        ajaxUrl: 'modules/inicio/ajax.php',
        chart: null,

        init: function () {
            if ($('#dashboardIngresosChart').length) {
                this.cargarGrafico();
            }

            $('#btnActualizarDashboard').on('click', function () {
                DashboardContreras.cargarGrafico();
                DashboardContreras.actualizarAvisos();
            });
        },

        cargarGrafico: function () {
            if (!window.Chart) {
                AppUI.warning('No se cargó Chart.js local.');
                return;
            }

            AppAjax.get(this.ajaxUrl, {
                action: 'grafico_ingresos'
            }, {
                showSuccess: false,
                onSuccess: function (response) {
                    if (!response || !response.ok || !response.chart) {
                        return;
                    }

                    DashboardContreras.renderGrafico(response.chart);
                }
            });
        },

        renderGrafico: function (chartData) {
            var canvas = document.getElementById('dashboardIngresosChart');

            if (!canvas) {
                return;
            }

            var ctx = canvas.getContext('2d');

            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        label: 'Ingresos',
                        data: chartData.data || [],
                        fill: false,
                        lineTension: 0.25,
                        pointRadius: 4,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true
                    },
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem) {
                                var value = parseFloat(tooltipItem.yLabel || 0);
                                return 'Ingresos: S/ ' + value.toFixed(2);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    return 'S/ ' + parseFloat(value || 0).toFixed(0);
                                }
                            }
                        }]
                    }
                }
            });
        },

        actualizarAvisos: function () {
            AppAjax.get(this.ajaxUrl, {
                action: 'avisos'
            }, {
                showSuccess: false,
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    if (response.avisos_html !== undefined) {
                        $('#dashboardAvisosContainer').html(response.avisos_html);
                    }

                    if (response.vencidos_html !== undefined) {
                        $('#dashboardVencidosContainer').html(response.vencidos_html);
                    }

                    if (response.resumen) {
                        $('#dashIngresosHoy').text(DashboardContreras.money(response.resumen.ingresos_hoy));
                        $('#dashIngresosMes').text(DashboardContreras.money(response.resumen.ingresos_mes));
                        $('#dashPendientes').text(DashboardContreras.money(response.resumen.pendientes));
                        $('#dashProformasEmitidas').text(response.resumen.proformas_emitidas);
                        $('#dashRecibosEmitidos').text(response.resumen.recibos_emitidos);
                        $('#dashProximosAvisos').text(response.resumen.proximos_avisos);
                        $('#dashVencidos').text(response.resumen.vencidos);
                    }
                }
            });
        },

        money: function (value) {
            value = parseFloat(value || 0);
            return 'S/ ' + value.toFixed(2);
        }
    };

    window.DashboardContreras = DashboardContreras;

    $(function () {
        DashboardContreras.init();
    });
})(window, window.jQuery);