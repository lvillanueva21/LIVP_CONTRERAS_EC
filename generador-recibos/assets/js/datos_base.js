(function () {
    'use strict';

    var appData = window.appDemoData || {
        clientes: [],
        servicios: [],
        plantillas: [],
        recibos: [],
        auditoria: []
    };

    var estadoDatosBase = {
        tabActiva: 'clientes',
        eliminacionPendiente: null
    };

    document.addEventListener('DOMContentLoaded', function () {
        prepararDataExtendida();
        iniciarTabsDatosBase();
        iniciarBotonesDatosBase();
        iniciarFormulariosDatosBase();
        pintarTodoDatosBase();
    });

    function prepararDataExtendida() {
        appData.clientes = [
            {
                codigo: 1129,
                tipo: 'Empresa',
                documento: '20601234567',
                razonSocial: 'EMPRESA DE TRANSPORTES TUBOS DE CARTON SAC',
                nombres: 'CARLOS ALBERTO CONTRERAS RAMOS',
                direccion: 'AV. LOS PROCERES 245 - TARAPOTO',
                celular: '987654321',
                correo: 'administracion@tubosdecarton.test',
                estado: 'Activo',
                nombre: 'EMPRESA DE TRANSPORTES TUBOS DE CARTON SAC'
            },
            {
                codigo: 1130,
                tipo: 'Empresa',
                documento: '20555666777',
                razonSocial: 'COMERCIALIZADORA LOS ANDES SAC',
                nombres: 'MARIA ELENA VARGAS PAREDES',
                direccion: 'JR. SAN MARTIN 520 - TARAPOTO',
                celular: '976543210',
                correo: 'contabilidad@losandes.test',
                estado: 'Activo',
                nombre: 'COMERCIALIZADORA LOS ANDES SAC'
            },
            {
                codigo: 1131,
                tipo: 'Persona natural',
                documento: '45879632',
                razonSocial: '',
                nombres: 'JUAN CARLOS RAMOS PAREDES',
                direccion: 'CALLE LAS FLORES 118 - MORALES',
                celular: '965432109',
                correo: 'juan.ramos@test.com',
                estado: 'Activo',
                nombre: 'JUAN CARLOS RAMOS PAREDES'
            }
        ];

        appData.servicios = [
            {
                codigo: 1,
                nombre: 'HONORARIOS',
                categoria: 'Servicios de contabilidad',
                descripcionBase: 'HONORARIOS',
                montoSugerido: 500.00,
                estado: 'Activo'
            },
            {
                codigo: 2,
                nombre: 'AFP',
                categoria: 'Servicios de contabilidad',
                descripcionBase: 'AFP',
                montoSugerido: 276.00,
                estado: 'Activo'
            },
            {
                codigo: 3,
                nombre: 'DECLARACIÓN ANUAL DEL IMPUESTO A LA RENTA',
                categoria: 'Servicios de contabilidad',
                descripcionBase: 'DECLARACIÓN ANUAL DEL IMPUESTO A LA RENTA',
                montoSugerido: 500.00,
                estado: 'Activo'
            },
            {
                codigo: 4,
                nombre: 'ESSALUD',
                categoria: 'Aportaciones del empleador',
                descripcionBase: 'ESSALUD',
                montoSugerido: 276.00,
                estado: 'Activo'
            },
            {
                codigo: 5,
                nombre: 'RENTA',
                categoria: 'Servicios de contabilidad',
                descripcionBase: 'RENTA',
                montoSugerido: 1171.00,
                estado: 'Activo'
            },
            {
                codigo: 6,
                nombre: 'TRÁMITE SUNAT',
                categoria: 'Otros servicios o trámites',
                descripcionBase: 'TRÁMITE SUNAT',
                montoSugerido: 80.00,
                estado: 'Activo'
            }
        ];

        appData.bancos = [
            {
                codigo: 1,
                nombre: 'BCP',
                estado: 'Activo'
            },
            {
                codigo: 2,
                nombre: 'BBVA Continental',
                estado: 'Activo'
            },
            {
                codigo: 3,
                nombre: 'Scotiabank',
                estado: 'Activo'
            },
            {
                codigo: 4,
                nombre: 'Interbank',
                estado: 'Activo'
            },
            {
                codigo: 5,
                nombre: 'Yape',
                estado: 'Activo'
            },
            {
                codigo: 6,
                nombre: 'Plin',
                estado: 'Activo'
            }
        ];

        appData.cuentas = [
            {
                codigo: 1,
                bancoCodigo: 1,
                tipoCuenta: 'Ahorros',
                numeroCuenta: '193-12345678-0-11',
                cci: '00219300123456780112',
                titular: 'MIRTHA VETTY BACA CONTRERAS',
                porDefecto: true,
                estado: 'Activo'
            },
            {
                codigo: 2,
                bancoCodigo: 2,
                tipoCuenta: 'Ahorros',
                numeroCuenta: '0011-0222-0333444555',
                cci: '01100110022203334445559',
                titular: 'MIRTHA VETTY BACA CONTRERAS',
                porDefecto: false,
                estado: 'Activo'
            },
            {
                codigo: 3,
                bancoCodigo: 5,
                tipoCuenta: 'Yape',
                numeroCuenta: '987654321',
                cci: '',
                titular: 'MIRTHA VETTY BACA CONTRERAS',
                porDefecto: false,
                estado: 'Activo'
            }
        ];

        window.appDemoData = appData;
    }

    function iniciarTabsDatosBase() {
        var tabs = document.querySelectorAll('[data-tab-base]');

        for (var i = 0; i < tabs.length; i++) {
            tabs[i].addEventListener('click', function () {
                cambiarTabDatosBase(this.getAttribute('data-tab-base'));
            });
        }
    }

    function cambiarTabDatosBase(tab) {
        var tabs = document.querySelectorAll('[data-tab-base]');
        var panels = document.querySelectorAll('.tab-base-panel');

        estadoDatosBase.tabActiva = tab;

        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.remove('activo');
        }

        for (var j = 0; j < panels.length; j++) {
            panels[j].classList.remove('activo');
        }

        var tabActivo = document.querySelector('[data-tab-base="' + tab + '"]');
        var panelActivo = document.getElementById('tab-base-' + tab);

        if (tabActivo) {
            tabActivo.classList.add('activo');
        }

        if (panelActivo) {
            panelActivo.classList.add('activo');
        }

        actualizarBotonPrincipalDatosBase();
    }

    function actualizarBotonPrincipalDatosBase() {
        var contenedor = document.getElementById('accionesDatosBase');

        if (!contenedor) {
            return;
        }

        if (estadoDatosBase.tabActiva === 'clientes') {
            contenedor.innerHTML = '<button type="button" class="btn btn--primary" id="btnNuevoCliente">Nuevo cliente</button>';
        }

        if (estadoDatosBase.tabActiva === 'servicios') {
            contenedor.innerHTML = '<button type="button" class="btn btn--primary" id="btnNuevoServicio">Nuevo servicio</button>';
        }

        if (estadoDatosBase.tabActiva === 'bancos') {
            contenedor.innerHTML = '<button type="button" class="btn btn--primary" id="btnNuevoBanco">Nuevo banco</button>';
        }

        if (estadoDatosBase.tabActiva === 'cuentas') {
            contenedor.innerHTML = '<button type="button" class="btn btn--primary" id="btnNuevaCuenta">Nueva cuenta de ahorro</button>';
        }

        iniciarBotonesDatosBase();
    }

    function iniciarBotonesDatosBase() {
        asignarClickSiExiste('btnNuevoCliente', abrirNuevoCliente);
        asignarClickSiExiste('btnNuevoServicio', abrirNuevoServicio);
        asignarClickSiExiste('btnNuevoBanco', abrirNuevoBanco);
        asignarClickSiExiste('btnNuevaCuenta', abrirNuevaCuenta);

        asignarDelegacionTabla('tablaClientesBase');
        asignarDelegacionTabla('tablaServiciosBase');
        asignarDelegacionTabla('tablaBancosBase');
        asignarDelegacionTabla('tablaCuentasBase');

        asignarClickSiExiste('btnConfirmarEliminarBase', confirmarEliminacionPendiente);
    }

    function asignarClickSiExiste(id, callback) {
        var elemento = document.getElementById(id);

        if (!elemento) {
            return;
        }

        elemento.onclick = callback;
    }

    function asignarDelegacionTabla(idTabla) {
        var tabla = document.getElementById(idTabla);

        if (!tabla) {
            return;
        }

        tabla.onclick = function (evento) {
            var boton = obtenerBotonAccion(evento.target);

            if (!boton) {
                return;
            }

            var accion = boton.getAttribute('data-accion');
            var tipo = boton.getAttribute('data-tipo');
            var codigo = parseInt(boton.getAttribute('data-codigo'), 10);

            if (accion === 'editar') {
                editarRegistro(tipo, codigo);
            }

            if (accion === 'eliminar') {
                pedirEliminarRegistro(tipo, codigo);
            }
        };
    }

    function obtenerBotonAccion(elemento) {
        while (elemento && elemento !== document) {
            if (elemento.getAttribute && elemento.getAttribute('data-accion')) {
                return elemento;
            }

            elemento = elemento.parentNode;
        }

        return null;
    }

    function iniciarFormulariosDatosBase() {
        var formCliente = document.getElementById('formClienteBase');
        var formServicio = document.getElementById('formServicioBase');
        var formBanco = document.getElementById('formBancoBase');
        var formCuenta = document.getElementById('formCuentaBase');

        if (formCliente) {
            formCliente.onsubmit = guardarCliente;
        }

        if (formServicio) {
            formServicio.onsubmit = guardarServicio;
        }

        if (formBanco) {
            formBanco.onsubmit = guardarBanco;
        }

        if (formCuenta) {
            formCuenta.onsubmit = guardarCuenta;
        }
    }

    function abrirNuevoCliente() {
        limpiarFormularioCliente();
        asignarTexto('tituloModalClienteBase', 'Nuevo cliente');
        asignarValor('clienteModoBase', 'crear');
        abrirModalSeguro('modalClienteBase');
    }

    function abrirNuevoServicio() {
        limpiarFormularioServicio();
        asignarTexto('tituloModalServicioBase', 'Nuevo servicio');
        asignarValor('servicioModoBase', 'crear');
        abrirModalSeguro('modalServicioBase');
    }

    function abrirNuevoBanco() {
        limpiarFormularioBanco();
        asignarTexto('tituloModalBancoBase', 'Nuevo banco');
        asignarValor('bancoModoBase', 'crear');
        abrirModalSeguro('modalBancoBase');
    }

    function abrirNuevaCuenta() {
        limpiarFormularioCuenta();
        cargarSelectBancosCuenta();
        asignarTexto('tituloModalCuentaBase', 'Nueva cuenta de ahorro');
        asignarValor('cuentaModoBase', 'crear');
        abrirModalSeguro('modalCuentaBase');
    }

    function editarRegistro(tipo, codigo) {
        if (tipo === 'cliente') {
            editarCliente(codigo);
        }

        if (tipo === 'servicio') {
            editarServicio(codigo);
        }

        if (tipo === 'banco') {
            editarBanco(codigo);
        }

        if (tipo === 'cuenta') {
            editarCuenta(codigo);
        }
    }

    function editarCliente(codigo) {
        var cliente = buscarPorCodigo(appData.clientes, codigo);

        if (!cliente) {
            mostrarAviso('error', 'Cliente no encontrado', 'No se encontró el cliente temporal seleccionado.');
            return;
        }

        asignarTexto('tituloModalClienteBase', 'Editar cliente');
        asignarValor('clienteModoBase', 'editar');
        asignarValor('clienteCodigoBase', cliente.codigo);
        asignarValor('clienteTipoBase', cliente.tipo);
        asignarValor('clienteDocumentoBase', cliente.documento);
        asignarValor('clienteRazonBase', cliente.razonSocial);
        asignarValor('clienteNombresBase', cliente.nombres);
        asignarValor('clienteDireccionBase', cliente.direccion);
        asignarValor('clienteCelularBase', cliente.celular);
        asignarValor('clienteCorreoBase', cliente.correo);
        asignarValor('clienteEstadoBase', cliente.estado);

        abrirModalSeguro('modalClienteBase');
    }

    function editarServicio(codigo) {
        var servicio = buscarPorCodigo(appData.servicios, codigo);

        if (!servicio) {
            mostrarAviso('error', 'Servicio no encontrado', 'No se encontró el servicio temporal seleccionado.');
            return;
        }

        asignarTexto('tituloModalServicioBase', 'Editar servicio');
        asignarValor('servicioModoBase', 'editar');
        asignarValor('servicioCodigoBase', servicio.codigo);
        asignarValor('servicioNombreBase', servicio.nombre);
        asignarValor('servicioCategoriaBase', servicio.categoria);
        asignarValor('servicioDescripcionBase', servicio.descripcionBase);
        asignarValor('servicioMontoBase', servicio.montoSugerido);
        asignarValor('servicioEstadoBase', servicio.estado);

        abrirModalSeguro('modalServicioBase');
    }

    function editarBanco(codigo) {
        var banco = buscarPorCodigo(appData.bancos, codigo);

        if (!banco) {
            mostrarAviso('error', 'Banco no encontrado', 'No se encontró el banco temporal seleccionado.');
            return;
        }

        asignarTexto('tituloModalBancoBase', 'Editar banco');
        asignarValor('bancoModoBase', 'editar');
        asignarValor('bancoCodigoBase', banco.codigo);
        asignarValor('bancoNombreBase', banco.nombre);
        asignarValor('bancoEstadoBase', banco.estado);

        abrirModalSeguro('modalBancoBase');
    }

    function editarCuenta(codigo) {
        var cuenta = buscarPorCodigo(appData.cuentas, codigo);

        if (!cuenta) {
            mostrarAviso('error', 'Cuenta no encontrada', 'No se encontró la cuenta temporal seleccionada.');
            return;
        }

        cargarSelectBancosCuenta();

        asignarTexto('tituloModalCuentaBase', 'Editar cuenta de ahorro');
        asignarValor('cuentaModoBase', 'editar');
        asignarValor('cuentaCodigoBase', cuenta.codigo);
        asignarValor('cuentaBancoBase', cuenta.bancoCodigo);
        asignarValor('cuentaTipoBase', cuenta.tipoCuenta);
        asignarValor('cuentaNumeroBase', cuenta.numeroCuenta);
        asignarValor('cuentaCciBase', cuenta.cci);
        asignarValor('cuentaTitularBase', cuenta.titular);
        asignarValor('cuentaDefectoBase', cuenta.porDefecto ? 'Sí' : 'No');
        asignarValor('cuentaEstadoBase', cuenta.estado);

        abrirModalSeguro('modalCuentaBase');
    }

    function guardarCliente(evento) {
        evento.preventDefault();

        var modo = obtenerValor('clienteModoBase');
        var codigo = parseInt(obtenerValor('clienteCodigoBase'), 10);
        var tipo = obtenerValor('clienteTipoBase');
        var documento = limpiarTexto(obtenerValor('clienteDocumentoBase'));
        var razonSocial = limpiarTexto(obtenerValor('clienteRazonBase'));
        var nombres = limpiarTexto(obtenerValor('clienteNombresBase'));

        if (documento === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el RUC/DNI del cliente.');
            return;
        }

        if (tipo === 'Empresa' && razonSocial === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa la razón social del cliente empresa.');
            return;
        }

        if (tipo === 'Persona natural' && nombres === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa los nombres y apellidos del cliente.');
            return;
        }

        var cliente = {
            codigo: modo === 'editar' ? codigo : obtenerSiguienteCodigo(appData.clientes),
            tipo: tipo,
            documento: documento,
            razonSocial: razonSocial,
            nombres: nombres,
            direccion: limpiarTexto(obtenerValor('clienteDireccionBase')),
            celular: limpiarTexto(obtenerValor('clienteCelularBase')),
            correo: limpiarTexto(obtenerValor('clienteCorreoBase')),
            estado: obtenerValor('clienteEstadoBase'),
            nombre: tipo === 'Empresa' ? razonSocial : nombres
        };

        if (modo === 'editar') {
            reemplazarPorCodigo(appData.clientes, codigo, cliente);
            registrarAuditoriaDatosBase('Cliente editado', 'Se editó el cliente temporal ' + obtenerNombreCliente(cliente) + '.');
            mostrarAviso('success', 'Cliente actualizado', 'El cliente temporal fue actualizado correctamente.');
        } else {
            appData.clientes.push(cliente);
            registrarAuditoriaDatosBase('Cliente creado', 'Se creó el cliente temporal ' + obtenerNombreCliente(cliente) + '.');
            mostrarAviso('success', 'Cliente creado', 'El cliente temporal fue agregado correctamente.');
        }

        cerrarModalSeguro('modalClienteBase');
        pintarTodoDatosBase();
    }

    function guardarServicio(evento) {
        evento.preventDefault();

        var modo = obtenerValor('servicioModoBase');
        var codigo = parseInt(obtenerValor('servicioCodigoBase'), 10);
        var nombre = limpiarTexto(obtenerValor('servicioNombreBase'));
        var categoria = obtenerValor('servicioCategoriaBase');
        var descripcionBase = limpiarTexto(obtenerValor('servicioDescripcionBase'));
        var monto = parseFloat(obtenerValor('servicioMontoBase'));

        if (nombre === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el nombre del servicio.');
            return;
        }

        if (descripcionBase === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa la descripción base del servicio.');
            return;
        }

        if (isNaN(monto) || monto < 0) {
            mostrarAviso('warning', 'Monto inválido', 'Ingresa un monto sugerido válido en soles.');
            return;
        }

        var servicio = {
            codigo: modo === 'editar' ? codigo : obtenerSiguienteCodigo(appData.servicios),
            nombre: nombre,
            categoria: categoria,
            descripcionBase: descripcionBase,
            montoSugerido: monto,
            estado: obtenerValor('servicioEstadoBase')
        };

        if (modo === 'editar') {
            reemplazarPorCodigo(appData.servicios, codigo, servicio);
            registrarAuditoriaDatosBase('Servicio editado', 'Se editó el servicio temporal ' + servicio.nombre + '.');
            mostrarAviso('success', 'Servicio actualizado', 'El servicio temporal fue actualizado correctamente.');
        } else {
            appData.servicios.push(servicio);
            registrarAuditoriaDatosBase('Servicio creado', 'Se creó el servicio temporal ' + servicio.nombre + '.');
            mostrarAviso('success', 'Servicio creado', 'El servicio temporal fue agregado correctamente.');
        }

        cerrarModalSeguro('modalServicioBase');
        pintarTodoDatosBase();
    }

    function guardarBanco(evento) {
        evento.preventDefault();

        var modo = obtenerValor('bancoModoBase');
        var codigo = parseInt(obtenerValor('bancoCodigoBase'), 10);
        var nombre = limpiarTexto(obtenerValor('bancoNombreBase'));

        if (nombre === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el nombre del banco.');
            return;
        }

        var banco = {
            codigo: modo === 'editar' ? codigo : obtenerSiguienteCodigo(appData.bancos),
            nombre: nombre,
            estado: obtenerValor('bancoEstadoBase')
        };

        if (modo === 'editar') {
            reemplazarPorCodigo(appData.bancos, codigo, banco);
            registrarAuditoriaDatosBase('Banco editado', 'Se editó el banco temporal ' + banco.nombre + '.');
            mostrarAviso('success', 'Banco actualizado', 'El banco temporal fue actualizado correctamente.');
        } else {
            appData.bancos.push(banco);
            registrarAuditoriaDatosBase('Banco creado', 'Se creó el banco temporal ' + banco.nombre + '.');
            mostrarAviso('success', 'Banco creado', 'El banco temporal fue agregado correctamente.');
        }

        cerrarModalSeguro('modalBancoBase');
        pintarTodoDatosBase();
    }

    function guardarCuenta(evento) {
        evento.preventDefault();

        var modo = obtenerValor('cuentaModoBase');
        var codigo = parseInt(obtenerValor('cuentaCodigoBase'), 10);
        var bancoCodigo = parseInt(obtenerValor('cuentaBancoBase'), 10);
        var tipoCuenta = obtenerValor('cuentaTipoBase');
        var numeroCuenta = limpiarTexto(obtenerValor('cuentaNumeroBase'));
        var titular = limpiarTexto(obtenerValor('cuentaTitularBase'));
        var porDefecto = obtenerValor('cuentaDefectoBase') === 'Sí';

        if (isNaN(bancoCodigo)) {
            mostrarAviso('warning', 'Dato obligatorio', 'Selecciona un banco.');
            return;
        }

        if (numeroCuenta === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el número de cuenta.');
            return;
        }

        if (titular === '') {
            mostrarAviso('warning', 'Dato obligatorio', 'Ingresa el titular de la cuenta.');
            return;
        }

        if (porDefecto) {
            quitarCuentasPorDefecto();
        }

        var cuenta = {
            codigo: modo === 'editar' ? codigo : obtenerSiguienteCodigo(appData.cuentas),
            bancoCodigo: bancoCodigo,
            tipoCuenta: tipoCuenta,
            numeroCuenta: numeroCuenta,
            cci: limpiarTexto(obtenerValor('cuentaCciBase')),
            titular: titular,
            porDefecto: porDefecto,
            estado: obtenerValor('cuentaEstadoBase')
        };

        if (modo === 'editar') {
            reemplazarPorCodigo(appData.cuentas, codigo, cuenta);
            registrarAuditoriaDatosBase('Cuenta editada', 'Se editó una cuenta temporal de ' + obtenerNombreBanco(cuenta.bancoCodigo) + '.');
            mostrarAviso('success', 'Cuenta actualizada', 'La cuenta temporal fue actualizada correctamente.');
        } else {
            appData.cuentas.push(cuenta);
            registrarAuditoriaDatosBase('Cuenta creada', 'Se creó una cuenta temporal de ' + obtenerNombreBanco(cuenta.bancoCodigo) + '.');
            mostrarAviso('success', 'Cuenta creada', 'La cuenta temporal fue agregada correctamente.');
        }

        cerrarModalSeguro('modalCuentaBase');
        pintarTodoDatosBase();
    }

    function quitarCuentasPorDefecto() {
        for (var i = 0; i < appData.cuentas.length; i++) {
            appData.cuentas[i].porDefecto = false;
        }
    }

    function pedirEliminarRegistro(tipo, codigo) {
        var nombre = '';

        if (tipo === 'cliente') {
            var cliente = buscarPorCodigo(appData.clientes, codigo);
            nombre = cliente ? obtenerNombreCliente(cliente) : '';
        }

        if (tipo === 'servicio') {
            var servicio = buscarPorCodigo(appData.servicios, codigo);
            nombre = servicio ? servicio.nombre : '';
        }

        if (tipo === 'banco') {
            var banco = buscarPorCodigo(appData.bancos, codigo);
            nombre = banco ? banco.nombre : '';
        }

        if (tipo === 'cuenta') {
            var cuenta = buscarPorCodigo(appData.cuentas, codigo);
            nombre = cuenta ? obtenerNombreBanco(cuenta.bancoCodigo) + ' - ' + cuenta.numeroCuenta : '';
        }

        estadoDatosBase.eliminacionPendiente = {
            tipo: tipo,
            codigo: codigo,
            nombre: nombre
        };

        asignarTexto(
            'textoConfirmarEliminarBase',
            'Se eliminará el registro temporal: ' + nombre + '.'
        );

        abrirModalSeguro('modalConfirmarEliminarBase');
    }

    function confirmarEliminacionPendiente() {
        var pendiente = estadoDatosBase.eliminacionPendiente;

        if (!pendiente) {
            cerrarModalSeguro('modalConfirmarEliminarBase');
            return;
        }

        if (pendiente.tipo === 'cliente') {
            eliminarPorCodigo(appData.clientes, pendiente.codigo);
            registrarAuditoriaDatosBase('Cliente eliminado', 'Se eliminó el cliente temporal ' + pendiente.nombre + '.');
            mostrarAviso('success', 'Cliente eliminado', 'El cliente temporal fue eliminado correctamente.');
        }

        if (pendiente.tipo === 'servicio') {
            eliminarPorCodigo(appData.servicios, pendiente.codigo);
            registrarAuditoriaDatosBase('Servicio eliminado', 'Se eliminó el servicio temporal ' + pendiente.nombre + '.');
            mostrarAviso('success', 'Servicio eliminado', 'El servicio temporal fue eliminado correctamente.');
        }

        if (pendiente.tipo === 'banco') {
            eliminarPorCodigo(appData.bancos, pendiente.codigo);
            registrarAuditoriaDatosBase('Banco eliminado', 'Se eliminó el banco temporal ' + pendiente.nombre + '.');
            mostrarAviso('success', 'Banco eliminado', 'El banco temporal fue eliminado correctamente.');
        }

        if (pendiente.tipo === 'cuenta') {
            eliminarPorCodigo(appData.cuentas, pendiente.codigo);
            registrarAuditoriaDatosBase('Cuenta eliminada', 'Se eliminó la cuenta temporal ' + pendiente.nombre + '.');
            mostrarAviso('success', 'Cuenta eliminada', 'La cuenta temporal fue eliminada correctamente.');
        }

        estadoDatosBase.eliminacionPendiente = null;
        cerrarModalSeguro('modalConfirmarEliminarBase');
        pintarTodoDatosBase();
    }

    function pintarTodoDatosBase() {
        pintarClientesBase();
        pintarServiciosBase();
        pintarBancosBase();
        pintarCuentasBase();
        pintarDashboardDatosBase();
        pintarVistaRapidaDatosBase();
        pintarAuditoriaDatosBase();
    }

    function pintarClientesBase() {
        var tbody = document.getElementById('tablaClientesBase');

        if (!tbody) {
            return;
        }

        if (appData.clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="estado-vacio"><strong>No hay clientes temporales</strong>Agrega un cliente para usarlo luego en los recibos.</td></tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.clientes.length; i++) {
            var cliente = appData.clientes[i];

            html += '<tr>';
            html += '<td><span class="codigo-pill">' + cliente.codigo + '</span></td>';
            html += '<td>' + escaparHtml(cliente.tipo) + '</td>';
            html += '<td>' + escaparHtml(cliente.documento) + '</td>';
            html += '<td>' + escaparHtml(cliente.razonSocial) + '</td>';
            html += '<td>' + escaparHtml(cliente.nombres) + '</td>';
            html += '<td>' + escaparHtml(cliente.celular) + '</td>';
            html += '<td>' + obtenerBadgeEstado(cliente.estado) + '</td>';
            html += '<td>' + obtenerBotonesAccion('cliente', cliente.codigo) + '</td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
    }

    function pintarServiciosBase() {
        var tbody = document.getElementById('tablaServiciosBase');

        if (!tbody) {
            return;
        }

        if (appData.servicios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="estado-vacio"><strong>No hay servicios temporales</strong>Agrega servicios para construir los conceptos del recibo.</td></tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.servicios.length; i++) {
            var servicio = appData.servicios[i];

            html += '<tr>';
            html += '<td><span class="codigo-pill">S-' + servicio.codigo + '</span></td>';
            html += '<td>' + escaparHtml(servicio.nombre) + '</td>';
            html += '<td>' + escaparHtml(servicio.categoria) + '</td>';
            html += '<td>' + escaparHtml(servicio.descripcionBase) + '</td>';
            html += '<td class="monto-soles">' + formatearSoles(servicio.montoSugerido) + '</td>';
            html += '<td>' + obtenerBadgeEstado(servicio.estado) + '</td>';
            html += '<td>' + obtenerBotonesAccion('servicio', servicio.codigo) + '</td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
    }

    function pintarBancosBase() {
        var tbody = document.getElementById('tablaBancosBase');

        if (!tbody) {
            return;
        }

        if (appData.bancos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="estado-vacio"><strong>No hay bancos temporales</strong>Agrega bancos para vincularlos a cuentas de ahorro.</td></tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.bancos.length; i++) {
            var banco = appData.bancos[i];

            html += '<tr>';
            html += '<td><span class="codigo-pill">B-' + banco.codigo + '</span></td>';
            html += '<td>' + escaparHtml(banco.nombre) + '</td>';
            html += '<td>' + obtenerBadgeEstado(banco.estado) + '</td>';
            html += '<td>' + obtenerBotonesAccion('banco', banco.codigo) + '</td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
    }

    function pintarCuentasBase() {
        var tbody = document.getElementById('tablaCuentasBase');

        if (!tbody) {
            return;
        }

        if (appData.cuentas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="estado-vacio"><strong>No hay cuentas temporales</strong>Agrega cuentas de ahorro para mostrarlas luego en los recibos.</td></tr>';
            return;
        }

        var html = '';

        for (var i = 0; i < appData.cuentas.length; i++) {
            var cuenta = appData.cuentas[i];

            html += '<tr>';
            html += '<td><span class="codigo-pill">C-' + cuenta.codigo + '</span></td>';
            html += '<td>' + escaparHtml(obtenerNombreBanco(cuenta.bancoCodigo)) + '</td>';
            html += '<td>' + escaparHtml(cuenta.tipoCuenta) + '</td>';
            html += '<td>' + escaparHtml(cuenta.numeroCuenta) + '</td>';
            html += '<td>' + escaparHtml(cuenta.cci) + '</td>';
            html += '<td>' + escaparHtml(cuenta.titular) + '</td>';
            html += '<td>' + (cuenta.porDefecto ? '<span class="badge badge--warning">Sí</span>' : '<span class="badge badge--info">No</span>') + '</td>';
            html += '<td>' + obtenerBadgeEstado(cuenta.estado) + '</td>';
            html += '<td>' + obtenerBotonesAccion('cuenta', cuenta.codigo) + '</td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
    }

    function pintarDashboardDatosBase() {
        asignarTexto('statClientes', appData.clientes.length);
        asignarTexto('statServicios', appData.servicios.length);
        asignarTexto('statPlantillas', appData.plantillas ? appData.plantillas.length : 0);
        asignarTexto('statRecibos', appData.recibos ? appData.recibos.length : 0);
    }

    function pintarVistaRapidaDatosBase() {
        var tbody = document.getElementById('tablaVistaRapida');

        if (!tbody) {
            return;
        }

        var cliente = appData.clientes.length > 0 ? appData.clientes[0] : null;
        var servicio = appData.servicios.length > 0 ? appData.servicios[0] : null;
        var cuenta = appData.cuentas.length > 0 ? obtenerCuentaPorDefecto() : null;

        var html = '';

        if (cliente) {
            html += '<tr>';
            html += '<td>Cliente</td>';
            html += '<td>' + escaparHtml(obtenerNombreCliente(cliente)) + '</td>';
            html += '<td>' + obtenerBadgeEstado(cliente.estado) + '</td>';
            html += '</tr>';
        }

        if (servicio) {
            html += '<tr>';
            html += '<td>Servicio</td>';
            html += '<td>' + escaparHtml(servicio.nombre) + ' - ' + formatearSoles(servicio.montoSugerido) + '</td>';
            html += '<td>' + obtenerBadgeEstado(servicio.estado) + '</td>';
            html += '</tr>';
        }

        if (cuenta) {
            html += '<tr>';
            html += '<td>Cuenta por defecto</td>';
            html += '<td>' + escaparHtml(obtenerNombreBanco(cuenta.bancoCodigo)) + ' - ' + escaparHtml(cuenta.numeroCuenta) + '</td>';
            html += '<td><span class="badge badge--warning">Demo</span></td>';
            html += '</tr>';
        }

        if (html === '') {
            html = '<tr><td colspan="3">No hay datos temporales para mostrar.</td></tr>';
        }

        tbody.innerHTML = html;
    }

    function pintarAuditoriaDatosBase() {
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

    function cargarSelectBancosCuenta() {
        var select = document.getElementById('cuentaBancoBase');

        if (!select) {
            return;
        }

        var html = '';

        for (var i = 0; i < appData.bancos.length; i++) {
            if (appData.bancos[i].estado === 'Activo') {
                html += '<option value="' + appData.bancos[i].codigo + '">' + escaparHtml(appData.bancos[i].nombre) + '</option>';
            }
        }

        if (html === '') {
            html = '<option value="">No hay bancos activos</option>';
        }

        select.innerHTML = html;
    }

    function obtenerBotonesAccion(tipo, codigo) {
        var html = '';

        html += '<div class="acciones-tabla">';
        html += '<button type="button" class="btn btn--light btn--sm" data-accion="editar" data-tipo="' + tipo + '" data-codigo="' + codigo + '">Editar</button>';
        html += '<button type="button" class="btn btn--danger btn--sm" data-accion="eliminar" data-tipo="' + tipo + '" data-codigo="' + codigo + '">Eliminar</button>';
        html += '</div>';

        return html;
    }

    function obtenerBadgeEstado(estado) {
        if (estado === 'Activo' || estado === 'Activa') {
            return '<span class="badge badge--success">' + escaparHtml(estado) + '</span>';
        }

        return '<span class="badge badge--danger">' + escaparHtml(estado) + '</span>';
    }

    function obtenerCuentaPorDefecto() {
        for (var i = 0; i < appData.cuentas.length; i++) {
            if (appData.cuentas[i].porDefecto) {
                return appData.cuentas[i];
            }
        }

        return appData.cuentas.length > 0 ? appData.cuentas[0] : null;
    }

    function obtenerNombreBanco(codigo) {
        var banco = buscarPorCodigo(appData.bancos, codigo);
        return banco ? banco.nombre : 'Banco no encontrado';
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

    function eliminarPorCodigo(lista, codigo) {
        for (var i = lista.length - 1; i >= 0; i--) {
            if (parseInt(lista[i].codigo, 10) === parseInt(codigo, 10)) {
                lista.splice(i, 1);
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

    function registrarAuditoriaDatosBase(tipo, descripcion) {
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

    function limpiarFormularioCliente() {
        resetFormulario('formClienteBase');
        asignarValor('clienteTipoBase', 'Empresa');
        asignarValor('clienteEstadoBase', 'Activo');
    }

    function limpiarFormularioServicio() {
        resetFormulario('formServicioBase');
        asignarValor('servicioCategoriaBase', 'Servicios de contabilidad');
        asignarValor('servicioMontoBase', '0.00');
        asignarValor('servicioEstadoBase', 'Activo');
    }

    function limpiarFormularioBanco() {
        resetFormulario('formBancoBase');
        asignarValor('bancoEstadoBase', 'Activo');
    }

    function limpiarFormularioCuenta() {
        resetFormulario('formCuentaBase');
        asignarValor('cuentaTipoBase', 'Ahorros');
        asignarValor('cuentaDefectoBase', 'No');
        asignarValor('cuentaEstadoBase', 'Activo');
    }

    function resetFormulario(idFormulario) {
        var form = document.getElementById(idFormulario);

        if (form) {
            form.reset();
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