(function (window, $) {
    'use strict';

    var Proformas = {
        ajaxUrl: 'modules/proformas/ajax.php',
        documentoUrl: 'modules/proformas/documento.php',
        items: [],
        ultimoIdGuardado: 0,
        ultimoCodigoGuardado: '',

        init: function () {
            this.bindEventos();
            this.renderItems();
        },

        bindEventos: function () {
            $('#btnNuevaProforma').on('click', function () {
                Proformas.limpiarFormulario();
                $('#modalProformaTitulo').text('Nueva proforma');
                $('#proformaManualEmergencia').val('0');
                $('#proformaServiciosPanel').show();
                AppUI.openModal('#modalProforma');
            });

            $('#btnProformaEmergencia').on('click', function () {
                Proformas.limpiarFormulario();
                $('#modalProformaTitulo').text('Documento manual de emergencia');
                $('#proformaManualEmergencia').val('1');
                $('#proformaServiciosPanel').hide();
                AppUI.openModal('#modalProforma');
            });

            $('#proformaClienteId').on('change', function () {
                Proformas.cargarServiciosCliente();
            });

            $(document).on('change', '.pfServicioCheck', function () {
                Proformas.syncServicio($(this));
            });

            $('#btnAgregarItemManual').on('click', function () {
                Proformas.agregarItemManual();
            });

            $('#proformaDescuento').on('input change', function () {
                Proformas.calcularTotales();
            });

            $('#btnLimpiarProforma').on('click', function () {
                Proformas.limpiarItems();
            });

            $('#formProforma').on('submit', function (event) {
                event.preventDefault();
                Proformas.guardar(this);
            });

            $(document).on('click', '.btnEditarProforma', function () {
                Proformas.editar($(this).attr('data-id'));
            });

            $(document).on('click', '.btnVerProforma', function () {
                Proformas.verDocumento($(this).attr('data-id'));
            });

            $(document).on('click', '.btnExportarProforma', function () {
                Proformas.exportar($(this).attr('data-id'), $(this).attr('data-tipo'));
            });

            $(document).on('click', '.pfBtnQuitarItem', function () {
                Proformas.quitarItem($(this).attr('data-key'));
            });

            $(document).on('change', '.pfItemBloque', function () {
                Proformas.actualizarItem($(this).attr('data-key'), 'bloque', $(this).val());
            });

            $(document).on('input change', '.pfItemCantidad', function () {
                Proformas.actualizarItem($(this).attr('data-key'), 'cantidad', $(this).val());
            });

            $(document).on('input change', '.pfItemPrecio', function () {
                Proformas.actualizarItem($(this).attr('data-key'), 'precio_unitario', $(this).val());
            });

            $('#btnExitoVerDocumento').on('click', function () {
                Proformas.verDocumento(Proformas.ultimoIdGuardado);
            });

            $('#btnExitoDescargarJpg').on('click', function () {
                Proformas.exportar(Proformas.ultimoIdGuardado, 'jpg');
            });

            $('#btnExitoDescargarPdf').on('click', function () {
                Proformas.exportar(Proformas.ultimoIdGuardado, 'pdf');
            });
        },

        limpiarFormulario: function () {
            $('#formProforma')[0].reset();
            $('#proformaId').val('0');
            $('#proformaFechaEmision').val(this.fechaHoy());
            $('#proformaFechaVencimiento').val('');
            $('#proformaDescuento').val('0.00');
            $('#proformaServiciosClienteContainer').html(
                '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-user"></i></div><h5>Seleccione un cliente</h5><p>Los servicios pendientes para proformar aparecerán aquí.</p></div>'
            );
            this.items = [];
            this.renderItems();
            AppUI.refresh();
        },

        limpiarItems: function () {
            this.items = [];
            $('.pfServicioCheck').prop('checked', false);
            $('#proformaDescuento').val('0.00');
            this.renderItems();
        },

        fechaHoy: function () {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        },

        timestampArchivo: function () {
            var d = new Date();

            return d.getFullYear() +
                String(d.getMonth() + 1).padStart(2, '0') +
                String(d.getDate()).padStart(2, '0') + '-' +
                String(d.getHours()).padStart(2, '0') +
                String(d.getMinutes()).padStart(2, '0') +
                String(d.getSeconds()).padStart(2, '0');
        },

        clienteExportacionDesdeDocumento: function (documento, id) {
            if (documento && documento.querySelector) {
                var nodo = documento.querySelector('.pf-doc-cliente strong');

                if (nodo) {
                    var texto = $.trim(nodo.textContent || nodo.innerText || '');

                    if (texto !== '') {
                        return texto;
                    }
                }
            }

            return 'cliente-' + id;
        },

        nombreArchivoExportacion: function (documento, id) {
            return 'proforma-' + this.clienteExportacionDesdeDocumento(documento, id) + '-' + this.timestampArchivo();
        },

        cargarServiciosCliente: function () {
            var clienteId = $('#proformaClienteId').val();
            var proformaId = $('#proformaId').val();

            AppAjax.get(this.ajaxUrl, {
                action: 'servicios_cliente',
                cliente_id: clienteId,
                proforma_id: proformaId
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#proformaServiciosClienteContainer').html(response.html);

                        $('.pfServicioCheck:checked').each(function () {
                            Proformas.syncServicio($(this), true);
                        });
                    }
                }
            });
        },

        itemKey: function (tipo, id) {
            return tipo + '_' + id;
        },

        syncServicio: function (check, silent) {
            var id = check.val();
            var key = this.itemKey('Servicio', id);

            if (check.is(':checked')) {
                if (!this.buscarItem(key)) {
                    this.items.push({
                        key: key,
                        tipo_item: 'Servicio',
                        cliente_servicio_id: parseInt(id, 10),
                        bloque: check.attr('data-bloque') || 'Actuales',
                        descripcion: check.attr('data-descripcion') || '',
                        cantidad: 1,
                        precio_unitario: parseFloat(check.attr('data-monto') || '0')
                    });
                }
            } else {
                this.items = $.grep(this.items, function (item) {
                    return item.key !== key;
                });
            }

            if (!silent) {
                this.renderItems();
            }
        },

        buscarItem: function (key) {
            for (var i = 0; i < this.items.length; i++) {
                if (this.items[i].key === key) {
                    return this.items[i];
                }
            }

            return null;
        },

        agregarItemManual: function () {
            var descripcion = $.trim($('#manualDescripcion').val());
            var cantidad = parseFloat($('#manualCantidad').val() || '1');
            var precio = parseFloat($('#manualPrecio').val() || '0');
            var bloque = $('#manualBloque').val();

            if (descripcion === '') {
                AppUI.warning('Ingrese la descripción del ítem manual.');
                return;
            }

            if (cantidad <= 0 || precio < 0) {
                AppUI.warning('Ingrese cantidad y precio válidos.');
                return;
            }

            this.items.push({
                key: 'Manual_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
                tipo_item: 'Manual',
                cliente_servicio_id: null,
                bloque: bloque,
                descripcion: descripcion,
                cantidad: cantidad,
                precio_unitario: precio
            });

            $('#manualDescripcion').val('');
            $('#manualCantidad').val('1');
            $('#manualPrecio').val('0.00');

            this.renderItems();
        },

        quitarItem: function (key) {
            this.items = $.grep(this.items, function (item) {
                return item.key !== key;
            });

            if (key.indexOf('Servicio_') === 0) {
                var id = key.replace('Servicio_', '');
                $('.pfServicioCheck[value="' + id + '"]').prop('checked', false);
            }

            this.renderItems();
        },

        actualizarItem: function (key, campo, valor) {
            var item = this.buscarItem(key);

            if (!item) {
                return;
            }

            if (campo === 'cantidad' || campo === 'precio_unitario') {
                valor = parseFloat(valor || '0');

                if (valor < 0) {
                    valor = 0;
                }
            }

            item[campo] = valor;
            this.renderItems();
        },

        renderItems: function () {
            var container = $('#proformaItemsContainer');
            var html = '';

            if (this.items.length === 0) {
                container.html(
                    '<div class="app-empty-state" id="proformaItemsVacio">' +
                    '   <div class="app-empty-state-icon"><i class="fas fa-inbox"></i></div>' +
                    '   <h5>Sin ítems</h5>' +
                    '   <p>Selecciona servicios o agrega ítems manuales.</p>' +
                    '</div>'
                );
                this.calcularTotales();
                return;
            }

            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered pf-items-table">';
            html += '<thead><tr>';
            html += '<th>Tipo</th><th>Bloque</th><th>Descripción</th><th width="95">Cant.</th><th width="120">Precio</th><th width="120">Total</th><th width="55"></th>';
            html += '</tr></thead><tbody>';

            $.each(this.items, function (index, item) {
                var total = parseFloat(item.cantidad || 0) * parseFloat(item.precio_unitario || 0);

                html += '<tr>';
                html += '<td><span class="badge badge-' + (item.tipo_item === 'Servicio' ? 'primary' : 'secondary') + '">' + AppUI.escapeHtml(item.tipo_item) + '</span></td>';
                html += '<td>';
                html += '<select class="custom-select custom-select-sm pfItemBloque" data-key="' + AppUI.escapeHtml(item.key) + '">';
                html += '<option value="Actuales"' + (item.bloque === 'Actuales' ? ' selected' : '') + '>Actuales</option>';
                html += '<option value="Pendientes de pago"' + (item.bloque === 'Pendientes de pago' ? ' selected' : '') + '>Pendientes de pago</option>';
                html += '<option value="Otros servicios o trámites"' + (item.bloque === 'Otros servicios o trámites' ? ' selected' : '') + '>Otros servicios o trámites</option>';
                html += '</select>';
                html += '</td>';
                html += '<td>' + AppUI.escapeHtml(item.descripcion) + '</td>';
                html += '<td><input type="number" class="form-control form-control-sm pfItemCantidad" data-key="' + AppUI.escapeHtml(item.key) + '" min="0.01" step="0.01" value="' + parseFloat(item.cantidad || 1).toFixed(2) + '"></td>';
                html += '<td><input type="number" class="form-control form-control-sm pfItemPrecio" data-key="' + AppUI.escapeHtml(item.key) + '" min="0" step="0.01" value="' + parseFloat(item.precio_unitario || 0).toFixed(2) + '"></td>';
                html += '<td class="text-right">S/ ' + total.toFixed(2) + '</td>';
                html += '<td><button type="button" class="btn btn-sm btn-danger pfBtnQuitarItem" data-key="' + AppUI.escapeHtml(item.key) + '"><i class="fas fa-times"></i></button></td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';

            container.html(html);
            this.calcularTotales();
        },

        calcularTotales: function () {
            var subtotal = 0;
            var bloques = {
                'Actuales': 0,
                'Pendientes de pago': 0,
                'Otros servicios o trámites': 0
            };

            $.each(this.items, function (index, item) {
                var itemTotal = parseFloat(item.cantidad || 0) * parseFloat(item.precio_unitario || 0);
                subtotal += itemTotal;
                if (bloques.hasOwnProperty(item.bloque)) {
                    bloques[item.bloque] += itemTotal;
                }
            });

            var descuento = parseFloat($('#proformaDescuento').val() || '0');

            if (descuento < 0) {
                descuento = 0;
            }

            if (descuento > subtotal) {
                descuento = subtotal;
                $('#proformaDescuento').val(descuento.toFixed(2));
            }

            var total = subtotal - descuento;

            $('#proformaBloqueActualesTexto').text('S/ ' + bloques['Actuales'].toFixed(2));
            $('#proformaBloquePendientesTexto').text('S/ ' + bloques['Pendientes de pago'].toFixed(2));
            $('#proformaBloqueOtrosTexto').text('S/ ' + bloques['Otros servicios o trámites'].toFixed(2));
            $('#proformaSubtotalTexto').text('S/ ' + subtotal.toFixed(2));
            $('#proformaTotalTexto').text('S/ ' + total.toFixed(2));
        },

        prepararItemsJson: function () {
            var salida = [];

            $.each(this.items, function (index, item) {
                salida.push({
                    tipo_item: item.tipo_item,
                    cliente_servicio_id: item.cliente_servicio_id,
                    bloque: item.bloque,
                    descripcion: item.descripcion,
                    cantidad: item.cantidad,
                    precio_unitario: item.precio_unitario
                });
            });

            $('#proformaItemsJson').val(JSON.stringify(salida));
        },

        guardar: function (form) {
            this.prepararItemsJson();

            AppAjax.sendForm(form, {
                url: this.ajaxUrl,
                onSuccess: function (response) {
                    if (response && response.ok) {
                        Proformas.ultimoIdGuardado = response.id;
                        Proformas.ultimoCodigoGuardado = response.codigo;

                        $('#proformasTablaContainer').html(response.html);
                        AppTablas.refresh();
                        AppUI.closeModal('#modalProforma');

                        $('#proformaExitoCodigo').text(response.codigo);
                        AppUI.openModal('#modalExitoProforma');
                    }
                }
            });
        },

        editar: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_proforma',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    Proformas.limpiarFormulario();

                    var p = response.proforma;

                    $('#modalProformaTitulo').text('Editar proforma ' + p.codigo);
                    $('#proformaId').val(p.id);
                    $('#proformaClienteId').val(p.cliente_id);
                    $('#proformaPlantillaId').val(p.plantilla_id);
                    $('#proformaFechaEmision').val(p.fecha_emision);
                    $('#proformaFechaVencimiento').val(p.fecha_vencimiento);
                    $('#proformaObservacion').val(p.observacion);
                    $('#proformaDescuento').val(parseFloat(p.descuento || 0).toFixed(2));
                    $('#proformaManualEmergencia').val('0');
                    $('#proformaServiciosPanel').show();

                    Proformas.items = [];

                    $.each(response.detalles || [], function (index, d) {
                        Proformas.items.push({
                            key: d.tipo_item + '_' + (d.cliente_servicio_id || ('manual_' + d.id)),
                            tipo_item: d.tipo_item,
                            cliente_servicio_id: d.cliente_servicio_id ? parseInt(d.cliente_servicio_id, 10) : null,
                            bloque: d.bloque,
                            descripcion: d.descripcion,
                            cantidad: parseFloat(d.cantidad || 1),
                            precio_unitario: parseFloat(d.precio_unitario || 0)
                        });
                    });

                    Proformas.renderItems();
                    Proformas.cargarServiciosCliente();
                    AppUI.refresh();
                    AppUI.openModal('#modalProforma');
                }
            });
        },

        verDocumento: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'documento_proforma',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#documentoProformaContenido').html(response.html);
                        AppUI.openModal('#modalDocumentoProforma');
                    }
                }
            });
        },

exportar: function (id, tipo) {
    if (!window.AppExportador) {
        AppUI.error('No se cargó el exportador local.');
        return;
    }

    var self = this;

    AppExportador.exportarDocumentoAjax({
        ajaxUrl: this.ajaxUrl,
        documentoAction: 'documento_proforma',
        auditoriaAction: 'exportar_proforma',
        id: id,
        tipo: tipo,
        nombreArchivo: 'proforma-' + id,
        generarNombreArchivo: function (documento) {
            return self.nombreArchivoExportacion(documento, id);
        },
        selectorDocumento: '#pfDocumentoExportable, .pf-documento',
        orientacion: 'auto'
    });
}
    };

    window.Proformas = Proformas;

    $(function () {
        Proformas.init();
    });
})(window, window.jQuery);
