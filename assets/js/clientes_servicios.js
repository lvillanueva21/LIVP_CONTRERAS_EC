(function (window, $) {
    'use strict';

    var ClientesServicios = {
        ajaxUrl: 'modules/clientes_servicios/ajax.php',
        confirmCallback: null,
        clienteDetalleActual: 0,

        init: function () {
            this.bindClientes();
            this.bindServicios();
            this.bindEtiquetas();
            this.bindConfirmacion();
            this.actualizarTipoCliente();
        },

        bindClientes: function () {
            $('#btnNuevoCliente').on('click', function () {
                ClientesServicios.limpiarCliente();
                $('#modalClienteTitulo').text('Nuevo cliente');
                AppUI.openModal('#modalCliente');
            });

            $('#clienteTipo').on('change', function () {
                ClientesServicios.actualizarTipoCliente();
            });

            $('#formCliente').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: ClientesServicios.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            $('#clientesTablaContainer').html(response.html);
                            AppTablas.refresh();
                            AppUI.closeModal('#modalCliente');
                        }
                    }
                });
            });

            $(document).on('click', '.btnEditarCliente', function () {
                ClientesServicios.editarCliente($(this).attr('data-id'));
            });

            $(document).on('click', '.btnVerCliente', function () {
                ClientesServicios.verDetalleCliente($(this).attr('data-id'));
            });

            $(document).on('click', '.btnDesactivarCliente', function () {
                var id = $(this).attr('data-id');
                var nombre = $(this).attr('data-nombre');

                ClientesServicios.confirmar('Se desactivará el cliente: ' + nombre, function () {
                    ClientesServicios.desactivarCliente(id);
                });
            });
        },

        bindServicios: function () {
            $(document).on('click', '.btnNuevoServicioCliente', function () {
                ClientesServicios.limpiarServicio();
                $('#servicioClienteId').val($(this).attr('data-cliente-id'));
                $('#modalServicioClienteTitulo').text('Asignar servicio');
                AppUI.openModal('#modalServicioCliente');
            });

            $('#servicioId').on('change', function () {
                var precio = $('#servicioId option:selected').attr('data-precio');

                if ($('#clienteServicioId').val() === '0' && precio && parseFloat(precio) > 0) {
                    $('#servicioMonto').val(parseFloat(precio).toFixed(2));
                }
            });

            $('#formServicioCliente').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: ClientesServicios.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            $('#detalleClienteContenido').html(response.detalle_html);
                            $('#clientesTablaContainer').html(response.clientes_html);
                            AppTablas.refresh();
                            AppUI.closeModal('#modalServicioCliente');
                        }
                    }
                });
            });

            $(document).on('click', '.btnEditarServicioCliente', function () {
                ClientesServicios.editarServicioCliente($(this).attr('data-id'));
            });

            $(document).on('click', '.btnAnularServicioCliente', function () {
                var id = $(this).attr('data-id');
                var nombre = $(this).attr('data-nombre');

                ClientesServicios.confirmar('Se anulará el servicio asignado: ' + nombre, function () {
                    ClientesServicios.anularServicioCliente(id);
                });
            });
        },

        bindEtiquetas: function () {
            $('#btnNuevaEtiqueta').on('click', function () {
                ClientesServicios.limpiarEtiqueta();
                AppUI.openModal('#modalEtiqueta');
            });

            $('#formEtiqueta').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: ClientesServicios.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok && response.etiqueta) {
                            ClientesServicios.agregarEtiquetaSelector(response.etiqueta);
                            AppUI.closeModal('#modalEtiqueta');
                        }
                    }
                });
            });
        },

        bindConfirmacion: function () {
            $('#btnConfirmarAccion').on('click', function () {
                AppUI.closeModal('#modalConfirmacionAccion');

                if (typeof ClientesServicios.confirmCallback === 'function') {
                    ClientesServicios.confirmCallback();
                }

                ClientesServicios.confirmCallback = null;
            });
        },

        limpiarCliente: function () {
            $('#formCliente')[0].reset();
            $('#clienteId').val('0');
            $('#clienteTipo').val('Empresa');
            $('#clienteDocumentoTipo').val('RUC');
            $('#clienteEstado').val('1');
            this.actualizarTipoCliente();
            AppUI.refresh();
        },

        actualizarTipoCliente: function () {
            var tipo = $('#clienteTipo').val();

            if (tipo === 'Persona natural') {
                $('.cliente-campo-empresa').hide();
                $('.cliente-campo-persona').show();
                $('#clienteDocumentoTipo').val('DNI');
                return;
            }

            $('.cliente-campo-empresa').show();
            $('.cliente-campo-persona').hide();
            $('#clienteDocumentoTipo').val('RUC');
        },

        editarCliente: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_cliente',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var c = response.cliente;

                    ClientesServicios.limpiarCliente();

                    $('#modalClienteTitulo').text('Editar cliente');
                    $('#clienteId').val(c.id);
                    $('#clienteTipo').val(c.tipo_cliente);
                    $('#clienteDocumentoTipo').val(c.documento_tipo);
                    $('#clienteNumeroDocumento').val(c.numero_documento);
                    $('#clienteRazonSocial').val(c.razon_social);
                    $('#clienteNombreComercial').val(c.nombre_comercial);
                    $('#clienteNombres').val(c.nombres);
                    $('#clienteApellidos').val(c.apellidos);
                    $('#clienteDireccion').val(c.direccion);
                    $('#clienteCelular').val(c.celular);
                    $('#clienteCorreo').val(c.correo);
                    $('#clienteObservacion').val(c.observacion);
                    $('#clienteEstado').val(String(c.estado));

                    ClientesServicios.actualizarTipoCliente();
                    AppUI.refresh();
                    AppUI.openModal('#modalCliente');
                }
            });
        },

        desactivarCliente: function (id) {
            AppAjax.post(this.ajaxUrl, {
                action: 'desactivar_cliente',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#clientesTablaContainer').html(response.html);
                        AppTablas.refresh();
                    }
                }
            });
        },

        verDetalleCliente: function (id) {
            this.clienteDetalleActual = id;

            AppAjax.get(this.ajaxUrl, {
                action: 'detalle_cliente',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#detalleClienteContenido').html(response.html);
                        AppTablas.refresh();
                        AppUI.openModal('#modalDetalleCliente');
                    }
                }
            });
        },

        limpiarServicio: function () {
            $('#formServicioCliente')[0].reset();
            $('#clienteServicioId').val('0');
            $('#servicioClienteId').val('0');
            $('#servicioId').val('');
            $('#servicioEtiquetas').val([]);
            $('#servicioMonto').val('');
            $('#servicioBloque').val('Actuales');
            $('#servicioModoAviso').val('Sin aviso');
            $('#servicioEstado').val('Pendiente');
            AppUI.refresh();
        },

        editarServicioCliente: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_servicio_cliente',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var s = response.servicio;

                    ClientesServicios.limpiarServicio();

                    $('#modalServicioClienteTitulo').text('Editar servicio asignado');
                    $('#clienteServicioId').val(s.id);
                    $('#servicioClienteId').val(s.cliente_id);
                    $('#servicioId').val(s.servicio_id);
                    $('#servicioEtiquetas').val(s.etiquetas_ids || []);
                    $('#servicioDescripcion').val(s.descripcion_personalizada);
                    $('#servicioPeriodo').val(s.periodo);
                    $('#servicioMonto').val(s.monto);
                    $('#servicioBloque').val(s.bloque_documento);
                    $('#servicioFechaAviso').val(s.fecha_aviso);
                    $('#servicioModoAviso').val(s.modo_aviso);
                    $('#servicioEstado').val(s.estado);

                    AppUI.refresh();
                    AppUI.openModal('#modalServicioCliente');
                }
            });
        },

        anularServicioCliente: function (id) {
            AppAjax.post(this.ajaxUrl, {
                action: 'anular_servicio_cliente',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#detalleClienteContenido').html(response.detalle_html);
                        $('#clientesTablaContainer').html(response.clientes_html);
                        AppTablas.refresh();
                    }
                }
            });
        },

        limpiarEtiqueta: function () {
            $('#formEtiqueta')[0].reset();
            $('#etiquetaColor').val('#6c757d');
            AppUI.refresh();
        },

        agregarEtiquetaSelector: function (etiqueta) {
            var select = $('#servicioEtiquetas');
            var exists = select.find('option[value="' + etiqueta.id + '"]').length > 0;

            if (!exists) {
                select.append('<option value="' + etiqueta.id + '">' + AppUI.escapeHtml(etiqueta.nombre) + '</option>');
            }

            var valores = select.val() || [];

            if ($.inArray(String(etiqueta.id), valores) === -1) {
                valores.push(String(etiqueta.id));
            }

            select.val(valores);
        },

        confirmar: function (texto, callback) {
            $('#modalConfirmacionTexto').text(texto);
            this.confirmCallback = callback;
            AppUI.openModal('#modalConfirmacionAccion');
        }
    };

    window.ClientesServicios = ClientesServicios;

    $(function () {
        ClientesServicios.init();
    });
})(window, window.jQuery);