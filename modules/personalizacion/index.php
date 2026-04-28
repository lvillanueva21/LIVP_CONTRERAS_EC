<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$configuracion = pz_obtener_configuracion();
$logo_url = pz_logo_url($configuracion);
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-palette mr-1"></i>
                    Personalización
                </h3>
            </div>

            <form id="formPersonalizacion" autocomplete="off" enctype="multipart/form-data">
                <div class="card-body">
                    <input type="hidden" name="action" value="guardar_personalizacion">

                    <h5 class="mb-3">Datos de la empresa</h5>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nombre comercial <span class="app-required">*</span></label>
                            <input type="text" name="nombre_comercial" id="pzNombreComercial" class="form-control" maxlength="180" data-char-counter="true" value="<?php echo e($configuracion['nombre_comercial']); ?>" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label>RUC <span class="app-required">*</span></label>
                            <input type="text" name="ruc" id="pzRuc" class="form-control" maxlength="20" data-char-counter="true" value="<?php echo e($configuracion['ruc']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Razón social <span class="app-required">*</span></label>
                        <input type="text" name="razon_social" id="pzRazonSocial" class="form-control" maxlength="180" data-char-counter="true" value="<?php echo e($configuracion['razon_social']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Rubro</label>
                        <input type="text" name="rubro" id="pzRubro" class="form-control" maxlength="180" data-char-counter="true" value="<?php echo e($configuracion['rubro']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" id="pzDireccion" class="form-control" maxlength="255" data-char-counter="true" value="<?php echo e($configuracion['direccion']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Celular</label>
                            <input type="text" name="celular" id="pzCelular" class="form-control" maxlength="30" data-char-counter="true" value="<?php echo e($configuracion['celular']); ?>">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Correo</label>
                            <input type="email" name="correo" id="pzCorreo" class="form-control" maxlength="120" data-char-counter="true" value="<?php echo e($configuracion['correo']); ?>">
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Logo</h5>

                    <div class="form-group">
                        <label>Logo</label>
                        <input type="file" name="logo" id="pzLogo" class="form-control-file" accept="image/png,image/jpeg,image/webp">
                        <small class="app-form-help">Formatos permitidos: PNG, JPG, WEBP. Máximo 5 MB.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Tipo logo</label>
                            <select name="logo_tipo" id="pzLogoTipo" class="custom-select">
                                <option value="Cuadrado" <?php echo $configuracion['logo_tipo'] === 'Cuadrado' ? 'selected' : ''; ?>>Cuadrado</option>
                                <option value="Rectangular" <?php echo $configuracion['logo_tipo'] === 'Rectangular' ? 'selected' : ''; ?>>Rectangular</option>
                                <option value="Banner" <?php echo $configuracion['logo_tipo'] === 'Banner' ? 'selected' : ''; ?>>Banner</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Zoom</label>
                            <input type="number" name="logo_zoom" id="pzLogoZoom" class="form-control" min="0.50" max="3.00" step="0.05" value="<?php echo e($configuracion['logo_zoom']); ?>">
                        </div>

                        <div class="form-group col-md-2">
                            <label>Mover X</label>
                            <input type="number" name="logo_pos_x" id="pzLogoPosX" class="form-control" step="1" value="<?php echo e($configuracion['logo_pos_x']); ?>">
                        </div>

                        <div class="form-group col-md-2">
                            <label>Mover Y</label>
                            <input type="number" name="logo_pos_y" id="pzLogoPosY" class="form-control" step="1" value="<?php echo e($configuracion['logo_pos_y']); ?>">
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Color y pie de página</h5>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Tipo de color</label>
                            <select name="color_tipo" id="pzColorTipo" class="custom-select">
                                <option value="Solido" <?php echo $configuracion['color_tipo'] === 'Solido' ? 'selected' : ''; ?>>Sólido</option>
                                <option value="Degradado" <?php echo $configuracion['color_tipo'] === 'Degradado' ? 'selected' : ''; ?>>Degradado</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Color primario</label>
                            <input type="color" name="color_primario" id="pzColorPrimario" class="form-control" value="<?php echo e($configuracion['color_primario']); ?>">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Color secundario</label>
                            <input type="color" name="color_secundario" id="pzColorSecundario" class="form-control" value="<?php echo e($configuracion['color_secundario'] ?: '#163a5a'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pie de página</label>
                        <textarea name="pie_pagina" id="pzPiePagina" class="form-control" maxlength="1000" data-char-counter="true" rows="4"><?php echo e($configuracion['pie_pagina']); ?></textarea>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Guardar personalización
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye mr-1"></i>
                    Vista previa
                </h3>
            </div>

            <div class="card-body">
                <div class="pz-preview-card" id="pzPreviewCard">
                    <div class="pz-preview-logo-box pz-logo-<?php echo e(strtolower($configuracion['logo_tipo'])); ?>" id="pzPreviewLogoBox">
                        <?php if ($logo_url !== '') { ?>
                            <img src="<?php echo e($logo_url); ?>" id="pzPreviewLogo" alt="Logo" style="transform: scale(<?php echo e($configuracion['logo_zoom']); ?>) translate(<?php echo e($configuracion['logo_pos_x']); ?>px, <?php echo e($configuracion['logo_pos_y']); ?>px);">
                        <?php } else { ?>
                            <div class="pz-preview-logo-placeholder" id="pzPreviewLogoPlaceholder">
                                <i class="fas fa-image"></i>
                                <span>Sin logo</span>
                            </div>
                            <img src="" id="pzPreviewLogo" alt="Logo" style="display:none;">
                        <?php } ?>
                    </div>

                    <div class="pz-preview-header" id="pzPreviewHeader">
                        <h5 id="pzPreviewNombre"><?php echo e($configuracion['nombre_comercial']); ?></h5>
                        <p id="pzPreviewRazon"><?php echo e($configuracion['razon_social']); ?></p>
                    </div>

                    <div class="pz-preview-body">
                        <p><strong>RUC:</strong> <span id="pzPreviewRuc"><?php echo e($configuracion['ruc']); ?></span></p>
                        <p><strong>Rubro:</strong> <span id="pzPreviewRubro"><?php echo e($configuracion['rubro']); ?></span></p>
                        <p><strong>Dirección:</strong> <span id="pzPreviewDireccion"><?php echo e($configuracion['direccion']); ?></span></p>
                        <p><strong>Contacto:</strong> <span id="pzPreviewContacto"><?php echo e(trim((string)$configuracion['celular'] . ' ' . (string)$configuracion['correo'])); ?></span></p>
                    </div>

                    <div class="pz-preview-footer" id="pzPreviewPie">
                        <?php echo e($configuracion['pie_pagina']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>