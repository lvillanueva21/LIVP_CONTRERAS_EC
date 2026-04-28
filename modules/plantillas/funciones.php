<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function pl_external_id()
{
    $user = auth_user();
    return isset($user['mode']) && $user['mode'] !== '' ? $user['mode'] : 'demo';
}

function pl_listar()
{
    $pdo = app_pdo();

    $sql = "
        SELECT
            p.*,
            COALESCE(mp.metodos_visibles, 0) AS metodos_visibles
        FROM ecc_plantillas p
        LEFT JOIN (
            SELECT plantilla_id, SUM(CASE WHEN mostrar = 1 THEN 1 ELSE 0 END) AS metodos_visibles
            FROM ecc_plantilla_metodos_pago
            GROUP BY plantilla_id
        ) mp ON mp.plantilla_id = p.id
        ORDER BY p.es_predeterminada DESC, p.id DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function pl_obtener($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_plantillas WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function pl_listar_metodos_pago()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT *
        FROM ecc_metodos_pago
        WHERE estado = 1
        ORDER BY orden ASC, id ASC
    ");

    return $stmt->fetchAll();
}

function pl_metodos_visibles_ids($plantilla_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT metodo_pago_id
        FROM ecc_plantilla_metodos_pago
        WHERE plantilla_id = :plantilla_id AND mostrar = 1
        ORDER BY orden ASC, id ASC
    ");

    $stmt->execute(array(':plantilla_id' => (int)$plantilla_id));

    $ids = array();

    foreach ($stmt->fetchAll() as $row) {
        $ids[] = (int)$row['metodo_pago_id'];
    }

    return $ids;
}

function pl_metodos_visibles_detalle($plantilla_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT mp.*
        FROM ecc_plantilla_metodos_pago pmp
        INNER JOIN ecc_metodos_pago mp ON mp.id = pmp.metodo_pago_id
        WHERE pmp.plantilla_id = :plantilla_id
          AND pmp.mostrar = 1
          AND mp.estado = 1
        ORDER BY pmp.orden ASC, mp.orden ASC, mp.id ASC
    ");

    $stmt->execute(array(':plantilla_id' => (int)$plantilla_id));

    return $stmt->fetchAll();
}

function pl_configuracion_empresa()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT ce.*, a.ruta_relativa AS logo_ruta
        FROM ecc_configuracion_empresa ce
        LEFT JOIN ecc_archivos a ON a.id = ce.logo_archivo_id AND a.estado = 1
        WHERE ce.id = 1
        LIMIT 1
    ");

    $row = $stmt->fetch();

    if ($row) {
        return $row;
    }

    return array(
        'nombre_comercial' => 'Estudio Contable Contreras',
        'ruc' => '00000000000',
        'razon_social' => 'Estudio Contable Contreras',
        'rubro' => 'Servicios contables y tributarios',
        'direccion' => '',
        'correo' => '',
        'celular' => '',
        'logo_ruta' => '',
        'pie_pagina' => ''
    );
}

function pl_badge_estado($estado)
{
    if ((int)$estado === 1) {
        return '<span class="badge badge-success">Activa</span>';
    }

    return '<span class="badge badge-secondary">Inactiva</span>';
}

function pl_badge_predeterminada($predeterminada)
{
    if ((int)$predeterminada === 1) {
        return '<span class="badge badge-primary">Predeterminada</span>';
    }

    return '<span class="badge badge-light border">Normal</span>';
}

function pl_badge_orientacion($orientacion)
{
    $tipo = $orientacion === 'Horizontal' ? 'info' : 'secondary';
    return '<span class="badge badge-' . e($tipo) . '">' . e($orientacion) . '</span>';
}

function pl_render_metodos_checks($selected_ids = array())
{
    $metodos = pl_listar_metodos_pago();

    if (empty($metodos)) {
        return '<div class="alert alert-warning mb-0">No hay métodos de pago activos para seleccionar.</div>';
    }

    ob_start();
    ?>
    <div class="pl-metodos-check-list">
        <?php foreach ($metodos as $metodo) { ?>
            <?php $checked = in_array((int)$metodo['id'], $selected_ids, true) ? 'checked' : ''; ?>
            <label class="pl-metodo-check-item">
                <input type="checkbox" name="metodos_pago[]" value="<?php echo e($metodo['id']); ?>" <?php echo $checked; ?>>
                <span>
                    <strong><?php echo e($metodo['titulo_visible']); ?></strong>
                    <small>
                        <?php echo e($metodo['tipo']); ?>
                        <?php if ($metodo['tipo'] === 'Cuenta de ahorro' && trim((string)$metodo['banco']) !== '') { ?>
                            | <?php echo e($metodo['banco']); ?>
                        <?php } ?>
                        <?php if (($metodo['tipo'] === 'Yape' || $metodo['tipo'] === 'Plin') && trim((string)$metodo['numero_celular']) !== '') { ?>
                            | <?php echo e($metodo['numero_celular']); ?>
                        <?php } ?>
                    </small>
                </span>
            </label>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function pl_render_table()
{
    $plantillas = pl_listar();

    ob_start();
    ?>
    <table class="table table-sm" data-app-table="true" data-page-length="10" data-empty-text="No hay plantillas registradas.">
        <thead>
            <tr>
                <th>Plantilla</th>
                <th>Orientación</th>
                <th>Visibilidad</th>
                <th>Métodos visibles</th>
                <th>Estado</th>
                <th width="165">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plantillas as $plantilla) { ?>
                <tr data-id="<?php echo e($plantilla['id']); ?>">
                    <td>
                        <strong><?php echo e($plantilla['nombre']); ?></strong>
                        <br>
                        <?php echo pl_badge_predeterminada($plantilla['es_predeterminada']); ?>
                        <?php if (trim((string)$plantilla['descripcion']) !== '') { ?>
                            <br>
                            <small class="text-muted"><?php echo e($plantilla['descripcion']); ?></small>
                        <?php } ?>
                    </td>
                    <td>
                        <?php echo pl_badge_orientacion($plantilla['orientacion']); ?>
                        <br>
                        <small class="text-muted">Logo: <?php echo e($plantilla['logo_tipo']); ?></small>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo (int)$plantilla['logo_visible'] === 1 ? 'success' : 'secondary'; ?>">Logo</span>
                        <span class="badge badge-<?php echo (int)$plantilla['datos_empresa_visible'] === 1 ? 'success' : 'secondary'; ?>">Empresa</span>
                        <span class="badge badge-<?php echo (int)$plantilla['datos_cliente_visible'] === 1 ? 'success' : 'secondary'; ?>">Cliente</span>
                        <span class="badge badge-<?php echo (int)$plantilla['pie_pagina_visible'] === 1 ? 'success' : 'secondary'; ?>">Pie</span>
                    </td>
                    <td>
                        <?php if ((int)$plantilla['metodos_visibles'] > 0) { ?>
                            <span class="badge badge-info"><?php echo e($plantilla['metodos_visibles']); ?> visibles</span>
                        <?php } else { ?>
                            <span class="badge badge-light border">Sin métodos</span>
                        <?php } ?>
                    </td>
                    <td><?php echo pl_badge_estado($plantilla['estado']); ?></td>
                    <td>
                        <div class="app-action-buttons">
                            <button type="button" class="btn btn-sm btn-info btnVistaPreviaPlantilla" data-id="<?php echo e($plantilla['id']); ?>" title="Vista previa">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary btnEditarPlantilla" data-id="<?php echo e($plantilla['id']); ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm <?php echo (int)$plantilla['estado'] === 1 ? 'btn-danger' : 'btn-success'; ?> btnCambiarEstadoPlantilla" data-id="<?php echo e($plantilla['id']); ?>" data-estado="<?php echo e($plantilla['estado']); ?>" title="Activar o inactivar">
                                <i class="fas <?php echo (int)$plantilla['estado'] === 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function pl_render_preview($plantilla_id)
{
    $plantilla = pl_obtener($plantilla_id);

    if (!$plantilla) {
        return '<div class="alert alert-warning mb-0">Plantilla no encontrada.</div>';
    }

    $empresa = pl_configuracion_empresa();
    $metodos = pl_metodos_visibles_detalle($plantilla_id);
    $logo_url = trim((string)$empresa['logo_ruta']) !== '' ? app_url($empresa['logo_ruta']) : '';

    $bloques = array(
        'Actuales' => array(
            array('descripcion' => 'Declaración mensual Abril 2026', 'monto' => 150.00)
        ),
        'Pendientes de pago' => array(
            array('descripcion' => 'Planilla mensual Abril 2026', 'monto' => 120.00)
        ),
        'Otros servicios o trámites' => array(
            array('descripcion' => 'Trámite SUNAT', 'monto' => 80.00)
        )
    );

    $total = 0;

    foreach ($bloques as $items) {
        foreach ($items as $item) {
            $total += (float)$item['monto'];
        }
    }

    $header_style = '';

    if ($plantilla['color_tipo'] === 'Degradado' && trim((string)$plantilla['color_secundario']) !== '') {
        $header_style = 'background: linear-gradient(135deg, ' . e($plantilla['color_primario']) . ', ' . e($plantilla['color_secundario']) . ');';
    } else {
        $header_style = 'background: ' . e($plantilla['color_primario']) . ';';
    }

    ob_start();
    ?>
    <div class="pl-preview-shell">
        <div class="pl-documento-preview pl-orientacion-<?php echo e(strtolower($plantilla['orientacion'])); ?>">
            <div class="pl-doc-header" style="<?php echo $header_style; ?>">
                <?php if ((int)$plantilla['logo_visible'] === 1) { ?>
                    <div class="pl-doc-logo pl-logo-<?php echo e(strtolower($plantilla['logo_tipo'])); ?>">
                        <?php if ($logo_url !== '') { ?>
                            <img src="<?php echo e($logo_url); ?>" alt="Logo">
                        <?php } else { ?>
                            <div class="pl-doc-logo-empty">
                                <i class="fas fa-image"></i>
                                <span>Logo</span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if ((int)$plantilla['datos_empresa_visible'] === 1) { ?>
                    <div class="pl-doc-empresa">
                        <h4><?php echo e($empresa['nombre_comercial']); ?></h4>
                        <p><?php echo e($empresa['razon_social']); ?></p>
                        <p>RUC: <?php echo e($empresa['ruc']); ?></p>
                        <?php if (trim((string)$empresa['direccion']) !== '') { ?>
                            <p><?php echo e($empresa['direccion']); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <div class="pl-doc-body">
                <div class="pl-doc-title">
                    <h5>Vista previa de documento</h5>
                    <span><?php echo e($plantilla['nombre']); ?></span>
                </div>

                <?php if ((int)$plantilla['datos_cliente_visible'] === 1) { ?>
                    <div class="pl-doc-cliente">
                        <strong>Cliente demo:</strong> Comercial Demo SAC<br>
                        <span>RUC 20600000001 | contacto@demoempresa.pe</span>
                    </div>
                <?php } ?>

                <div class="pl-doc-nota">
                    Los bloques de servicios no dependen de la plantilla. Aparecen automáticamente solo si tienen ítems.
                </div>

                <?php foreach ($bloques as $bloque_nombre => $items) { ?>
                    <?php if (!empty($items)) { ?>
                        <div class="pl-doc-bloque">
                            <h6><?php echo e($bloque_nombre); ?></h6>
                            <table>
                                <tbody>
                                    <?php foreach ($items as $item) { ?>
                                        <tr>
                                            <td><?php echo e($item['descripcion']); ?></td>
                                            <td class="text-right"><?php echo e(app_money($item['monto'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                <?php } ?>

                <div class="pl-doc-total">
                    <span>Total referencial</span>
                    <strong><?php echo e(app_money($total)); ?></strong>
                </div>

                <?php if (!empty($metodos)) { ?>
                    <div class="pl-doc-metodos">
                        <h6>Métodos de pago</h6>
                        <?php foreach ($metodos as $metodo) { ?>
                            <div class="pl-doc-metodo-item">
                                <strong><?php echo e($metodo['titulo_visible']); ?></strong>
                                <span>
                                    <?php echo e($metodo['tipo']); ?>
                                    <?php if ($metodo['tipo'] === 'Cuenta de ahorro') { ?>
                                        | <?php echo e($metodo['banco']); ?> | <?php echo e($metodo['numero_cuenta']); ?>
                                        <?php if (trim((string)$metodo['cci']) !== '') { ?>
                                            | CCI: <?php echo e($metodo['cci']); ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        | <?php echo e($metodo['numero_celular']); ?>
                                    <?php } ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if ((int)$plantilla['pie_pagina_visible'] === 1 && trim((string)$plantilla['pie_pagina']) !== '') { ?>
                    <div class="pl-doc-footer">
                        <?php echo nl2br(e($plantilla['pie_pagina'])); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function pl_sync_metodos_pago($plantilla_id, $metodos_ids)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("DELETE FROM ecc_plantilla_metodos_pago WHERE plantilla_id = :plantilla_id");
    $stmt->execute(array(':plantilla_id' => (int)$plantilla_id));

    if (!is_array($metodos_ids) || empty($metodos_ids)) {
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO ecc_plantilla_metodos_pago
        (plantilla_id, metodo_pago_id, mostrar, orden)
        VALUES
        (:plantilla_id, :metodo_pago_id, 1, :orden)
    ");

    $orden = 1;

    foreach ($metodos_ids as $metodo_id) {
        $metodo_id = (int)$metodo_id;

        if ($metodo_id <= 0) {
            continue;
        }

        $insert->execute(array(
            ':plantilla_id' => (int)$plantilla_id,
            ':metodo_pago_id' => $metodo_id,
            ':orden' => $orden
        ));

        $orden++;
    }
}

function pl_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Plantillas',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => pl_external_id(),
        ':created_by_external_id' => pl_external_id()
    ));
}