(function () {
    'use strict';

    var appData = window.appDemoData || {
        clientes: [],
        servicios: [],
        plantillas: [],
        recibos: [],
        auditoria: []
    };

    var logoTemporalDataUrl = '';

    document.addEventListener('DOMContentLoaded', function () {
        prepararConfiguracionEstudio();
        prepararPlantillasDemo();
        iniciarPersonalizacionEstudio();
        iniciarConstructorPlantillas();
        pintarFormularioPersonalizacion();
        pintarPreviewPersonalizacion();
        pintarPlantillas();
        actualizarDashboardPlantillas();
        notificarActualizacionGenerador();
    });

    function prepararConfiguracionEstudio() {
        if (!appData.configuracionEstudio) {
            appData.configuracionEstudio = {
                logoDataUrl: '',
                nombreComercial: 'Estudio Contable Contreras',
                ruc: '10730652441',
                razonSocial: 'MIRTHA VETTY BACA CONTRERAS',
                rubro: 'Asesoría contable y tributaria',
                direccion: 'CALLE MARTINEZ DE CAMPAÑON 911',
                colorPrincipal: '#0f766e',
                colorSecundario: '#14b8a6',
                textoSuperior: 'Generador de recibos personalizable',
                piePagina: 'Gracias por su preferencia.'
            };
        }

        aplicarColoresEstudio();
    }

    function prepararPlantillasDemo() {
        if (!appData.plantillas || appData.plantillas.length === 0 || appData.plantillas[0].mostrarLogo === undefined) {
            appData.plantillas = [
                {
                    codigo: 1,
                    nombre: 'Recibo horizontal completo',
                    orientacion: 'Horizontal',
                    mostrarLogo: true,
                    mostrarDatosEmpresa: true,
                    mostrarRucEmpresa: true,
                    mostrarRazonSocialEmpresa: true,
                    mostrarRubroEmpresa: true,
                    mostrarDireccionEmpresa: true,
                    mostrarCodigoCliente: true,
                    mostrarRazonSocialCliente: true,
                    mostrarNombresCliente: true,
                    mostrarCuentasBancarias: true,
                    usarCuentaPorDefecto: true,
                    permitirElegirOtraCuenta: true,
                    mostrarServiciosContabilidad: true,
                    mostrarPeriodosPendientes: true,
                    mostrarAportacionesEmpleador: true,
                    mostrarOtrosServicios: true,
                    mostrarTotalGeneral: true,
                    estado: 'Activa'
                },
                {
                    codigo: 2,
                    nombre: 'Recibo vertical simple',
                    orientacion: 'Vertical',
                    mostrarLogo: true,
                    mostrarDatosEmpresa: true,
                    mostrarRucEmpresa: true,
                    mostrarRazonSocialEmpresa: true,
                    mostrarRubroEmpresa: true,
                    mostrarDireccionEmpresa: true,
                    mostrarCodigoCliente: true,
                    mostrarRazonSocialCliente: true,
                    mostrarNombresCliente: true,
                    mostrarCuentasBancarias: true,
                    usarCuentaPorDefecto: true,
                    permitirElegirOtraCuenta: false,
                    mostrarServiciosContabilidad: true,
                    mostrarPeriodosPendientes: false,
                    mostrarAportacionesEmpleador: false,
                    mostrarOtrosServicios: true,
                    mostrarTotalGeneral: true,
                    estado: 'Activa'
                },
                {
                    codigo: 3,
                    nombre: 'Recibo sin logo',
                    orientacion: 'Horizontal',
                    mostrarLogo: false,
                    mostrarDatosEmpresa: true,
                    mostrarRucEmpresa: true,
                    mostrarRazonSocialEmpresa: true,
                    mostrarRubroEmpresa: true,
                    mostrarDireccionEmpresa: true,
                    mostrarCodigoCliente: true,
                    mostrarRazonSocialCliente: true,
                    mostrarNombresCliente: true,
                    mostrarCuentasBancarias: true,
                    usarCuentaPorDefecto: true,
                    permitirElegirOtraCuenta: true,
                    mostrarServiciosContabilidad: true,
                    mostrarPeriodosPendientes: true,
                    mostrarAportacionesEmpleador: true,
                    mostrarOtrosServicios: true,
                    mostrarTotalGeneral: true,
                    estado: 'Activa'
                }
            ];
        }

        window.appDemoData = appData;
    }

    function iniciarPersonalizacionEstudio() {
        var form = document.getElementById('formPersonalizacionEstudio');
        var inputLogo = document.getElementById('personalizacionLogo');
        var camposPreview = [
            'personalizacionNombreComercial',
            'personalizacionRuc',
            'personalizacionRazonSocial',
            'personalizacionRubro',
            'personalizacionDireccion',
            'personalizacionColorPrincipal',
            'personalizacionColorSecundario',
            'personalizacionTextoSuperior',
            'personalizacionPiePagina'
        ];

        if (form) {
            form.onsubmit = guardarPersonalizacionEstudio;
        }

        if (inputLogo) {
            inputLogo.onchange = procesarLogoTemporal;
        }

        for (var i = 0; i < camposPreview.length; i++) {
            asignarEventoPreview(camposPreview[i]);
        }
    }

    function asignarEventoPreview(id) {
        var campo = document.getElementById(id);

        if (!campo) {
            return;
        }

        campo.oninput = pintarPreviewPersonalizacionDesdeFormulario;
        campo.onchange = pintarPreviewPersonalizacionDesdeFormulario;
    }

    function procesarLogoTemporal(evento) {
        var archivo = evento.target.files && evento.target.files.length > 0 ? evento.target.files[0] : null;

        if (!archivo) {
            return;
        }

        if (!archivo.type || archivo.type.indexOf('image/') !== 0) {
            mostrarAviso('warning', 'Logo inválido', 'Selecciona una imagen válida para el logo.');
            return;
        }

        var lector = new FileReader();

        lector.onload = function (e) {
            logoTemporalDataUrl = e.target.result;
            pintarLogoPersonalizacion(logoTemporalDataUrl);
            pintarPreviewPersonalizacionDesdeFormulario();
        };

        lector.readAsDataURL(archivo);
    }

    function guardarPersonalizacionEstudio(evento) {
        evento.preventDefault();

        var nombreComercial = limpiarTexto(obtenerValor('personalizacionNombreComercial'));
        var ruc = limpiarTexto(obtenerValor('personalizacionRuc'));
        var razonSocial = limpiarTexto(obtenerValor('personalizacionRazonSocial'));
        var rubro = limpiarTexto(obtenerValor('personalizacionRubro'));
        var direccion = limpiarTexto(obtenerValor('personalizacionDireccion'));

        if (nombreComercial === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el nombre comercial.');
            return;
        }

        if (ruc === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el RUC.');
            return;
        }

        if (razonSocial === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa la razón social.');
            return;
        }

        appData.configuracionEstudio.nombreComercial = nombreComercial;
        appData.configuracionEstudio.ruc = ruc;
        appData.configuracionEstudio.razonSocial = razonSocial;
        appData.configuracionEstudio.rubro = rubro;
        appData.configuracionEstudio.direccion = direccion;
        appData.configuracionEstudio.colorPrincipal = obtenerValor('personalizacionColorPrincipal') || '#0f766e';
        appData.configuracionEstudio.colorSecundario = obtenerValor('personalizacionColorSecundario') || '#14b8a6';
        appData.configuracionEstudio.textoSuperior = limpiarTexto(obtenerValor('personalizacionTextoSuperior'));
        appData.configuracionEstudio.piePagina = limpiarTexto(obtenerValor('personalizacionPiePagina'));

        if (logoTemporalDataUrl !== '') {
            appData.configuracionEstudio.logoDataUrl = logoTemporalDataUrl;
        }

        aplicarColoresEstudio();
        pintarPreviewPersonalizacion();
        registrarAuditoriaPlantillas('Configuración personalizada', 'Se actualizó la personalización temporal del estudio contable.');

        mostrarAviso('success', 'Personalización guardada', 'Los datos del estudio fueron actualizados temporalmente.');
        pintarAuditoriaGlobal();
    }

    function pintarFormularioPersonalizacion() {
        var config = appData.configuracionEstudio;

        asignarValor('personalizacionNombreComercial', config.nombreComercial);
        asignarValor('personalizacionRuc', config.ruc);
        asignarValor('personalizacionRazonSocial', config.razonSocial);
        asignarValor('personalizacionRubro', config.rubro);
        asignarValor('personalizacionDireccion', config.direccion);
        asignarValor('personalizacionColorPrincipal', config.colorPrincipal);
        asignarValor('personalizacionColorSecundario', config.colorSecundario);
        asignarValor('personalizacionTextoSuperior', config.textoSuperior);
        asignarValor('personalizacionPiePagina', config.piePagina);

        pintarLogoPersonalizacion(config.logoDataUrl);
    }

    function pintarPreviewPersonalizacionDesdeFormulario() {
        var configTemporal = {
            logoDataUrl: logoTemporalDataUrl || appData.configuracionEstudio.logoDataUrl,
            nombreComercial: obtenerValor('personalizacionNombreComercial'),
            ruc: obtenerValor('personalizacionRuc'),
            razonSocial: obtenerValor('personalizacionRazonSocial'),
            rubro: obtenerValor('personalizacionRubro'),
            direccion: obtenerValor('personalizacionDireccion'),
            colorPrincipal: obtenerValor('personalizacionColorPrincipal') || '#0f766e',
            colorSecundario: obtenerValor('personalizacionColorSecundario') || '#14b8a6',
            textoSuperior: obtenerValor('personalizacionTextoSuperior'),
            piePagina: obtenerValor('personalizacionPiePagina')
        };

        pintarPreviewPersonalizacionConConfig(configTemporal);
    }

    function pintarPreviewPersonalizacion() {
        pintarPreviewPersonalizacionConConfig(appData.configuracionEstudio);
    }

    function pintarPreviewPersonalizacionConConfig(config) {
        asignarTexto('previewMiniNombreComercial', config.nombreComercial);
        asignarTexto('previewMiniTextoSuperior', config.textoSuperior);
        asignarTexto('previewMiniRuc', config.ruc);
        asignarTexto('previewMiniRazonSocial', config.razonSocial);
        asignarTexto('previewMiniRubro', config.rubro);
        asignarTexto('previewMiniDireccion', config.direccion);
        asignarTexto('previewMiniPiePagina', config.piePagina);

        var header = document.getElementById('previewMiniHeaderPersonalizacion');

        if (header) {
            header.style.background = 'linear-gradient(135deg, ' + config.colorPrincipal + ', ' + config.colorSecundario + ')';
        }

        pintarLogoMiniPersonalizacion(config.logoDataUrl);
    }

    function pintarLogoPersonalizacion(dataUrl) {
        var img = document.getElementById('personalizacionLogoPreview');
        var texto = document.getElementById('personalizacionLogoTexto');

        if (!img || !texto) {
            return;
        }

        if (dataUrl && dataUrl !== '') {
            img.src = dataUrl;
            img.style.display = 'block';
            texto.style.display = 'none';
        } else {
            img.removeAttribute('src');
            img.style.display = 'none';
            texto.style.display = 'block';
        }
    }

    function pintarLogoMiniPersonalizacion(dataUrl) {
        var img = document.getElementById('previewMiniLogoImgPersonalizacion');
        var texto = document.getElementById('previewMiniLogoTextoPersonalizacion');

        if (!img || !texto) {
            return;
        }

        if (dataUrl && dataUrl !== '') {
            img.src = dataUrl;
            img.style.display = 'block';
            texto.style.display = 'none';
        } else {
            img.removeAttribute('src');
            img.style.display = 'none';
            texto.style.display = 'block';
        }
    }

    function aplicarColoresEstudio() {
        if (!appData.configuracionEstudio) {
            return;
        }

        document.documentElement.style.setProperty('--color-primary', appData.configuracionEstudio.colorPrincipal);
        document.documentElement.style.setProperty('--color-secondary', appData.configuracionEstudio.colorSecundario);
    }

    function iniciarConstructorPlantillas() {
        asignarClickSiExiste('btnNuevaPlantilla', abrirNuevaPlantilla);

        var form = document.getElementById('formPlantilla');
        var contenedor = document.getElementById('contenedorPlantillas');

        if (form) {
            form.onsubmit = guardarPlantilla;
        }

        if (contenedor) {
            contenedor.onclick = function (evento) {
                var boton = obtenerBotonPlantilla(evento.target);

                if (!boton) {
                    return;
                }

                var accion = boton.getAttribute('data-plantilla-accion');
                var codigo = parseInt(boton.getAttribute('data-plantilla-codigo'), 10);

                if (accion === 'editar') {
                    abrirEditarPlantilla(codigo);
                }

                if (accion === 'duplicar') {
                    duplicarPlantilla(codigo);
                }

                if (accion === 'vista') {
                    abrirVistaPreviaPlantilla(codigo);
                }
            };
        }
    }

    function abrirNuevaPlantilla() {
        limpiarFormularioPlantilla();
        asignarTexto('tituloModalPlantilla', 'Nueva plantilla');
        asignarValor('plantillaModo', 'crear');
        abrirModalSeguro('modalPlantilla');
    }

    function abrirEditarPlantilla(codigo) {
        var plantilla = buscarPorCodigo(appData.plantillas, codigo);

        if (!plantilla) {
            mostrarAviso('error', 'Plantilla no encontrada', 'No se encontró la plantilla temporal seleccionada.');
            return;
        }

        limpiarFormularioPlantilla();
        asignarTexto('tituloModalPlantilla', 'Editar plantilla');
        asignarValor('plantillaModo', 'editar');
        cargarPlantillaEnFormulario(plantilla);
        abrirModalSeguro('modalPlantilla');
    }

    function guardarPlantilla(evento) {
        evento.preventDefault();

        var modo = obtenerValor('plantillaModo');
        var codigo = parseInt(obtenerValor('plantillaCodigo'), 10);
        var nombre = limpiarTexto(obtenerValor('plantillaNombre'));

        if (nombre === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el nombre de la plantilla.');
            return;
        }

        var plantilla = obtenerPlantillaDesdeFormulario();

        if (modo === 'editar') {
            plantilla.codigo = codigo;
            reemplazarPorCodigo(appData.plantillas, codigo, plantilla);
            registrarAuditoriaPlantillas('Plantilla editada', 'Se editó la plantilla temporal ' + plantilla.nombre + '.');
            mostrarAviso('success', 'Plantilla actualizada', 'La plantilla temporal fue actualizada correctamente.');
        } else {
            plantilla.codigo = obtenerSiguienteCodigo(appData.plantillas);
            appData.plantillas.push(plantilla);
            registrarAuditoriaPlantillas('Plantilla creada', 'Se creó la plantilla temporal ' + plantilla.nombre + '.');
            mostrarAviso('success', 'Plantilla creada', 'La plantilla temporal fue agregada correctamente.');
        }

        cerrarModalSeguro('modalPlantilla');
        pintarPlantillas();
        actualizarDashboardPlantillas();
        pintarAuditoriaGlobal();
        notificarActualizacionGenerador();
    }

    function duplicarPlantilla(codigo) {
        var plantilla = buscarPorCodigo(appData.plantillas, codigo);

        if (!plantilla) {
            mostrarAviso('error', 'Plantilla no encontrada', 'No se encontró la plantilla temporal seleccionada.');
            return;
        }

        var copia = copiarPlantilla(plantilla);
        copia.codigo = obtenerSiguienteCodigo(appData.plantillas);
        copia.nombre = plantilla.nombre + ' copia';
        copia.estado = 'Activa';

        appData.plantillas.push(copia);

        registrarAuditoriaPlantillas('Plantilla duplicada', 'Se duplicó la plantilla temporal ' + plantilla.nombre + '.');
        mostrarAviso('success', 'Plantilla duplicada', 'La plantilla fue duplicada correctamente.');

        pintarPlantillas();
        actualizarDashboardPlantillas();
        pintarAuditoriaGlobal();
        notificarActualizacionGenerador();
    }

    function abrirVistaPreviaPlantilla(codigo) {
        var plantilla = buscarPorCodigo(appData.plantillas, codigo);
        var contenedor = document.getElementById('vistaPreviaPlantillaContenido');

        if (!plantilla || !contenedor) {
            mostrarAviso('error', 'Vista previa no disponible', 'No se pudo generar la vista previa de la plantilla.');
            return;
        }

        contenedor.innerHTML = generarHtmlVistaPreviaPlantilla(plantilla);

        registrarAuditoriaPlantillas('Vista previa de plantilla', 'Se abrió la vista previa de la plantilla ' + plantilla.nombre + '.');
        pintarAuditoriaGlobal();
        abrirModalSeguro('modalVistaPreviaPlantilla');
    }

    function obtenerPlantillaDesdeFormulario() {
        return {
            codigo: 0,
            nombre: limpiarTexto(obtenerValor('plantillaNombre')),
            orientacion: obtenerValor('plantillaOrientacion'),
            mostrarLogo: valorSiNoABooleano(obtenerValor('plantillaMostrarLogo')),
            mostrarDatosEmpresa: valorSiNoABooleano(obtenerValor('plantillaMostrarDatosEmpresa')),
            mostrarRucEmpresa: valorSiNoABooleano(obtenerValor('plantillaMostrarRucEmpresa')),
            mostrarRazonSocialEmpresa: valorSiNoABooleano(obtenerValor('plantillaMostrarRazonSocialEmpresa')),
            mostrarRubroEmpresa: valorSiNoABooleano(obtenerValor('plantillaMostrarRubroEmpresa')),
            mostrarDireccionEmpresa: valorSiNoABooleano(obtenerValor('plantillaMostrarDireccionEmpresa')),
            mostrarCodigoCliente: valorSiNoABooleano(obtenerValor('plantillaMostrarCodigoCliente')),
            mostrarRazonSocialCliente: valorSiNoABooleano(obtenerValor('plantillaMostrarRazonSocialCliente')),
            mostrarNombresCliente: valorSiNoABooleano(obtenerValor('plantillaMostrarNombresCliente')),
            mostrarCuentasBancarias: valorSiNoABooleano(obtenerValor('plantillaMostrarCuentasBancarias')),
            usarCuentaPorDefecto: valorSiNoABooleano(obtenerValor('plantillaUsarCuentaPorDefecto')),
            permitirElegirOtraCuenta: valorSiNoABooleano(obtenerValor('plantillaPermitirElegirOtraCuenta')),
            mostrarServiciosContabilidad: valorSiNoABooleano(obtenerValor('plantillaMostrarServiciosContabilidad')),
            mostrarPeriodosPendientes: valorSiNoABooleano(obtenerValor('plantillaMostrarPeriodosPendientes')),
            mostrarAportacionesEmpleador: valorSiNoABooleano(obtenerValor('plantillaMostrarAportacionesEmpleador')),
            mostrarOtrosServicios: valorSiNoABooleano(obtenerValor('plantillaMostrarOtrosServicios')),
            mostrarTotalGeneral: valorSiNoABooleano(obtenerValor('plantillaMostrarTotalGeneral')),
            estado: obtenerValor('plantillaEstado')
        };
    }

    function cargarPlantillaEnFormulario(plantilla) {
        asignarValor('plantillaCodigo', plantilla.codigo);
        asignarValor('plantillaNombre', plantilla.nombre);
        asignarValor('plantillaOrientacion', plantilla.orientacion);
        asignarValor('plantillaEstado', plantilla.estado);
        asignarValor('plantillaMostrarLogo', booleanoASiNo(plantilla.mostrarLogo));
        asignarValor('plantillaMostrarDatosEmpresa', booleanoASiNo(plantilla.mostrarDatosEmpresa));
        asignarValor('plantillaMostrarRucEmpresa', booleanoASiNo(plantilla.mostrarRucEmpresa));
        asignarValor('plantillaMostrarRazonSocialEmpresa', booleanoASiNo(plantilla.mostrarRazonSocialEmpresa));
        asignarValor('plantillaMostrarRubroEmpresa', booleanoASiNo(plantilla.mostrarRubroEmpresa));
        asignarValor('plantillaMostrarDireccionEmpresa', booleanoASiNo(plantilla.mostrarDireccionEmpresa));
        asignarValor('plantillaMostrarCodigoCliente', booleanoASiNo(plantilla.mostrarCodigoCliente));
        asignarValor('plantillaMostrarRazonSocialCliente', booleanoASiNo(plantilla.mostrarRazonSocialCliente));
        asignarValor('plantillaMostrarNombresCliente', booleanoASiNo(plantilla.mostrarNombresCliente));
        asignarValor('plantillaMostrarCuentasBancarias', booleanoASiNo(plantilla.mostrarCuentasBancarias));
        asignarValor('plantillaUsarCuentaPorDefecto', booleanoASiNo(plantilla.usarCuentaPorDefecto));
        asignarValor('plantillaPermitirElegirOtraCuenta', booleanoASiNo(plantilla.permitirElegirOtraCuenta));
        asignarValor('plantillaMostrarServiciosContabilidad', booleanoASiNo(plantilla.mostrarServiciosContabilidad));
        asignarValor('plantillaMostrarPeriodosPendientes', booleanoASiNo(plantilla.mostrarPeriodosPendientes));
        asignarValor('plantillaMostrarAportacionesEmpleador', booleanoASiNo(plantilla.mostrarAportacionesEmpleador));
        asignarValor('plantillaMostrarOtrosServicios', booleanoASiNo(plantilla.mostrarOtrosServicios));
        asignarValor('plantillaMostrarTotalGeneral', booleanoASiNo(plantilla.mostrarTotalGeneral));
    }

    function limpiarFormularioPlantilla() {
        var form = document.getElementById('formPlantilla');

        if (form) {
            form.reset();
        }

        asignarValor('plantillaModo', 'crear');
        asignarValor('plantillaCodigo', '');
        asignarValor('plantillaNombre', '');
        asignarValor('plantillaOrientacion', 'Horizontal');
        asignarValor('plantillaEstado', 'Activa');
        asignarValor('plantillaMostrarLogo', 'Sí');
        asignarValor('plantillaMostrarDatosEmpresa', 'Sí');
        asignarValor('plantillaMostrarRucEmpresa', 'Sí');
        asignarValor('plantillaMostrarRazonSocialEmpresa', 'Sí');
        asignarValor('plantillaMostrarRubroEmpresa', 'Sí');
        asignarValor('plantillaMostrarDireccionEmpresa', 'Sí');
        asignarValor('plantillaMostrarCodigoCliente', 'Sí');
        asignarValor('plantillaMostrarRazonSocialCliente', 'Sí');
        asignarValor('plantillaMostrarNombresCliente', 'Sí');
        asignarValor('plantillaMostrarCuentasBancarias', 'Sí');
        asignarValor('plantillaUsarCuentaPorDefecto', 'Sí');
        asignarValor('plantillaPermitirElegirOtraCuenta', 'Sí');
        asignarValor('plantillaMostrarServiciosContabilidad', 'Sí');
        asignarValor('plantillaMostrarPeriodosPendientes', 'Sí');
        asignarValor('plantillaMostrarAportacionesEmpleador', 'Sí');
        asignarValor('plantillaMostrarOtrosServicios', 'Sí');
        asignarValor('plantillaMostrarTotalGeneral', 'Sí');
    }

    function pintarPlantillas() {
        var contenedor = document.getElementById('contenedorPlantillas');

        if (!contenedor) {
            return;
        }

        if (!appData.plantillas || appData.plantillas.length === 0) {
            contenedor.innerHTML =
                '<article class="simple-card">' +
                    '<div class="simple-card__top">' +
                        '<span class="badge badge--warning">Vacío</span>' +
                        '<span>Demo</span>' +
                    '</div>' +
                    '<h3>No hay plantillas temporales</h3>' +
                    '<p>Crea una plantilla para generar vistas previas de recibos.</p>' +
                '</article>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.plantillas.length; i++) {
            html += generarCardPlantilla(appData.plantillas[i]);
        }

        contenedor.innerHTML = html;
    }

    function generarCardPlantilla(plantilla) {
        var html = '';

        html += '<article class="simple-card plantilla-card">';
        html += '<div class="simple-card__top">';
        html += plantilla.estado === 'Activa' ? '<span class="badge badge--success">Activa</span>' : '<span class="badge badge--danger">Inactiva</span>';
        html += '<span>' + escaparHtml(plantilla.orientacion) + '</span>';
        html += '</div>';

        html += '<h3>' + escaparHtml(plantilla.nombre) + '</h3>';
        html += '<p>Código: <strong>' + plantilla.codigo + '</strong></p>';

        html += '<div class="plantilla-card__meta">';
        html += obtenerBadgePlantilla('Logo', plantilla.mostrarLogo);
        html += obtenerBadgePlantilla('Empresa', plantilla.mostrarDatosEmpresa);
        html += obtenerBadgePlantilla('Cliente', plantilla.mostrarCodigoCliente || plantilla.mostrarRazonSocialCliente || plantilla.mostrarNombresCliente);
        html += obtenerBadgePlantilla('Cuentas', plantilla.mostrarCuentasBancarias);
        html += obtenerBadgePlantilla('Total', plantilla.mostrarTotalGeneral);
        html += '</div>';

        html += '<div class="plantilla-card__checks">';
        html += obtenerLineaCheck('Servicios de contabilidad', plantilla.mostrarServiciosContabilidad);
        html += obtenerLineaCheck('Periodos pendientes de pago', plantilla.mostrarPeriodosPendientes);
        html += obtenerLineaCheck('Aportaciones del empleador', plantilla.mostrarAportacionesEmpleador);
        html += obtenerLineaCheck('Otros servicios o trámites', plantilla.mostrarOtrosServicios);
        html += '</div>';

        html += '<div class="plantilla-card__acciones">';
        html += '<button type="button" class="btn btn--light btn--sm" data-plantilla-accion="vista" data-plantilla-codigo="' + plantilla.codigo + '">Vista previa</button>';
        html += '<button type="button" class="btn btn--primary btn--sm" data-plantilla-accion="editar" data-plantilla-codigo="' + plantilla.codigo + '">Editar</button>';
        html += '<button type="button" class="btn btn--warning btn--sm" data-plantilla-accion="duplicar" data-plantilla-codigo="' + plantilla.codigo + '">Duplicar plantilla</button>';
        html += '</div>';

        html += '</article>';

        return html;
    }

    function obtenerBadgePlantilla(texto, activo) {
        if (activo) {
            return '<span class="badge badge--success">' + escaparHtml(texto) + '</span>';
        }

        return '<span class="badge badge--danger">' + escaparHtml(texto) + '</span>';
    }

    function obtenerLineaCheck(texto, activo) {
        var clase = activo ? 'plantilla-check plantilla-check--si' : 'plantilla-check plantilla-check--no';
        var icono = activo ? '✓' : '×';

        return '<div class="' + clase + '"><span>' + icono + '</span>' + escaparHtml(texto) + '</div>';
    }

    function generarHtmlVistaPreviaPlantilla(plantilla) {
        var config = appData.configuracionEstudio;
        var cliente = obtenerClienteDemo();
        var cuenta = obtenerCuentaDemo();
        var claseOrientacion = plantilla.orientacion === 'Vertical' ? 'recibo-preview--vertical' : 'recibo-preview--horizontal';
        var total = 0;
        var html = '';

        html += '<div class="recibo-preview recibo-preview--documento ' + claseOrientacion + '">';

        html += '<div class="recibo-preview__header' + (plantilla.mostrarLogo ? '' : ' recibo-preview__header--sin-logo') + '">';

        if (plantilla.mostrarLogo) {
            html += '<div class="recibo-preview__logo">';
            if (config.logoDataUrl && config.logoDataUrl !== '') {
                html += '<img src="' + escaparHtml(config.logoDataUrl) + '" alt="Logo">';
            } else {
                html += 'ECC';
            }
            html += '</div>';
        }

        html += '<div class="recibo-preview__empresa">';
        html += '<h3>' + escaparHtml(config.nombreComercial) + '</h3>';

        if (plantilla.mostrarDatosEmpresa) {
            if (plantilla.mostrarRucEmpresa) {
                html += '<p>RUC: ' + escaparHtml(config.ruc) + '</p>';
            }

            if (plantilla.mostrarRazonSocialEmpresa) {
                html += '<p>' + escaparHtml(config.razonSocial) + '</p>';
            }

            if (plantilla.mostrarRubroEmpresa) {
                html += '<p>' + escaparHtml(config.rubro) + '</p>';
            }

            if (plantilla.mostrarDireccionEmpresa) {
                html += '<p>' + escaparHtml(config.direccion) + '</p>';
            }
        }

        html += '</div>';

        html += '<div class="recibo-preview__codigo">';
        html += '<span>Recibo demo</span>';
        html += '<strong>Código 1</strong>';
        html += '</div>';

        html += '</div>';

        html += '<div class="recibo-preview__body">';

        html += '<div class="recibo-preview__cliente">';
        html += '<h4>Datos del cliente</h4>';
        html += '<div class="recibo-preview__grid">';

        if (plantilla.mostrarCodigoCliente) {
            html += obtenerDatoPreview('Código', cliente.codigo);
        }

        if (plantilla.mostrarRazonSocialCliente) {
            html += obtenerDatoPreview('Razón social', cliente.razonSocial);
        }

        if (plantilla.mostrarNombresCliente) {
            html += obtenerDatoPreview('Nombres y apellidos', cliente.nombres);
        }

        html += obtenerDatoPreview('RUC/DNI', cliente.documento);
        html += '</div>';
        html += '</div>';

        if (plantilla.mostrarCuentasBancarias) {
            html += '<div class="recibo-preview__cuenta">';
            html += '<h4>Cuenta bancaria</h4>';
            html += '<div class="recibo-preview__grid">';
            html += obtenerDatoPreview('Banco', cuenta.banco);
            html += obtenerDatoPreview('Tipo de cuenta', cuenta.tipoCuenta);
            html += obtenerDatoPreview('Número de cuenta', cuenta.numeroCuenta);
            html += obtenerDatoPreview('CCI', cuenta.cci);
            html += obtenerDatoPreview('Titular', cuenta.titular);
            html += obtenerDatoPreview('Regla de plantilla', plantilla.usarCuentaPorDefecto ? 'Usa cuenta por defecto' : 'Permite seleccionar cuenta');
            html += '</div>';
            html += '</div>';
        }

        html += '<div class="recibo-preview__bloques-grid">';

        if (plantilla.mostrarServiciosContabilidad) {
            html += obtenerBloquePreview('Servicios de contabilidad', [
                ['HONORARIOS MAYO', 500.00],
                ['AFP MAYO', 276.00],
                ['RENTA MAYO', 1171.00]
            ]);
            total += 1947.00;
        }

        if (plantilla.mostrarPeriodosPendientes) {
            html += obtenerBloquePreview('Periodos pendientes de pago', [
                ['HONORARIOS ABRIL', 500.00],
                ['ESSALUD ABRIL', 276.00]
            ]);
            total += 776.00;
        }

        if (plantilla.mostrarAportacionesEmpleador) {
            html += obtenerBloquePreview('Aportaciones del empleador', [
                ['ESSALUD MAYO', 276.00]
            ]);
            total += 276.00;
        }

        if (plantilla.mostrarOtrosServicios) {
            html += obtenerBloquePreview('Otros servicios o trámites', [
                ['TRÁMITE SUNAT', 80.00]
            ]);
            total += 80.00;
        }

        html += '</div>';

        if (plantilla.mostrarTotalGeneral) {
            html += '<div class="recibo-preview__total">';
            html += '<span>Total general</span>';
            html += '<strong>' + formatearSoles(total) + '</strong>';
            html += '</div>';
        }

        html += '</div>';

        html += '<div class="recibo-preview__footer">';
        html += escaparHtml(config.piePagina);
        html += '</div>';

        html += '</div>';

        return html;
    }

    function obtenerDatoPreview(etiqueta, valor) {
        return '<div class="recibo-preview__dato"><span>' + escaparHtml(etiqueta) + '</span><strong>' + escaparHtml(valor) + '</strong></div>';
    }

    function obtenerBloquePreview(titulo, filas) {
        var html = '';

        html += '<div class="recibo-preview__bloque">';
        html += '<h4>' + escaparHtml(titulo) + '</h4>';

        for (var i = 0; i < filas.length; i++) {
            html += '<div class="recibo-preview__fila">';
            html += '<span>' + escaparHtml(filas[i][0]) + '</span>';
            html += '<strong>' + formatearSoles(filas[i][1]) + '</strong>';
            html += '</div>';
        }

        html += '</div>';

        return html;
    }

    function obtenerClienteDemo() {
        var cliente = appData.clientes && appData.clientes.length > 0 ? appData.clientes[0] : null;

        if (!cliente) {
            return {
                codigo: 1129,
                documento: '20601234567',
                razonSocial: 'EMPRESA DEMO SAC',
                nombres: 'CLIENTE DEMO'
            };
        }

        return {
            codigo: cliente.codigo || 1129,
            documento: cliente.documento || '',
            razonSocial: cliente.razonSocial || cliente.nombre || '',
            nombres: cliente.nombres || cliente.nombre || ''
        };
    }

    function obtenerCuentaDemo() {
        var cuenta = null;

        if (appData.cuentas && appData.cuentas.length > 0) {
            for (var i = 0; i < appData.cuentas.length; i++) {
                if (appData.cuentas[i].porDefecto) {
                    cuenta = appData.cuentas[i];
                    break;
                }
            }

            if (!cuenta) {
                cuenta = appData.cuentas[0];
            }
        }

        if (!cuenta) {
            return {
                banco: 'BCP',
                tipoCuenta: 'Ahorros',
                numeroCuenta: '193-12345678-0-11',
                cci: '00219300123456780112',
                titular: 'MIRTHA VETTY BACA CONTRERAS'
            };
        }

        return {
            banco: obtenerNombreBanco(cuenta.bancoCodigo),
            tipoCuenta: cuenta.tipoCuenta,
            numeroCuenta: cuenta.numeroCuenta,
            cci: cuenta.cci,
            titular: cuenta.titular
        };
    }

    function obtenerNombreBanco(codigo) {
        if (!appData.bancos) {
            return 'Banco demo';
        }

        for (var i = 0; i < appData.bancos.length; i++) {
            if (parseInt(appData.bancos[i].codigo, 10) === parseInt(codigo, 10)) {
                return appData.bancos[i].nombre;
            }
        }

        return 'Banco demo';
    }

    function obtenerBotonPlantilla(elemento) {
        while (elemento && elemento !== document) {
            if (elemento.getAttribute && elemento.getAttribute('data-plantilla-accion')) {
                return elemento;
            }

            elemento = elemento.parentNode;
        }

        return null;
    }

    function copiarPlantilla(plantilla) {
        return {
            codigo: plantilla.codigo,
            nombre: plantilla.nombre,
            orientacion: plantilla.orientacion,
            mostrarLogo: plantilla.mostrarLogo,
            mostrarDatosEmpresa: plantilla.mostrarDatosEmpresa,
            mostrarRucEmpresa: plantilla.mostrarRucEmpresa,
            mostrarRazonSocialEmpresa: plantilla.mostrarRazonSocialEmpresa,
            mostrarRubroEmpresa: plantilla.mostrarRubroEmpresa,
            mostrarDireccionEmpresa: plantilla.mostrarDireccionEmpresa,
            mostrarCodigoCliente: plantilla.mostrarCodigoCliente,
            mostrarRazonSocialCliente: plantilla.mostrarRazonSocialCliente,
            mostrarNombresCliente: plantilla.mostrarNombresCliente,
            mostrarCuentasBancarias: plantilla.mostrarCuentasBancarias,
            usarCuentaPorDefecto: plantilla.usarCuentaPorDefecto,
            permitirElegirOtraCuenta: plantilla.permitirElegirOtraCuenta,
            mostrarServiciosContabilidad: plantilla.mostrarServiciosContabilidad,
            mostrarPeriodosPendientes: plantilla.mostrarPeriodosPendientes,
            mostrarAportacionesEmpleador: plantilla.mostrarAportacionesEmpleador,
            mostrarOtrosServicios: plantilla.mostrarOtrosServicios,
            mostrarTotalGeneral: plantilla.mostrarTotalGeneral,
            estado: plantilla.estado
        };
    }

    function valorSiNoABooleano(valor) {
        return valor === 'Sí';
    }

    function booleanoASiNo(valor) {
        return valor ? 'Sí' : 'No';
    }

    function buscarPorCodigo(lista, codigo) {
        for (var i = 0; i < lista.length; i++) {
            if (parseInt(lista[i].codigo, 10) === parseInt(codigo, 10)) {
                return lista[i];
            }
        }

        return null;
    }

    function reemplazarPorCodigo(lista, codigo, nuevoRegistro) {
        for (var i = 0; i < lista.length; i++) {
            if (parseInt(lista[i].codigo, 10) === parseInt(codigo, 10)) {
                lista[i] = nuevoRegistro;
                return;
            }
        }
    }

    function obtenerSiguienteCodigo(lista) {
        var mayor = 0;

        for (var i = 0; i < lista.length; i++) {
            if (parseInt(lista[i].codigo, 10) > mayor) {
                mayor = parseInt(lista[i].codigo, 10);
            }
        }

        return mayor + 1;
    }

    function actualizarDashboardPlantillas() {
        asignarTexto('statPlantillas', appData.plantillas ? appData.plantillas.length : 0);
    }

    function registrarAuditoriaPlantillas(tipo, descripcion) {
        if (!appData.auditoria) {
            appData.auditoria = [];
        }

        appData.auditoria.unshift({
            fecha: formatearFechaHora(new Date()),
            tipo: tipo,
            descripcion: descripcion,
            usuario: 'Gerencia'
        });
    }

    function pintarAuditoriaGlobal() {
        var tbody = document.getElementById('tablaAuditoria');

        if (!tbody) {
            return;
        }

        if (!appData.auditoria || appData.auditoria.length === 0) {
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

    function asignarClickSiExiste(id, callback) {
        var elemento = document.getElementById(id);

        if (elemento) {
            elemento.onclick = callback;
        }
    }

    function asignarTexto(id, texto) {
        var elemento = document.getElementById(id);

        if (elemento) {
            elemento.textContent = texto;
        }
    }

    function asignarValor(id, valor) {
        var elemento = document.getElementById(id);

        if (elemento) {
            elemento.value = valor;
        }
    }

    function obtenerValor(id) {
        var elemento = document.getElementById(id);

        if (!elemento) {
            return '';
        }

        return elemento.value;
    }

    function limpiarTexto(texto) {
        if (typeof texto !== 'string') {
            return '';
        }

        return texto.replace(/^\s+|\s+$/g, '');
    }

    function notificarActualizacionGenerador() {
        if (typeof window.actualizarSelectsGeneradorRecibo === 'function') {
            window.actualizarSelectsGeneradorRecibo();
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

    function cerrarModalSeguro(idModal) {
        if (typeof window.cerrarModal === 'function') {
            window.cerrarModal(idModal);
            return;
        }

        var modal = document.getElementById(idModal);

        if (modal) {
            modal.classList.remove('activo');
        }
    }

    function mostrarAviso(tipo, titulo, mensaje) {
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion(tipo, titulo, mensaje);
            return;
        }

        alert(titulo + '\n' + mensaje);
    }
})();
