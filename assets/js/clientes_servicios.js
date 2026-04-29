(function (window, $) {
    'use strict';

    var ClientesServicios = {
        ajaxUrl: 'modules/clientes_servicios/ajax.php',
        confirmCallback: null,
        clienteDetalleActual: 0,

        init: function () {
            this.bindClientes();
            this.bindCatalogoServicios();
            this.bindServicios();
            this.bindEtiquetas();
            this.bindConfirmacion();
            this.actualizarTipoCliente();
            this.bindValidacionesCliente();
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

            $(document).on('click', '.btnCargarServicioCliente', function () {
                var clienteId = $(this).attr('data-id');
                var clienteNombre = $(this).attr('data-nombre') || '';

                ClientesServicios.limpiarServicio();
                $('#servicioClienteId').val(clienteId);
                $('#modalServicioClienteTitulo').text('Cargar servicio al cliente');

                if (clienteNombre !== '') {
                    $('#modalServicioClienteTitulo').text('Cargar servicio al cliente: ' + clienteNombre);
                }

                AppUI.openModal('#modalServicioCliente');
            });

            $('#formCliente').on('submit', function (event) {
                event.preventDefault();

                if (!ClientesServicios.validarClienteFormulario()) {
                    return;
                }

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

        bindCatalogoServicios: function () {
            $('#btnCatalogoServicios').on('click', function () {
                AppUI.openModal('#modalCatalogoServicios');
                AppTablas.refresh();
            });

            $('#btnNuevoServicioBase').on('click', function () {
                ClientesServicios.limpiarServicioBase();
                $('#modalServicioBaseTitulo').text('Nuevo servicio base');
                AppUI.openModal('#modalServicioBase');
            });

            $('#formServicioBase').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: ClientesServicios.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            ClientesServicios.actualizarCatalogoYSelect(response, $('#servicioId').val() || '');
                            AppUI.closeModal('#modalServicioBase');
                        }
                    }
                });
            });

            $(document).on('click', '.btnEditarServicioBase', function () {
                ClientesServicios.editarServicioBase($(this).attr('data-id'));
            });

            $(document).on('click', '.btnToggleServicioBase', function () {
                var id = $(this).attr('data-id');
                var estado = $(this).attr('data-estado');
                var nombre = $(this).attr('data-nombre') || '';
                var texto = estado === '1' ? 'Se activará el servicio base: ' : 'Se inactivará el servicio base: ';

                ClientesServicios.confirmar(texto + nombre, function () {
                    AppAjax.post(ClientesServicios.ajaxUrl, {
                        action: 'toggle_servicio_base',
                        id: id,
                        estado: estado
                    }, {
                        onSuccess: function (response) {
                            if (response && response.ok) {
                                ClientesServicios.actualizarCatalogoYSelect(response, $('#servicioId').val() || '');
                            }
                        }
                    });
                });
            });
        },

        bindServicios: function () {
            $(document).on('click', '.btnNuevoServicioCliente', function () {
                ClientesServicios.limpiarServicio();
                $('#servicioClienteId').val($(this).attr('data-cliente-id'));
                $('#modalServicioClienteTitulo').text('Cargar servicio al cliente');
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
                            ClientesServicios.agregarEtiquetaSelector(response.etiqueta, '#servicioEtiquetas');
                            ClientesServicios.agregarEtiquetaSelector(response.etiqueta, '#servicioBaseEtiquetas');
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
            $('#clienteEstado').val('1');
            this.actualizarTipoCliente();
            AppUI.refresh();
        },

        actualizarTipoCliente: function () {
            var tipo = $('#clienteTipo').val();
            var documentoTipo = $('#clienteDocumentoTipo');
            var numeroDocumento = $('#clienteNumeroDocumento');
            var ayuda = $('#clienteContactoAyuda');

            if (tipo === 'Persona natural') {
                $('.cliente-campo-empresa').hide();
                $('.cliente-campo-contacto').show();
                documentoTipo.val('DNI');
                numeroDocumento.attr('maxlength', '8');
                numeroDocumento.attr('inputmode', 'numeric');
                numeroDocumento.attr('placeholder', 'DNI de 8 dígitos');
                ayuda.text('Para persona natural, nombres y apellidos son obligatorios.');
                return;
            }

            $('.cliente-campo-empresa').show();
            $('.cliente-campo-contacto').show();
            documentoTipo.val('RUC');
            numeroDocumento.attr('maxlength', '11');
            numeroDocumento.attr('inputmode', 'numeric');
            numeroDocumento.attr('placeholder', 'RUC de 11 dígitos');
            ayuda.text('Para empresa, nombres y apellidos son opcionales como contacto o representante.');
        },

        bindValidacionesCliente: function () {
            $('#clienteNumeroDocumento').on('input', function () {
                var tipo = $('#clienteTipo').val();
                var value = String($(this).val() || '');

                if (tipo === 'Empresa' || tipo === 'Persona natural') {
                    $(this).val(value.replace(/\D+/g, ''));
                }
            });

            $('#clienteDocumentoTipo').on('change', function () {
                var tipo = $('#clienteTipo').val();

                if (tipo === 'Empresa') {
                    $(this).val('RUC');
                    return;
                }

                if (tipo === 'Persona natural') {
                    $(this).val('DNI');
                }
            });
        },

        validarClienteFormulario: function () {
            var tipo = $('#clienteTipo').val();
            var numeroDocumento = String($('#clienteNumeroDocumento').val() || '').trim();
            var nombres = String($('#clienteNombres').val() || '').trim();
            var apellidos = String($('#clienteApellidos').val() || '').trim();
            var razonSocial = String($('#clienteRazonSocial').val() || '').trim();

            if (tipo === 'Empresa') {
                if (razonSocial === '') {
                    AppUI.warning('Ingrese la razón social de la empresa.');
                    return false;
                }

                if (!/^\d{11}$/.test(numeroDocumento)) {
                    AppUI.warning('El RUC debe tener exactamente 11 dígitos numéricos.');
                    return false;
                }
            }

            if (tipo === 'Persona natural') {
                if (nombres === '' || apellidos === '') {
                    AppUI.warning('Para persona natural, ingrese nombres y apellidos.');
                    return false;
                }

                if (!/^\d{8}$/.test(numeroDocumento)) {
                    AppUI.warning('El DNI debe tener exactamente 8 dígitos numéricos.');
                    return false;
                }
            }

            return true;
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

        fechaAvisoParaInput: function (value) {
            if (!value) {
                return '';
            }

            return String(value).replace(' ', 'T').substring(0, 16);
        },

        limpiarServicio: function () {
            $('#formServicioCliente')[0].reset();
            $('#clienteServicioId').val('0');
            $('#servicioClienteId').val('0');
            $('#servicioId').val('');
            $('#servicioEtiquetas').val([]);
            $('#servicioMonto').val('');
            $('#servicioFechaVencimiento').val('');
            $('#servicioFechaAviso').val('');
            $('#servicioModoAviso').val('Sin aviso');
            $('#servicioAvisoValor').val('');
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
                    $('#servicioFechaVencimiento').val(s.fecha_vencimiento);
                    $('#servicioFechaAviso').val(ClientesServicios.fechaAvisoParaInput(s.fecha_aviso));
                    $('#servicioModoAviso').val(s.modo_aviso);
                    $('#servicioAvisoValor').val(s.aviso_valor);
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

        limpiarServicioBase: function () {
            $('#formServicioBase')[0].reset();
            $('#servicioBaseId').val('0');
            $('#servicioBasePrecio').val('0.00');
            $('#servicioBaseEstado').val('1');
            $('#servicioBaseEtiquetas').val([]);
            AppUI.refresh();
        },

        editarServicioBase: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_servicio_base',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var s = response.servicio;
                    ClientesServicios.limpiarServicioBase();
                    $('#modalServicioBaseTitulo').text('Editar servicio base');
                    $('#servicioBaseId').val(s.id);
                    $('#servicioBaseNombre').val(s.nombre);
                    $('#servicioBaseDescripcion').val(s.descripcion);
                    $('#servicioBasePrecio').val(parseFloat(s.precio_base || 0).toFixed(2));
                    $('#servicioBaseEstado').val(String(s.estado));
                    $('#servicioBaseEtiquetas').val(s.etiquetas_ids || []);
                    AppUI.refresh();
                    AppUI.openModal('#modalServicioBase');
                }
            });
        },

        actualizarCatalogoYSelect: function (response, selectedServicioId) {
            if (response.tabla_html) {
                $('#catalogoServiciosTablaContainer').html(response.tabla_html);
                AppTablas.refresh();
            }

            if (response.servicios_options) {
                $('#servicioId').html(response.servicios_options);

                if (selectedServicioId) {
                    $('#servicioId').val(String(selectedServicioId));
                }

                AppUI.refresh();
            }
        },

        limpiarEtiqueta: function () {
            $('#formEtiqueta')[0].reset();
            $('#etiquetaColor').val('#6c757d');
            AppUI.refresh();
        },

        agregarEtiquetaSelector: function (etiqueta, selector) {
            var select = $(selector);
            var exists = select.find('option[value="' + etiqueta.id + '"]').length > 0;

            if (!exists) {
                select.append('<option value="' + etiqueta.id + '">' + AppUI.escapeHtml(etiqueta.nombre) + '</option>');
            }

            var valores = select.val() || [];

            if ($.inArray(String(etiqueta.id), valores) === -1) {
                valores.push(String(etiqueta.id));
            }

            select.val(valores);
            AppUI.refresh();
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
