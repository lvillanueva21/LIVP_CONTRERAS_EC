(function (window, $) {
    'use strict';

    var AppUI = {
        init: function () {
            this.prepareToastr();
            this.bindStaticModals();
            this.initCharacterCounters();
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
            toastr.options.newestOnTop = true;
        },

        notify: function (type, message, title) {
            var cleanType = type || 'info';
            var cleanTitle = title || 'Aviso';
            var cleanMessage = message || '';

            if (typeof toastr !== 'undefined' && typeof toastr[cleanType] === 'function') {
                toastr[cleanType](cleanMessage, cleanTitle);
                return;
            }

            if (window.console && window.console.log) {
                window.console.log(cleanTitle + ': ' + cleanMessage);
            }
        },

        success: function (message, title) {
            this.notify('success', message, title || 'Correcto');
        },

        info: function (message, title) {
            this.notify('info', message, title || 'Información');
        },

        warning: function (message, title) {
            this.notify('warning', message, title || 'Aviso');
        },

        error: function (message, title) {
            this.notify('error', message, title || 'Error');
        },

        loading: function (show) {
            if (window.AppContreras && typeof window.AppContreras.loading === 'function') {
                window.AppContreras.loading(show);
                return;
            }

            var layer = $('#appLoadingLayer');

            if (layer.length === 0) {
                $('body').append(
                    '<div class="app-loading-layer" id="appLoadingLayer">' +
                    '    <div class="app-loading-box">' +
                    '        <i class="fas fa-spinner fa-spin mr-2"></i>' +
                    '        Procesando' +
                    '    </div>' +
                    '</div>'
                );
                layer = $('#appLoadingLayer');
            }

            if (show) {
                layer.css('display', 'flex');
                return;
            }

            layer.hide();
        },

        bindStaticModals: function () {
            $(document).on('show.bs.modal', '.modal', function () {
                var modalInstance = $(this).data('bs.modal');

                if (modalInstance && modalInstance._config) {
                    modalInstance._config.backdrop = 'static';
                    modalInstance._config.keyboard = false;
                }
            });
        },

        openModal: function (selector) {
            var modal = $(selector);

            if (modal.length === 0) {
                return;
            }

            modal.modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
        },

        closeModal: function (selector) {
            var modal = $(selector);

            if (modal.length === 0) {
                return;
            }

            modal.modal('hide');
        },

        escapeHtml: function (value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        badge: function (text, type) {
            var cleanType = type || 'secondary';
            return '<span class="badge badge-' + cleanType + '">' + this.escapeHtml(text) + '</span>';
        },

        stateBadge: function (state) {
            var cleanState = String(state || '').toLowerCase();
            var type = 'secondary';

            if (cleanState === 'activo' || cleanState === 'pagado' || cleanState === 'emitido' || cleanState === 'convertida') {
                type = 'success';
            }

            if (cleanState === 'pendiente' || cleanState === 'en proforma' || cleanState === 'parcial' || cleanState === 'borrador') {
                type = 'warning';
            }

            if (cleanState === 'anulado' || cleanState === 'inactivo' || cleanState === 'vencido') {
                type = 'danger';
            }

            if (cleanState === 'emitida') {
                type = 'info';
            }

            return this.badge(state, type);
        },

        emptyState: function (title, text, icon) {
            var cleanTitle = title || 'Sin registros';
            var cleanText = text || 'No hay información para mostrar.';
            var cleanIcon = icon || 'fas fa-inbox';

            return '' +
                '<div class="app-empty-state">' +
                '    <div class="app-empty-state-icon">' +
                '        <i class="' + cleanIcon + '"></i>' +
                '    </div>' +
                '    <h5>' + this.escapeHtml(cleanTitle) + '</h5>' +
                '    <p>' + this.escapeHtml(cleanText) + '</p>' +
                '</div>';
        },

        actionButton: function (options) {
            var config = options || {};
            var type = config.type || 'secondary';
            var icon = config.icon || 'fas fa-circle';
            var text = config.text || '';
            var title = config.title || text;
            var attrs = config.attrs || '';
            var size = config.size || 'sm';

            return '' +
                '<button type="button" class="btn btn-' + type + ' btn-' + size + ' app-btn-action" title="' + this.escapeHtml(title) + '" ' + attrs + '>' +
                '    <i class="' + icon + '"></i>' +
                (text !== '' ? ' <span>' + this.escapeHtml(text) + '</span>' : '') +
                '</button>';
        },

        initCharacterCounters: function () {
            var selector = 'textarea[maxlength], input[data-char-counter="true"][maxlength], textarea[data-char-counter="true"][maxlength]';

            $(selector).each(function () {
                var input = $(this);

                if (input.data('char-counter-ready')) {
                    return;
                }

                var max = parseInt(input.attr('maxlength'), 10);

                if (!max || max <= 0) {
                    return;
                }

                var counter = $('<small class="form-text text-muted app-char-counter"></small>');
                input.after(counter);
                input.data('char-counter-ready', true);

                var updateCounter = function () {
                    var current = String(input.val() || '').length;
                    counter.text(current + ' / ' + max);
                };

                input.on('input keyup change', updateCounter);
                updateCounter();
            });
        },

        refresh: function () {
            this.initCharacterCounters();
        }
    };

    window.AppUI = AppUI;

    $(function () {
        AppUI.init();
    });
})(window, window.jQuery);