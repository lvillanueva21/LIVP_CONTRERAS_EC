(function (window, $) {
    'use strict';

    var AppContreras = {
        init: function () {
            this.prepareToastr();
            this.createLoadingLayer();
            this.startClock();
            this.bindAjaxLinks();
        },

        prepareToastr: function () {
            if (typeof toastr === 'undefined') {
                return;
            }

            toastr.options.closeButton = true;
            toastr.options.progressBar = true;
            toastr.options.positionClass = 'toast-top-right';
            toastr.options.timeOut = 3500;
            toastr.options.extendedTimeOut = 1200;
            toastr.options.preventDuplicates = true;
        },

        notify: function (type, message, title) {
            var cleanType = type || 'info';
            var cleanTitle = title || 'Aviso';

            if (typeof toastr !== 'undefined' && typeof toastr[cleanType] === 'function') {
                toastr[cleanType](message, cleanTitle);
                return;
            }

            console.log(cleanTitle + ': ' + message);
        },

        createLoadingLayer: function () {
            if ($('#appLoadingLayer').length > 0) {
                return;
            }

            var html = ''
                + '<div class="app-loading-layer" id="appLoadingLayer">'
                + '    <div class="app-loading-box">'
                + '        <i class="fas fa-spinner fa-spin mr-2"></i>'
                + '        Procesando'
                + '    </div>'
                + '</div>';

            $('body').append(html);
        },

        loading: function (show) {
            var layer = $('#appLoadingLayer');

            if (show) {
                layer.css('display', 'flex');
                return;
            }

            layer.hide();
        },

        startClock: function () {
            var clock = $('#appClock');

            if (clock.length === 0) {
                return;
            }

            setInterval(function () {
                var now = new Date();
                var day = String(now.getDate()).padStart(2, '0');
                var month = String(now.getMonth() + 1).padStart(2, '0');
                var year = now.getFullYear();
                var hour = String(now.getHours()).padStart(2, '0');
                var minute = String(now.getMinutes()).padStart(2, '0');

                clock.text(day + '/' + month + '/' + year + ' ' + hour + ':' + minute);
            }, 30000);
        },

        bindAjaxLinks: function () {
            $(document).on('click', '[data-app-disabled]', function (event) {
                event.preventDefault();
                AppContreras.notify('info', 'Esta opción se implementará en una fase posterior.', 'Módulo pendiente');
            });
        },

        ajax: function (options) {
            var ajaxOptions = options || {};

            AppContreras.loading(true);

            return $.ajax(ajaxOptions)
                .done(function (response) {
                    if (response && response.message) {
                        AppContreras.notify(response.ok ? 'success' : 'warning', response.message, response.ok ? 'Correcto' : 'Aviso');
                    }
                })
                .fail(function () {
                    AppContreras.notify('error', 'No se pudo completar la solicitud.', 'Error');
                })
                .always(function () {
                    AppContreras.loading(false);
                });
        }
    };

    window.AppContreras = AppContreras;

    $(function () {
        AppContreras.init();
    });
})(window, window.jQuery);