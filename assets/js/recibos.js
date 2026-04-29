(function (window, $) {
    'use strict';

    var Recibos = {
        ajaxUrl: 'modules/recibos/ajax.php',
        items: [],
        ultimoIdGuardado: 0,
        ultimoCodigoGuardado: '',
        totalProforma: 0,
        pagadoProformaPrevio: 0,

        init: function () {
            this.bindEventos();
            this.renderItems();
        },

        bindEventos: function () {
            $('#btnConfirmarPagoProforma').on('click', function () {
                Recibos.limpiarFormulario();
                $('#modalReciboTitulo').text('Confirmar pago desde proforma');
                $('#reciboManualEmergencia').val('0');
                $('#reciboProformaRow').show();
                $('#reciboDetallesProformaPanel').show();
                $('#reciboClienteId').prop('disabled', true);
                $('#reciboPlantillaId').prop('disabled', true);
                AppUI.openModal('#modalRecibo');
            });

            $('#btnReciboManualEmergencia').on('click', function () {
                Recibos.limpiarFormulario();
                $('#modalReciboTitulo').text('Recibo manual de emergencia');
                $('#reciboManualEmergencia').val('1');
                $('#reciboProformaRow').hide();
                $('#reciboDetallesProformaPanel').hide();
                $('#reciboClienteId').prop('disabled', false);
                $('#reciboPlantillaId').prop('disabled', false);
                AppUI.openModal('#modalRecibo');
            });

            $('#reciboProformaId').on('change', function () {
                Recibos.cargarProforma();
            });

            $('#reciboClienteId').on('change', function () {
                Recibos.cargarServiciosCliente();
            });

            $(document).on('change', '.rbDetalleProformaCheck', function () {
                Recibos.syncDetalleProforma($(this));
            });

            $(document).on('input change', '.rbMontoDetalleProforma', function () {
                var check = $(this).closest('.rb-item-pago').find('.rbDetalleProformaCheck');

                if (check.is(':checked')) {
                    Recibos.syncDetalleProforma(check);
                }
            });

            $(document).on('change', '.rbServicioAdicionalCheck', function () {
                Recibos.syncServicioAdicional($(this));
            });

            $(document).on('input change', '.rbMontoServicioAdicional', function () {
                var check = $(this).closest('.rb-item-pago').find('.rbServicioAdicionalCheck');

                if (check.is(':checked')) {
                    Recibos.syncServicioAdicional(check);
                }
            });

            $('#btnAgregarManualRecibo').on('click', function () {
                Recibos.agregarItemManual();
            });

            $('#btnLimpiarRecibo').on('click', function () {
                Recibos.limpiarItems();
            });

            $(document).on('click', '.rbBtnQuitarItem', function () {
                Recibos.quitarItem($(this).attr('data-key'));
            });

            $(document).on('input change', '.rbItemMontoPagado', function () {
                Recibos.actualizarMontoItem($(this).attr('data-key'), $(this).val());
            });

            $('#formRecibo').on('submit', function (event) {
                event.preventDefault();
                Recibos.guardar(this);
            });

            $(document).on('click', '.btnVerRecibo', function () {
                Recibos.verDocumento($(this).attr('data-id'));
            });

            $(document).on('click', '.btnExportarRecibo', function () {
                Recibos.exportar($(this).attr('data-id'), $(this).attr('data-tipo'));
            });

            $('#btnExitoVerRecibo').on('click', function () {
                Recibos.verDocumento(Recibos.ultimoIdGuardado);
            });

            $('#btnExitoDescargarReciboJpg').on('click', function () {
                Recibos.exportar(Recibos.ultimoIdGuardado, 'jpg');
            });

            $('#btnExitoDescargarReciboPdf').on('click', function () {
                Recibos.exportar(Recibos.ultimoIdGuardado, 'pdf');
            });
        },

        fechaHoy: function () {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        },

        limpiarFormulario: function () {
            $('#formRecibo')[0].reset();
            $('#reciboFechaEmision').val(this.fechaHoy());
            $('#reciboFechaPago').val(this.fechaHoy());
            $('#reciboClienteId').prop('disabled', false).val('');
            $('#reciboPlantillaId').prop('disabled', false).val('');
            $('#reciboMetodoPagoId').val('');
            $('#reciboProformaId').val('');
            $('#reciboObservacion').val('');
            $('#reciboDetallesProformaContainer').html(
                '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-file-invoice-dollar"></i></div><h5>Seleccione una proforma</h5><p>Los ítems pendientes aparecerán aquí.</p></div>'
            );
            $('#reciboServiciosAdicionalesContainer').html(
                '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-user"></i></div><h5>Seleccione cliente</h5><p>Los servicios adicionales disponibles aparecerán aquí.</p></div>'
            );
            this.totalProforma = 0;
            this.pagadoProformaPrevio = 0;
            this.items = [];
            this.renderItems();
            AppUI.refresh();
        },

        limpiarItems: function () {
            this.items = [];
            $('.rbDetalleProformaCheck, .rbServicioAdicionalCheck').prop('checked', false);
            this.renderItems();
        },

        cargarProforma: function () {
            var proformaId = $('#reciboProformaId').val();

            if (!proformaId) {
                return;
            }

            AppAjax.get(this.ajaxUrl, {
                action: 'cargar_proforma',
                proforma_id: proformaId
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var p = response.proforma;

                    $('#reciboClienteId').val(p.cliente_id);
                    $('#reciboPlantillaId').val(p.plantilla_id);
                    $('#reciboDetallesProformaContainer').html(response.detalles_html);
                    $('#reciboServiciosAdicionalesContainer').html(response.servicios_html);

                    Recibos.totalProforma = parseFloat(p.total || 0);
                    Recibos.items = [];
                    Recibos.renderItems();
                }
            });
        },

        cargarServiciosCliente: function () {
            var clienteId = $('#reciboClienteId').val();
            var proformaId = $('#reciboProformaId').val();

            AppAjax.get(this.ajaxUrl, {
                action: 'servicios_cliente',
                cliente_id: clienteId,
                proforma_id: proformaId
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#reciboServiciosAdicionalesContainer').html(response.html);
                    }
                }
            });
        },

        buscarItem: function (key) {
            for (var i = 0; i < this.items.length; i++) {
                if (this.items[i].key === key) {
                    return this.items[i];
                }
            }

            return null;
        },

        syncDetalleProforma: function (check) {
            var id = check.val();
            var key = 'Proforma_' + id;
            var montoInput = check.closest('.rb-item-pago').find('.rbMontoDetalleProforma');
            var montoPagado = parseFloat(montoInput.val() || '0');
            var montoOriginal = parseFloat(check.attr('data-monto-original') || '0');
            var saldo = parseFloat(check.attr('data-saldo') || '0');

            if (montoPagado > saldo) {
                montoPagado = saldo;
                montoInput.val(montoPagado.toFixed(2));
            }

            this.items = $.grep(this.items, function (item) {
                return item.key !== key;
            });

            if (check.is(':checked') && montoPagado > 0) {
                this.items.push({
                    key: key,
                    origen: 'Proforma',
                    proforma_detalle_id: parseInt(id, 10),
                    cliente_servicio_id: parseInt(check.attr('data-cliente-servicio-id') || '0', 10) || null,
                    bloque: check.attr('data-bloque') || 'Actuales',
                    descripcion: check.attr('data-descripcion') || '',
                    monto_original: montoOriginal,
                    monto_pagado: montoPagado
                });
            }

            this.renderItems();
        },

        syncServicioAdicional: function (check) {
            var id = check.val();
            var key = 'Servicio_' + id;
            var montoInput = check.closest('.rb-item-pago').find('.rbMontoServicioAdicional');
            var montoPagado = parseFloat(montoInput.val() || '0');
            var montoOriginal = parseFloat(check.attr('data-monto-original') || '0');

            if (montoPagado > montoOriginal) {
                montoPagado = montoOriginal;
                montoInput.val(montoPagado.toFixed(2));
            }

            this.items = $.grep(this.items, function (item) {
                return item.key !== key;
            });

            if (check.is(':checked') && montoPagado > 0) {
                this.items.push({
                    key: key,
                    origen: 'Servicio adicional',
                    proforma_detalle_id: null,
                    cliente_servicio_id: parseInt(id, 10),
                    bloque: check.attr('data-bloque') || 'Actuales',
                    descripcion: check.attr('data-descripcion') || '',
                    monto_original: montoOriginal,
                    monto_pagado: montoPagado
                });
            }

            this.renderItems();
        },

        agregarItemManual: function () {
            var descripcion = $.trim($('#reciboManualDescripcion').val());
            var monto = parseFloat($('#reciboManualMonto').val() || '0');
            var bloque = $('#reciboManualBloque').val();

            if (descripcion === '') {
                AppUI.warning('Ingrese la descripción del ítem manual.');
                return;
            }

            if (monto <= 0) {
                AppUI.warning('Ingrese un monto mayor a cero.');
                return;
            }

            this.items.push({
                key: 'Manual_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
                origen: 'Manual',
                proforma_detalle_id: null,
                cliente_servicio_id: null,
                bloque: bloque,
                descripcion: descripcion,
                monto_original: monto,
                monto_pagado: monto
            });

            $('#reciboManualDescripcion').val('');
            $('#reciboManualMonto').val('0.00');

            this.renderItems();
        },

        quitarItem: function (key) {
            this.items = $.grep(this.items, function (item) {
                return item.key !== key;
            });

            if (key.indexOf('Proforma_') === 0) {
                $('.rbDetalleProformaCheck[value="' + key.replace('Proforma_', '') + '"]').prop('checked', false);
            }

            if (key.indexOf('Servicio_') === 0) {
                $('.rbServicioAdicionalCheck[value="' + key.replace('Servicio_', '') + '"]').prop('checked', false);
            }

            this.renderItems();
        },

        actualizarMontoItem: function (key, value) {
            var item = this.buscarItem(key);

            if (!item) {
                return;
            }

            var monto = parseFloat(value || '0');

            if (monto < 0) {
                monto = 0;
            }

            if (monto > parseFloat(item.monto_original || 0)) {
                monto = parseFloat(item.monto_original || 0);
            }

            item.monto_pagado = monto;
            this.renderItems();
        },

        renderItems: function () {
            var container = $('#reciboItemsContainer');
            var html = '';

            if (this.items.length === 0) {
                container.html(
                    '<div class="app-empty-state">' +
                    '   <div class="app-empty-state-icon"><i class="fas fa-inbox"></i></div>' +
                    '   <h5>Sin ítems</h5>' +
                    '   <p>Selecciona ítems de proforma, servicios adicionales o agrega un ítem manual.</p>' +
                    '</div>'
                );
                this.calcularTotales();
                return;
            }

            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered rb-items-table">';
            html += '<thead><tr>';
            html += '<th>Origen</th><th>Bloque</th><th>Descripción</th><th width="125">Original</th><th width="135">Pagado</th><th width="100">Estado</th><th width="55"></th>';
            html += '</tr></thead><tbody>';

            $.each(this.items, function (index, item) {
                var estado = parseFloat(item.monto_pagado || 0) >= parseFloat(item.monto_original || 0) ? 'Pagado' : 'Pendiente';

                html += '<tr>';
                html += '<td><span class="badge badge-' + (item.origen === 'Proforma' ? 'primary' : (item.origen === 'Servicio adicional' ? 'info' : 'secondary')) + '">' + AppUI.escapeHtml(item.origen) + '</span></td>';
                html += '<td>' + AppUI.escapeHtml(item.bloque) + '</td>';
                html += '<td>' + AppUI.escapeHtml(item.descripcion) + '</td>';
                html += '<td class="text-right">S/ ' + parseFloat(item.monto_original || 0).toFixed(2) + '</td>';
                html += '<td><input type="number" class="form-control form-control-sm rbItemMontoPagado" data-key="' + AppUI.escapeHtml(item.key) + '" min="0" step="0.01" value="' + parseFloat(item.monto_pagado || 0).toFixed(2) + '"></td>';
                html += '<td><span class="badge badge-' + (estado === 'Pagado' ? 'success' : 'warning') + '">' + estado + '</span></td>';
                html += '<td><button type="button" class="btn btn-sm btn-danger rbBtnQuitarItem" data-key="' + AppUI.escapeHtml(item.key) + '"><i class="fas fa-times"></i></button></td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';

            container.html(html);
            this.calcularTotales();
        },

        calcularTotales: function () {
            var totalPagado = 0;
            var totalProforma = this.totalProforma;

            $.each(this.items, function (index, item) {
                totalPagado += parseFloat(item.monto_pagado || 0);
            });

            var pagadoProformaActual = 0;

            $.each(this.items, function (index, item) {
                if (item.origen === 'Proforma') {
                    pagadoProformaActual += parseFloat(item.monto_pagado || 0);
                }
            });

            if ($('#reciboManualEmergencia').val() === '1') {
                totalProforma = totalPagado;
            }

            var saldo = Math.max(totalProforma - pagadoProformaActual, 0);

            $('#reciboTotalProformaTexto').text('S/ ' + totalProforma.toFixed(2));
            $('#reciboTotalPagadoTexto').text('S/ ' + totalPagado.toFixed(2));
            $('#reciboSaldoPendienteTexto').text('S/ ' + saldo.toFixed(2));
        },

        prepararItemsJson: function () {
            var salida = [];

            $.each(this.items, function (index, item) {
                salida.push({
                    origen: item.origen,
                    proforma_detalle_id: item.proforma_detalle_id,
                    cliente_servicio_id: item.cliente_servicio_id,
                    bloque: item.bloque,
                    descripcion: item.descripcion,
                    monto_original: item.monto_original,
                    monto_pagado: item.monto_pagado
                });
            });

            $('#reciboItemsJson').val(JSON.stringify(salida));
        },

        guardar: function (form) {
            this.prepararItemsJson();

            $('#reciboClienteId').prop('disabled', false);
            $('#reciboPlantillaId').prop('disabled', false);

            AppAjax.sendForm(form, {
                url: this.ajaxUrl,
                onSuccess: function (response) {
                    if (response && response.ok) {
                        Recibos.ultimoIdGuardado = response.id;
                        Recibos.ultimoCodigoGuardado = response.codigo;

                        $('#recibosTablaContainer').html(response.html);
                        AppTablas.refresh();
                        AppUI.closeModal('#modalRecibo');

                        $('#reciboExitoCodigo').text(response.codigo);
                        AppUI.openModal('#modalExitoRecibo');
                    }
                },
                onError: function () {
                    if ($('#reciboManualEmergencia').val() === '0') {
                        $('#reciboClienteId').prop('disabled', true);
                        $('#reciboPlantillaId').prop('disabled', true);
                    }
                }
            });
        },

        verDocumento: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'documento_recibo',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#documentoReciboContenido').html(response.html);
                        AppUI.openModal('#modalDocumentoRecibo');
                    }
                }
            });
        },

        exportar: function (id, tipo) {
            AppAjax.get(this.ajaxUrl, {
                action: 'exportar_recibo',
                id: id,
                tipo: tipo
            });
        }
    };

    window.Recibos = Recibos;

    $(function () {
        Recibos.init();
    });
})(window, window.jQuery);