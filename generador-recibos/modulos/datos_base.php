<section class="section-page" id="section-datos-base">
    <div class="page-title">
        <div>
            <span class="eyebrow">Catálogos temporales</span>
            <h2>Datos base</h2>
            <p>
                Aquí se administrarán clientes, servicios, bancos y cuentas de ahorro temporales.
            </p>
        </div>

        <div class="page-actions" id="accionesDatosBase">
            <button type="button" class="btn btn--primary" id="btnNuevoCliente">
                Nuevo cliente
            </button>
        </div>
    </div>

    <div class="tabs-demo tabs-datos-base">
        <button type="button" class="tab-demo activo" data-tab-base="clientes">Clientes</button>
        <button type="button" class="tab-demo" data-tab-base="servicios">Servicios</button>
        <button type="button" class="tab-demo" data-tab-base="bancos">Bancos</button>
        <button type="button" class="tab-demo" data-tab-base="cuentas">Cuentas de ahorro</button>
    </div>

    <div class="tab-base-panel activo" id="tab-base-clientes">
        <div class="panel">
            <div class="panel__header">
                <div>
                    <h3>Clientes temporales</h3>
                    <p>Clientes demo que podrán usarse después al generar recibos temporales.</p>
                </div>
                <span class="badge badge--info">Sin MySQL</span>
            </div>

            <div class="table-responsive">
                <table class="app-table app-table--acciones">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>RUC/DNI</th>
                            <th>Razón social</th>
                            <th>Nombres y apellidos</th>
                            <th>Celular</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaClientesBase">
                        <tr>
                            <td colspan="8">Cargando clientes temporales...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-base-panel" id="tab-base-servicios">
        <div class="panel">
            <div class="panel__header">
                <div>
                    <h3>Servicios temporales</h3>
                    <p>Catálogo demo de conceptos para recibos en soles.</p>
                </div>
                <span class="badge badge--success">S/</span>
            </div>

            <div class="table-responsive">
                <table class="app-table app-table--acciones">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del servicio</th>
                            <th>Categoría</th>
                            <th>Descripción base</th>
                            <th>Monto sugerido</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaServiciosBase">
                        <tr>
                            <td colspan="7">Cargando servicios temporales...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-base-panel" id="tab-base-bancos">
        <div class="panel">
            <div class="panel__header">
                <div>
                    <h3>Bancos temporales</h3>
                    <p>Catálogo demo de bancos, billeteras y medios vinculados a cuentas.</p>
                </div>
                <span class="badge badge--info">Demo</span>
            </div>

            <div class="table-responsive">
                <table class="app-table app-table--acciones">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del banco</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBancosBase">
                        <tr>
                            <td colspan="4">Cargando bancos temporales...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-base-panel" id="tab-base-cuentas">
        <div class="panel">
            <div class="panel__header">
                <div>
                    <h3>Cuentas de ahorro temporales</h3>
                    <p>Cuentas demo que podrán mostrarse en las plantillas de recibos.</p>
                </div>
                <span class="badge badge--warning">Cuenta por defecto</span>
            </div>

            <div class="table-responsive">
                <table class="app-table app-table--acciones">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Banco</th>
                            <th>Tipo de cuenta</th>
                            <th>Número de cuenta</th>
                            <th>CCI</th>
                            <th>Titular</th>
                            <th>Por defecto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCuentasBase">
                        <tr>
                            <td colspan="9">Cargando cuentas temporales...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div class="modal" id="modalClienteBase">
    <div class="modal__dialog">
        <form id="formClienteBase">
            <div class="modal__header">
                <div>
                    <h3 id="tituloModalClienteBase">Nuevo cliente</h3>
                    <p>El código del cliente se genera automáticamente.</p>
                </div>

                <button type="button" class="modal__close" data-close-modal="modalClienteBase">
                    ×
                </button>
            </div>

            <div class="modal__body">
                <input type="hidden" id="clienteModoBase" value="crear">
                <input type="hidden" id="clienteCodigoBase" value="">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tipo de cliente</label>
                        <select class="form-control" id="clienteTipoBase" required>
                            <option value="Empresa">Empresa</option>
                            <option value="Persona natural">Persona natural</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>RUC/DNI</label>
                        <input type="text" class="form-control" id="clienteDocumentoBase" required>
                    </div>

                    <div class="form-group">
                        <label>Razón social</label>
                        <input type="text" class="form-control" id="clienteRazonBase">
                    </div>

                    <div class="form-group">
                        <label>Nombres y apellidos</label>
                        <input type="text" class="form-control" id="clienteNombresBase">
                    </div>

                    <div class="form-group form-group--full">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="clienteDireccionBase">
                    </div>

                    <div class="form-group">
                        <label>Celular</label>
                        <input type="text" class="form-control" id="clienteCelularBase">
                    </div>

                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" class="form-control" id="clienteCorreoBase">
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" id="clienteEstadoBase" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn btn--light" data-close-modal="modalClienteBase">
                    Cancelar
                </button>
                <button type="submit" class="btn btn--primary">
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalServicioBase">
    <div class="modal__dialog">
        <form id="formServicioBase">
            <div class="modal__header">
                <div>
                    <h3 id="tituloModalServicioBase">Nuevo servicio</h3>
                    <p>El código del servicio se genera automáticamente.</p>
                </div>

                <button type="button" class="modal__close" data-close-modal="modalServicioBase">
                    ×
                </button>
            </div>

            <div class="modal__body">
                <input type="hidden" id="servicioModoBase" value="crear">
                <input type="hidden" id="servicioCodigoBase" value="">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre del servicio</label>
                        <input type="text" class="form-control" id="servicioNombreBase" required>
                    </div>

                    <div class="form-group">
                        <label>Categoría</label>
                        <select class="form-control" id="servicioCategoriaBase" required>
                            <option value="Servicios de contabilidad">Servicios de contabilidad</option>
                            <option value="Periodos pendientes de pago">Periodos pendientes de pago</option>
                            <option value="Aportaciones del empleador">Aportaciones del empleador</option>
                            <option value="Otros servicios o trámites">Otros servicios o trámites</option>
                        </select>
                    </div>

                    <div class="form-group form-group--full">
                        <label>Descripción base</label>
                        <input type="text" class="form-control" id="servicioDescripcionBase" required>
                    </div>

                    <div class="form-group">
                        <label>Monto sugerido en soles</label>
                        <input type="number" class="form-control" id="servicioMontoBase" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" id="servicioEstadoBase" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn btn--light" data-close-modal="modalServicioBase">
                    Cancelar
                </button>
                <button type="submit" class="btn btn--primary">
                    Guardar servicio
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalBancoBase">
    <div class="modal__dialog modal__dialog--small">
        <form id="formBancoBase">
            <div class="modal__header">
                <div>
                    <h3 id="tituloModalBancoBase">Nuevo banco</h3>
                    <p>El código del banco se genera automáticamente.</p>
                </div>

                <button type="button" class="modal__close" data-close-modal="modalBancoBase">
                    ×
                </button>
            </div>

            <div class="modal__body">
                <input type="hidden" id="bancoModoBase" value="crear">
                <input type="hidden" id="bancoCodigoBase" value="">

                <div class="form-grid">
                    <div class="form-group form-group--full">
                        <label>Nombre del banco</label>
                        <input type="text" class="form-control" id="bancoNombreBase" required>
                    </div>

                    <div class="form-group form-group--full">
                        <label>Estado</label>
                        <select class="form-control" id="bancoEstadoBase" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn btn--light" data-close-modal="modalBancoBase">
                    Cancelar
                </button>
                <button type="submit" class="btn btn--primary">
                    Guardar banco
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalCuentaBase">
    <div class="modal__dialog">
        <form id="formCuentaBase">
            <div class="modal__header">
                <div>
                    <h3 id="tituloModalCuentaBase">Nueva cuenta de ahorro</h3>
                    <p>Si marcas una cuenta como predeterminada, las demás dejarán de serlo.</p>
                </div>

                <button type="button" class="modal__close" data-close-modal="modalCuentaBase">
                    ×
                </button>
            </div>

            <div class="modal__body">
                <input type="hidden" id="cuentaModoBase" value="crear">
                <input type="hidden" id="cuentaCodigoBase" value="">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Banco</label>
                        <select class="form-control" id="cuentaBancoBase" required></select>
                    </div>

                    <div class="form-group">
                        <label>Tipo de cuenta</label>
                        <select class="form-control" id="cuentaTipoBase" required>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                            <option value="Yape">Yape</option>
                            <option value="Plin">Plin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Número de cuenta</label>
                        <input type="text" class="form-control" id="cuentaNumeroBase" required>
                    </div>

                    <div class="form-group">
                        <label>CCI</label>
                        <input type="text" class="form-control" id="cuentaCciBase">
                    </div>

                    <div class="form-group form-group--full">
                        <label>Titular</label>
                        <input type="text" class="form-control" id="cuentaTitularBase" required>
                    </div>

                    <div class="form-group">
                        <label>Cuenta por defecto</label>
                        <select class="form-control" id="cuentaDefectoBase" required>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" id="cuentaEstadoBase" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn btn--light" data-close-modal="modalCuentaBase">
                    Cancelar
                </button>
                <button type="submit" class="btn btn--primary">
                    Guardar cuenta
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalConfirmarEliminarBase">
    <div class="modal__dialog modal__dialog--small">
        <div class="modal__header">
            <div>
                <h3>Confirmar eliminación</h3>
                <p id="textoConfirmarEliminarBase">Esta acción eliminará el registro temporal.</p>
            </div>

            <button type="button" class="modal__close" data-close-modal="modalConfirmarEliminarBase">
                ×
            </button>
        </div>

        <div class="modal__body">
            <div class="alert-box alert-box--warning">
                <strong>Atención</strong>
                <p>Este cambio solo afecta la data temporal del demo. Al recargar la página, todo volverá a la data inicial.</p>
            </div>
        </div>

        <div class="modal__footer">
            <button type="button" class="btn btn--light" data-close-modal="modalConfirmarEliminarBase">
                Cancelar
            </button>
            <button type="button" class="btn btn--danger" id="btnConfirmarEliminarBase">
                Eliminar
            </button>
        </div>
    </div>
</div>