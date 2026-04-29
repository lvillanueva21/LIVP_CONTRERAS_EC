(function (window, $) {
    'use strict';

    var AppExportador = {
        escala: 2,
        margenPdfMm: 8,

        exportarDocumentoAjax: function (config) {
            var opciones = $.extend({
                ajaxUrl: '',
                documentoAction: '',
                auditoriaAction: '',
                id: 0,
                tipo: 'pdf',
                nombreArchivo: 'documento',
                selectorDocumento: '',
                orientacion: 'auto'
            }, config || {});

            if (!opciones.ajaxUrl || !opciones.documentoAction || !opciones.id || !opciones.selectorDocumento) {
                AppUI.error('No se pudo preparar la exportación.');
                return;
            }

            var tipo = String(opciones.tipo || 'pdf').toLowerCase();

            if (tipo !== 'jpg' && tipo !== 'pdf') {
                AppUI.warning('Tipo de exportación no válido.');
                return;
            }

            AppUI.info('Preparando documento para exportar...');

            AppAjax.get(opciones.ajaxUrl, {
                action: opciones.documentoAction,
                id: opciones.id
            }, {
                showSuccess: false,
                onSuccess: function (response) {
                    if (!response || !response.ok || !response.html) {
                        AppUI.error('No se pudo cargar el documento.');
                        return;
                    }

                    AppExportador.crearDomTemporal(response.html, opciones.selectorDocumento)
                        .then(function (payload) {
                            return AppExportador.esperarRecursos(payload.documento).then(function () {
                                payload.orientacionDetectada = AppExportador.obtenerOrientacionDocumento(
                                    payload.documento,
                                    opciones.orientacion
                                );
                                return payload;
                            });
                        })
                        .then(function (payload) {
                            if (tipo === 'jpg') {
                                return AppExportador.exportarJpg(payload.documento, opciones.nombreArchivo).then(function () {
                                    return payload;
                                });
                            }

                            return AppExportador.exportarPdf(
                                payload.documento,
                                opciones.nombreArchivo,
                                payload.orientacionDetectada
                            ).then(function () {
                                return payload;
                            });
                        })
                        .then(function (payload) {
                            AppExportador.limpiarDomTemporal(payload.wrapper);

                            if (opciones.auditoriaAction) {
                                AppExportador.registrarAuditoria(
                                    opciones.ajaxUrl,
                                    opciones.auditoriaAction,
                                    opciones.id,
                                    tipo
                                );
                            }

                            AppUI.success('Documento exportado correctamente.');
                        })
                        .catch(function (error) {
                            if (error && error.wrapper) {
                                AppExportador.limpiarDomTemporal(error.wrapper);
                            }

                            AppUI.error(error && error.message ? error.message : 'No se pudo exportar el documento.');
                        });
                }
            });
        },

        crearDomTemporal: function (html, selectorDocumento) {
            return new Promise(function (resolve, reject) {
                var wrapper = document.createElement('div');

                wrapper.className = 'app-export-wrapper';
                wrapper.innerHTML = html;
                document.body.appendChild(wrapper);

                var documento = wrapper.querySelector(selectorDocumento);

                if (!documento) {
                    AppExportador.limpiarDomTemporal(wrapper);
                    reject({
                        message: 'No se encontró el documento para exportar.',
                        wrapper: wrapper
                    });
                    return;
                }

                documento.classList.add('app-export-documento');
                AppExportador.normalizarImagenes(documento);

                resolve({
                    wrapper: wrapper,
                    documento: documento
                });
            });
        },

        normalizarImagenes: function (documento) {
            var imagenes = documento.querySelectorAll('img');

            Array.prototype.forEach.call(imagenes, function (img) {
                img.setAttribute('crossorigin', 'anonymous');
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.objectFit = 'contain';
            });
        },

        esperarRecursos: function (documento) {
            return Promise.all([
                AppExportador.esperarImagenes(documento),
                AppExportador.esperarFuentes(),
                AppExportador.esperarRender()
            ]);
        },

        esperarImagenes: function (documento) {
            var imagenes = documento.querySelectorAll('img');

            if (!imagenes.length) {
                return Promise.resolve();
            }

            var promesas = [];

            Array.prototype.forEach.call(imagenes, function (img) {
                promesas.push(new Promise(function (resolve) {
                    if (img.complete && img.naturalWidth > 0) {
                        resolve();
                        return;
                    }

                    var finalizado = false;

                    var terminar = function () {
                        if (!finalizado) {
                            finalizado = true;
                            resolve();
                        }
                    };

                    img.onload = terminar;
                    img.onerror = terminar;

                    setTimeout(terminar, 3000);
                }));
            });

            return Promise.all(promesas);
        },

        esperarFuentes: function () {
            if (document.fonts && document.fonts.ready) {
                return document.fonts.ready.catch(function () {
                    return true;
                });
            }

            return Promise.resolve();
        },

        esperarRender: function () {
            return new Promise(function (resolve) {
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(function () {
                        setTimeout(resolve, 180);
                    });
                });
            });
        },

        obtenerOrientacionDocumento: function (documento, preferencia) {
            var orientacion = String(preferencia || 'auto').toLowerCase();

            if (orientacion === 'horizontal' || orientacion === 'landscape') {
                return 'horizontal';
            }

            if (orientacion === 'vertical' || orientacion === 'portrait') {
                return 'vertical';
            }

            var dataOrientacion = documento.getAttribute('data-export-orientacion') || documento.getAttribute('data-orientacion') || '';

            dataOrientacion = String(dataOrientacion).toLowerCase();

            if (dataOrientacion === 'horizontal' || dataOrientacion === 'landscape') {
                return 'horizontal';
            }

            if (dataOrientacion === 'vertical' || dataOrientacion === 'portrait') {
                return 'vertical';
            }

            var ancho = documento.scrollWidth || documento.offsetWidth || 0;
            var alto = documento.scrollHeight || documento.offsetHeight || 0;

            if (ancho > alto) {
                return 'horizontal';
            }

            return 'vertical';
        },

        canvasDesdeDocumento: function (documento) {
            if (!window.html2canvas) {
                return Promise.reject({
                    message: 'Falta html2canvas local en plugins/html2canvas/html2canvas.min.js'
                });
            }

            return window.html2canvas(documento, {
                scale: AppExportador.escala,
                backgroundColor: '#ffffff',
                useCORS: true,
                allowTaint: false,
                logging: false,
                scrollX: 0,
                scrollY: 0,
                windowWidth: documento.scrollWidth,
                windowHeight: documento.scrollHeight
            }).then(function (canvas) {
                if (!canvas || canvas.width <= 0 || canvas.height <= 0) {
                    throw {
                        message: 'La captura salió vacía.'
                    };
                }

                return canvas;
            });
        },

        exportarJpg: function (documento, nombreArchivo) {
            return this.canvasDesdeDocumento(documento).then(function (canvas) {
                var dataUrl = canvas.toDataURL('image/jpeg', 0.95);

                if (!dataUrl || dataUrl.length < 100) {
                    throw {
                        message: 'No se pudo generar el JPG.'
                    };
                }

                AppExportador.descargarArchivo(dataUrl, nombreArchivo + '.jpg');
            });
        },

        exportarPdf: function (documento, nombreArchivo, orientacion) {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                return Promise.reject({
                    message: 'Falta jsPDF local en plugins/jspdf/jspdf.umd.min.js'
                });
            }

            return this.canvasDesdeDocumento(documento).then(function (canvas) {
                var pdfOrientacion = orientacion === 'horizontal' ? 'landscape' : 'portrait';
                var pdf = new window.jspdf.jsPDF(pdfOrientacion, 'mm', 'a4');
                var pageWidth = pdf.internal.pageSize.getWidth();
                var pageHeight = pdf.internal.pageSize.getHeight();
                var margen = AppExportador.margenPdfMm;
                var usableWidth = pageWidth - (margen * 2);
                var usableHeight = pageHeight - (margen * 2);
                var canvasWidth = canvas.width;
                var canvasHeight = canvas.height;
                var imgWidth = usableWidth;
                var imgHeight = (canvasHeight * imgWidth) / canvasWidth;
                var imgData = canvas.toDataURL('image/jpeg', 0.95);

                if (!imgData || imgData.length < 100) {
                    throw {
                        message: 'No se pudo generar la imagen para PDF.'
                    };
                }

                if (imgHeight <= usableHeight) {
                    pdf.addImage(imgData, 'JPEG', margen, margen, imgWidth, imgHeight);
                    pdf.save(nombreArchivo + '.pdf');
                    return;
                }

                var ratioPxPorMm = canvasWidth / imgWidth;
                var pageHeightPx = Math.floor(usableHeight * ratioPxPorMm);
                var pageCanvas = document.createElement('canvas');
                var posicionY = 0;
                var pagina = 0;

                pageCanvas.width = canvasWidth;

                while (posicionY < canvasHeight) {
                    var altoCorte = Math.min(pageHeightPx, canvasHeight - posicionY);
                    var pageCtx;

                    pageCanvas.height = altoCorte;
                    pageCtx = pageCanvas.getContext('2d');
                    pageCtx.fillStyle = '#ffffff';
                    pageCtx.fillRect(0, 0, pageCanvas.width, pageCanvas.height);
                    pageCtx.drawImage(
                        canvas,
                        0,
                        posicionY,
                        canvasWidth,
                        altoCorte,
                        0,
                        0,
                        canvasWidth,
                        altoCorte
                    );

                    var paginaData = pageCanvas.toDataURL('image/jpeg', 0.95);
                    var paginaHeightMm = altoCorte / ratioPxPorMm;

                    if (pagina > 0) {
                        pdf.addPage();
                    }

                    pdf.addImage(paginaData, 'JPEG', margen, margen, imgWidth, paginaHeightMm);

                    posicionY += altoCorte;
                    pagina++;
                }

                pdf.save(nombreArchivo + '.pdf');
            });
        },

        descargarArchivo: function (dataUrl, nombreArchivo) {
            var link = document.createElement('a');

            link.href = dataUrl;
            link.download = nombreArchivo;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        limpiarDomTemporal: function (wrapper) {
            if (wrapper && wrapper.parentNode) {
                wrapper.parentNode.removeChild(wrapper);
            }
        },

        registrarAuditoria: function (ajaxUrl, action, id, tipo) {
            AppAjax.post(ajaxUrl, {
                action: action,
                id: id,
                tipo: tipo
            }, {
                showSuccess: false,
                showError: false
            });
        },

        nombreSeguro: function (texto) {
            texto = String(texto || 'documento');
            texto = texto.toLowerCase();
            texto = texto.replace(/[^a-z0-9_-]+/g, '-');
            texto = texto.replace(/-+/g, '-');
            texto = texto.replace(/^-|-$/g, '');

            return texto !== '' ? texto : 'documento';
        }
    };

    window.AppExportador = AppExportador;
})(window, window.jQuery);