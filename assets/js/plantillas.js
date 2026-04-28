(function (window, $) {
    'use strict';

    var Plantillas = {
        ajaxUrl: 'modules/plantillas/ajax.php',
        confirmCallback: null,

        init: function () {
            this.bindEventos();
            this.actualizarColorSecundario();
        },

        bindEventos: function () {
            $('#btnNuevaPlantilla').on('click', function () {
                Plantillas.limpiarFormulario();
                $('#modalPlantillaTitulo').text('Nueva plantilla');
                AppUI.openModal('#modalPlantilla');
            });

            $('#plantillaColorTipo').on('change', function () {
                Plantillas.actualizarColorSecundario();
            });

            $('#formPlantilla').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: Plantillas.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            $('#plantillasTablaContainer').html(response.html);
                            AppTablas.refresh();
                            AppUI.closeModal('#modalPlantilla');
                        }
                    }
                });
            });

            $(document).on('click', '.btnEditarPlantilla', function () {
                Plantillas.editar($(this).attr('data-id'));
            });

            $(document).on('click', '.btnVistaPreviaPlantilla', function () {
                Plantillas.vistaPrevia($(this).attr('data-id'));
            });

            $(document).on('click', '.btnCambiarEstadoPlantilla', function () {
                var id = $(this).attr('data-id');
                var estado = parseInt($(this).attr('data-estado'), 10);
                var texto = estado === 1 ? 'Se inactivará esta plantilla.' : 'Se activará esta plantilla.';

                Plantillas.confirmar(texto, function () {
                    Plantillas.cambiarEstado(id);
                });
            });

            $('#btnConfirmarPlantilla').on('click', function () {
                AppUI.closeModal('#modalConfirmarPlantilla');

                if (typeof Plantillas.confirmCallback === 'function') {
                    Plantillas.confirmCallback();
                }

                Plantillas.confirmCallback = null;
            });
        },

        limpiarFormulario: function () {
            $('#formPlantilla')[0].reset();
            $('#plantillaId').val('0');
            $('#plantillaOrientacion').val('Vertical');
            $('#plantillaLogoVisible').val('1');
            $('#plantillaLogoTipo').val('Rectangular');
            $('#plantillaDatosEmpresaVisible').val('1');
            $('#plantillaDatosClienteVisible').val('1');
            $('#plantillaColorTipo').val('Solido');
            $('#plantillaColorPrimario').val('#1f4e79');
            $('#plantillaColorSecundario').val('#163a5a');
            $('#plantillaPieVisible').val('1');
            $('#plantillaPredeterminada').val('0');
            $('#plantillaEstado').val('1');
            $('#plantillaMetodosPagoContainer input[type="checkbox"]').prop('checked', false);
            this.actualizarColorSecundario();
            AppUI.refresh();
        },

        actualizarColorSecundario: function () {
            var tipo = $('#plantillaColorTipo').val();

            if (tipo === 'Degradado') {
                $('#plantillaColorSecundario').prop('disabled', false);
                return;
            }

            $('#plantillaColorSecundario').prop('disabled', true);
        },

        editar: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_plantilla',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var p = response.plantilla;
                    var ids = p.metodos_pago_ids || [];

                    Plantillas.limpiarFormulario();

                    $('#modalPlantillaTitulo').text('Editar plantilla');
                    $('#plantillaId').val(p.id);
                    $('#plantillaNombre').val(p.nombre);
                    $('#plantillaDescripcion').val(p.descripcion);
                    $('#plantillaOrientacion').val(p.orientacion);
                    $('#plantillaLogoVisible').val(String(p.logo_visible));
                    $('#plantillaLogoTipo').val(p.logo_tipo);
                    $('#plantillaDatosEmpresaVisible').val(String(p.datos_empresa_visible));
                    $('#plantillaDatosClienteVisible').val(String(p.datos_cliente_visible));
                    $('#plantillaColorTipo').val(p.color_tipo);
                    $('#plantillaColorPrimario').val(p.color_primario);
                    $('#plantillaColorSecundario').val(p.color_secundario || '#163a5a');
                    $('#plantillaPieVisible').val(String(p.pie_pagina_visible));
                    $('#plantillaPiePagina').val(p.pie_pagina);
                    $('#plantillaPredeterminada').val(String(p.es_predeterminada));
                    $('#plantillaEstado').val(String(p.estado));

                    $('#plantillaMetodosPagoContainer input[type="checkbox"]').prop('checked', false);

                    $.each(ids, function (index, idMetodo) {
                        $('#plantillaMetodosPagoContainer input[value="' + idMetodo + '"]').prop('checked', true);
                    });

                    Plantillas.actualizarColorSecundario();
                    AppUI.refresh();
                    AppUI.openModal('#modalPlantilla');
                }
            });
        },

        cambiarEstado: function (id) {
            AppAjax.post(this.ajaxUrl, {
                action: 'cambiar_estado_plantilla',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#plantillasTablaContainer').html(response.html);
                        AppTablas.refresh();
                    }
                }
            });
        },

        vistaPrevia: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'vista_previa_plantilla',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#plantillaVistaPreviaContenido').html(response.html);
                        AppUI.openModal('#modalVistaPreviaPlantilla');
                    }
                }
            });
        },

        confirmar: function (texto, callback) {
            $('#modalConfirmarPlantillaTexto').text(texto);
            this.confirmCallback = callback;
            AppUI.openModal('#modalConfirmarPlantilla');
        }
    };

    window.Plantillas = Plantillas;

    $(function () {
        Plantillas.init();
    });
})(window, window.jQuery);