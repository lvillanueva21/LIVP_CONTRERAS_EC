(function (window, $) {
    'use strict';

    function setError(message) {
        var box = $('#registroErrorCliente');

        if (!message) {
            box.addClass('d-none').text('');
            return;
        }

        box.removeClass('d-none').text(message);
    }

    $(function () {
        $(document).on('click', '[data-toggle-pass]', function () {
            var selector = $(this).attr('data-toggle-pass');
            var input = $(selector);

            if (input.length === 0) {
                return;
            }

            var current = input.attr('type');
            input.attr('type', current === 'password' ? 'text' : 'password');
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        $('#formRegistroTemporal').on('submit', function () {
            var dni = String($('input[name="dni"]').val() || '').trim();
            var clave = String($('input[name="clave"]').val() || '');
            var clave2 = String($('input[name="clave_repetir"]').val() || '');

            setError('');

            if (!/^\d{8}$/.test(dni)) {
                setError('El DNI debe tener exactamente 8 dígitos numéricos.');
                return false;
            }

            if (clave.length < 8) {
                setError('La contraseña debe tener al menos 8 caracteres.');
                return false;
            }

            if (clave !== clave2) {
                setError('La confirmación de contraseña no coincide.');
                return false;
            }

            return true;
        });
    });
})(window, window.jQuery);
