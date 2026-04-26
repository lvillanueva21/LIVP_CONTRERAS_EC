(function () {
    'use strict';

    var appData = window.appDemoData || {
        clientes: [],
        servicios: [],
        plantillas: [],
        recibos: [],
        auditoria: []
    };

    document.addEventListener('DOMContentLoaded', function () {
        prepararExportacionRecibos();
        iniciarEventosExportacionRecibos();
        pintarRecibosGenerados();
        pintarDashboardFinal();
        pintarAuditoriaFinal();
    });

    function prepararExportacionRecibos() {
        if (!appData.recibos) {
            appData.recibos = [];
        }

        if (!appData.auditoria) {
            appData.auditoria = [];
        }

        parchearPushRecibos();
        parchearUnshiftAuditoria();

        window.appDemoData = appData;
    }

    function parchearPushRecibos() {
        if (!appData.recibos || appData.recibos._parcheExportacionActivo) {
            return;
        }

        var pushOriginal = appData.recibos.push;

        appData.recibos.push = function () {
            var resultado = pushOriginal.apply(this, arguments);

            setTimeout(function () {
                pintarRecibosGenerados();
                pintarDashboardFinal();
                pintarAuditoriaFinal();
            }, 50);

            return resultado;
        };

        appData.recibos._parcheExportacionActivo = true;
    }

    function parchearUnshiftAuditoria() {
        if (!appData.auditoria || appData.auditoria._parcheExportacionActivo) {
            return;
        }

        var unshiftOriginal = appData.auditoria.unshift;

        appData.auditoria.unshift = function () {
            var resultado = unshiftOriginal.apply(this, arguments);

            setTimeout(function () {
                pintarDashboardFinal();
                pintarAuditoriaFinal();
            }, 50);

            return resultado;
        };

        appData.auditoria._parcheExportacionActivo = true;
    }

    function iniciarEventosExportacionRecibos() {
        var tabla = document.getElementById('tablaRecibosGenerados');

        if (!tabla) {
            return;
        }

        tabla.onclick = function (evento) {
            var boton = obtenerBotonAccionRecibo(evento.target);

            if (!boton) {
                return;
            }

            var accion = boton.getAttribute('data-recibo-accion');
            var codigo = boton.getAttribute('data-recibo-codigo');

            if (accion === 'ver') {
                verReciboGenerado(codigo);
            }

            if (accion === 'abrir') {
                abrirVistaPreviaEnPestana(codigo);
            }

            if (accion === 'jpg') {
                descargarReciboJpg(codigo);
            }

            if (accion === 'pdf') {
                descargarReciboPdf(codigo);
            }

            if (accion === 'duplicar') {
                duplicarReciboTemporal(codigo);
            }

            if (accion === 'eliminar') {
                eliminarReciboTemporal(codigo);
            }
        };
    }

    function obtenerBotonAccionRecibo(elemento) {
        while (elemento && elemento !== document) {
            if (elemento.getAttribute && elemento.getAttribute('data-recibo-accion')) {
                return elemento;
            }

            elemento = elemento.parentNode;
        }

        return null;
    }

    function pintarRecibosGenerados() {
        var tbody = document.getElementById('tablaRecibosGenerados');

        if (!tbody) {
            return;
        }

        if (!appData.recibos || appData.recibos.length === 0) {
            tbody.innerHTML =
                '<tr>' +
                    '<td colspan="7" class="estado-vacio">' +
                        '<strong>No hay recibos temporales generados</strong>' +
                        'Genera un recibo temporal para verlo en esta lista.' +
                    '</td>' +
                '</tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.recibos.length; i++) {
            html += obtenerFilaRecibo(appData.recibos[i]);
        }

        tbody.innerHTML = html;
    }

    function obtenerFilaRecibo(recibo) {
        var total = recibo.totales && recibo.totales.totalGeneral ? recibo.totales.totalGeneral : 0;
        var html = '';

        html += '<tr>';
        html += '<td><span class="codigo-pill">' + escaparHtml(recibo.codigo) + '</span></td>';
        html += '<td>' + escaparHtml(recibo.fecha) + '</td>';
        html += '<td>' + escaparHtml(recibo.clienteNombre) + '</td>';
        html += '<td>' + escaparHtml(recibo.plantillaNombre) + '</td>';
        html += '<td class="monto-soles">' + formatearSoles(total) + '</td>';
        html += '<td><span class="badge badge--info">' + escaparHtml(recibo.estado || 'Temporal') + '</span></td>';
        html += '<td>';
        html += '<div class="recibo-acciones">';
        html += '<button type="button" class="btn btn--light btn--sm" data-recibo-accion="ver" data-recibo-codigo="' + escaparHtml(recibo.codigo) + '">Ver</button>';
        html += '<button type="button" class="btn btn--light btn--sm" data-recibo-accion="abrir" data-recibo-codigo="' + escaparHtml(recibo.codigo) + '">Abrir vista previa</button>';
        html += '<button type="button" class="btn btn--success btn--sm" data-recibo-accion="jpg" data-recibo-codigo="' + escaparHtml(recibo.codigo) + '">Descargar JPG</button>';
        html += '<button type="button" class="btn btn--primary btn--sm" data-recibo-accion="pdf" data-recibo-codigo="' + escaparHtml(recibo.codigo) + '">Descargar PDF</button>';
        html += '<button type="button" class="btn btn--warning btn--sm" data-recibo-accion="duplicar" data-recibo-codigo="' + escaparHtml(recibo.codigo) + '">Duplicar</button>';
        html += '<button type="button" class="btn btn--danger btn--sm" data-recibo-accion="eliminar" data-recibo-codigo="' + escaparHtml(recibo.codigo) + '">Eliminar temporal</button>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';

        return html;
    }

    function verReciboGenerado(codigo) {
        var recibo = buscarReciboPorCodigo(codigo);
        var contenedor = document.getElementById('contenidoReciboGenerado');

        if (!recibo || !contenedor) {
            mostrarAviso('error', 'Recibo no encontrado', 'No se encontró el recibo temporal seleccionado.');
            return;
        }

        contenedor.innerHTML = recibo.htmlVista;

        registrarAuditoriaExportacion('Vista previa abierta', 'Se abrió la vista del recibo temporal ' + recibo.codigo + '.');
        abrirModalSeguro('modalVerReciboGenerado');
    }

    function abrirVistaPreviaEnPestana(codigo) {
        var recibo = buscarReciboPorCodigo(codigo);

        if (!recibo) {
            mostrarAviso('error', 'Recibo no encontrado', 'No se encontró el recibo temporal seleccionado.');
            return;
        }

        var ventana = window.open('', '_blank');

        if (!ventana) {
            mostrarAviso('warning', 'Ventana bloqueada', 'El navegador bloqueó la nueva pestaña. Permite ventanas emergentes para este sitio.');
            return;
        }

        ventana.document.open();
        ventana.document.write(obtenerHtmlImprimible(recibo));
        ventana.document.close();

        registrarAuditoriaExportacion('Vista previa abierta', 'Se abrió una nueva pestaña con el recibo temporal ' + recibo.codigo + '.');
        mostrarAviso('info', 'Vista previa abierta', 'Se abrió el recibo en una nueva pestaña.');
    }

    function descargarReciboJpg(codigo) {
        var recibo = buscarReciboPorCodigo(codigo);

        if (!recibo) {
            mostrarAviso('error', 'Recibo no encontrado', 'No se encontró el recibo temporal seleccionado.');
            return;
        }

        if (typeof window.html2canvas !== 'function') {
            mostrarAviso('error', 'Librería no disponible', 'No se cargó html2canvas desde CDN.');
            return;
        }

        var contenedor = crearContenedorTemporalRecibo(recibo);

        document.body.classList.add('exportando-recibo');

        window.html2canvas(contenedor, {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true
        }).then(function (canvas) {
            var enlace = document.createElement('a');

            enlace.href = canvas.toDataURL('image/jpeg', 0.95);
            enlace.download = obtenerNombreArchivo(recibo, 'jpg');
            document.body.appendChild(enlace);
            enlace.click();
            document.body.removeChild(enlace);

            registrarAuditoriaExportacion('JPG descargado', 'Se descargó el JPG del recibo temporal ' + recibo.codigo + '.');
            mostrarAviso('success', 'JPG descargado', 'El recibo se descargó como imagen JPG.');
        }).catch(function () {
            mostrarAviso('error', 'Error al generar JPG', 'No se pudo generar la imagen del recibo.');
        }).finally(function () {
            eliminarContenedorTemporalRecibo(contenedor);
            document.body.classList.remove('exportando-recibo');
        });
    }

    function descargarReciboPdf(codigo) {
        var recibo = buscarReciboPorCodigo(codigo);

        if (!recibo) {
            mostrarAviso('error', 'Recibo no encontrado', 'No se encontró el recibo temporal seleccionado.');
            return;
        }

        if (typeof window.html2pdf !== 'function') {
            mostrarAviso('error', 'Librería no disponible', 'No se cargó html2pdf.js desde CDN.');
            return;
        }

        var contenedor = crearContenedorTemporalRecibo(recibo);
        var orientacion = obtenerOrientacionPdf(recibo);
        var opciones = {
            margin: 8,
            filename: obtenerNombreArchivo(recibo, 'pdf'),
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2,
                backgroundColor: '#ffffff',
                useCORS: true
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: orientacion
            }
        };

        document.body.classList.add('exportando-recibo');

        window.html2pdf().set(opciones).from(contenedor).save().then(function () {
            registrarAuditoriaExportacion('PDF descargado', 'Se descargó el PDF del recibo temporal ' + recibo.codigo + '.');
            mostrarAviso('success', 'PDF descargado', 'El recibo se descargó como PDF.');
        }).catch(function () {
            mostrarAviso('error', 'Error al generar PDF', 'No se pudo generar el PDF del recibo.');
        }).finally(function () {
            eliminarContenedorTemporalRecibo(contenedor);
            document.body.classList.remove('exportando-recibo');
        });
    }

    function duplicarReciboTemporal(codigo) {
        var recibo = buscarReciboPorCodigo(codigo);

        if (!recibo) {
            mostrarAviso('error', 'Recibo no encontrado', 'No se encontró el recibo temporal seleccionado.');
            return;
        }

        var nuevoCodigo = obtenerSiguienteCodigoRecibo();
        var copia = {
            codigo: nuevoCodigo,
            fecha: formatearFechaHora(new Date()),
            clienteCodigo: recibo.clienteCodigo,
            clienteNombre: recibo.clienteNombre,
            plantillaCodigo: recibo.plantillaCodigo,
            plantillaNombre: recibo.plantillaNombre,
            cuentaCodigo: recibo.cuentaCodigo,
            conceptos: copiarConceptosRecibo(recibo.conceptos),
            totales: copiarTotalesRecibo(recibo.totales),
            estado: 'Temporal',
            htmlVista: reemplazarCodigoHtmlRecibo(recibo.htmlVista, recibo.codigo, nuevoCodigo)
        };

        appData.recibos.push(copia);

        registrarAuditoriaExportacion('Recibo duplicado', 'Se duplicó el recibo temporal ' + recibo.codigo + ' como ' + nuevoCodigo + '.');
        mostrarAviso('success', 'Recibo duplicado', 'Se duplicó el recibo temporal correctamente.');
    }

    function eliminarReciboTemporal(codigo) {
        var recibo = buscarReciboPorCodigo(codigo);

        if (!recibo) {
            mostrarAviso('error', 'Recibo no encontrado', 'No se encontró el recibo temporal seleccionado.');
            return;
        }

        if (!confirm('¿Eliminar temporalmente el recibo ' + recibo.codigo + '?')) {
            return;
        }

        for (var i = appData.recibos.length - 1; i >= 0; i--) {
            if (appData.recibos[i].codigo === codigo) {
                appData.recibos.splice(i, 1);
                break;
            }
        }

        registrarAuditoriaExportacion('Recibo eliminado', 'Se eliminó temporalmente el recibo ' + recibo.codigo + '.');
        pintarRecibosGenerados();
        pintarDashboardFinal();
        pintarAuditoriaFinal();

        mostrarAviso('success', 'Recibo eliminado', 'El recibo temporal fue eliminado correctamente.');
    }

    function crearContenedorTemporalRecibo(recibo) {
        var contenedor = document.createElement('div');

        contenedor.className = 'exportacion-recibo-captura';
        contenedor.innerHTML = recibo.htmlVista;

        document.body.appendChild(contenedor);

        return contenedor;
    }

    function eliminarContenedorTemporalRecibo(contenedor) {
        if (contenedor && contenedor.parentNode) {
            contenedor.parentNode.removeChild(contenedor);
        }
    }

    function obtenerHtmlImprimible(recibo) {
        var css = obtenerCssLocal();
        var titulo = 'Recibo ' + recibo.codigo;

        return '<!DOCTYPE html>' +
            '<html lang="es">' +
            '<head>' +
                '<meta charset="UTF-8">' +
                '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
                '<title>' + escaparHtml(titulo) + '</title>' +
                '<style>' +
                    css +
                    'body{background:#f8fafc;margin:0;padding:24px;font-family:Arial,Helvetica,sans-serif;}' +
                    '.barra-impresion{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:0 auto 18px;max-width:1120px;}' +
                    '.barra-impresion h1{font-size:18px;margin:0;color:#0f172a;}' +
                    '.barra-impresion button{border:0;border-radius:12px;background:#0f766e;color:#fff;padding:10px 14px;font-weight:800;cursor:pointer;}' +
                    '@media print{body{background:#fff;padding:0}.barra-impresion{display:none}.recibo-preview{box-shadow:none;border-radius:0}}' +
                '</style>' +
            '</head>' +
            '<body>' +
                '<div class="barra-impresion">' +
                    '<h1>' + escaparHtml(titulo) + '</h1>' +
                    '<button type="button" onclick="window.print()">Imprimir</button>' +
                '</div>' +
                recibo.htmlVista +
            '</body>' +
            '</html>';
    }

    function obtenerCssLocal() {
        var css = '';

        for (var i = 0; i < document.styleSheets.length; i++) {
            try {
                var reglas = document.styleSheets[i].cssRules;

                if (!reglas) {
                    continue;
                }

                for (var j = 0; j < reglas.length; j++) {
                    css += reglas[j].cssText + '\n';
                }
            } catch (error) {
                css += '';
            }
        }

        return css;
    }

    function pintarDashboardFinal() {
        var total = obtenerTotalAcumuladoRecibos();
        var ultimaAccion = obtenerUltimaAccion();

        asignarTexto('statClientes', appData.clientes ? appData.clientes.length : 0);
        asignarTexto('statServicios', appData.servicios ? appData.servicios.length : 0);
        asignarTexto('statPlantillas', appData.plantillas ? appData.plantillas.length : 0);
        asignarTexto('statRecibos', appData.recibos ? appData.recibos.length : 0);
        asignarTexto('statTotalAcumulado', formatearSoles(total));
        asignarTexto('statUltimaAccion', ultimaAccion);
    }

    function pintarAuditoriaFinal() {
        var tbody = document.getElementById('tablaAuditoria');

        if (!tbody) {
            return;
        }

        if (!appData.auditoria || appData.auditoria.length === 0) {
            tbody.innerHTML =
                '<tr>' +
                    '<td colspan="4" class="estado-vacio">' +
                        '<strong>Todavía no hay acciones registradas</strong>' +
                        'Las acciones temporales aparecerán aquí durante la sesión demo.' +
                    '</td>' +
                '</tr>';
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

    function registrarAuditoriaExportacion(tipo, descripcion) {
        if (!appData.auditoria) {
            appData.auditoria = [];
        }

        appData.auditoria.unshift({
            fecha: formatearFechaHora(new Date()),
            tipo: tipo,
            descripcion: descripcion,
            usuario: 'Gerencia'
        });

        pintarDashboardFinal();
        pintarAuditoriaFinal();
    }

    function obtenerTotalAcumuladoRecibos() {
        var total = 0;

        if (!appData.recibos) {
            return total;
        }

        for (var i = 0; i < appData.recibos.length; i++) {
            if (appData.recibos[i].totales && appData.recibos[i].totales.totalGeneral) {
                total += parseFloat(appData.recibos[i].totales.totalGeneral);
            }
        }

        if (isNaN(total)) {
            total = 0;
        }

        return total;
    }

    function obtenerUltimaAccion() {
        if (!appData.auditoria || appData.auditoria.length === 0) {
            return 'Sin acciones';
        }

        return appData.auditoria[0].tipo;
    }

    function buscarReciboPorCodigo(codigo) {
        if (!appData.recibos) {
            return null;
        }

        for (var i = 0; i < appData.recibos.length; i++) {
            if (appData.recibos[i].codigo === codigo) {
                return appData.recibos[i];
            }
        }

        return null;
    }

    function obtenerSiguienteCodigoRecibo() {
        var mayor = 0;

        if (!appData.recibos) {
            return 'REC-1';
        }

        for (var i = 0; i < appData.recibos.length; i++) {
            var partes = String(appData.recibos[i].codigo).split('-');
            var numero = parseInt(partes[partes.length - 1], 10);

            if (!isNaN(numero) && numero > mayor) {
                mayor = numero;
            }
        }

        return 'REC-' + (mayor + 1);
    }

    function copiarConceptosRecibo(conceptos) {
        var copia = [];

        if (!conceptos) {
            return copia;
        }

        for (var i = 0; i < conceptos.length; i++) {
            copia.push({
                codigo: conceptos[i].codigo,
                categoria: conceptos[i].categoria,
                servicioCodigo: conceptos[i].servicioCodigo,
                tipoPeriodo: conceptos[i].tipoPeriodo,
                mes: conceptos[i].mes,
                anio: conceptos[i].anio,
                fechaDesde: conceptos[i].fechaDesde,
                fechaHasta: conceptos[i].fechaHasta,
                descripcionBase: conceptos[i].descripcionBase,
                descripcionGenerada: conceptos[i].descripcionGenerada,
                descripcionEditable: conceptos[i].descripcionEditable,
                monto: conceptos[i].monto
            });
        }

        return copia;
    }

    function copiarTotalesRecibo(totales) {
        if (!totales) {
            return {
                serviciosContabilidad: 0,
                periodosPendientes: 0,
                aportacionesEmpleador: 0,
                otrosServicios: 0,
                totalGeneral: 0
            };
        }

        return {
            serviciosContabilidad: totales.serviciosContabilidad,
            periodosPendientes: totales.periodosPendientes,
            aportacionesEmpleador: totales.aportacionesEmpleador,
            otrosServicios: totales.otrosServicios,
            totalGeneral: totales.totalGeneral
        };
    }

    function reemplazarCodigoHtmlRecibo(html, codigoAnterior, codigoNuevo) {
        return String(html).replace(new RegExp(escaparRegExp(codigoAnterior), 'g'), codigoNuevo);
    }

    function obtenerOrientacionPdf(recibo) {
        var plantilla = buscarPlantillaPorCodigo(recibo.plantillaCodigo);

        if (plantilla && plantilla.orientacion === 'Horizontal') {
            return 'landscape';
        }

        if (plantilla && plantilla.orientacion === 'Vertical') {
            return 'portrait';
        }

        if (String(recibo.htmlVista).indexOf('recibo-preview--horizontal') >= 0) {
            return 'landscape';
        }

        return 'portrait';
    }

    function buscarPlantillaPorCodigo(codigo) {
        if (!appData.plantillas) {
            return null;
        }

        for (var i = 0; i < appData.plantillas.length; i++) {
            if (parseInt(appData.plantillas[i].codigo, 10) === parseInt(codigo, 10)) {
                return appData.plantillas[i];
            }
        }

        return null;
    }

    function obtenerNombreArchivo(recibo, extension) {
        return 'recibo-' + normalizarSlug(recibo.codigo) + '-' + normalizarSlug(recibo.clienteNombre) + '.' + extension;
    }

    function normalizarSlug(texto) {
        var limpio = String(texto || '')
            .toLowerCase()
            .replace(/[áàäâ]/g, 'a')
            .replace(/[éèëê]/g, 'e')
            .replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o')
            .replace(/[úùüû]/g, 'u')
            .replace(/ñ/g, 'n')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        if (limpio === '') {
            limpio = 'recibo';
        }

        return limpio;
    }

    function escaparRegExp(texto) {
        return String(texto).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function formatearFechaHora(fecha) {
        return agregarCero(fecha.getDate()) + '/' +
            agregarCero(fecha.getMonth() + 1) + '/' +
            fecha.getFullYear() + ' ' +
            agregarCero(fecha.getHours()) + ':' +
            agregarCero(fecha.getMinutes()) + ':' +
            agregarCero(fecha.getSeconds());
    }

    function agregarCero(numero) {
        return numero < 10 ? '0' + numero : String(numero);
    }

    function formatearSoles(numero) {
        var monto = parseFloat(numero);

        if (isNaN(monto)) {
            monto = 0;
        }

        return 'S/ ' + monto.toFixed(2);
    }

    function asignarTexto(id, texto) {
        var elemento = document.getElementById(id);

        if (elemento) {
            elemento.textContent = texto;
        }
    }

    function escaparHtml(texto) {
        var div = document.createElement('div');
        div.textContent = texto === null || texto === undefined ? '' : String(texto);
        return div.innerHTML;
    }

    function abrirModalSeguro(idModal) {
        if (typeof window.abrirModal === 'function') {
            window.abrirModal(idModal);
            return;
        }

        var modal = document.getElementById(idModal);

        if (modal) {
            modal.classList.add('activo');
        }
    }

    function mostrarAviso(tipo, titulo, mensaje) {
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion(tipo, titulo, mensaje);
            return;
        }

        alert(titulo + '\n' + mensaje);
    }

    window.refrescarCierreDemo = function () {
        pintarRecibosGenerados();
        pintarDashboardFinal();
        pintarAuditoriaFinal();
    };
})();