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
        var titulo = 'Recibo ' + recibo.codigo;

        return '<!DOCTYPE html>' +
            '<html lang="es">' +
            '<head>' +
                '<meta charset="UTF-8">' +
                '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
                '<title>' + escaparHtml(titulo) + '</title>' +
                '<style>' + obtenerCssImprimibleRecibo() + '</style>' +
            '</head>' +
            '<body>' +
                '<div class="barra-impresion">' +
                    '<h1>' + escaparHtml(titulo) + '</h1>' +
                    '<button type="button" onclick="window.print()">Imprimir</button>' +
                '</div>' +
                '<main class="pagina-recibo">' +
                    recibo.htmlVista +
                '</main>' +
            '</body>' +
            '</html>';
    }

    function obtenerCssImprimibleRecibo() {
        return '' +
            ':root{' +
                '--color-primary:#0f766e;' +
                '--color-primary-dark:#115e59;' +
                '--color-secondary:#14b8a6;' +
                '--color-dark:#0f172a;' +
                '--color-muted:#64748b;' +
                '--color-border:#e2e8f0;' +
                '--color-white:#ffffff;' +
                '--shadow-soft:0 18px 45px rgba(15,23,42,0.08);' +
            '}' +
            '*{box-sizing:border-box;}' +
            'body{' +
                'margin:0;' +
                'padding:24px;' +
                'font-family:Arial,Helvetica,sans-serif;' +
                'background:#f8fafc;' +
                'color:#0f172a;' +
            '}' +
            '.pagina-recibo{' +
                'width:100%;' +
                'margin:0 auto;' +
            '}' +
            '.barra-impresion{' +
                'display:flex;' +
                'justify-content:space-between;' +
                'align-items:center;' +
                'gap:12px;' +
                'margin:0 auto 18px;' +
                'max-width:1120px;' +
            '}' +
            '.barra-impresion h1{' +
                'font-size:18px;' +
                'margin:0;' +
                'color:#0f172a;' +
                'line-height:1.25;' +
            '}' +
            '.barra-impresion button{' +
                'border:0;' +
                'border-radius:12px;' +
                'background:#0f766e;' +
                'color:#ffffff;' +
                'padding:10px 14px;' +
                'font-weight:800;' +
                'cursor:pointer;' +
            '}' +
            '.recibo-preview{' +
                'background:#ffffff;' +
                'color:#0f172a;' +
                'border-radius:22px;' +
                'border:1px solid #e2e8f0;' +
                'box-shadow:0 18px 45px rgba(15,23,42,0.08);' +
                'margin:0 auto;' +
                'overflow:hidden;' +
                'font-family:Arial,Helvetica,sans-serif;' +
                'line-height:1.45;' +
            '}' +
            '.recibo-preview--documento{' +
                'width:100%;' +
            '}' +
            '.recibo-preview--horizontal{' +
                'max-width:1120px;' +
            '}' +
            '.recibo-preview--vertical{' +
                'max-width:760px;' +
            '}' +
            '.recibo-preview__header{' +
                'display:grid;' +
                'grid-template-columns:auto 1fr auto;' +
                'gap:18px;' +
                'align-items:center;' +
                'padding:22px;' +
                'color:#ffffff;' +
                'background:linear-gradient(135deg,#0f766e,#14b8a6);' +
            '}' +
            '.recibo-preview__header--sin-logo{' +
                'grid-template-columns:1fr auto;' +
            '}' +
            '.recibo-preview__logo{' +
                'width:82px;' +
                'height:82px;' +
                'border-radius:22px;' +
                'display:grid;' +
                'place-items:center;' +
                'overflow:hidden;' +
                'background:rgba(255,255,255,0.20);' +
                'border:1px solid rgba(255,255,255,0.30);' +
                'font-weight:900;' +
            '}' +
            '.recibo-preview__logo img{' +
                'width:100%;' +
                'height:100%;' +
                'object-fit:cover;' +
            '}' +
            '.recibo-preview__empresa{' +
                'min-width:0;' +
            '}' +
            '.recibo-preview__empresa h3{' +
                'margin:0 0 7px;' +
                'font-size:24px;' +
                'line-height:1.15;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '.recibo-preview__empresa p{' +
                'margin:3px 0;' +
                'color:rgba(255,255,255,0.86);' +
                'line-height:1.35;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '.recibo-preview__codigo{' +
                'text-align:right;' +
                'min-width:0;' +
            '}' +
            '.recibo-preview__codigo span{' +
                'display:block;' +
                'font-size:12px;' +
                'font-weight:900;' +
                'text-transform:uppercase;' +
                'opacity:0.86;' +
            '}' +
            '.recibo-preview__codigo strong{' +
                'display:block;' +
                'font-size:26px;' +
                'margin-top:4px;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '.recibo-preview__meta-line{' +
                'display:flex;' +
                'flex-wrap:wrap;' +
                'gap:8px;' +
                'margin-top:10px;' +
            '}' +
            '.recibo-preview__meta-line span{' +
                'background:rgba(255,255,255,0.18);' +
                'border:1px solid rgba(255,255,255,0.26);' +
                'border-radius:999px;' +
                'padding:6px 10px;' +
                'font-size:12px;' +
                'font-weight:800;' +
            '}' +
            '.recibo-preview__body{' +
                'padding:22px;' +
                'display:grid;' +
                'gap:18px;' +
            '}' +
            '.recibo-preview__cliente,' +
            '.recibo-preview__cuenta,' +
            '.recibo-preview__bloque,' +
            '.recibo-preview__total{' +
                'border:1px solid #e2e8f0;' +
                'border-radius:18px;' +
                'padding:16px;' +
                'background:#ffffff;' +
                'min-width:0;' +
            '}' +
            '.recibo-preview__cliente h4,' +
            '.recibo-preview__cuenta h4,' +
            '.recibo-preview__bloque h4{' +
                'margin:0 0 12px;' +
                'color:#115e59;' +
                'font-size:15px;' +
                'text-transform:uppercase;' +
                'letter-spacing:0.4px;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '.recibo-preview__grid{' +
                'display:grid;' +
                'grid-template-columns:repeat(2,minmax(0,1fr));' +
                'gap:10px;' +
            '}' +
            '.recibo-preview__dato{' +
                'min-width:0;' +
            '}' +
            '.recibo-preview__dato span{' +
                'display:block;' +
                'color:#64748b;' +
                'font-size:12px;' +
                'font-weight:900;' +
                'margin-bottom:4px;' +
                'text-transform:uppercase;' +
            '}' +
            '.recibo-preview__dato strong{' +
                'display:block;' +
                'font-size:14px;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '.recibo-preview__bloques-grid{' +
                'display:grid;' +
                'grid-template-columns:repeat(2,minmax(0,1fr));' +
                'gap:14px;' +
            '}' +
            '.recibo-preview--vertical .recibo-preview__bloques-grid{' +
                'grid-template-columns:1fr;' +
            '}' +
            '.recibo-preview__fila{' +
                'display:flex;' +
                'justify-content:space-between;' +
                'align-items:flex-start;' +
                'gap:12px;' +
                'padding:10px 0;' +
                'border-bottom:1px dashed #e2e8f0;' +
                'font-size:14px;' +
            '}' +
            '.recibo-preview__fila:last-child{' +
                'border-bottom:0;' +
            '}' +
            '.recibo-preview__fila span{' +
                'min-width:0;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '.recibo-preview__fila strong{' +
                'white-space:nowrap;' +
                'text-align:right;' +
                'flex:0 0 auto;' +
            '}' +
            '.recibo-preview__bloque-vacio{' +
                'border:1px dashed #cbd5e1;' +
                'background:#f8fafc;' +
                'color:#64748b;' +
                'border-radius:14px;' +
                'padding:12px;' +
                'font-size:14px;' +
                'line-height:1.5;' +
                'margin:0;' +
            '}' +
            '.recibo-preview__total{' +
                'display:flex;' +
                'justify-content:space-between;' +
                'align-items:center;' +
                'background:#ecfeff;' +
                'border-color:rgba(20,184,166,0.30);' +
            '}' +
            '.recibo-preview__total span{' +
                'font-weight:900;' +
                'color:#115e59;' +
            '}' +
            '.recibo-preview__total strong{' +
                'font-size:26px;' +
                'line-height:1.1;' +
                'color:#115e59;' +
                'white-space:nowrap;' +
            '}' +
            '.recibo-preview__footer{' +
                'padding:16px 22px;' +
                'color:#64748b;' +
                'border-top:1px solid #e2e8f0;' +
                'font-size:13px;' +
                'line-height:1.5;' +
                'background:#f8fafc;' +
                'overflow-wrap:anywhere;' +
            '}' +
            '@media(max-width:820px){' +
                'body{padding:14px;}' +
                '.barra-impresion{align-items:flex-start;flex-direction:column;}' +
                '.recibo-preview__header{grid-template-columns:1fr;text-align:left;}' +
                '.recibo-preview__header--sin-logo{grid-template-columns:1fr;}' +
                '.recibo-preview__codigo{text-align:left;}' +
                '.recibo-preview__grid,' +
                '.recibo-preview__bloques-grid{grid-template-columns:1fr;}' +
                '.recibo-preview__fila{flex-direction:column;}' +
                '.recibo-preview__fila strong{text-align:left;}' +
                '.recibo-preview__total{align-items:flex-start;flex-direction:column;}' +
            '}' +
            '@media print{' +
                'body{background:#ffffff;padding:0;}' +
                '.barra-impresion{display:none;}' +
                '.pagina-recibo{width:100%;}' +
                '.recibo-preview{box-shadow:none;border-radius:0;border:0;}' +
            '}';
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