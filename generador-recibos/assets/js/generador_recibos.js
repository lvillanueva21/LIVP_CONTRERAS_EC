(function () {
    'use strict';

    var appData = window.appDemoData || {
        clientes: [],
        servicios: [],
        plantillas: [],
        recibos: [],
        auditoria: []
    };

    var estadoGenerador = {
        conceptos: [],
        contadorConceptos: 0
    };

    var categoriasRecibo = [
        'Servicios de contabilidad',
        'Periodos pendientes de pago',
        'Aportaciones del empleador',
        'Otros servicios o trámites'
    ];

    var mesesRecibo = [
        'ENERO',
        'FEBRERO',
        'MARZO',
        'ABRIL',
        'MAYO',
        'JUNIO',
        'JULIO',
        'AGOSTO',
        'SETIEMBRE',
        'OCTUBRE',
        'NOVIEMBRE',
        'DICIEMBRE'
    ];

    document.addEventListener('DOMContentLoaded', function () {
        prepararDatosMinimosGenerador();
        iniciarGeneradorRecibos();
        cargarSelectsGeneradorRecibo();
        pintarConceptosRecibo();
        calcularTotalesRecibo();
    });

    function prepararDatosMinimosGenerador() {
        if (!appData.recibos) {
            appData.recibos = [];
        }

        if (!appData.auditoria) {
            appData.auditoria = [];
        }

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

        window.appDemoData = appData;
    }

    function iniciarGeneradorRecibos() {
        var form = document.getElementById('formGenerarRecibo');
        var btnVistaPrevia = document.getElementById('btnVistaPreviaRecibo');
        var btnGenerarDesdeVista = document.getElementById('btnGenerarDesdeVistaPrevia');
        var btnNuevoCliente = document.getElementById('btnReciboNuevoCliente');
        var selectPlantilla = document.getElementById('reciboPlantilla');
        var toolbar = document.querySelector('.conceptos-toolbar');

        if (form) {
            form.onsubmit = generarReciboTemporalDesdeSubmit;
        }

        if (btnVistaPrevia) {
            btnVistaPrevia.onclick = abrirVistaPreviaRecibo;
        }

        if (btnGenerarDesdeVista) {
            btnGenerarDesdeVista.onclick = generarReciboTemporalDesdeBoton;
        }

        if (btnNuevoCliente) {
            btnNuevoCliente.onclick = abrirModalNuevoClienteDesdeRecibo;
        }

        if (selectPlantilla) {
            selectPlantilla.onchange = function () {
                aplicarReglasCuentaSegunPlantilla();
                pintarConceptosRecibo();
            };
        }

        if (toolbar) {
            toolbar.onclick = function (evento) {
                var boton = obtenerBotonAgregarConcepto(evento.target);

                if (!boton) {
                    return;
                }

                agregarConceptoRecibo(boton.getAttribute('data-agregar-categoria'));
            };
        }

        asignarEventosTablaConceptos('tbodyConceptosServiciosContabilidad');
        asignarEventosTablaConceptos('tbodyConceptosPeriodosPendientes');
        asignarEventosTablaConceptos('tbodyConceptosAportacionesEmpleador');
        asignarEventosTablaConceptos('tbodyConceptosOtrosServicios');

        var formClienteBase = document.getElementById('formClienteBase');

        if (formClienteBase) {
            formClienteBase.addEventListener('submit', function () {
                setTimeout(function () {
                    cargarSelectClientesRecibo();
                }, 100);
            });
        }
    }

    function cargarSelectsGeneradorRecibo() {
        cargarSelectPlantillasRecibo();
        cargarSelectClientesRecibo();
        cargarSelectCuentasRecibo();
        aplicarReglasCuentaSegunPlantilla();
    }

    function cargarSelectPlantillasRecibo() {
        var select = document.getElementById('reciboPlantilla');

        if (!select) {
            return;
        }

        var html = '<option value="">Seleccionar plantilla</option>';

        if (appData.plantillas && appData.plantillas.length > 0) {
            for (var i = 0; i < appData.plantillas.length; i++) {
                if (appData.plantillas[i].estado === 'Activa') {
                    html += '<option value="' + appData.plantillas[i].codigo + '">' + escaparHtml(appData.plantillas[i].nombre) + ' - ' + escaparHtml(appData.plantillas[i].orientacion) + '</option>';
                }
            }
        }

        select.innerHTML = html;
    }

    function cargarSelectClientesRecibo() {
        var select = document.getElementById('reciboCliente');
        var valorActual = select ? select.value : '';

        if (!select) {
            return;
        }

        var html = '<option value="">Seleccionar cliente</option>';

        if (appData.clientes && appData.clientes.length > 0) {
            for (var i = 0; i < appData.clientes.length; i++) {
                if (appData.clientes[i].estado === 'Activo') {
                    html += '<option value="' + appData.clientes[i].codigo + '">' + appData.clientes[i].codigo + ' - ' + escaparHtml(obtenerNombreCliente(appData.clientes[i])) + '</option>';
                }
            }
        }

        select.innerHTML = html;

        if (valorActual !== '') {
            select.value = valorActual;
        }
    }

    function cargarSelectCuentasRecibo() {
        var select = document.getElementById('reciboCuenta');
        var valorActual = select ? select.value : '';

        if (!select) {
            return;
        }

        var html = '<option value="">Seleccionar cuenta</option>';

        if (appData.cuentas && appData.cuentas.length > 0) {
            for (var i = 0; i < appData.cuentas.length; i++) {
                if (appData.cuentas[i].estado === 'Activo') {
                    html += '<option value="' + appData.cuentas[i].codigo + '">' + escaparHtml(obtenerNombreBanco(appData.cuentas[i].bancoCodigo)) + ' - ' + escaparHtml(appData.cuentas[i].numeroCuenta) + '</option>';
                }
            }
        }

        select.innerHTML = html;

        if (valorActual !== '') {
            select.value = valorActual;
        }
    }

    function aplicarReglasCuentaSegunPlantilla() {
        var plantilla = obtenerPlantillaSeleccionada();
        var selectCuenta = document.getElementById('reciboCuenta');
        var bloqueCuenta = document.getElementById('bloqueCuentaRecibo');
        var info = document.getElementById('infoReglaCuentaRecibo');

        if (!selectCuenta || !bloqueCuenta || !info) {
            return;
        }

        if (!plantilla) {
            bloqueCuenta.style.display = 'block';
            selectCuenta.disabled = false;
            info.textContent = 'Selecciona una plantilla para aplicar las reglas de cuenta bancaria.';
            return;
        }

        if (!plantilla.mostrarCuentasBancarias) {
            bloqueCuenta.style.display = 'none';
            selectCuenta.value = '';
            info.textContent = 'Esta plantilla no muestra cuentas bancarias.';
            return;
        }

        bloqueCuenta.style.display = 'block';

        if (plantilla.usarCuentaPorDefecto) {
            var cuentaDefault = obtenerCuentaPorDefecto();

            if (cuentaDefault) {
                selectCuenta.value = cuentaDefault.codigo;
            }

            if (plantilla.permitirElegirOtraCuenta) {
                selectCuenta.disabled = false;
                info.textContent = 'La plantilla usa la cuenta por defecto, pero permite elegir otra cuenta.';
            } else {
                selectCuenta.disabled = true;
                info.textContent = 'La plantilla usa obligatoriamente la cuenta por defecto.';
            }

            return;
        }

        selectCuenta.disabled = !plantilla.permitirElegirOtraCuenta;

        if (plantilla.permitirElegirOtraCuenta) {
            info.textContent = 'La plantilla permite seleccionar una cuenta bancaria activa.';
        } else {
            info.textContent = 'La plantilla muestra cuentas, pero no permite cambiar la cuenta desde este formulario.';
        }
    }

    function abrirModalNuevoClienteDesdeRecibo() {
        asignarValor('clienteModoBase', 'crear');
        asignarValor('clienteCodigoBase', '');
        asignarValor('clienteTipoBase', 'Empresa');
        asignarValor('clienteDocumentoBase', '');
        asignarValor('clienteRazonBase', '');
        asignarValor('clienteNombresBase', '');
        asignarValor('clienteDireccionBase', '');
        asignarValor('clienteCelularBase', '');
        asignarValor('clienteCorreoBase', '');
        asignarValor('clienteEstadoBase', 'Activo');
        asignarTexto('tituloModalClienteBase', 'Nuevo cliente');

        abrirModalSeguro('modalClienteBase');
    }

    function obtenerBotonAgregarConcepto(elemento) {
        while (elemento && elemento !== document) {
            if (elemento.getAttribute && elemento.getAttribute('data-agregar-categoria')) {
                return elemento;
            }

            elemento = elemento.parentNode;
        }

        return null;
    }

    function agregarConceptoRecibo(categoria) {
        var servicio = obtenerPrimerServicioPorCategoria(categoria);
        var fecha = new Date();
        var mes = mesesRecibo[fecha.getMonth()];
        var anio = fecha.getFullYear();
        var descripcionBase = servicio ? servicio.descripcionBase : 'CONCEPTO';
        var descripcionGenerada = descripcionBase + ' ' + mes;

        estadoGenerador.contadorConceptos++;

        estadoGenerador.conceptos.push({
            codigo: estadoGenerador.contadorConceptos,
            categoria: categoria,
            servicioCodigo: servicio ? servicio.codigo : '',
            tipoPeriodo: 'Solo mes',
            mes: mes,
            anio: anio,
            fechaDesde: '',
            fechaHasta: '',
            descripcionBase: descripcionBase,
            descripcionGenerada: descripcionGenerada,
            descripcionEditable: descripcionGenerada,
            monto: servicio ? parseFloat(servicio.montoSugerido) : 0
        });

        pintarConceptosRecibo();
        calcularTotalesRecibo();

        mostrarAviso('success', 'Concepto agregado', 'Se agregó un concepto temporal al bloque ' + categoria + '.');
    }

    function asignarEventosTablaConceptos(idTbody) {
        var tbody = document.getElementById(idTbody);

        if (!tbody) {
            return;
        }

        tbody.onchange = manejarCambioConcepto;
        tbody.oninput = manejarCambioConcepto;
        tbody.onclick = function (evento) {
            var boton = obtenerBotonEliminarConcepto(evento.target);

            if (!boton) {
                return;
            }

            eliminarConceptoRecibo(parseInt(boton.getAttribute('data-concepto-codigo'), 10));
        };
    }

    function manejarCambioConcepto(evento) {
        var campo = obtenerCampoConcepto(evento.target);

        if (!campo) {
            return;
        }

        var codigo = parseInt(campo.getAttribute('data-concepto-codigo'), 10);
        var propiedad = campo.getAttribute('data-concepto-campo');
        var concepto = buscarConceptoPorCodigo(codigo);

        if (!concepto) {
            return;
        }

        if (propiedad === 'categoria') {
            concepto.categoria = campo.value;
            var primerServicio = obtenerPrimerServicioPorCategoria(concepto.categoria);
            concepto.servicioCodigo = primerServicio ? primerServicio.codigo : '';
            concepto.descripcionBase = primerServicio ? primerServicio.descripcionBase : 'CONCEPTO';
            concepto.monto = primerServicio ? parseFloat(primerServicio.montoSugerido) : 0;
            actualizarDescripcionConcepto(concepto, true);
            pintarConceptosRecibo();
            calcularTotalesRecibo();
            return;
        }

        if (propiedad === 'servicioCodigo') {
            concepto.servicioCodigo = parseInt(campo.value, 10);
            var servicio = buscarServicioPorCodigo(concepto.servicioCodigo);

            if (servicio) {
                concepto.descripcionBase = servicio.descripcionBase;
                concepto.monto = parseFloat(servicio.montoSugerido);
            }

            actualizarDescripcionConcepto(concepto, true);
            pintarConceptosRecibo();
            calcularTotalesRecibo();
            return;
        }

        if (propiedad === 'monto') {
            concepto.monto = parseFloat(campo.value);
            calcularTotalesRecibo();
            return;
        }

        if (propiedad === 'descripcionEditable') {
            concepto.descripcionEditable = campo.value;
            return;
        }

        concepto[propiedad] = campo.value;
        actualizarDescripcionConcepto(concepto, true);
        pintarConceptosRecibo();
        calcularTotalesRecibo();
    }

    function obtenerCampoConcepto(elemento) {
        while (elemento && elemento !== document) {
            if (elemento.getAttribute && elemento.getAttribute('data-concepto-campo')) {
                return elemento;
            }

            elemento = elemento.parentNode;
        }

        return null;
    }

    function obtenerBotonEliminarConcepto(elemento) {
        while (elemento && elemento !== document) {
            if (elemento.getAttribute && elemento.getAttribute('data-concepto-codigo')) {
                return elemento;
            }

            elemento = elemento.parentNode;
        }

        return null;
    }

    function eliminarConceptoRecibo(codigo) {
        for (var i = estadoGenerador.conceptos.length - 1; i >= 0; i--) {
            if (parseInt(estadoGenerador.conceptos[i].codigo, 10) === parseInt(codigo, 10)) {
                estadoGenerador.conceptos.splice(i, 1);
                break;
            }
        }

        pintarConceptosRecibo();
        calcularTotalesRecibo();

        mostrarAviso('info', 'Concepto eliminado', 'El concepto temporal fue eliminado del recibo.');
    }

    function actualizarDescripcionConcepto(concepto, actualizarEditable) {
        var descripcion = concepto.descripcionBase;

        if (concepto.tipoPeriodo === 'Solo mes') {
            descripcion = concepto.descripcionBase + ' ' + concepto.mes;
        }

        if (concepto.tipoPeriodo === 'Rango de fechas') {
            descripcion = concepto.descripcionBase + ' DEL ' + obtenerDiaFecha(concepto.fechaDesde) + ' AL ' + obtenerDiaFecha(concepto.fechaHasta) + ' DE ' + concepto.mes;
        }

        if (concepto.tipoPeriodo === 'Anual') {
            descripcion = concepto.descripcionBase + ' ANUAL ' + concepto.anio;
        }

        if (concepto.tipoPeriodo === 'Libre') {
            descripcion = concepto.descripcionEditable || concepto.descripcionBase;
        }

        concepto.descripcionGenerada = descripcion;

        if (actualizarEditable && concepto.tipoPeriodo !== 'Libre') {
            concepto.descripcionEditable = descripcion;
        }
    }

    function pintarConceptosRecibo() {
        pintarConceptosPorCategoria(
            'Servicios de contabilidad',
            'tbodyConceptosServiciosContabilidad',
            'contadorServiciosContabilidad',
            'No hay servicios de contabilidad'
        );

        pintarConceptosPorCategoria(
            'Periodos pendientes de pago',
            'tbodyConceptosPeriodosPendientes',
            'contadorPeriodosPendientes',
            'No hay periodos pendientes de pago'
        );

        pintarConceptosPorCategoria(
            'Aportaciones del empleador',
            'tbodyConceptosAportacionesEmpleador',
            'contadorAportacionesEmpleador',
            'No hay aportaciones del empleador'
        );

        pintarConceptosPorCategoria(
            'Otros servicios o trámites',
            'tbodyConceptosOtrosServicios',
            'contadorOtrosServicios',
            'No hay otros servicios o trámites'
        );

        aplicarVisibilidadBloquesPorPlantilla();
    }

    function pintarConceptosPorCategoria(categoria, idTbody, idContador, textoVacio) {
        var tbody = document.getElementById(idTbody);
        var contador = document.getElementById(idContador);
        var conceptos = obtenerConceptosPorCategoria(categoria);

        if (!tbody) {
            return;
        }

        if (contador) {
            contador.textContent = conceptos.length + (conceptos.length === 1 ? ' concepto' : ' conceptos');
        }

        if (conceptos.length === 0) {
            tbody.innerHTML =
                '<tr>' +
                    '<td colspan="11" class="estado-vacio">' +
                        '<strong>' + escaparHtml(textoVacio) + '</strong>' +
                        'Agrega conceptos para este bloque.' +
                    '</td>' +
                '</tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < conceptos.length; i++) {
            html += obtenerFilaConcepto(conceptos[i]);
        }

        tbody.innerHTML = html;
    }

    function obtenerFilaConcepto(concepto) {
        var html = '';

        html += '<tr>';
        html += '<td>' + obtenerSelectCategoria(concepto) + '</td>';
        html += '<td>' + obtenerSelectServicio(concepto) + '</td>';
        html += '<td>' + obtenerSelectTipoPeriodo(concepto) + '</td>';
        html += '<td>' + obtenerSelectMes(concepto) + '</td>';
        html += '<td><input type="number" class="form-control concepto-input" value="' + concepto.anio + '" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="anio"></td>';
        html += '<td><input type="date" class="form-control concepto-input" value="' + escaparHtml(concepto.fechaDesde) + '" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="fechaDesde"></td>';
        html += '<td><input type="date" class="form-control concepto-input" value="' + escaparHtml(concepto.fechaHasta) + '" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="fechaHasta"></td>';
        html += '<td><span class="concepto-descripcion-generada">' + escaparHtml(concepto.descripcionGenerada) + '</span></td>';
        html += '<td><input type="text" class="form-control concepto-input--descripcion" value="' + escaparHtml(concepto.descripcionEditable) + '" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="descripcionEditable"></td>';
        html += '<td><input type="number" class="form-control concepto-monto" min="0" step="0.01" value="' + obtenerMontoInput(concepto.monto) + '" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="monto"></td>';
        html += '<td><button type="button" class="btn btn--danger btn--sm" data-concepto-codigo="' + concepto.codigo + '">Eliminar</button></td>';
        html += '</tr>';

        return html;
    }

    function obtenerSelectCategoria(concepto) {
        var html = '<select class="form-control concepto-input" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="categoria">';

        for (var i = 0; i < categoriasRecibo.length; i++) {
            html += '<option value="' + escaparHtml(categoriasRecibo[i]) + '"' + obtenerSelected(categoriasRecibo[i], concepto.categoria) + '>' + escaparHtml(categoriasRecibo[i]) + '</option>';
        }

        html += '</select>';

        return html;
    }

    function obtenerSelectServicio(concepto) {
        var servicios = obtenerServiciosPorCategoria(concepto.categoria);
        var html = '<select class="form-control concepto-input" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="servicioCodigo">';

        if (servicios.length === 0) {
            html += '<option value="">Sin servicios</option>';
        }

        for (var i = 0; i < servicios.length; i++) {
            html += '<option value="' + servicios[i].codigo + '"' + obtenerSelected(servicios[i].codigo, concepto.servicioCodigo) + '>' + escaparHtml(servicios[i].nombre) + '</option>';
        }

        html += '</select>';

        return html;
    }

    function obtenerSelectTipoPeriodo(concepto) {
        var tipos = ['Solo mes', 'Rango de fechas', 'Anual', 'Libre'];
        var html = '<select class="form-control concepto-input" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="tipoPeriodo">';

        for (var i = 0; i < tipos.length; i++) {
            html += '<option value="' + tipos[i] + '"' + obtenerSelected(tipos[i], concepto.tipoPeriodo) + '>' + tipos[i] + '</option>';
        }

        html += '</select>';

        return html;
    }

    function obtenerSelectMes(concepto) {
        var html = '<select class="form-control concepto-input" data-concepto-codigo="' + concepto.codigo + '" data-concepto-campo="mes">';

        for (var i = 0; i < mesesRecibo.length; i++) {
            html += '<option value="' + mesesRecibo[i] + '"' + obtenerSelected(mesesRecibo[i], concepto.mes) + '>' + mesesRecibo[i] + '</option>';
        }

        html += '</select>';

        return html;
    }

    function aplicarVisibilidadBloquesPorPlantilla() {
        var plantilla = obtenerPlantillaSeleccionada();

        if (!plantilla) {
            mostrarBloqueConceptos('bloqueConceptosServiciosContabilidad', true);
            mostrarBloqueConceptos('bloqueConceptosPeriodosPendientes', true);
            mostrarBloqueConceptos('bloqueConceptosAportacionesEmpleador', true);
            mostrarBloqueConceptos('bloqueConceptosOtrosServicios', true);
            return;
        }

        mostrarBloqueConceptos('bloqueConceptosServiciosContabilidad', plantilla.mostrarServiciosContabilidad);
        mostrarBloqueConceptos('bloqueConceptosPeriodosPendientes', plantilla.mostrarPeriodosPendientes);
        mostrarBloqueConceptos('bloqueConceptosAportacionesEmpleador', plantilla.mostrarAportacionesEmpleador);
        mostrarBloqueConceptos('bloqueConceptosOtrosServicios', plantilla.mostrarOtrosServicios);
    }

    function mostrarBloqueConceptos(id, mostrar) {
        var bloque = document.getElementById(id);

        if (bloque) {
            bloque.style.display = mostrar ? 'block' : 'none';
        }
    }

    function calcularTotalesRecibo() {
        var totales = obtenerTotalesRecibo();

        asignarTexto('totalServiciosContabilidad', formatearSoles(totales.serviciosContabilidad));
        asignarTexto('totalPeriodosPendientes', formatearSoles(totales.periodosPendientes));
        asignarTexto('totalAportacionesEmpleador', formatearSoles(totales.aportacionesEmpleador));
        asignarTexto('totalOtrosServicios', formatearSoles(totales.otrosServicios));
        asignarTexto('totalGeneralRecibo', formatearSoles(totales.totalGeneral));
    }

    function obtenerTotalesRecibo() {
        var totales = {
            serviciosContabilidad: 0,
            periodosPendientes: 0,
            aportacionesEmpleador: 0,
            otrosServicios: 0,
            totalGeneral: 0
        };

        for (var i = 0; i < estadoGenerador.conceptos.length; i++) {
            var concepto = estadoGenerador.conceptos[i];
            var monto = parseFloat(concepto.monto);

            if (isNaN(monto)) {
                monto = 0;
            }

            if (concepto.categoria === 'Servicios de contabilidad') {
                totales.serviciosContabilidad += monto;
            }

            if (concepto.categoria === 'Periodos pendientes de pago') {
                totales.periodosPendientes += monto;
            }

            if (concepto.categoria === 'Aportaciones del empleador') {
                totales.aportacionesEmpleador += monto;
            }

            if (concepto.categoria === 'Otros servicios o trámites') {
                totales.otrosServicios += monto;
            }

            totales.totalGeneral += monto;
        }

        return totales;
    }

    function abrirVistaPreviaRecibo() {
        var validacion = validarRecibo(false);

        if (!validacion.ok) {
            mostrarAviso('warning', 'Faltan datos', validacion.mensaje);
            return;
        }

        var contenedor = document.getElementById('vistaPreviaReciboContenido');

        if (!contenedor) {
            return;
        }

        contenedor.innerHTML = generarHtmlReciboTemporal('Vista previa');
        abrirModalSeguro('modalVistaPreviaRecibo');
    }

    function generarReciboTemporalDesdeSubmit(evento) {
        evento.preventDefault();
        generarReciboTemporal();
    }

    function generarReciboTemporalDesdeBoton() {
        generarReciboTemporal();
    }

    function generarReciboTemporal() {
        var validacion = validarRecibo(true);

        if (!validacion.ok) {
            mostrarAviso('warning', 'No se puede generar', validacion.mensaje);
            return;
        }

        var cliente = obtenerClienteSeleccionado();
        var plantilla = obtenerPlantillaSeleccionada();
        var cuenta = obtenerCuentaSeleccionada();
        var codigo = 'REC-' + (appData.recibos.length + 1);
        var totales = obtenerTotalesRecibo();

        var recibo = {
            codigo: codigo,
            fecha: formatearFechaHora(new Date()),
            clienteCodigo: cliente.codigo,
            clienteNombre: obtenerNombreCliente(cliente),
            plantillaCodigo: plantilla.codigo,
            plantillaNombre: plantilla.nombre,
            cuentaCodigo: cuenta ? cuenta.codigo : '',
            conceptos: copiarConceptos(),
            totales: totales,
            estado: 'Temporal',
            htmlVista: generarHtmlReciboTemporal(codigo)
        };

        appData.recibos.push(recibo);

        registrarAuditoriaGenerador('Recibo generado', 'Se generó el recibo temporal ' + codigo + ' para ' + obtenerNombreCliente(cliente) + '.');

        actualizarDashboardGenerador();
        pintarAuditoriaGenerador();

        mostrarAviso('success', 'Recibo generado', 'El recibo temporal ' + codigo + ' fue guardado en memoria durante esta sesión.');

        cerrarModalSeguro('modalVistaPreviaRecibo');
    }

    function validarRecibo(validarMontos) {
        var plantilla = obtenerPlantillaSeleccionada();
        var cliente = obtenerClienteSeleccionado();

        if (!plantilla) {
            return {
                ok: false,
                mensaje: 'Selecciona una plantilla activa.'
            };
        }

        if (!cliente) {
            return {
                ok: false,
                mensaje: 'Selecciona un cliente activo.'
            };
        }

        if (estadoGenerador.conceptos.length === 0) {
            return {
                ok: false,
                mensaje: 'Agrega por lo menos un concepto al recibo.'
            };
        }

        if (validarMontos) {
            for (var i = 0; i < estadoGenerador.conceptos.length; i++) {
                var concepto = estadoGenerador.conceptos[i];
                var monto = parseFloat(concepto.monto);

                if (isNaN(monto) || monto <= 0) {
                    return {
                        ok: false,
                        mensaje: 'Todos los conceptos deben tener un monto mayor a cero.'
                    };
                }

                if (limpiarTexto(concepto.descripcionEditable) === '') {
                    return {
                        ok: false,
                        mensaje: 'Todos los conceptos deben tener una descripción editable.'
                    };
                }
            }
        }

        return {
            ok: true,
            mensaje: ''
        };
    }

    function generarHtmlReciboTemporal(codigoRecibo) {
        var config = appData.configuracionEstudio;
        var plantilla = obtenerPlantillaSeleccionada();
        var cliente = obtenerClienteSeleccionado();
        var cuenta = obtenerCuentaSeleccionada();
        var totales = obtenerTotalesRecibo();
        var claseOrientacion = plantilla.orientacion === 'Vertical' ? 'recibo-preview--vertical' : 'recibo-preview--horizontal';
        var html = '';

        html += '<div class="recibo-preview ' + claseOrientacion + '">';

        html += '<div class="recibo-preview__header' + (plantilla.mostrarLogo ? '' : ' recibo-preview__header--sin-logo') + '" style="background: linear-gradient(135deg, ' + escaparHtml(config.colorPrincipal) + ', ' + escaparHtml(config.colorSecundario) + ');">';

        if (plantilla.mostrarLogo) {
            html += '<div class="recibo-preview__logo">';
            if (config.logoDataUrl && config.logoDataUrl !== '') {
                html += '<img src="' + config.logoDataUrl + '" alt="Logo">';
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

        html += '<div class="recibo-preview__meta-line">';
        html += '<span>Plantilla: ' + escaparHtml(plantilla.nombre) + '</span>';
        html += '<span>Orientación: ' + escaparHtml(plantilla.orientacion) + '</span>';
        html += '</div>';

        html += '</div>';

        html += '<div class="recibo-preview__codigo">';
        html += '<span>Recibo temporal</span>';
        html += '<strong>Código ' + escaparHtml(codigoRecibo) + '</strong>';
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
            html += obtenerDatoPreview('Razón social', cliente.razonSocial || cliente.nombre || '');
        }

        if (plantilla.mostrarNombresCliente) {
            html += obtenerDatoPreview('Nombres y apellidos', cliente.nombres || '');
        }

        html += obtenerDatoPreview('RUC/DNI', cliente.documento || '');
        html += '</div>';
        html += '</div>';

        if (plantilla.mostrarCuentasBancarias && cuenta) {
            html += '<div class="recibo-preview__cuenta">';
            html += '<h4>Cuenta bancaria</h4>';
            html += '<div class="recibo-preview__grid">';
            html += obtenerDatoPreview('Banco', obtenerNombreBanco(cuenta.bancoCodigo));
            html += obtenerDatoPreview('Tipo de cuenta', cuenta.tipoCuenta);
            html += obtenerDatoPreview('Número de cuenta', cuenta.numeroCuenta);
            html += obtenerDatoPreview('CCI', cuenta.cci);
            html += obtenerDatoPreview('Titular', cuenta.titular);
            html += obtenerDatoPreview('Cuenta por defecto', cuenta.porDefecto ? 'Sí' : 'No');
            html += '</div>';
            html += '</div>';
        }

        html += '<div class="recibo-preview__bloques-grid">';

        if (plantilla.mostrarServiciosContabilidad) {
            html += obtenerBloqueReciboHtml('Servicios de contabilidad', obtenerConceptosPorCategoria('Servicios de contabilidad'));
        }

        if (plantilla.mostrarPeriodosPendientes) {
            html += obtenerBloqueReciboHtml('Periodos pendientes de pago', obtenerConceptosPorCategoria('Periodos pendientes de pago'));
        }

        if (plantilla.mostrarAportacionesEmpleador) {
            html += obtenerBloqueReciboHtml('Aportaciones del empleador', obtenerConceptosPorCategoria('Aportaciones del empleador'));
        }

        if (plantilla.mostrarOtrosServicios) {
            html += obtenerBloqueReciboHtml('Otros servicios o trámites', obtenerConceptosPorCategoria('Otros servicios o trámites'));
        }

        html += '</div>';

        if (plantilla.mostrarTotalGeneral) {
            html += '<div class="recibo-preview__total">';
            html += '<span>Total general</span>';
            html += '<strong>' + formatearSoles(totales.totalGeneral) + '</strong>';
            html += '</div>';
        }

        html += '</div>';

        html += '<div class="recibo-preview__footer">';
        html += escaparHtml(config.piePagina || 'Gracias por su preferencia.');
        html += '</div>';

        html += '</div>';

        return html;
    }

    function obtenerBloqueReciboHtml(titulo, conceptos) {
        var html = '';

        html += '<div class="recibo-preview__bloque">';
        html += '<h4>' + escaparHtml(titulo) + '</h4>';

        if (conceptos.length === 0) {
            html += '<p class="recibo-preview__bloque-vacio">Sin conceptos agregados para este bloque.</p>';
        }

        for (var i = 0; i < conceptos.length; i++) {
            html += '<div class="recibo-preview__fila">';
            html += '<span>' + escaparHtml(conceptos[i].descripcionEditable) + '</span>';
            html += '<strong>' + formatearSoles(conceptos[i].monto) + '</strong>';
            html += '</div>';
        }

        html += '</div>';

        return html;
    }

    function obtenerDatoPreview(etiqueta, valor) {
        return '<div class="recibo-preview__dato"><span>' + escaparHtml(etiqueta) + '</span><strong>' + escaparHtml(valor) + '</strong></div>';
    }

    function obtenerConceptosPorCategoria(categoria) {
        var conceptos = [];

        for (var i = 0; i < estadoGenerador.conceptos.length; i++) {
            if (estadoGenerador.conceptos[i].categoria === categoria) {
                conceptos.push(estadoGenerador.conceptos[i]);
            }
        }

        return conceptos;
    }

    function obtenerPrimerServicioPorCategoria(categoria) {
        var servicios = obtenerServiciosPorCategoria(categoria);

        if (servicios.length > 0) {
            return servicios[0];
        }

        return null;
    }

    function obtenerServiciosPorCategoria(categoria) {
        var servicios = [];

        if (!appData.servicios) {
            return servicios;
        }

        for (var i = 0; i < appData.servicios.length; i++) {
            if (appData.servicios[i].estado === 'Activo' && appData.servicios[i].categoria === categoria) {
                servicios.push(appData.servicios[i]);
            }
        }

        return servicios;
    }

    function buscarServicioPorCodigo(codigo) {
        if (!appData.servicios) {
            return null;
        }

        for (var i = 0; i < appData.servicios.length; i++) {
            if (parseInt(appData.servicios[i].codigo, 10) === parseInt(codigo, 10)) {
                return appData.servicios[i];
            }
        }

        return null;
    }

    function buscarConceptoPorCodigo(codigo) {
        for (var i = 0; i < estadoGenerador.conceptos.length; i++) {
            if (parseInt(estadoGenerador.conceptos[i].codigo, 10) === parseInt(codigo, 10)) {
                return estadoGenerador.conceptos[i];
            }
        }

        return null;
    }

    function obtenerPlantillaSeleccionada() {
        var codigo = parseInt(obtenerValor('reciboPlantilla'), 10);

        if (isNaN(codigo) || !appData.plantillas) {
            return null;
        }

        for (var i = 0; i < appData.plantillas.length; i++) {
            if (parseInt(appData.plantillas[i].codigo, 10) === codigo) {
                return appData.plantillas[i];
            }
        }

        return null;
    }

    function obtenerClienteSeleccionado() {
        var codigo = parseInt(obtenerValor('reciboCliente'), 10);

        if (isNaN(codigo) || !appData.clientes) {
            return null;
        }

        for (var i = 0; i < appData.clientes.length; i++) {
            if (parseInt(appData.clientes[i].codigo, 10) === codigo) {
                return appData.clientes[i];
            }
        }

        return null;
    }

    function obtenerCuentaSeleccionada() {
        var codigo = parseInt(obtenerValor('reciboCuenta'), 10);

        if (isNaN(codigo)) {
            return obtenerCuentaPorDefecto();
        }

        if (!appData.cuentas) {
            return null;
        }

        for (var i = 0; i < appData.cuentas.length; i++) {
            if (parseInt(appData.cuentas[i].codigo, 10) === codigo) {
                return appData.cuentas[i];
            }
        }

        return null;
    }

    function obtenerCuentaPorDefecto() {
        if (!appData.cuentas || appData.cuentas.length === 0) {
            return null;
        }

        for (var i = 0; i < appData.cuentas.length; i++) {
            if (appData.cuentas[i].porDefecto && appData.cuentas[i].estado === 'Activo') {
                return appData.cuentas[i];
            }
        }

        return appData.cuentas[0];
    }

    function obtenerNombreCliente(cliente) {
        if (!cliente) {
            return '';
        }

        if (cliente.tipo === 'Empresa') {
            return cliente.razonSocial || cliente.nombres || cliente.nombre || '';
        }

        return cliente.nombres || cliente.razonSocial || cliente.nombre || '';
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

    function copiarConceptos() {
        var copia = [];

        for (var i = 0; i < estadoGenerador.conceptos.length; i++) {
            copia.push({
                codigo: estadoGenerador.conceptos[i].codigo,
                categoria: estadoGenerador.conceptos[i].categoria,
                servicioCodigo: estadoGenerador.conceptos[i].servicioCodigo,
                tipoPeriodo: estadoGenerador.conceptos[i].tipoPeriodo,
                mes: estadoGenerador.conceptos[i].mes,
                anio: estadoGenerador.conceptos[i].anio,
                fechaDesde: estadoGenerador.conceptos[i].fechaDesde,
                fechaHasta: estadoGenerador.conceptos[i].fechaHasta,
                descripcionBase: estadoGenerador.conceptos[i].descripcionBase,
                descripcionGenerada: estadoGenerador.conceptos[i].descripcionGenerada,
                descripcionEditable: estadoGenerador.conceptos[i].descripcionEditable,
                monto: estadoGenerador.conceptos[i].monto
            });
        }

        return copia;
    }

    function registrarAuditoriaGenerador(tipo, descripcion) {
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

    function pintarAuditoriaGenerador() {
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

    function actualizarDashboardGenerador() {
        asignarTexto('statClientes', appData.clientes ? appData.clientes.length : 0);
        asignarTexto('statServicios', appData.servicios ? appData.servicios.length : 0);
        asignarTexto('statPlantillas', appData.plantillas ? appData.plantillas.length : 0);
        asignarTexto('statRecibos', appData.recibos ? appData.recibos.length : 0);
    }

    function obtenerDiaFecha(fechaTexto) {
        if (!fechaTexto || fechaTexto === '') {
            return '';
        }

        var partes = fechaTexto.split('-');

        if (partes.length !== 3) {
            return '';
        }

        return String(parseInt(partes[2], 10));
    }

    function obtenerSelected(valor, actual) {
        return String(valor) === String(actual) ? ' selected' : '';
    }

    function obtenerMontoInput(monto) {
        var numero = parseFloat(monto);

        if (isNaN(numero)) {
            numero = 0;
        }

        return numero.toFixed(2);
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

    window.actualizarSelectsGeneradorRecibo = cargarSelectsGeneradorRecibo;
})();