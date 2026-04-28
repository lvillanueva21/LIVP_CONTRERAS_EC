(function (window, $) {
    'use strict';

    var Personalizacion = {
        ajaxUrl: 'modules/personalizacion/ajax.php',

        init: function () {
            this.bindEventos();
            this.actualizarPreview();
        },

        bindEventos: function () {
            $('#formPersonalizacion').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: Personalizacion.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            if (response.logo_url) {
                                Personalizacion.setLogoPreview(response.logo_url);
                            }

                            Personalizacion.actualizarPreview();
                        }
                    }
                });
            });

            $('#formPersonalizacion input, #formPersonalizacion textarea, #formPersonalizacion select').on('input change', function () {
                Personalizacion.actualizarPreview();
            });

            $('#pzLogo').on('change', function () {
                Personalizacion.previewArchivo(this);
            });
        },

        actualizarPreview: function () {
            var nombre = $('#pzNombreComercial').val();
            var razon = $('#pzRazonSocial').val();
            var ruc = $('#pzRuc').val();
            var rubro = $('#pzRubro').val();
            var direccion = $('#pzDireccion').val();
            var celular = $('#pzCelular').val();
            var correo = $('#pzCorreo').val();
            var pie = $('#pzPiePagina').val();
            var colorTipo = $('#pzColorTipo').val();
            var colorPrimario = $('#pzColorPrimario').val();
            var colorSecundario = $('#pzColorSecundario').val();
            var logoTipo = $('#pzLogoTipo').val();
            var zoom = parseFloat($('#pzLogoZoom').val() || '1');
            var posX = parseFloat($('#pzLogoPosX').val() || '0');
            var posY = parseFloat($('#pzLogoPosY').val() || '0');

            $('#pzPreviewNombre').text(nombre || 'Nombre comercial');
            $('#pzPreviewRazon').text(razon || 'Razón social');
            $('#pzPreviewRuc').text(ruc || 'RUC');
            $('#pzPreviewRubro').text(rubro || 'Rubro');
            $('#pzPreviewDireccion').text(direccion || 'Dirección');
            $('#pzPreviewContacto').text($.trim(celular + ' ' + correo) || 'Contacto');
            $('#pzPreviewPie').text(pie || '');

            if (colorTipo === 'Degradado') {
                $('#pzPreviewHeader').css('background', 'linear-gradient(135deg, ' + colorPrimario + ', ' + colorSecundario + ')');
            } else {
                $('#pzPreviewHeader').css('background', colorPrimario);
            }

            $('#pzPreviewLogoBox')
                .removeClass('pz-logo-cuadrado pz-logo-rectangular pz-logo-banner')
                .addClass('pz-logo-' + String(logoTipo).toLowerCase());

            $('#pzPreviewLogo').css('transform', 'scale(' + zoom + ') translate(' + posX + 'px, ' + posY + 'px)');
        },

        previewArchivo: function (input) {
            if (!input.files || !input.files[0]) {
                return;
            }

            var reader = new FileReader();

            reader.onload = function (event) {
                Personalizacion.setLogoPreview(event.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        },

        setLogoPreview: function (url) {
            $('#pzPreviewLogoPlaceholder').hide();
            $('#pzPreviewLogo').attr('src', url).show();
        }
    };

    window.Personalizacion = Personalizacion;

    $(function () {
        Personalizacion.init();
    });
})(window, window.jQuery);