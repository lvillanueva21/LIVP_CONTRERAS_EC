(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var boton = document.getElementById('btnColapsarSidebar');
        var icono = boton ? boton.querySelector('.sidebar-collapse-icon') : null;

        if (!boton || !icono) {
            return;
        }

        actualizarBotonSidebar();

        boton.addEventListener('click', function () {
            if (esVistaMovil()) {
                document.body.classList.remove('sidebar-colapsado');
                actualizarBotonSidebar();
                return;
            }

            document.body.classList.toggle('sidebar-colapsado');
            actualizarBotonSidebar();
        });

        window.addEventListener('resize', function () {
            if (esVistaMovil()) {
                document.body.classList.remove('sidebar-colapsado');
            }

            actualizarBotonSidebar();
        });

        function actualizarBotonSidebar() {
            var estaColapsado = document.body.classList.contains('sidebar-colapsado');

            if (estaColapsado) {
                icono.textContent = '›';
                boton.setAttribute('title', 'Expandir menú');
                boton.setAttribute('aria-label', 'Expandir menú');
                boton.setAttribute('aria-pressed', 'true');
            } else {
                icono.textContent = '‹';
                boton.setAttribute('title', 'Contraer menú');
                boton.setAttribute('aria-label', 'Contraer menú');
                boton.setAttribute('aria-pressed', 'false');
            }
        }

        function esVistaMovil() {
            return window.matchMedia('(max-width: 820px)').matches;
        }
    });
})();