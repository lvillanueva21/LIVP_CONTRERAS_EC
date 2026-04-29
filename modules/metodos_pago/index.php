<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$tabla_metodos = mp_render_table();
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-credit-card mr-1"></i>
                    Métodos de pago
                </h3>

                <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnNuevoMetodoPago">
                    <i class="fas fa-plus mr-1"></i>
                    Nuevo método
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Registra las cuentas, Yape y Plin del gerente para mostrarlas en plantillas, proformas y recibos.
                </div>

                <div id="metodosPagoTablaContainer">
                    <?php echo $tabla_metodos; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMetodoPago" tabindex="-1" role="dialog" aria-labelledby="modalMetodoPagoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formMetodoPago" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMetodoPagoTitulo">Nuevo método de pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_metodo_pago">
                <input type="hidden" name="id" id="metodoPagoId" value="0">
                <input type="hidden" name="tomar_orden" id="metodoTomarOrden" value="0">

                <div class="form-group">
                    <label>Título visible <span class="app-required">*</span></label>
                    <input type="text" name="titulo_visible" id="metodoTituloVisible" class="form-control" maxlength="120" data-char-counter="true" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Tipo <span class="app-required">*</span></label>
                        <select name="tipo" id="metodoTipo" class="custom-select" required>
                            <option value="Cuenta de ahorro">Cuenta de ahorro</option>
                            <option value="Yape">Yape</option>
                            <option value="Plin">Plin</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Titular <span class="app-required">*</span></label>
                        <input type="text" name="titular" id="metodoTitular" class="form-control" maxlength="180" data-char-counter="true" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Estado</label>
                        <select name="estado" id="metodoEstado" class="custom-select">
                            <option value="1">Activo</option>
                            <option value="0">Desactivado</option>
                        </select>
                    </div>
                </div>

                <div class="form-row metodo-campo-cuenta">
                    <div class="form-group col-md-4">
                        <label>Banco <span class="app-required">*</span></label>
                        <input type="text" name="banco" id="metodoBanco" class="form-control" maxlength="120" data-char-counter="true">
                    </div>

                    <div class="form-group col-md-4">
                        <label>Número de cuenta <span class="app-required">*</span></label>
                        <input type="text" name="numero_cuenta" id="metodoNumeroCuenta" class="form-control" maxlength="80" data-char-counter="true">
                    </div>

                    <div class="form-group col-md-4">
                        <label>CCI</label>
                        <input type="text" name="cci" id="metodoCci" class="form-control" maxlength="80" data-char-counter="true">
                    </div>
                </div>

                <div class="form-group metodo-campo-celular">
                    <label>Número de celular <span class="app-required">*</span></label>
                    <input type="text" name="numero_celular" id="metodoNumeroCelular" class="form-control" maxlength="30" data-char-counter="true">
                </div>

                <div class="form-group">
                    <label>Descripción interna</label>
                    <input type="text" name="descripcion" id="metodoDescripcion" class="form-control" maxlength="180" data-char-counter="true">
                </div>

                <div class="form-group">
                    <label>Orden</label>
                    <input type="number" name="orden" id="metodoOrden" class="form-control" min="1" step="1" value="1">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar método
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalConfirmarMetodoPago" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarMetodoPagoTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalConfirmarMetodoPagoTitulo">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar acción
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p id="modalConfirmarMetodoPagoTexto" class="mb-0">Confirma la acción seleccionada.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnConfirmarMetodoPago">
                    <i class="fas fa-check mr-1"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
