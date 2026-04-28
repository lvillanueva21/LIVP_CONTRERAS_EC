<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$tabla_plantillas = pl_render_table();
$metodos_checks = pl_render_metodos_checks();
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-layer-group mr-1"></i>
                    Plantillas
                </h3>

                <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnNuevaPlantilla">
                    <i class="fas fa-plus mr-1"></i>
                    Nueva plantilla
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    La plantilla controla orientación, logo, datos visibles, colores, pie de página y métodos de pago. Los bloques de servicios aparecen automáticamente si tienen ítems.
                </div>

                <div id="plantillasTablaContainer">
                    <?php echo $tabla_plantillas; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPlantilla" tabindex="-1" role="dialog" aria-labelledby="modalPlantillaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="formPlantilla" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPlantillaTitulo">Nueva plantilla</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_plantilla">
                <input type="hidden" name="id" id="plantillaId" value="0">

                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Nombre <span class="app-required">*</span></label>
                        <input type="text" name="nombre" id="plantillaNombre" class="form-control" maxlength="120" data-char-counter="true" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Orientación</label>
                        <select name="orientacion" id="plantillaOrientacion" class="custom-select">
                            <option value="Vertical">Vertical</option>
                            <option value="Horizontal">Horizontal</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="plantillaDescripcion" class="form-control" maxlength="500" data-char-counter="true" rows="2"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Visibilidad</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Mostrar logo</label>
                                        <select name="logo_visible" id="plantillaLogoVisible" class="custom-select">
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Tipo de logo</label>
                                        <select name="logo_tipo" id="plantillaLogoTipo" class="custom-select">
                                            <option value="Cuadrado">Cuadrado</option>
                                            <option value="Rectangular">Rectangular</option>
                                            <option value="Banner">Banner</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Datos empresa visibles</label>
                                        <select name="datos_empresa_visible" id="plantillaDatosEmpresaVisible" class="custom-select">
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Datos cliente visibles</label>
                                        <select name="datos_cliente_visible" id="plantillaDatosClienteVisible" class="custom-select">
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Pie visible</label>
                                        <select name="pie_pagina_visible" id="plantillaPieVisible" class="custom-select">
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Predeterminada</label>
                                        <select name="es_predeterminada" id="plantillaPredeterminada" class="custom-select">
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Estado</label>
                                    <select name="estado" id="plantillaEstado" class="custom-select">
                                        <option value="1">Activa</option>
                                        <option value="0">Inactiva</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Color y pie de página</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Tipo color</label>
                                        <select name="color_tipo" id="plantillaColorTipo" class="custom-select">
                                            <option value="Solido">Sólido</option>
                                            <option value="Degradado">Degradado</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Color primario</label>
                                        <input type="color" name="color_primario" id="plantillaColorPrimario" class="form-control" value="#1f4e79">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Color secundario</label>
                                        <input type="color" name="color_secundario" id="plantillaColorSecundario" class="form-control" value="#163a5a">
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Pie de página</label>
                                    <textarea name="pie_pagina" id="plantillaPiePagina" class="form-control" maxlength="1000" data-char-counter="true" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">Métodos de pago visibles</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    Si no marcas ningún método de pago, el bloque de métodos no aparecerá y no dejará espacio vacío.
                                </p>

                                <div id="plantillaMetodosPagoContainer">
                                    <?php echo $metodos_checks; ?>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            La plantilla no controla los bloques de servicios. Los bloques Actuales, Pendientes de pago y Otros servicios o trámites se muestran automáticamente cuando tengan ítems.
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
                    Guardar plantilla
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalVistaPreviaPlantilla" tabindex="-1" role="dialog" aria-labelledby="modalVistaPreviaPlantillaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVistaPreviaPlantillaTitulo">Vista previa de plantilla</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="plantillaVistaPreviaContenido">
                <div class="app-empty-state">
                    <div class="app-empty-state-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h5>Vista previa</h5>
                    <p>Selecciona una plantilla para previsualizarla.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmarPlantilla" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarPlantillaTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalConfirmarPlantillaTitulo">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar acción
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p id="modalConfirmarPlantillaTexto" class="mb-0">Confirma la acción seleccionada.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnConfirmarPlantilla">
                    <i class="fas fa-check mr-1"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>