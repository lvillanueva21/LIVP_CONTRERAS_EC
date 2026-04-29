<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$tabla_proformas = pf_render_tabla();
$clientes_options = pf_render_clientes_options();
$plantillas_options = pf_render_plantillas_options();
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                    Proformas de pago
                </h3>

                <div class="ml-auto">
                    <button type="button" class="btn btn-sm btn-warning" id="btnProformaEmergencia">
                        <i class="fas fa-bolt mr-1"></i>
                        Documento manual de emergencia
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnNuevaProforma">
                        <i class="fas fa-plus mr-1"></i>
                        Nueva proforma
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    La proforma no registra ingreso. Los servicios seleccionados pasan a estado En proforma.
                </div>

                <div id="proformasTablaContainer">
                    <?php echo $tabla_proformas; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProforma" tabindex="-1" role="dialog" aria-labelledby="modalProformaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="formProforma" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProformaTitulo">Nueva proforma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_proforma">
                <input type="hidden" name="id" id="proformaId" value="0">
                <input type="hidden" name="items_json" id="proformaItemsJson" value="[]">
                <input type="hidden" name="manual_emergencia" id="proformaManualEmergencia" value="0">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Cliente <span class="app-required">*</span></label>
                        <select name="cliente_id" id="proformaClienteId" class="custom-select" required>
                            <?php echo $clientes_options; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Plantilla <span class="app-required">*</span></label>
                        <select name="plantilla_id" id="proformaPlantillaId" class="custom-select" required>
                            <?php echo $plantillas_options; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-2">
                        <label>Emisión <span class="app-required">*</span></label>
                        <input type="date" name="fecha_emision" id="proformaFechaEmision" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
                    </div>

                    <div class="form-group col-md-2">
                        <label>Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="proformaFechaVencimiento" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea name="observacion" id="proformaObservacion" class="form-control" maxlength="500" data-char-counter="true" rows="2"></textarea>
                </div>

                <div class="row">
                    <div class="col-lg-5" id="proformaServiciosPanel">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-tasks mr-1"></i>
                                    Servicios pendientes del cliente
                                </h3>
                            </div>
                            <div class="card-body" id="proformaServiciosClienteContainer">
                                <div class="app-empty-state">
                                    <div class="app-empty-state-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h5>Seleccione un cliente</h5>
                                    <p>Los servicios pendientes aparecerán aquí.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card card-outline card-primary">
                            <div class="card-header d-flex align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-list mr-1"></i>
                                    Ítems de la proforma
                                </h3>

                                <button type="button" class="btn btn-sm btn-secondary ml-auto" id="btnAgregarItemManual">
                                    <i class="fas fa-plus mr-1"></i>
                                    Ítem manual
                                </button>
                            </div>

                            <div class="card-body">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-4">
                                        <label>Bloque</label>
                                        <select id="manualBloque" class="custom-select">
                                            <option value="Actuales">Actuales</option>
                                            <option value="Pendientes de pago">Pendientes de pago</option>
                                            <option value="Otros servicios o trámites">Otros servicios o trámites</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Descripción manual</label>
                                        <input type="text" id="manualDescripcion" class="form-control" maxlength="255">
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>Cantidad</label>
                                        <input type="number" id="manualCantidad" class="form-control" min="0.01" step="0.01" value="1">
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>Precio</label>
                                        <input type="number" id="manualPrecio" class="form-control" min="0.01" step="0.01" value="0.00">
                                    </div>
                                </div>

                                <div id="proformaItemsContainer" class="pf-items-container">
                                    <div class="app-empty-state" id="proformaItemsVacio">
                                        <div class="app-empty-state-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h5>Sin ítems</h5>
                                        <p>Selecciona servicios o agrega ítems manuales.</p>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-outline-secondary" id="btnLimpiarProforma">
                                            <i class="fas fa-eraser mr-1"></i>
                                            Limpiar
                                        </button>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="pf-total-box">
                                            <div>
                                                <span>Subtotal</span>
                                                <strong id="proformaSubtotalTexto">S/ 0.00</strong>
                                            </div>
                                            <div>
                                                <span>Descuento</span>
                                                <input type="number" name="descuento" id="proformaDescuento" class="form-control form-control-sm" min="0" step="0.01" value="0.00">
                                            </div>
                                            <div class="pf-total-final">
                                                <span>Total</span>
                                                <strong id="proformaTotalTexto">S/ 0.00</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    La proforma no registra ingreso. El pago se confirma en Recibos de pago.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar proforma
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDocumentoProforma" tabindex="-1" role="dialog" aria-labelledby="modalDocumentoProformaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDocumentoProformaTitulo">Documento de proforma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="documentoProformaContenido">
                <div class="app-empty-state">
                    <div class="app-empty-state-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h5>Documento</h5>
                    <p>Seleccione una proforma para verla.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExitoProforma" tabindex="-1" role="dialog" aria-labelledby="modalExitoProformaTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="modalExitoProformaTitulo">
                    <i class="fas fa-check-circle mr-1"></i>
                    Proforma guardada
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="mb-1">La proforma fue guardada correctamente.</p>
                <p class="mb-0">
                    Código:
                    <strong id="proformaExitoCodigo">---</strong>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="btnExitoVerDocumento">
                    <i class="fas fa-eye mr-1"></i>
                    Ver documento
                </button>
                <button type="button" class="btn btn-secondary" id="btnExitoDescargarJpg">
                    <i class="fas fa-image mr-1"></i>
                    Descargar JPG
                </button>
                <button type="button" class="btn btn-danger" id="btnExitoDescargarPdf">
                    <i class="fas fa-file-pdf mr-1"></i>
                    Descargar PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>