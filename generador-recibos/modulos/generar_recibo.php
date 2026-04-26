<section class="section-page" id="section-generar-recibo">
    <form id="formGenerarRecibo">
        <div class="page-title">
            <div>
                <span class="eyebrow">Emisión temporal</span>
                <h2>Generar recibo</h2>
                <p>
                    Aquí se construirá el recibo temporal seleccionando cliente, plantilla,
                    servicios, periodos y totales en soles.
                </p>
            </div>

            <div class="page-actions">
                <button type="button" class="btn btn--light" id="btnVistaPreviaRecibo">
                    Vista previa del recibo
                </button>

                <button type="submit" class="btn btn--primary">
                    Generar recibo temporal
                </button>
            </div>
        </div>

        <div class="generador-grid">
            <article class="panel">
                <div class="panel__header">
                    <div>
                        <h3>Datos principales</h3>
                        <p>Selecciona plantilla, cliente y cuenta bancaria para el recibo temporal.</p>
                    </div>
                    <span class="badge badge--info">Soles</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Plantilla</label>
                        <select class="form-control" id="reciboPlantilla" required></select>
                    </div>

                    <div class="form-group">
                        <label>Cliente</label>
                        <div class="input-action-group">
                            <select class="form-control" id="reciboCliente" required></select>

                            <button type="button" class="btn btn--light" id="btnReciboNuevoCliente">
                                Nuevo cliente
                            </button>
                        </div>
                    </div>

                    <div class="form-group form-group--full" id="bloqueCuentaRecibo">
                        <label>Cuenta bancaria</label>
                        <select class="form-control" id="reciboCuenta"></select>
                        <p class="input-help" id="infoReglaCuentaRecibo">
                            La cuenta se ajustará según la plantilla seleccionada.
                        </p>
                    </div>
                </div>
            </article>

            <article class="panel resumen-totales">
                <div class="panel__header">
                    <div>
                        <h3>Totales del recibo</h3>
                        <p>Los importes se calculan automáticamente en soles.</p>
                    </div>
                    <span class="badge badge--success">S/</span>
                </div>

                <div class="totales-lista">
                    <div>
                        <span>Total servicios de contabilidad</span>
                        <strong id="totalServiciosContabilidad">S/ 0.00</strong>
                    </div>

                    <div>
                        <span>Total periodos pendientes de pago</span>
                        <strong id="totalPeriodosPendientes">S/ 0.00</strong>
                    </div>

                    <div>
                        <span>Total aportaciones del empleador</span>
                        <strong id="totalAportacionesEmpleador">S/ 0.00</strong>
                    </div>

                    <div>
                        <span>Total otros servicios o trámites</span>
                        <strong id="totalOtrosServicios">S/ 0.00</strong>
                    </div>

                    <div class="total-general-box">
                        <span>Total general</span>
                        <strong id="totalGeneralRecibo">S/ 0.00</strong>
                    </div>
                </div>
            </article>
        </div>

        <div class="panel conceptos-panel">
            <div class="panel__header">
                <div>
                    <h3>Conceptos del recibo</h3>
                    <p>Agrega conceptos por bloque. La descripción puede generarse por periodo y también editarse.</p>
                </div>
                <span class="badge badge--info">Temporal</span>
            </div>

            <div class="conceptos-toolbar">
                <button type="button" class="btn btn--light" data-agregar-categoria="Servicios de contabilidad">
                    Agregar servicios de contabilidad
                </button>

                <button type="button" class="btn btn--light" data-agregar-categoria="Periodos pendientes de pago">
                    Agregar periodos pendientes de pago
                </button>

                <button type="button" class="btn btn--light" data-agregar-categoria="Aportaciones del empleador">
                    Agregar aportaciones del empleador
                </button>

                <button type="button" class="btn btn--light" data-agregar-categoria="Otros servicios o trámites">
                    Agregar otros servicios o trámites
                </button>
            </div>

            <div class="concepto-bloque" id="bloqueConceptosServiciosContabilidad">
                <div class="concepto-bloque__header">
                    <h4>Servicios de contabilidad</h4>
                    <span id="contadorServiciosContabilidad">0 conceptos</span>
                </div>

                <div class="table-responsive">
                    <table class="app-table app-table--conceptos">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Servicio</th>
                                <th>Tipo de periodo</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Fecha desde</th>
                                <th>Fecha hasta</th>
                                <th>Descripción generada</th>
                                <th>Descripción editable</th>
                                <th>Monto en soles</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyConceptosServiciosContabilidad">
                            <tr>
                                <td colspan="11" class="estado-vacio">
                                    <strong>No hay servicios de contabilidad</strong>
                                    Agrega conceptos para este bloque.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="concepto-bloque" id="bloqueConceptosPeriodosPendientes">
                <div class="concepto-bloque__header">
                    <h4>Periodos pendientes de pago</h4>
                    <span id="contadorPeriodosPendientes">0 conceptos</span>
                </div>

                <div class="table-responsive">
                    <table class="app-table app-table--conceptos">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Servicio</th>
                                <th>Tipo de periodo</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Fecha desde</th>
                                <th>Fecha hasta</th>
                                <th>Descripción generada</th>
                                <th>Descripción editable</th>
                                <th>Monto en soles</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyConceptosPeriodosPendientes">
                            <tr>
                                <td colspan="11" class="estado-vacio">
                                    <strong>No hay periodos pendientes de pago</strong>
                                    Agrega conceptos para este bloque.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="concepto-bloque" id="bloqueConceptosAportacionesEmpleador">
                <div class="concepto-bloque__header">
                    <h4>Aportaciones del empleador</h4>
                    <span id="contadorAportacionesEmpleador">0 conceptos</span>
                </div>

                <div class="table-responsive">
                    <table class="app-table app-table--conceptos">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Servicio</th>
                                <th>Tipo de periodo</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Fecha desde</th>
                                <th>Fecha hasta</th>
                                <th>Descripción generada</th>
                                <th>Descripción editable</th>
                                <th>Monto en soles</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyConceptosAportacionesEmpleador">
                            <tr>
                                <td colspan="11" class="estado-vacio">
                                    <strong>No hay aportaciones del empleador</strong>
                                    Agrega conceptos para este bloque.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="concepto-bloque" id="bloqueConceptosOtrosServicios">
                <div class="concepto-bloque__header">
                    <h4>Otros servicios o trámites</h4>
                    <span id="contadorOtrosServicios">0 conceptos</span>
                </div>

                <div class="table-responsive">
                    <table class="app-table app-table--conceptos">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Servicio</th>
                                <th>Tipo de periodo</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Fecha desde</th>
                                <th>Fecha hasta</th>
                                <th>Descripción generada</th>
                                <th>Descripción editable</th>
                                <th>Monto en soles</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyConceptosOtrosServicios">
                            <tr>
                                <td colspan="11" class="estado-vacio">
                                    <strong>No hay otros servicios o trámites</strong>
                                    Agrega conceptos para este bloque.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</section>

<div class="modal" id="modalVistaPreviaRecibo">
    <div class="modal__dialog modal__dialog--preview">
        <div class="modal__header">
            <div>
                <h3>Vista previa del recibo</h3>
                <p>Representación temporal del recibo según la plantilla, cliente, cuenta y conceptos agregados.</p>
            </div>

            <button type="button" class="modal__close" data-close-modal="modalVistaPreviaRecibo">
                ×
            </button>
        </div>

        <div class="modal__body modal__body--preview">
            <div id="vistaPreviaReciboContenido"></div>
        </div>

        <div class="modal__footer">
            <button type="button" class="btn btn--light" data-close-modal="modalVistaPreviaRecibo">
                Cerrar
            </button>

            <button type="button" class="btn btn--primary" id="btnGenerarDesdeVistaPrevia">
                Generar recibo temporal
            </button>
        </div>
    </div>
</div>