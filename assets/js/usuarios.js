(function (window, $) {
    'use strict';

    var Usuarios = {
        ajaxUrl: 'modules/usuarios/ajax.php',
        confirmCallback: null,

        init: function () {
            this.bindEventos();
        },

        bindEventos: function () {
            $('#btnNuevoUsuario').on('click', function () {
                Usuarios.limpiarFormulario();
                $('#modalUsuarioTitulo').text('Nuevo usuario');
                $('.usuario-clave-row').show();
                $('#usuarioClave').prop('required', true);
                AppUI.openModal('#modalUsuario');
            });

            $('#formUsuario').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: Usuarios.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            $('#usuariosTablaContainer').html(response.html);
                            AppTablas.refresh();
                            AppUI.closeModal('#modalUsuario');
                        }
                    }
                });
            });

            $('#formUsuarioClave').on('submit', function (event) {
                event.preventDefault();

                AppAjax.sendForm(this, {
                    url: Usuarios.ajaxUrl,
                    onSuccess: function (response) {
                        if (response && response.ok) {
                            $('#usuariosTablaContainer').html(response.html);
                            AppTablas.refresh();
                            AppUI.closeModal('#modalUsuarioClave');
                        }
                    }
                });
            });

            $(document).on('click', '.btnEditarUsuario', function () {
                Usuarios.editar($(this).attr('data-id'));
            });

            $(document).on('click', '.btnCambiarClaveUsuario', function () {
                var id = $(this).attr('data-id');
                Usuarios.prepararModalClave(id);
            });

            $(document).on('click', '.btnCambiarEstadoUsuario', function () {
                var id = $(this).attr('data-id');
                var estado = parseInt($(this).attr('data-estado'), 10);
                var texto = estado === 1 ? 'Se desactivara este usuario.' : 'Se activara este usuario.';

                Usuarios.confirmarEstado(texto, function () {
                    Usuarios.cambiarEstado(id);
                });
            });

            $('#btnConfirmarUsuarioEstado').on('click', function () {
                AppUI.closeModal('#modalConfirmarUsuarioEstado');

                if (typeof Usuarios.confirmCallback === 'function') {
                    Usuarios.confirmCallback();
                }

                Usuarios.confirmCallback = null;
            });

            $(document).on('click', '[data-toggle-pass]', function () {
                var selector = $(this).attr('data-toggle-pass');
                Usuarios.togglePass(selector, this);
            });
        },

        limpiarFormulario: function () {
            $('#formUsuario')[0].reset();
            $('#usuarioId').val('0');
            $('#usuarioEstado').val('1');
            $('#usuarioClave').val('').prop('required', true);
            AppUI.refresh();
        },

        editar: function (id) {
            AppAjax.get(this.ajaxUrl, {
                action: 'obtener_usuario',
                id: id
            }, {
                onSuccess: function (response) {
                    if (!response || !response.ok) {
                        return;
                    }

                    var u = response.usuario;

                    Usuarios.limpiarFormulario();
                    $('#modalUsuarioTitulo').text('Editar usuario');
                    $('#usuarioId').val(u.id);
                    $('#usuarioDni').val(u.dni);
                    $('#usuarioNombres').val(u.nombres);
                    $('#usuarioApellidos').val(u.apellidos);
                    $('#usuarioEstado').val(String(u.estado));
                    $('#usuarioClave').val('').prop('required', false);
                    $('.usuario-clave-row').hide();

                    AppUI.refresh();
                    AppUI.openModal('#modalUsuario');
                }
            });
        },

        prepararModalClave: function (id) {
            $('#formUsuarioClave')[0].reset();
            $('#usuarioClaveId').val(id);
            AppUI.refresh();
            AppUI.openModal('#modalUsuarioClave');
        },

        cambiarEstado: function (id) {
            AppAjax.post(this.ajaxUrl, {
                action: 'cambiar_estado_usuario',
                id: id
            }, {
                onSuccess: function (response) {
                    if (response && response.ok) {
                        $('#usuariosTablaContainer').html(response.html);
                        AppTablas.refresh();
                    }
                }
            });
        },

        confirmarEstado: function (texto, callback) {
            $('#modalConfirmarUsuarioEstadoTexto').text(texto);
            this.confirmCallback = callback;
            AppUI.openModal('#modalConfirmarUsuarioEstado');
        },

        togglePass: function (selector, trigger) {
            var input = $(selector);

            if (input.length === 0) {
                return;
            }

            var isPass = input.attr('type') === 'password';
            input.attr('type', isPass ? 'text' : 'password');

            var icon = $(trigger).find('i');
            icon.toggleClass('fa-eye', !isPass);
            icon.toggleClass('fa-eye-slash', isPass);
        }
    };

    window.Usuarios = Usuarios;

    $(function () {
        Usuarios.init();
    });
})(window, window.jQuery);
