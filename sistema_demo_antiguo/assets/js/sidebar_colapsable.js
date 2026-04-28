(function () {
    'use strict';

    var claseColapsado = 'sidebar-colapsado';
    var storageKey = 'demo_sidebar_colapsado';

    document.addEventListener('DOMContentLoaded', function () {
        var boton = document.getElementById('btnColapsarSidebar');
        var icono = boton ? boton.querySelector('.sidebar-collapse-icon') : null;

        if (!boton) {
            return;
        }

        aplicarEstadoInicial();
        actualizarBotonSidebar();

        boton.addEventListener('click', function () {
            if (esVistaMovil()) {
                document.body.classList.remove(claseColapsado);
                actualizarBotonSidebar();
                return;
            }

            document.body.classList.toggle(claseColapsado);
            guardarPreferencia();
            actualizarBotonSidebar();
        });

        window.addEventListener('resize', function () {
            if (esVistaMovil()) {
                document.body.classList.remove(claseColapsado);
            }

            actualizarBotonSidebar();
        });

        function aplicarEstadoInicial() {
            if (esVistaMovil()) {
                document.body.classList.remove(claseColapsado);
                return;
            }

            if (leerPreferenciaColapsado()) {
                document.body.classList.add(claseColapsado);
            }
        }

        function guardarPreferencia() {
            try {
                window.localStorage.setItem(storageKey, document.body.classList.contains(claseColapsado) ? '1' : '0');
            } catch (error) {
                return;
            }
        }

        function leerPreferenciaColapsado() {
            try {
                return window.localStorage.getItem(storageKey) === '1';
            } catch (error) {
                return false;
            }
        }

        function actualizarBotonSidebar() {
            var estaColapsado = document.body.classList.contains(claseColapsado);

            if (estaColapsado) {
                if (icono) {
                    icono.textContent = '>';
                }

                boton.setAttribute('title', 'Expandir menú');
                boton.setAttribute('aria-label', 'Expandir menú');
                boton.setAttribute('aria-pressed', 'true');
                return;
            }

            if (icono) {
                icono.textContent = '<';
            }

            boton.setAttribute('title', 'Contraer menú');
            boton.setAttribute('aria-label', 'Contraer menú');
            boton.setAttribute('aria-pressed', 'false');
        }

        function esVistaMovil() {
            return window.matchMedia('(max-width: 820px)').matches;
        }
    });
})();