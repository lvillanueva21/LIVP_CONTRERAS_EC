<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$clientes_html = cs_render_clientes_table();
$servicios_options = cs_render_servicios_options();
$etiquetas_options = cs_render_etiquetas_options();
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-users mr-1"></i>
                    Clientes y servicios
                </h3>

                <div class="ml-auto">
                    <button type="button" class="btn btn-sm btn-secondary" id="btnNuevaEtiqueta">
                        <i class="fas fa-tags mr-1"></i>
                        Nueva etiqueta
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btnNuevoCliente">
                        <i class="fas fa-plus mr-1"></i>
                        Nuevo cliente
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Gestiona clientes, asigna servicios, agrega etiquetas y controla estados para el flujo de proformas y recibos.
                </div>

                <div id="clientesTablaContainer">
                    <?php echo $clientes_html; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-labelledby="modalClienteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formCliente" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalClienteTitulo">Nuevo cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_cliente">
                <input type="hidden" name="id" id="clienteId" value="0">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Tipo <span class="app-required">*</span></label>
                        <select name="tipo_cliente" id="clienteTipo" class="custom-select" required>
                            <option value="Empresa">Empresa</option>
                            <option value="Persona natural">Persona natural</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Tipo documento <span class="app-required">*</span></label>
                        <select name="documento_tipo" id="clienteDocumentoTipo" class="custom-select" required>
                            <option value="RUC">RUC</option>
                            <option value="DNI">DNI</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Número documento <span class="app-required">*</span></label>
                        <input type="text" name="numero_documento" id="clienteNumeroDocumento" class="form-control" maxlength="20" data-char-counter="true" required>
                    </div>
                </div>

                <div class="form-group cliente-campo-empresa">
                    <label>Razón social <span class="app-required">*</span></label>
                    <input type="text" name="razon_social" id="clienteRazonSocial" class="form-control" maxlength="180" data-char-counter="true">
                </div>

                <div class="form-group cliente-campo-empresa">
                    <label>Nombre comercial</label>
                    <input type="text" name="nombre_comercial" id="clienteNombreComercial" class="form-control" maxlength="180" data-char-counter="true">
                </div>

                <div class="form-row cliente-campo-contacto">
                    <div class="form-group col-md-6">
                        <label>Nombres</label>
                        <input type="text" name="nombres" id="clienteNombres" class="form-control" maxlength="120" data-char-counter="true">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" id="clienteApellidos" class="form-control" maxlength="120" data-char-counter="true">
                    </div>
                </div>
                <small class="app-form-help d-block mb-3" id="clienteContactoAyuda">
                    Para persona natural son obligatorios. Para empresa son opcionales como contacto o representante.
                </small>

                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" id="clienteDireccion" class="form-control" maxlength="255" data-char-counter="true">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Celular</label>
                        <input type="text" name="celular" id="clienteCelular" class="form-control" maxlength="30" data-char-counter="true">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Correo</label>
                        <input type="email" name="correo" id="clienteCorreo" class="form-control" maxlength="120" data-char-counter="true">
                    </div>
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea name="observacion" id="clienteObservacion" class="form-control" maxlength="500" data-char-counter="true" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" id="clienteEstado" class="custom-select">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetalleCliente" tabindex="-1" role="dialog" aria-labelledby="modalDetalleClienteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleClienteTitulo">Detalle del cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="detalleClienteContenido">
                <div class="app-empty-state">
                    <div class="app-empty-state-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5>Seleccione un cliente</h5>
                    <p>El detalle se cargará aquí.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalServicioCliente" tabindex="-1" role="dialog" aria-labelledby="modalServicioClienteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formServicioCliente" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalServicioClienteTitulo">Cargar servicio al cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_servicio_cliente">
                <input type="hidden" name="id" id="clienteServicioId" value="0">
                <input type="hidden" name="cliente_id" id="servicioClienteId" value="0">

                <div class="form-group">
                    <label>Servicio <span class="app-required">*</span></label>
                    <select name="servicio_id" id="servicioId" class="custom-select" required>
                        <?php echo $servicios_options; ?>
                    </select>
                    <small class="app-form-help">Las etiquetas se guardan sobre el servicio general seleccionado.</small>
                </div>

                <div class="form-group">
                    <label>Etiquetas</label>
                    <select name="etiquetas[]" id="servicioEtiquetas" class="custom-select" multiple>
                        <?php echo $etiquetas_options; ?>
                    </select>
                    <small class="app-form-help">Usa Ctrl o Cmd para seleccionar varias etiquetas.</small>
                </div>

                <div class="form-group">
                    <label>Descripción opcional</label>
                    <textarea name="descripcion_personalizada" id="servicioDescripcion" class="form-control" maxlength="500" data-char-counter="true" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Periodo</label>
                        <input type="text" name="periodo" id="servicioPeriodo" class="form-control" maxlength="60" data-char-counter="true" placeholder="Ejemplo: Abril 2026">
                    </div>

                    <div class="form-group col-md-8">
                        <label>Monto soles <span class="app-required">*</span></label>
                        <input type="number" name="monto" id="servicioMonto" class="form-control" min="0.01" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Fecha vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="servicioFechaVencimiento" class="form-control">
                    </div>

                    <div class="form-group col-md-3">
                        <label>Fecha de aviso</label>
                        <input type="datetime-local" name="fecha_aviso" id="servicioFechaAviso" class="form-control">
                    </div>

                    <div class="form-group col-md-3">
                        <label>Modo aviso</label>
                        <select name="modo_aviso" id="servicioModoAviso" class="custom-select">
                            <option value="Sin aviso">Sin aviso</option>
                            <option value="Fecha exacta">Fecha exacta</option>
                            <option value="Faltando X días">Faltando X días</option>
                            <option value="Faltando X horas">Faltando X horas</option>
                            <option value="Faltando X minutos">Faltando X minutos</option>
                            <option value="Antes de vencer">Antes de vencer</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Valor aviso</label>
                        <input type="number" name="aviso_valor" id="servicioAvisoValor" class="form-control" min="0" step="1" placeholder="Ejemplo: 2">
                    </div>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" id="servicioEstado" class="custom-select">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proforma">En proforma</option>
                        <option value="Pagado">Pagado</option>
                        <option value="Anulado">Anulado</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar servicio
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEtiqueta" tabindex="-1" role="dialog" aria-labelledby="modalEtiquetaTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formEtiqueta" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEtiquetaTitulo">Nueva etiqueta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="crear_etiqueta">

                <div class="form-group">
                    <label>Nombre <span class="app-required">*</span></label>
                    <input type="text" name="nombre" id="etiquetaNombre" class="form-control" maxlength="80" data-char-counter="true" required>
                </div>

                <div class="form-group">
                    <label>Color</label>
                    <input type="color" name="color" id="etiquetaColor" class="form-control" value="#6c757d">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar etiqueta
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalConfirmacionAccion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacionTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalConfirmacionTitulo">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar acción
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p id="modalConfirmacionTexto" class="mb-0">Confirma la acción seleccionada.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnConfirmarAccion">
                    <i class="fas fa-check mr-1"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

