(function (window, $) {
    'use strict';

    var AppAjax = {
        defaults: {
            method: 'POST',
            dataType: 'json',
            showLoading: true,
            notify: true
        },

        init: function () {
            this.bindForms();
            this.bindLinks();
        },

        request: function (options) {
            var config = $.extend({}, this.defaults, options || {});

            if (!config.url) {
                this.notifyError('No se definió la URL de la solicitud.');
                return $.Deferred().reject().promise();
            }

            if (config.showLoading) {
                this.loading(true);
            }

            return $.ajax({
                url: config.url,
                method: config.method,
                type: config.method,
                data: config.data || {},
                dataType: config.dataType,
                processData: config.processData !== undefined ? config.processData : true,
                contentType: config.contentType !== undefined ? config.contentType : 'application/x-www-form-urlencoded; charset=UTF-8',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function (response) {
                AppAjax.handleSuccess(response, config);
            }).fail(function (xhr) {
                AppAjax.handleError(xhr, config);
            }).always(function () {
                if (config.showLoading) {
                    AppAjax.loading(false);
                }
            });
        },

        get: function (url, data, options) {
            return this.request($.extend({}, options || {}, {
                url: url,
                method: 'GET',
                data: data || {}
            }));
        },

        post: function (url, data, options) {
            return this.request($.extend({}, options || {}, {
                url: url,
                method: 'POST',
                data: data || {}
            }));
        },

sendForm: function (form, options) {
    var $form = $(form);
    var config = options || {};
    var method = String($form.attr('method') || config.method || 'POST').toUpperCase();
    var url = config.url || $form.attr('action') || window.location.href;
    var hasFiles = $form.find('input[type="file"]').length > 0;
    var data = hasFiles ? new FormData($form[0]) : $form.serialize();

    return this.request($.extend({}, config, {
        url: url,
        method: method,
        data: data,
        processData: !hasFiles,
        contentType: hasFiles ? false : 'application/x-www-form-urlencoded; charset=UTF-8'
    }));
},

        handleSuccess: function (response, config) {
            if (config.notify && response && response.message) {
                if (response.ok) {
                    this.notifySuccess(response.message);
                } else {
                    this.notifyWarning(response.message);
                }
            }

            if (typeof config.onSuccess === 'function') {
                config.onSuccess(response);
            }
        },

        handleError: function (xhr, config) {
            var message = 'No se pudo completar la solicitud.';

            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr && xhr.responseText) {
                try {
                    var parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) {
                        message = parsed.message;
                    }
                } catch (error) {
                    message = 'Respuesta inválida del servidor.';
                }
            }

            if (config.notify) {
                this.notifyError(message);
            }

            if (typeof config.onError === 'function') {
                config.onError(xhr, message);
            }
        },

        bindForms: function () {
            $(document).on('submit', 'form[data-ajax-form="true"]', function (event) {
                event.preventDefault();

                var form = $(this);
                var target = form.attr('data-ajax-target') || '';
                var resetOnSuccess = form.attr('data-reset-on-success') === 'true';

                AppAjax.sendForm(form, {
                    onSuccess: function (response) {
                        if (response && response.ok && resetOnSuccess) {
                            form[0].reset();

                            if (window.AppUI && typeof window.AppUI.refresh === 'function') {
                                window.AppUI.refresh();
                            }
                        }

                        if (target !== '' && response && response.html !== undefined) {
                            $(target).html(response.html);

                            if (window.AppTablas && typeof window.AppTablas.refresh === 'function') {
                                window.AppTablas.refresh();
                            }

                            if (window.AppUI && typeof window.AppUI.refresh === 'function') {
                                window.AppUI.refresh();
                            }
                        }
                    }
                });
            });
        },

        bindLinks: function () {
            $(document).on('click', 'a[data-ajax-link="true"], button[data-ajax-link="true"]', function (event) {
                event.preventDefault();

                var element = $(this);
                var url = element.attr('href') || element.attr('data-url') || '';
                var target = element.attr('data-ajax-target') || '';

                if (url === '') {
                    AppAjax.notifyWarning('No se definió la ruta de la acción.');
                    return;
                }

                AppAjax.get(url, {}, {
                    onSuccess: function (response) {
                        if (target !== '' && response && response.html !== undefined) {
                            $(target).html(response.html);

                            if (window.AppTablas && typeof window.AppTablas.refresh === 'function') {
                                window.AppTablas.refresh();
                            }

                            if (window.AppUI && typeof window.AppUI.refresh === 'function') {
                                window.AppUI.refresh();
                            }
                        }
                    }
                });
            });
        },

        loading: function (show) {
            if (window.AppUI && typeof window.AppUI.loading === 'function') {
                window.AppUI.loading(show);
                return;
            }

            if (window.AppContreras && typeof window.AppContreras.loading === 'function') {
                window.AppContreras.loading(show);
            }
        },

        notifySuccess: function (message) {
            if (window.AppUI) {
                window.AppUI.success(message);
                return;
            }

            if (window.AppContreras) {
                window.AppContreras.notify('success', message, 'Correcto');
            }
        },

        notifyWarning: function (message) {
            if (window.AppUI) {
                window.AppUI.warning(message);
                return;
            }

            if (window.AppContreras) {
                window.AppContreras.notify('warning', message, 'Aviso');
            }
        },

        notifyError: function (message) {
            if (window.AppUI) {
                window.AppUI.error(message);
                return;
            }

            if (window.AppContreras) {
                window.AppContreras.notify('error', message, 'Error');
            }
        }
    };

    window.AppAjax = AppAjax;

    $(function () {
        AppAjax.init();
    });
})(window, window.jQuery);