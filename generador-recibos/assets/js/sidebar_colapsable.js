(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var boton = document.getElementById('btnColapsarSidebar');

        if (!boton) {
            return;
        }

        boton.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-colapsado');

            if (document.body.classList.contains('sidebar-colapsado')) {
                boton.textContent = 'Expandir menú';
            } else {
                boton.textContent = 'Colapsar menú';
            }
        });
    });
})();