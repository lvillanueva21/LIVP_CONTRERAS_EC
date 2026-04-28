<section class="section-page" id="section-plantillas">
    <div class="page-title">
        <div>
            <span class="eyebrow">Diseño de recibos</span>
            <h2>Plantillas</h2>
            <p>
                Aquí se crearán versiones de recibos con logo, sin logo, verticales,
                horizontales y con bloques visibles personalizados.
            </p>
        </div>

        <div class="page-actions">
            <button type="button" class="btn btn--primary" id="btnNuevaPlantilla">
                Nueva plantilla
            </button>
        </div>
    </div>

    <div class="cards-grid cards-grid--plantillas" id="contenedorPlantillas">
        <article class="simple-card">
            <div class="simple-card__top">
                <span class="badge badge--info">Cargando</span>
                <span>Demo</span>
            </div>
            <h3>Plantillas temporales</h3>
            <p>Cargando plantillas demo.</p>
        </article>
    </div>
</section>

<div class="modal" id="modalPlantilla">
    <div class="modal__dialog modal__dialog--wide">
        <form id="formPlantilla">
            <div class="modal__header">
                <div>
                    <h3 id="tituloModalPlantilla">Nueva plantilla</h3>
                    <p>Define qué datos se mostrarán en esta versión del recibo.</p>
                </div>

                <button type="button" class="modal__close" data-close-modal="modalPlantilla">
                    ×
                </button>
            </div>

            <div class="modal__body">
                <input type="hidden" id="plantillaModo" value="crear">
                <input type="hidden" id="plantillaCodigo" value="">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre de plantilla</label>
                        <input type="text" class="form-control" id="plantillaNombre" required>
                    </div>

                    <div class="form-group">
                        <label>Orientación</label>
                        <select class="form-control" id="plantillaOrientacion" required>
                            <option value="Horizontal">Horizontal</option>
                            <option value="Vertical">Vertical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select class="form-control" id="plantillaEstado" required>
                            <option value="Activa">Activa</option>
                            <option value="Inactiva">Inactiva</option>
                        </select>
                    </div>
                </div>

                <div class="opciones-plantilla-grid">
                    <div class="opciones-plantilla-card">
                        <h4>Empresa</h4>

                        <div class="form-grid form-grid--compact">
                            <div class="form-group">
                                <label>Mostrar logo</label>
                                <select class="form-control" id="plantillaMostrarLogo">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar datos de la empresa</label>
                                <select class="form-control" id="plantillaMostrarDatosEmpresa">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar RUC empresa</label>
                                <select class="form-control" id="plantillaMostrarRucEmpresa">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar razón social empresa</label>
                                <select class="form-control" id="plantillaMostrarRazonSocialEmpresa">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar rubro empresa</label>
                                <select class="form-control" id="plantillaMostrarRubroEmpresa">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar dirección empresa</label>
                                <select class="form-control" id="plantillaMostrarDireccionEmpresa">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="opciones-plantilla-card">
                        <h4>Cliente y cuentas</h4>

                        <div class="form-grid form-grid--compact">
                            <div class="form-group">
                                <label>Mostrar código cliente</label>
                                <select class="form-control" id="plantillaMostrarCodigoCliente">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar razón social cliente</label>
                                <select class="form-control" id="plantillaMostrarRazonSocialCliente">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar nombres y apellidos cliente</label>
                                <select class="form-control" id="plantillaMostrarNombresCliente">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar cuentas bancarias</label>
                                <select class="form-control" id="plantillaMostrarCuentasBancarias">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Usar cuenta por defecto</label>
                                <select class="form-control" id="plantillaUsarCuentaPorDefecto">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Permitir elegir otra cuenta</label>
                                <select class="form-control" id="plantillaPermitirElegirOtraCuenta">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="opciones-plantilla-card opciones-plantilla-card--full">
                        <h4>Bloques del recibo</h4>

                        <div class="form-grid form-grid--compact form-grid--four">
                            <div class="form-group">
                                <label>Mostrar servicios de contabilidad</label>
                                <select class="form-control" id="plantillaMostrarServiciosContabilidad">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar periodos pendientes de pago</label>
                                <select class="form-control" id="plantillaMostrarPeriodosPendientes">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar aportaciones del empleador</label>
                                <select class="form-control" id="plantillaMostrarAportacionesEmpleador">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar otros servicios o trámites</label>
                                <select class="form-control" id="plantillaMostrarOtrosServicios">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Mostrar total general</label>
                                <select class="form-control" id="plantillaMostrarTotalGeneral">
                                    <option value="Sí">Sí</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal__footer">
                <button type="button" class="btn btn--light" data-close-modal="modalPlantilla">
                    Cancelar
                </button>

                <button type="submit" class="btn btn--primary">
                    Guardar plantilla
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalVistaPreviaPlantilla">
    <div class="modal__dialog modal__dialog--preview">
        <div class="modal__header">
            <div>
                <h3>Vista previa de plantilla</h3>
                <p>Representación visual temporal según las opciones activadas.</p>
            </div>

            <button type="button" class="modal__close" data-close-modal="modalVistaPreviaPlantilla">
                ×
            </button>
        </div>

        <div class="modal__body modal__body--preview">
            <div id="vistaPreviaPlantillaContenido"></div>
        </div>

        <div class="modal__footer">
            <button type="button" class="btn btn--light" data-close-modal="modalVistaPreviaPlantilla">
                Cerrar
            </button>
        </div>
    </div>
</div>