<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function pf_external_id()
{
    $user = auth_user();
    return isset($user['mode']) && $user['mode'] !== '' ? $user['mode'] : 'demo';
}

function pf_cliente_nombre($cliente)
{
    if (!$cliente) {
        return '';
    }

    if ($cliente['tipo_cliente'] === 'Empresa') {
        return trim((string)$cliente['razon_social']) !== '' ? $cliente['razon_social'] : $cliente['numero_documento'];
    }

    $nombre = trim((string)$cliente['nombres'] . ' ' . (string)$cliente['apellidos']);
    return $nombre !== '' ? $nombre : $cliente['numero_documento'];
}

function pf_listar()
{
    $pdo = app_pdo();

    $sql = "
        SELECT
            p.*,
            c.tipo_cliente,
            c.documento_tipo,
            c.numero_documento,
            c.razon_social,
            c.nombres,
            c.apellidos,
            pl.nombre AS plantilla_nombre
        FROM ecc_proformas p
        INNER JOIN ecc_clientes c ON c.id = p.cliente_id
        LEFT JOIN ecc_plantillas pl ON pl.id = p.plantilla_id
        ORDER BY p.id DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function pf_obtener($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_proformas WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function pf_obtener_detalles($proforma_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT *
        FROM ecc_proforma_detalles
        WHERE proforma_id = :proforma_id
        ORDER BY orden ASC, id ASC
    ");

    $stmt->execute(array(':proforma_id' => (int)$proforma_id));

    return $stmt->fetchAll();
}

function pf_obtener_cliente($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_clientes WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function pf_obtener_plantilla($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_plantillas WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function pf_configuracion_empresa()
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
        'ruc' => '',
        'razon_social' => 'Estudio Contable Contreras',
        'rubro' => '',
        'direccion' => '',
        'correo' => '',
        'celular' => '',
        'logo_ruta' => '',
        'pie_pagina' => ''
    );
}

function pf_listar_clientes()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT *
        FROM ecc_clientes
        WHERE estado = 1
        ORDER BY id DESC
    ");

    return $stmt->fetchAll();
}

function pf_listar_plantillas()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT *
        FROM ecc_plantillas
        WHERE estado = 1
        ORDER BY es_predeterminada DESC, id DESC
    ");

    return $stmt->fetchAll();
}

function pf_listar_metodos_plantilla($plantilla_id)
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

function pf_listar_servicios_cliente($cliente_id, $proforma_id = 0)
{
    $pdo = app_pdo();

    $params = array(':cliente_id' => (int)$cliente_id);

    $sql = "
        SELECT
            cs.*,
            s.nombre AS servicio_nombre,
            s.precio_base,
            GROUP_CONCAT(DISTINCT e.nombre ORDER BY e.nombre SEPARATOR ', ') AS etiquetas
        FROM ecc_cliente_servicios cs
        INNER JOIN ecc_servicios s ON s.id = cs.servicio_id
        LEFT JOIN ecc_servicio_etiquetas se ON se.servicio_id = s.id
        LEFT JOIN ecc_etiquetas e ON e.id = se.etiqueta_id AND e.estado = 1
        WHERE cs.cliente_id = :cliente_id
    ";

    if ((int)$proforma_id > 0) {
        $sql .= "
          AND (
                cs.estado = 'Pendiente'
                OR cs.id IN (
                    SELECT cliente_servicio_id
                    FROM ecc_proforma_detalles
                    WHERE proforma_id = :proforma_id
                      AND cliente_servicio_id IS NOT NULL
                )
          )
        ";
        $params[':proforma_id'] = (int)$proforma_id;
    } else {
        $sql .= "
          AND cs.estado = 'Pendiente'
        ";
    }

    $sql .= "
        GROUP BY cs.id
        ORDER BY cs.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function pf_badge_estado($estado)
{
    $map = array(
        'Borrador' => 'secondary',
        'Emitida' => 'info',
        'Parcial' => 'warning',
        'Convertida' => 'success',
        'Anulada' => 'danger'
    );

    $tipo = isset($map[$estado]) ? $map[$estado] : 'secondary';

    return '<span class="badge badge-' . e($tipo) . '">' . e($estado) . '</span>';
}

function pf_badge_bloque($bloque)
{
    $map = array(
        'Actuales' => 'primary',
        'Pendientes de pago' => 'warning',
        'Otros servicios o trámites' => 'info'
    );

    $tipo = isset($map[$bloque]) ? $map[$bloque] : 'secondary';

    return '<span class="badge badge-' . e($tipo) . '">' . e($bloque) . '</span>';
}

function pf_generar_codigo()
{
    $pdo = app_pdo();

    $anio = date('y');
    $prefijo = 'P' . $anio . '-';

    $stmt = $pdo->prepare("
        SELECT MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) AS ultimo
        FROM ecc_proformas
        WHERE codigo LIKE :prefijo
    ");

    $stmt->execute(array(':prefijo' => $prefijo . '%'));
    $row = $stmt->fetch();

    $ultimo = isset($row['ultimo']) ? (int)$row['ultimo'] : 0;
    $siguiente = $ultimo > 0 ? $ultimo + 1 : 8;

    return $prefijo . str_pad((string)$siguiente, 6, '0', STR_PAD_LEFT);
}

function pf_render_clientes_options($selected_id = 0)
{
    $clientes = pf_listar_clientes();
    $html = '<option value="">Seleccione cliente</option>';

    foreach ($clientes as $cliente) {
        $selected = (int)$selected_id === (int)$cliente['id'] ? ' selected' : '';
        $label = pf_cliente_nombre($cliente) . ' | ' . $cliente['documento_tipo'] . ' ' . $cliente['numero_documento'];
        $html .= '<option value="' . e($cliente['id']) . '"' . $selected . '>' . e($label) . '</option>';
    }

    return $html;
}

function pf_render_plantillas_options($selected_id = 0)
{
    $plantillas = pf_listar_plantillas();
    $html = '<option value="">Seleccione plantilla</option>';

    foreach ($plantillas as $plantilla) {
        $selected = (int)$selected_id === (int)$plantilla['id'] ? ' selected' : '';
        $label = $plantilla['nombre'];

        if ((int)$plantilla['es_predeterminada'] === 1) {
            $label .= ' | Predeterminada';
        }

        $html .= '<option value="' . e($plantilla['id']) . '"' . $selected . '>' . e($label) . '</option>';
    }

    return $html;
}

function pf_render_tabla()
{
    $proformas = pf_listar();

    ob_start();
    ?>
    <table class="table table-sm" data-app-table="true" data-page-length="10" data-empty-text="No hay proformas registradas.">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Plantilla</th>
                <th>Fechas</th>
                <th>Total</th>
                <th>Estado</th>
                <th width="180">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proformas as $proforma) { ?>
                <tr data-id="<?php echo e($proforma['id']); ?>">
                    <td>
                        <strong><?php echo e($proforma['codigo']); ?></strong>
                        <br>
                        <small class="text-muted">ID <?php echo e($proforma['id']); ?></small>
                    </td>
                    <td>
                        <strong><?php echo e(pf_cliente_nombre($proforma)); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo e($proforma['documento_tipo']); ?> <?php echo e($proforma['numero_documento']); ?></small>
                    </td>
                    <td>
                        <?php echo trim((string)$proforma['plantilla_nombre']) !== '' ? e($proforma['plantilla_nombre']) : '<span class="text-muted">Sin plantilla</span>'; ?>
                    </td>
                    <td>
                        <div><strong>Emisión:</strong> <?php echo e(date('d/m/Y', strtotime($proforma['fecha_emision']))); ?></div>
                        <div>
                            <strong>Vence:</strong>
                            <?php echo $proforma['fecha_vencimiento'] ? e(date('d/m/Y', strtotime($proforma['fecha_vencimiento']))) : '<span class="text-muted">Sin fecha</span>'; ?>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo e(app_money($proforma['total'])); ?></strong>
                        <?php if ((float)$proforma['descuento'] > 0) { ?>
                            <br>
                            <small class="text-muted">Desc. <?php echo e(app_money($proforma['descuento'])); ?></small>
                        <?php } ?>
                    </td>
                    <td><?php echo pf_badge_estado($proforma['estado']); ?></td>
                    <td>
                        <div class="app-action-buttons">
                            <button type="button" class="btn btn-sm btn-info btnVerProforma" data-id="<?php echo e($proforma['id']); ?>" title="Ver documento">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($proforma['estado'] !== 'Convertida' && $proforma['estado'] !== 'Anulada') { ?>
                                <button type="button" class="btn btn-sm btn-primary btnEditarProforma" data-id="<?php echo e($proforma['id']); ?>" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php } ?>
                            <button type="button" class="btn btn-sm btn-secondary btnExportarProforma" data-id="<?php echo e($proforma['id']); ?>" data-tipo="jpg" title="Descargar JPG">
                                <i class="fas fa-image"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btnExportarProforma" data-id="<?php echo e($proforma['id']); ?>" data-tipo="pdf" title="Descargar PDF">
                                <i class="fas fa-file-pdf"></i>
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

function pf_render_servicios_cliente($cliente_id, $proforma_id = 0)
{
    $servicios = pf_listar_servicios_cliente($cliente_id, $proforma_id);
    $seleccionados = array();

    if ((int)$proforma_id > 0) {
        foreach (pf_obtener_detalles($proforma_id) as $detalle) {
            if ((int)$detalle['cliente_servicio_id'] > 0) {
                $seleccionados[(int)$detalle['cliente_servicio_id']] = $detalle;
            }
        }
    }

    if (empty($servicios)) {
        return '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-inbox"></i></div><h5>Sin servicios pendientes para proformar</h5><p>Este cliente no tiene servicios pendientes para proformar.</p></div>';
    }

    ob_start();
    ?>
    <div class="pf-servicios-lista">
        <?php foreach ($servicios as $servicio) { ?>
            <?php
            $servicio_id = (int)$servicio['id'];
            $marcado = isset($seleccionados[$servicio_id]);
            $bloque = $marcado ? $seleccionados[$servicio_id]['bloque'] : 'Actuales';
            $descripcion = $marcado ? $seleccionados[$servicio_id]['descripcion'] : (trim((string)$servicio['descripcion_personalizada']) !== '' ? $servicio['descripcion_personalizada'] : $servicio['servicio_nombre']);
            $monto = $marcado ? $seleccionados[$servicio_id]['precio_unitario'] : $servicio['monto'];
            ?>
            <div class="pf-servicio-item">
                <label class="mb-0">
                    <input type="checkbox"
                           class="pfServicioCheck"
                           value="<?php echo e($servicio_id); ?>"
                           data-descripcion="<?php echo e($descripcion); ?>"
                           data-monto="<?php echo e($monto); ?>"
                           data-bloque="<?php echo e($bloque); ?>"
                           <?php echo $marcado ? 'checked' : ''; ?>>
                    <span>
                        <strong><?php echo e($servicio['servicio_nombre']); ?></strong>
                        <small>
                            <?php echo e($descripcion); ?> |
                            <?php echo e(app_money($monto)); ?>
                        </small>
                    </span>
                </label>
                <div><?php echo pf_badge_bloque($bloque); ?></div>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function pf_totalizar_bloques_detalles($detalles)
{
    $totales = array(
        'Actuales' => 0.00,
        'Pendientes de pago' => 0.00,
        'Otros servicios o trámites' => 0.00
    );

    foreach ($detalles as $detalle) {
        $bloque = isset($detalle['bloque']) ? $detalle['bloque'] : 'Actuales';
        if (!isset($totales[$bloque])) {
            $totales[$bloque] = 0.00;
        }
        $totales[$bloque] += isset($detalle['total']) ? (float)$detalle['total'] : 0.00;
    }

    return $totales;
}

function pf_render_documento($proforma_id)
{
    $pdo = app_pdo();

    $proforma = pf_obtener($proforma_id);

    if (!$proforma) {
        return '<div class="alert alert-warning mb-0">Proforma no encontrada.</div>';
    }

    $cliente = pf_obtener_cliente($proforma['cliente_id']);
    $plantilla = $proforma['plantilla_id'] ? pf_obtener_plantilla($proforma['plantilla_id']) : null;
    $empresa = pf_configuracion_empresa();
    $detalles = pf_obtener_detalles($proforma_id);
    $metodos = $plantilla ? pf_listar_metodos_plantilla($plantilla['id']) : array();

    if (!$plantilla) {
        $plantilla = array(
            'nombre' => 'Plantilla básica',
            'orientacion' => 'Vertical',
            'logo_visible' => 1,
            'logo_tipo' => 'Rectangular',
            'datos_empresa_visible' => 1,
            'datos_cliente_visible' => 1,
            'color_tipo' => 'Solido',
            'color_primario' => '#1f4e79',
            'color_secundario' => null,
            'pie_pagina_visible' => 1,
            'pie_pagina' => ''
        );
    }

    $bloques = array(
        'Actuales' => array(),
        'Pendientes de pago' => array(),
        'Otros servicios o trámites' => array()
    );

    $totales_bloque = pf_totalizar_bloques_detalles($detalles);

    foreach ($detalles as $detalle) {
        if (!isset($bloques[$detalle['bloque']])) {
            $bloques[$detalle['bloque']] = array();
            $totales_bloque[$detalle['bloque']] = 0.00;
        }

        $bloques[$detalle['bloque']][] = $detalle;
    }

    $logo_url = trim((string)$empresa['logo_ruta']) !== '' ? app_url($empresa['logo_ruta']) : '';

    $header_style = '';

    if ($plantilla['color_tipo'] === 'Degradado' && trim((string)$plantilla['color_secundario']) !== '') {
        $header_style = 'background: linear-gradient(135deg, ' . e($plantilla['color_primario']) . ', ' . e($plantilla['color_secundario']) . ');';
    } else {
        $header_style = 'background: ' . e($plantilla['color_primario']) . ';';
    }

    ob_start();
    ?>
    <div class="pf-documento pf-documento-<?php echo e(strtolower($plantilla['orientacion'])); ?>" id="pfDocumentoExportable">
        <div class="pf-doc-header" style="<?php echo $header_style; ?>">
            <?php if ((int)$plantilla['logo_visible'] === 1) { ?>
                <div class="pf-doc-logo pf-logo-<?php echo e(strtolower($plantilla['logo_tipo'])); ?>">
                    <?php if ($logo_url !== '') { ?>
                        <img src="<?php echo e($logo_url); ?>" alt="Logo">
                    <?php } else { ?>
                        <div class="pf-doc-logo-empty">
                            <i class="fas fa-image"></i>
                            <span>Logo</span>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ((int)$plantilla['datos_empresa_visible'] === 1) { ?>
                <div class="pf-doc-empresa">
                    <h3><?php echo e($empresa['nombre_comercial']); ?></h3>
                    <p><?php echo e($empresa['razon_social']); ?></p>
                    <p>RUC: <?php echo e($empresa['ruc']); ?></p>
                    <?php if (trim((string)$empresa['direccion']) !== '') { ?>
                        <p><?php echo e($empresa['direccion']); ?></p>
                    <?php } ?>
                    <?php if (trim((string)$empresa['celular']) !== '' || trim((string)$empresa['correo']) !== '') { ?>
                        <p><?php echo e(trim((string)$empresa['celular'] . ' ' . (string)$empresa['correo'])); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="pf-doc-codigo">
                <span>PROFORMA</span>
                <strong><?php echo e($proforma['codigo']); ?></strong>
            </div>
        </div>

        <div class="pf-doc-body">
            <div class="pf-doc-meta">
                <div>
                    <strong>Fecha de emisión:</strong>
                    <?php echo e(date('d/m/Y', strtotime($proforma['fecha_emision']))); ?>
                </div>
                <div>
                    <strong>Fecha de vencimiento:</strong>
                    <?php echo $proforma['fecha_vencimiento'] ? e(date('d/m/Y', strtotime($proforma['fecha_vencimiento']))) : 'Sin fecha'; ?>
                </div>
                <div>
                    <strong>Estado:</strong>
                    <?php echo e($proforma['estado']); ?>
                </div>
            </div>

            <?php if ((int)$plantilla['datos_cliente_visible'] === 1 && $cliente) { ?>
                <div class="pf-doc-cliente">
                    <h5>Cliente</h5>
                    <?php $cliente_contacto = trim((string)$cliente['nombres'] . ' ' . (string)$cliente['apellidos']); ?>
                    <?php if ($cliente['tipo_cliente'] === 'Empresa') { ?>
                        <p><strong><?php echo e(trim((string)$cliente['razon_social']) !== '' ? $cliente['razon_social'] : pf_cliente_nombre($cliente)); ?></strong></p>
                        <p>RUC <?php echo e($cliente['numero_documento']); ?></p>
                        <?php if ($cliente_contacto !== '') { ?>
                            <p><strong>Contacto:</strong> <?php echo e($cliente_contacto); ?></p>
                        <?php } ?>
                    <?php } else { ?>
                        <p><strong><?php echo e(pf_cliente_nombre($cliente)); ?></strong></p>
                        <p><?php echo e($cliente['documento_tipo']); ?> <?php echo e($cliente['numero_documento']); ?></p>
                    <?php } ?>
                    <?php if (trim((string)$cliente['direccion']) !== '') { ?>
                        <p><?php echo e($cliente['direccion']); ?></p>
                    <?php } ?>
                    <?php if (trim((string)$cliente['celular']) !== '' || trim((string)$cliente['correo']) !== '') { ?>
                        <p><?php echo e(trim((string)$cliente['celular'] . ' ' . (string)$cliente['correo'])); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php foreach ($bloques as $bloque_nombre => $items) { ?>
                <?php if (!empty($items)) { ?>
                    <div class="pf-doc-bloque">
                        <h5><?php echo e($bloque_nombre); ?></h5>
                        <table>
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-right">Precio</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) { ?>
                                    <tr>
                                        <td><?php echo nl2br(e($item['descripcion'])); ?></td>
                                        <td class="text-center"><?php echo e(number_format((float)$item['cantidad'], 2)); ?></td>
                                        <td class="text-right"><?php echo e(app_money($item['precio_unitario'])); ?></td>
                                        <td class="text-right"><?php echo e(app_money($item['total'])); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="pf-doc-bloque-total">
                            <span>Total <?php echo e($bloque_nombre); ?></span>
                            <strong><?php echo e(app_money($totales_bloque[$bloque_nombre])); ?></strong>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

            <div class="pf-doc-totales">
                <div><span>Subtotal</span><strong><?php echo e(app_money($proforma['subtotal'])); ?></strong></div>
                <div><span>Descuento</span><strong><?php echo e(app_money($proforma['descuento'])); ?></strong></div>
                <div class="pf-doc-total-final"><span>Total</span><strong><?php echo e(app_money($proforma['total'])); ?></strong></div>
            </div>

            <?php if (!empty($metodos)) { ?>
                <div class="pf-doc-metodos">
                    <h5>Métodos de pago</h5>
                    <?php foreach ($metodos as $metodo) { ?>
                        <div class="pf-doc-metodo">
                            <strong><?php echo e($metodo['titulo_visible']); ?></strong>
                            <span>
                                <?php echo e($metodo['tipo']); ?>
                                <?php if ($metodo['tipo'] === 'Cuenta de ahorro') { ?>
                                    | Titular: <?php echo e($metodo['titular']); ?>
                                    | Banco: <?php echo e($metodo['banco']); ?>
                                    | Cuenta: <?php echo e($metodo['numero_cuenta']); ?>
                                    <?php if (trim((string)$metodo['cci']) !== '') { ?>
                                        | CCI: <?php echo e($metodo['cci']); ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    | Titular: <?php echo e($metodo['titular']); ?>
                                    | Celular: <?php echo e($metodo['numero_celular']); ?>
                                <?php } ?>
                            </span>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ((int)$plantilla['pie_pagina_visible'] === 1) { ?>
                <?php $pie = trim((string)$plantilla['pie_pagina']) !== '' ? $plantilla['pie_pagina'] : $empresa['pie_pagina']; ?>
                <?php if (trim((string)$pie) !== '') { ?>
                    <div class="pf-doc-footer">
                        <?php echo nl2br(e($pie)); ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function pf_recalcular_estado_servicio($cliente_servicio_id)
{
    if ((int)$cliente_servicio_id <= 0) {
        return;
    }

    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM ecc_proforma_detalles pd
        INNER JOIN ecc_proformas p ON p.id = pd.proforma_id
        WHERE pd.cliente_servicio_id = :cliente_servicio_id
          AND p.estado NOT IN ('Anulada','Convertida')
    ");

    $stmt->execute(array(':cliente_servicio_id' => (int)$cliente_servicio_id));
    $row = $stmt->fetch();

    $nuevo_estado = ((int)$row['total'] > 0) ? 'En proforma' : 'Pendiente';

    $stmt = $pdo->prepare("
        UPDATE ecc_cliente_servicios
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
          AND estado NOT IN ('Pagado','Anulado')
    ");

    $stmt->execute(array(
        ':estado' => $nuevo_estado,
        ':updated_by_external_id' => pf_external_id(),
        ':id' => (int)$cliente_servicio_id
    ));
}

function pf_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Proformas de pago',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => pf_external_id(),
        ':created_by_external_id' => pf_external_id()
    ));
}
