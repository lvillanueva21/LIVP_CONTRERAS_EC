(function () {
    'use strict';

    var appData = {
        clientes: [
            {
                codigo: 1129,
                tipo: 'Empresa',
                nombre: 'EMPRESA DE TRANSPORTES TUBOS DE CARTON SAC',
                estado: 'Activo'
            },
            {
                codigo: 1130,
                tipo: 'Empresa',
                nombre: 'COMERCIALIZADORA LOS ANDES SAC',
                estado: 'Activo'
            },
            {
                codigo: 1131,
                tipo: 'Persona natural',
                nombre: 'JUAN CARLOS RAMOS PAREDES',
                estado: 'Activo'
            }
        ],
        servicios: [
            {
                codigo: 1,
                categoria: 'Servicios de contabilidad',
                nombre: 'HONORARIOS',
                estado: 'Activo'
            },
            {
                codigo: 2,
                categoria: 'Servicios de contabilidad',
                nombre: 'AFP',
                estado: 'Activo'
            },
            {
                codigo: 3,
                categoria: 'Servicios de contabilidad',
                nombre: 'DECLARACIÓN ANUAL DEL IMPUESTO A LA RENTA',
                estado: 'Activo'
            },
            {
                codigo: 4,
                categoria: 'Otros servicios o trámites',
                nombre: 'TRÁMITE SUNAT',
                estado: 'Activo'
            }
        ],
        plantillas: [
            {
                codigo: 1,
                nombre: 'Recibo horizontal completo',
                orientacion: 'Horizontal',
                estado: 'Activa'
            },
            {
                codigo: 2,
                nombre: 'Recibo vertical simple',
                orientacion: 'Vertical',
                estado: 'Activa'
            },
            {
                codigo: 3,
                nombre: 'Recibo sin logo',
                orientacion: 'Horizontal',
                estado: 'Activa'
            }
        ],
        recibos: [],
        auditoria: []
    };

    window.appDemoData = appData;

    document.addEventListener('DOMContentLoaded', function () {
        iniciarNavegacion();
        iniciarSidebarMobile();
        iniciarModales();
        iniciarBotonesDemo();
        pintarDashboard();
        pintarVistaRapida();
        pintarDatosBase();
        pintarAuditoria();

        mostrarNotificacion(
            'info',
            'Demo cargado',
            'La primera implementación visual del generador está lista.'
        );
    });

    function iniciarNavegacion() {
        var links = document.querySelectorAll('[data-section]');
        var accesosDirectos = document.querySelectorAll('[data-section-go]');

        for (var i = 0; i < links.length; i++) {
            links[i].addEventListener('click', function () {
                cambiarSeccion(this.getAttribute('data-section'));
                cerrarSidebarMobile();
            });
        }

        for (var j = 0; j < accesosDirectos.length; j++) {
            accesosDirectos[j].addEventListener('click', function () {
                cambiarSeccion(this.getAttribute('data-section-go'));
                cerrarSidebarMobile();
            });
        }
    }

    function cambiarSeccion(nombreSeccion) {
        var secciones = document.querySelectorAll('.section-page');
        var links = document.querySelectorAll('.sidebar__link');
        var idSeccion = 'section-' + nombreSeccion;

        for (var i = 0; i < secciones.length; i++) {
            secciones[i].classList.remove('activo');
        }

        for (var j = 0; j < links.length; j++) {
            links[j].classList.remove('activo');
        }

        var seccionActiva = document.getElementById(idSeccion);
        var linkActivo = document.querySelector('[data-section="' + nombreSeccion + '"]');

        if (seccionActiva) {
            seccionActiva.classList.add('activo');
        }

        if (linkActivo) {
            linkActivo.classList.add('activo');
        }
    }

    function iniciarSidebarMobile() {
        var btnAbrirSidebar = document.getElementById('btnAbrirSidebar');
        var overlayMobile = document.getElementById('overlayMobile');

        if (btnAbrirSidebar) {
            btnAbrirSidebar.addEventListener('click', function () {
                var sidebar = document.getElementById('sidebar');

                if (sidebar) {
                    sidebar.classList.add('abierto');
                }

                if (overlayMobile) {
                    overlayMobile.classList.add('activo');
                }
            });
        }

        if (overlayMobile) {
            overlayMobile.addEventListener('click', function () {
                cerrarSidebarMobile();
            });
        }
    }

    function cerrarSidebarMobile() {
        var sidebar = document.getElementById('sidebar');
        var overlayMobile = document.getElementById('overlayMobile');

        if (sidebar) {
            sidebar.classList.remove('abierto');
        }

        if (overlayMobile) {
            overlayMobile.classList.remove('activo');
        }
    }

    function iniciarModales() {
        var botonesAbrir = document.querySelectorAll('[data-open-modal]');
        var botonesCerrar = document.querySelectorAll('[data-close-modal]');

        for (var i = 0; i < botonesAbrir.length; i++) {
            botonesAbrir[i].addEventListener('click', function () {
                abrirModal(this.getAttribute('data-open-modal'));
            });
        }

        for (var j = 0; j < botonesCerrar.length; j++) {
            botonesCerrar[j].addEventListener('click', function () {
                cerrarModal(this.getAttribute('data-close-modal'));
            });
        }

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') {
                cerrarTodosLosModales();
            }
        });

        var modales = document.querySelectorAll('.modal');

        for (var k = 0; k < modales.length; k++) {
            modales[k].addEventListener('click', function (evento) {
                if (evento.target === this) {
                    this.classList.remove('activo');
                }
            });
        }
    }

    function abrirModal(idModal) {
        var modal = document.getElementById(idModal);

        if (modal) {
            modal.classList.add('activo');
        }
    }

    function cerrarModal(idModal) {
        var modal = document.getElementById(idModal);

        if (modal) {
            modal.classList.remove('activo');
        }
    }

    function cerrarTodosLosModales() {
        var modales = document.querySelectorAll('.modal');

        for (var i = 0; i < modales.length; i++) {
            modales[i].classList.remove('activo');
        }
    }

    function iniciarBotonesDemo() {
        var btnDemoNotificacion = document.getElementById('btnDemoNotificacion');
        var btnGuardarPersonalizacionDemo = document.getElementById('btnGuardarPersonalizacionDemo');
        var btnAgregarAuditoriaDemo = document.getElementById('btnAgregarAuditoriaDemo');

        if (btnDemoNotificacion) {
            btnDemoNotificacion.addEventListener('click', function () {
                mostrarNotificacion(
                    'success',
                    'Notificación correcta',
                    'Este es el estilo base con cierre manual y autocierre en 5 segundos.'
                );
            });
        }

        if (btnGuardarPersonalizacionDemo) {
            btnGuardarPersonalizacionDemo.addEventListener('click', function () {
                agregarAuditoria(
                    'Configuración personalizada',
                    'Se guardó una personalización demo del estudio contable.'
                );

                mostrarNotificacion(
                    'success',
                    'Personalización guardada',
                    'Los datos fueron actualizados temporalmente en la demo.'
                );

                pintarAuditoria();
                pintarDashboard();
            });
        }

        if (btnAgregarAuditoriaDemo) {
            btnAgregarAuditoriaDemo.addEventListener('click', function () {
                agregarAuditoria(
                    'Acción demo',
                    'Se registró una acción temporal desde el módulo de auditoría.'
                );

                mostrarNotificacion(
                    'info',
                    'Auditoría actualizada',
                    'Se agregó un nuevo registro temporal.'
                );

                pintarAuditoria();
                pintarDashboard();
            });
        }
    }

    function pintarDashboard() {
        asignarTexto('statClientes', appData.clientes.length);
        asignarTexto('statServicios', appData.servicios.length);
        asignarTexto('statPlantillas', appData.plantillas.length);
        asignarTexto('statRecibos', appData.recibos.length);
    }

    function pintarVistaRapida() {
        var tbody = document.getElementById('tablaVistaRapida');

        if (!tbody) {
            return;
        }

        var html = '';

        html += '<tr>';
        html += '<td>Cliente</td>';
        html += '<td>' + escaparHtml(appData.clientes[0].nombre) + '</td>';
        html += '<td><span class="badge badge--success">Activo</span></td>';
        html += '</tr>';

        html += '<tr>';
        html += '<td>Servicio</td>';
        html += '<td>' + escaparHtml(appData.servicios[0].nombre) + '</td>';
        html += '<td><span class="badge badge--success">Activo</span></td>';
        html += '</tr>';

        html += '<tr>';
        html += '<td>Plantilla</td>';
        html += '<td>' + escaparHtml(appData.plantillas[0].nombre) + '</td>';
        html += '<td><span class="badge badge--info">Demo</span></td>';
        html += '</tr>';

        tbody.innerHTML = html;
    }

    function pintarDatosBase() {
        var tbody = document.getElementById('tablaDatosBase');

        if (!tbody) {
            return;
        }

        var html = '';

        for (var i = 0; i < appData.clientes.length; i++) {
            html += '<tr>';
            html += '<td>' + appData.clientes[i].codigo + '</td>';
            html += '<td>' + escaparHtml(appData.clientes[i].tipo) + '</td>';
            html += '<td>' + escaparHtml(appData.clientes[i].nombre) + '</td>';
            html += '<td><span class="badge badge--success">' + escaparHtml(appData.clientes[i].estado) + '</span></td>';
            html += '</tr>';
        }

        for (var j = 0; j < appData.servicios.length; j++) {
            html += '<tr>';
            html += '<td>S-' + appData.servicios[j].codigo + '</td>';
            html += '<td>' + escaparHtml(appData.servicios[j].categoria) + '</td>';
            html += '<td>' + escaparHtml(appData.servicios[j].nombre) + '</td>';
            html += '<td><span class="badge badge--success">' + escaparHtml(appData.servicios[j].estado) + '</span></td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
    }

    function agregarAuditoria(tipo, descripcion) {
        var ahora = new Date();

        appData.auditoria.unshift({
            fecha: formatearFechaHora(ahora),
            tipo: tipo,
            descripcion: descripcion,
            usuario: 'Gerencia'
        });
    }

    function pintarAuditoria() {
        var tbody = document.getElementById('tablaAuditoria');

        if (!tbody) {
            return;
        }

        if (appData.auditoria.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4">Todavía no hay acciones registradas.</td></tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.auditoria.length; i++) {
            html += '<tr>';
            html += '<td>' + escaparHtml(appData.auditoria[i].fecha) + '</td>';
            html += '<td>' + escaparHtml(appData.auditoria[i].tipo) + '</td>';
            html += '<td>' + escaparHtml(appData.auditoria[i].descripcion) + '</td>';
            html += '<td>' + escaparHtml(appData.auditoria[i].usuario) + '</td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
    }

    function mostrarNotificacion(tipo, titulo, mensaje) {
        var contenedor = document.getElementById('toastContainer');

        if (!contenedor) {
            return;
        }

        var toast = document.createElement('div');
        var claseTipo = 'toast--info';

        if (tipo === 'success') {
            claseTipo = 'toast--success';
        }

        if (tipo === 'error') {
            claseTipo = 'toast--error';
        }

        if (tipo === 'warning') {
            claseTipo = 'toast--warning';
        }

        if (tipo === 'info') {
            claseTipo = 'toast--info';
        }

        toast.className = 'toast ' + claseTipo;

        toast.innerHTML =
            '<div>' +
                '<h4>' + escaparHtml(titulo) + '</h4>' +
                '<p>' + escaparHtml(mensaje) + '</p>' +
            '</div>' +
            '<button type="button" class="toast__close" aria-label="Cerrar notificación">×</button>';

        contenedor.appendChild(toast);

        var botonCerrar = toast.querySelector('.toast__close');

        if (botonCerrar) {
            botonCerrar.addEventListener('click', function () {
                eliminarToast(toast);
            });
        }

        setTimeout(function () {
            eliminarToast(toast);
        }, 5000);
    }

    function eliminarToast(toast) {
        if (!toast || !toast.parentNode) {
            return;
        }

        toast.style.opacity = '0';
        toast.style.transform = 'translateX(16px)';

        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 180);
    }

    function asignarTexto(id, valor) {
        var elemento = document.getElementById(id);

        if (elemento) {
            elemento.textContent = valor;
        }
    }

    function formatearFechaHora(fecha) {
        var dia = agregarCero(fecha.getDate());
        var mes = agregarCero(fecha.getMonth() + 1);
        var anio = fecha.getFullYear();
        var horas = agregarCero(fecha.getHours());
        var minutos = agregarCero(fecha.getMinutes());
        var segundos = agregarCero(fecha.getSeconds());

        return dia + '/' + mes + '/' + anio + ' ' + horas + ':' + minutos + ':' + segundos;
    }

    function agregarCero(numero) {
        return numero < 10 ? '0' + numero : String(numero);
    }

    function escaparHtml(texto) {
        var div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    window.mostrarNotificacion = mostrarNotificacion;
    window.abrirModal = abrirModal;
    window.cerrarModal = cerrarModal;
    window.cambiarSeccion = cambiarSeccion;
})();