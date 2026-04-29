<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$tabla_recibos = rb_render_tabla();
$proformas_options = rb_render_proformas_options();
$clientes_options = rb_render_clientes_options();
$plantillas_options = rb_render_plantillas_options();
$metodos_options = rb_render_metodos_pago_options();
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-receipt mr-1"></i>
                    Recibos de pago
                </h3>

                <div class="ml-auto">
                    <button type="button" class="btn btn-sm btn-warning" id="btnReciboManualEmergencia">
                        <i class="fas fa-bolt mr-1"></i>
                        Recibo manual de emergencia
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnConfirmarPagoProforma">
                        <i class="fas fa-check-circle mr-1"></i>
                        Confirmar pago desde proforma
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    El recibo sí registra ingreso. Los servicios pagados pasan a Pagado y los no pagados quedan como Pendiente.
                </div>

                <div id="recibosTablaContainer">
                    <?php echo $tabla_recibos; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecibo" tabindex="-1" role="dialog" aria-labelledby="modalReciboTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="formRecibo" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReciboTitulo">Confirmar pago desde proforma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_recibo">
                <input type="hidden" name="items_json" id="reciboItemsJson" value="[]">
                <input type="hidden" name="manual_emergencia" id="reciboManualEmergencia" value="0">

                <div class="form-row" id="reciboProformaRow">
                    <div class="form-group col-md-12">
                        <label>Proforma <span class="app-required">*</span></label>
                        <select name="proforma_id" id="reciboProformaId" class="custom-select">
                            <?php echo $proformas_options; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Cliente <span class="app-required">*</span></label>
                        <select name="cliente_id" id="reciboClienteId" class="custom-select" required>
                            <?php echo $clientes_options; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Plantilla <span class="app-required">*</span></label>
                        <select name="plantilla_id" id="reciboPlantillaId" class="custom-select" required>
                            <?php echo $plantillas_options; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Método de pago <span class="app-required">*</span></label>
                        <select name="metodo_pago_id" id="reciboMetodoPagoId" class="custom-select" required>
                            <?php echo $metodos_options; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Emisión <span class="app-required">*</span></label>
                        <input type="date" name="fecha_emision" id="reciboFechaEmision" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
                    </div>

                    <div class="form-group col-md-3">
                        <label>Fecha de pago <span class="app-required">*</span></label>
                        <input type="date" name="fecha_pago" id="reciboFechaPago" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Observación</label>
                        <input type="text" name="observacion" id="reciboObservacion" class="form-control" maxlength="255" data-char-counter="true">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7" id="reciboDetallesProformaPanel">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i>
                                    Ítems de la proforma
                                </h3>
                            </div>
                            <div class="card-body" id="reciboDetallesProformaContainer">
                                <div class="app-empty-state">
                                    <div class="app-empty-state-icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <h5>Seleccione una proforma</h5>
                                    <p>Los ítems pendientes aparecerán aquí.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    Servicios adicionales
                                </h3>
                            </div>
                            <div class="card-body" id="reciboServiciosAdicionalesContainer">
                                <div class="app-empty-state">
                                    <div class="app-empty-state-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h5>Seleccione cliente</h5>
                                    <p>Los servicios adicionales disponibles aparecerán aquí.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-pen mr-1"></i>
                                    Ítem manual
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Bloque</label>
                                    <select id="reciboManualBloque" class="custom-select">
                                        <option value="Actuales">Actuales</option>
                                        <option value="Pendientes de pago">Pendientes de pago</option>
                                        <option value="Otros servicios o trámites">Otros servicios o trámites</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" id="reciboManualDescripcion" class="form-control" maxlength="255">
                                </div>

                                <div class="form-group">
                                    <label>Monto pagado</label>
                                    <input type="number" id="reciboManualMonto" class="form-control" min="0.01" step="0.01" value="0.00">
                                </div>

                                <button type="button" class="btn btn-sm btn-warning" id="btnAgregarManualRecibo">
                                    <i class="fas fa-plus mr-1"></i>
                                    Agregar ítem manual
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list mr-1"></i>
                            Ítems a pagar
                        </h3>

                        <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" id="btnLimpiarRecibo">
                            <i class="fas fa-eraser mr-1"></i>
                            Limpiar
                        </button>
                    </div>

                    <div class="card-body">
                        <div id="reciboItemsContainer" class="rb-items-container">
                            <div class="app-empty-state">
                                <div class="app-empty-state-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h5>Sin ítems</h5>
                                <p>Selecciona ítems de proforma, servicios adicionales o agrega un ítem manual.</p>
                            </div>
                        </div>

                        <div class="rb-total-box mt-3">
                            <div class="rb-bloque-line"><span>Actuales (original/pagado/saldo)</span><strong id="reciboBloqueActualesTexto">S/ 0.00 / S/ 0.00 / S/ 0.00</strong></div>
                            <div class="rb-bloque-line"><span>Pendientes de pago (original/pagado/saldo)</span><strong id="reciboBloquePendientesTexto">S/ 0.00 / S/ 0.00 / S/ 0.00</strong></div>
                            <div class="rb-bloque-line"><span>Otros servicios o trámites (original/pagado/saldo)</span><strong id="reciboBloqueOtrosTexto">S/ 0.00 / S/ 0.00 / S/ 0.00</strong></div>
                            <div>
                                <span>Total proforma</span>
                                <strong id="reciboTotalProformaTexto">S/ 0.00</strong>
                            </div>
                            <div>
                                <span>Total pagado</span>
                                <strong id="reciboTotalPagadoTexto">S/ 0.00</strong>
                            </div>
                            <div class="rb-total-final">
                                <span>Saldo pendiente</span>
                                <strong id="reciboSaldoPendienteTexto">S/ 0.00</strong>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            El recibo registra ingreso. Solo los ítems completamente pagados cambiarán a Pagado.
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
                    Generar recibo
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDocumentoRecibo" tabindex="-1" role="dialog" aria-labelledby="modalDocumentoReciboTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Documento de recibo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="documentoReciboContenido">
                <div class="app-empty-state">
                    <div class="app-empty-state-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h5>Documento</h5>
                    <p>Seleccione un recibo para verlo.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExitoRecibo" tabindex="-1" role="dialog" aria-labelledby="modalExitoReciboTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="modalExitoReciboTitulo">
                    <i class="fas fa-check-circle mr-1"></i>
                    Recibo generado
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="mb-1">El recibo fue generado correctamente.</p>
                <p class="mb-0">
                    Código:
                    <strong id="reciboExitoCodigo">---</strong>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-info" id="btnExitoVerRecibo">
                    <i class="fas fa-eye mr-1"></i>
                    Ver documento
                </button>
                <button type="button" class="btn btn-secondary" id="btnExitoDescargarReciboJpg">
                    <i class="fas fa-image mr-1"></i>
                    Descargar JPG
                </button>
                <button type="button" class="btn btn-danger" id="btnExitoDescargarReciboPdf">
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
