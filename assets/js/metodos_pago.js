(function (window, $) {
    'use strict';

    var MetodosPago = {
        ajaxUrl: 'modules/metodos_pago/ajax.php',
        confirmCallback: null,

        init: function () {
            this.bindEventos();
            this.actualizarTipo();
        },

        bindEventos: function () {
            $('#btnNuevoMetodoPago').on('click', function () {
                MetodosPago.limpiarFormulario();
                $('#modalMetodoPagoTitulo').text('Nuevo método de pago');
                AppUI.openModal('#modalMetodoPago');
            });

            $('#metodoTipo').on('change', function () {
                MetodosPago.actualizarTipo();
            });

            $('#formMetodoPago').on('submit', function (event) {
                event.preventDefault();
                MetodosPago.guardarFormulario();
            });

            $(document).on('click', '.btnEditarMetodoPago', function () {
                MetodosPago.editar($(this).attr('data-id'));
            });

            $(document).on('click', '.btnCambiarEstadoMetodoPago', function () {
                var id = $(this).attr('data-id');
                var estado = parseInt($(this).attr('data-estado'), 10);
                var texto = estado === 1 ? 'Se desactivará este método de pago.' : 'Se activará este método de pago.';

                MetodosPago.confirmar(texto, function () {
                    MetodosPago.cambiarEstado(id);
                });
            });

            $(document).on('click', '.btnEliminarMetodoPago', function () {
                var id = $(this).attr('data-id');
                var texto = 'Se eliminará físicamente este método de pago. Esta acción no se puede deshacer.';

                MetodosPago.confirmar(texto, function () {
                    MetodosPago.eliminar(id);
                });
            });

            $('#btnConfirmarMetodoPago').on('click', function () {
                AppUI.closeModal('#modalConfirmarMetodoPago');

                if (typeof MetodosPago.confirmCallback === 'function') {
                    MetodosPago.confirmCallback();
                }

                MetodosPago.confirmCallback = null;
            });
        },

        limpiarFormulario: function () {
            $('#formMetodoPago')[0].reset();
            $('#metodoPagoId').val('0');
            $('#metodoTipo').val('Cuenta de ahorro');
            $('#metodoEstado').val('1');
            $('#metodoOrden').val('1');
            $('#metodoTomarOrden').val('0');
            this.actualizarTipo();
            AppUI.refresh();
        },

        guardarFormulario: function () {
            AppAjax.sendForm($('#formMetodoPago')[0], {
                url: MetodosPago.ajaxUrl,
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#metodosPagoTablaContainer').html(response.html);
                        AppTablas.refresh();
                        AppUI.closeModal('#modalMetodoPago');
                    }
                },
                onError: function (xhr) {
                    var data = xhr && xhr.responseJSON ? xhr.responseJSON : null;

                    if (!data || data.code !== 'orden_ocupado') {
                        return;
                    }

                    var ocupado = data.ocupado || {};
                    var nombre = ocupado.titulo_visible || 'otro método';
                    var orden = ocupado.orden || $('#metodoOrden').val();
                    var texto = 'El orden ' + orden + ' ya está asignado a "' + nombre + '". Si confirmas, este método tomará ese orden y el anterior quedará sin orden.';

                    MetodosPago.confirmar(texto, function () {
                        $('#metodoTomarOrden').val('1');
                        MetodosPago.guardarFormulario();
                    });
                }
            });
        },

        actualizarTipo: function () {
            var tipo = $('#metodoTipo').val();

            if (tipo === 'Cuenta de ahorro') {
                $('.metodo-campo-cuenta').show();
                $('.metodo-campo-celular').hide();
                return;
            }

            $('.metodo-campo-cuenta').hide();
            $('.metodo-campo-celular').show();
        },

        editar: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_metodo_pago',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var metodo = response.metodo;

                    MetodosPago.limpiarFormulario();

                    $('#modalMetodoPagoTitulo').text('Editar método de pago');
                    $('#metodoPagoId').val(metodo.id);
                    $('#metodoTituloVisible').val(metodo.titulo_visible);
                    $('#metodoTipo').val(metodo.tipo);
                    $('#metodoTitular').val(metodo.titular);
                    $('#metodoBanco').val(metodo.banco);
                    $('#metodoNumeroCuenta').val(metodo.numero_cuenta);
                    $('#metodoCci').val(metodo.cci);
                    $('#metodoNumeroCelular').val(metodo.numero_celular);
                    $('#metodoDescripcion').val(metodo.descripcion);
                    $('#metodoOrden').val(metodo.orden);
                    $('#metodoEstado').val(String(metodo.estado));
                    $('#metodoTomarOrden').val('0');

                    MetodosPago.actualizarTipo();
                    AppUI.refresh();
                    AppUI.openModal('#modalMetodoPago');
                }
            });
        },

        cambiarEstado: function (id) {
            AppAjax.post(this.ajaxUrl, {
                action: 'cambiar_estado_metodo_pago',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#metodosPagoTablaContainer').html(response.html);
                        AppTablas.refresh();
                    }
                }
            });
        },

        eliminar: function (id) {
            AppAjax.post(this.ajaxUrl, {
                action: 'eliminar_metodo_pago',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#metodosPagoTablaContainer').html(response.html);
                        AppTablas.refresh();
                    }
                }
            });
        },

        confirmar: function (texto, callback) {
            $('#modalConfirmarMetodoPagoTexto').text(texto);
            this.confirmCallback = callback;
            AppUI.openModal('#modalConfirmarMetodoPago');
        }
    };

    window.MetodosPago = MetodosPago;

    $(function () {
        MetodosPago.init();
    });
})(window, window.jQuery);
