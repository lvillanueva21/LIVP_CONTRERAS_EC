(function (window, $) {
    'use strict';

    var Auditoria = {
        ajaxUrl: 'modules/auditoria/ajax.php',

        init: function () {
            $('#formAuditoriaFiltros').on('submit', function (event) {
                event.preventDefault();
                Auditoria.listar();
            });

            $('#btnLimpiarAuditoria').on('click', function () {
                $('#formAuditoriaFiltros')[0].reset();
                Auditoria.listar();
            });
        },

        listar: function () {
            AppAjax.post(this.ajaxUrl, $('#formAuditoriaFiltros').serialize(), {
                showSuccess: false,
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    $('#auditoriaTablaContainer').html(response.html);

                    if (response.kpis) {
                        $('#audKpiTotal').text(response.kpis.total);
                        $('#audKpiHoy').text(response.kpis.hoy);
                        $('#audKpiDescargas').text(response.kpis.descargas);
                        $('#audKpiCambios').text(response.kpis.cambios);
                    }

                    AppTablas.refresh();
                    AppUI.refresh();
                }
            });
        }
    };

    window.Auditoria = Auditoria;

    $(function () {
        Auditoria.init();
    });
})(window, window.jQuery);