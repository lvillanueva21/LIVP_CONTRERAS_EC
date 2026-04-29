<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function rb_external_id()
{
    $user = auth_user();

    if (isset($user['usuario']) && trim((string)$user['usuario']) !== '') {
        return trim((string)$user['usuario']);
    }

    if (isset($user['dni']) && trim((string)$user['dni']) !== '') {
        return trim((string)$user['dni']);
    }

    if (isset($user['mode']) && trim((string)$user['mode']) !== '') {
        return trim((string)$user['mode']);
    }

    return 'sistema';
}

function rb_cliente_nombre($cliente)
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

function rb_generar_codigo()
{
    $pdo = app_pdo();

    $anio = date('y');
    $prefijo = 'R' . $anio . '-';

    $stmt = $pdo->prepare("
        SELECT MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) AS ultimo
        FROM ecc_recibos
        WHERE codigo LIKE :prefijo
    ");

    $stmt->execute(array(':prefijo' => $prefijo . '%'));
    $row = $stmt->fetch();

    $ultimo = isset($row['ultimo']) ? (int)$row['ultimo'] : 0;
    $siguiente = $ultimo > 0 ? $ultimo + 1 : 596;

    return $prefijo . str_pad((string)$siguiente, 6, '0', STR_PAD_LEFT);
}

function rb_listar()
{
    $pdo = app_pdo();

    $sql = "
        SELECT
            r.*,
            p.codigo AS proforma_codigo,
            c.tipo_cliente,
            c.documento_tipo,
            c.numero_documento,
            c.razon_social,
            c.nombres,
            c.apellidos,
            mp.titulo_visible AS metodo_pago_titulo,
            mp.tipo AS metodo_pago_tipo
        FROM ecc_recibos r
        INNER JOIN ecc_clientes c ON c.id = r.cliente_id
        LEFT JOIN ecc_proformas p ON p.id = r.proforma_id
        LEFT JOIN ecc_metodos_pago mp ON mp.id = r.metodo_pago_id
        ORDER BY r.id DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function rb_obtener($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_recibos WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function rb_obtener_detalles($recibo_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT *
        FROM ecc_recibo_detalles
        WHERE recibo_id = :recibo_id
        ORDER BY orden ASC, id ASC
    ");

    $stmt->execute(array(':recibo_id' => (int)$recibo_id));

    return $stmt->fetchAll();
}

function rb_obtener_cliente($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_clientes WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function rb_obtener_proforma($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_proformas WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function rb_obtener_plantilla($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_plantillas WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function rb_obtener_metodo_pago($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_metodos_pago WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function rb_configuracion_empresa()
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

function rb_listar_clientes()
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

function rb_listar_plantillas()
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

function rb_listar_metodos_pago()
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

function rb_listar_proformas_pendientes()
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
            c.apellidos
        FROM ecc_proformas p
        INNER JOIN ecc_clientes c ON c.id = p.cliente_id
        WHERE p.estado IN ('Emitida','Parcial')
        ORDER BY p.id DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function rb_listar_detalles_proforma($proforma_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT
            pd.*,
            COALESCE(pagos.pagado, 0) AS monto_pagado_acumulado,
            GREATEST(pd.total - COALESCE(pagos.pagado, 0), 0) AS saldo_detalle
        FROM ecc_proforma_detalles pd
        LEFT JOIN (
            SELECT
                rd.proforma_detalle_id,
                SUM(rd.monto_pagado) AS pagado
            FROM ecc_recibo_detalles rd
            INNER JOIN ecc_recibos r ON r.id = rd.recibo_id
            WHERE r.estado = 'Emitido'
              AND rd.proforma_detalle_id IS NOT NULL
            GROUP BY rd.proforma_detalle_id
        ) pagos ON pagos.proforma_detalle_id = pd.id
        WHERE pd.proforma_id = :proforma_id
        ORDER BY pd.orden ASC, pd.id ASC
    ");

    $stmt->execute(array(':proforma_id' => (int)$proforma_id));

    return $stmt->fetchAll();
}

function rb_listar_servicios_adicionales_cliente($cliente_id, $proforma_id = 0)
{
    $pdo = app_pdo();

    $params = array(':cliente_id' => (int)$cliente_id);

    $sql = "
        SELECT
            cs.*,
            s.nombre AS servicio_nombre,
            s.precio_base
        FROM ecc_cliente_servicios cs
        INNER JOIN ecc_servicios s ON s.id = cs.servicio_id
        WHERE cs.cliente_id = :cliente_id
          AND cs.estado = 'Pendiente'
    ";

    if ((int)$proforma_id > 0) {
        $sql .= "
          AND cs.id NOT IN (
              SELECT cliente_servicio_id
              FROM ecc_proforma_detalles
              WHERE proforma_id = :proforma_id
                AND cliente_servicio_id IS NOT NULL
          )
        ";
        $params[':proforma_id'] = (int)$proforma_id;
    }

    $sql .= " ORDER BY cs.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function rb_metodos_plantilla($plantilla_id)
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

function rb_render_clientes_options($selected_id = 0)
{
    $clientes = rb_listar_clientes();
    $html = '<option value="">Seleccione cliente</option>';

    foreach ($clientes as $cliente) {
        $selected = (int)$selected_id === (int)$cliente['id'] ? ' selected' : '';
        $label = rb_cliente_nombre($cliente) . ' | ' . $cliente['documento_tipo'] . ' ' . $cliente['numero_documento'];
        $html .= '<option value="' . e($cliente['id']) . '"' . $selected . '>' . e($label) . '</option>';
    }

    return $html;
}

function rb_render_plantillas_options($selected_id = 0)
{
    $plantillas = rb_listar_plantillas();
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

function rb_render_metodos_pago_options($selected_id = 0)
{
    $metodos = rb_listar_metodos_pago();
    $html = '<option value="">Seleccione método</option>';

    foreach ($metodos as $metodo) {
        $selected = (int)$selected_id === (int)$metodo['id'] ? ' selected' : '';
        $label = $metodo['titulo_visible'] . ' | ' . $metodo['tipo'];
        $html .= '<option value="' . e($metodo['id']) . '"' . $selected . '>' . e($label) . '</option>';
    }

    return $html;
}

function rb_render_proformas_options()
{
    $proformas = rb_listar_proformas_pendientes();
    $html = '<option value="">Seleccione proforma</option>';

    foreach ($proformas as $proforma) {
        $label = $proforma['codigo'] . ' | ' . rb_cliente_nombre($proforma) . ' | ' . app_money($proforma['total']);
        $html .= '<option value="' . e($proforma['id']) . '">' . e($label) . '</option>';
    }

    return $html;
}

function rb_badge_estado($estado)
{
    $map = array(
        'Emitido' => 'success',
        'Anulado' => 'danger'
    );

    $tipo = isset($map[$estado]) ? $map[$estado] : 'secondary';

    return '<span class="badge badge-' . e($tipo) . '">' . e($estado) . '</span>';
}

function rb_render_tabla()
{
    $recibos = rb_listar();

    ob_start();
    ?>
    <table class="table table-sm" data-app-table="true" data-page-length="10" data-empty-text="No hay recibos registrados.">
        <thead>
            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Proforma</th>
                <th>Pago</th>
                <th>Importes</th>
                <th>Estado</th>
                <th width="155">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recibos as $recibo) { ?>
                <tr data-id="<?php echo e($recibo['id']); ?>">
                    <td>
                        <strong><?php echo e($recibo['codigo']); ?></strong>
                        <br>
                        <small class="text-muted">ID <?php echo e($recibo['id']); ?></small>
                    </td>
                    <td>
                        <strong><?php echo e(rb_cliente_nombre($recibo)); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo e($recibo['documento_tipo']); ?> <?php echo e($recibo['numero_documento']); ?></small>
                    </td>
                    <td>
                        <?php echo trim((string)$recibo['proforma_codigo']) !== '' ? e($recibo['proforma_codigo']) : '<span class="text-muted">Manual</span>'; ?>
                    </td>
                    <td>
                        <div><strong>Fecha:</strong> <?php echo e(date('d/m/Y', strtotime($recibo['fecha_pago']))); ?></div>
                        <div>
                            <strong>Método:</strong>
                            <?php echo trim((string)$recibo['metodo_pago_titulo']) !== '' ? e($recibo['metodo_pago_titulo']) : '<span class="text-muted">No registrado</span>'; ?>
                        </div>
                    </td>
                    <td>
                        <div><strong>Pagado:</strong> <?php echo e(app_money($recibo['total_pagado'])); ?></div>
                        <div><strong>Saldo:</strong> <?php echo e(app_money($recibo['saldo_pendiente'])); ?></div>
                    </td>
                    <td><?php echo rb_badge_estado($recibo['estado']); ?></td>
                    <td>
                        <div class="app-action-buttons">
                            <button type="button" class="btn btn-sm btn-info btnVerRecibo" data-id="<?php echo e($recibo['id']); ?>" title="Ver recibo">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary btnExportarRecibo" data-id="<?php echo e($recibo['id']); ?>" data-tipo="jpg" title="Descargar JPG">
                                <i class="fas fa-image"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btnExportarRecibo" data-id="<?php echo e($recibo['id']); ?>" data-tipo="pdf" title="Descargar PDF">
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

function rb_render_detalles_proforma($proforma_id)
{
    $proforma = rb_obtener_proforma($proforma_id);

    if (!$proforma) {
        return '<div class="alert alert-warning mb-0">Proforma no encontrada.</div>';
    }

    $detalles = rb_listar_detalles_proforma($proforma_id);

    if (empty($detalles)) {
        return '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-inbox"></i></div><h5>Sin detalles</h5><p>La proforma no tiene detalles disponibles.</p></div>';
    }

    ob_start();
    ?>
    <div class="rb-items-lista">
        <?php foreach ($detalles as $detalle) { ?>
            <?php
            $saldo = (float)$detalle['saldo_detalle'];
            $disabled = $saldo <= 0 ? ' disabled' : '';
            ?>
            <div class="rb-item-pago <?php echo $saldo <= 0 ? 'rb-item-pagado' : ''; ?>">
                <label>
                    <input type="checkbox"
                           class="rbDetalleProformaCheck"
                           value="<?php echo e($detalle['id']); ?>"
                           data-cliente-servicio-id="<?php echo e($detalle['cliente_servicio_id']); ?>"
                           data-bloque="<?php echo e($detalle['bloque']); ?>"
                           data-descripcion="<?php echo e($detalle['descripcion']); ?>"
                           data-monto-original="<?php echo e($detalle['total']); ?>"
                           data-saldo="<?php echo e($saldo); ?>"
                           <?php echo $disabled; ?>>
                    <span>
                        <strong><?php echo e($detalle['descripcion']); ?></strong>
                        <small>
                            <?php echo e($detalle['bloque']); ?> |
                            Original: <?php echo e(app_money($detalle['total'])); ?> |
                            Pagado: <?php echo e(app_money($detalle['monto_pagado_acumulado'])); ?> |
                            Saldo: <?php echo e(app_money($saldo)); ?>
                        </small>
                    </span>
                </label>
                <input type="number" class="form-control form-control-sm rbMontoDetalleProforma" min="0" step="0.01" value="<?php echo e(number_format($saldo, 2, '.', '')); ?>" <?php echo $disabled; ?>>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function rb_render_servicios_adicionales($cliente_id, $proforma_id = 0)
{
    $servicios = rb_listar_servicios_adicionales_cliente($cliente_id, $proforma_id);

    if (empty($servicios)) {
        return '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-inbox"></i></div><h5>Sin servicios adicionales</h5><p>No hay servicios pendientes adicionales para este cliente.</p></div>';
    }

    ob_start();
    ?>
    <div class="rb-items-lista">
        <?php foreach ($servicios as $servicio) { ?>
            <?php
            $descripcion = trim((string)$servicio['descripcion_personalizada']) !== '' ? $servicio['descripcion_personalizada'] : $servicio['servicio_nombre'];
            ?>
            <div class="rb-item-pago">
                <label>
                    <input type="checkbox"
                           class="rbServicioAdicionalCheck"
                           value="<?php echo e($servicio['id']); ?>"
                           data-descripcion="<?php echo e($descripcion); ?>"
                           data-monto-original="<?php echo e($servicio['monto']); ?>">
                    <span>
                        <strong><?php echo e($servicio['servicio_nombre']); ?></strong>
                        <small>
                            <?php echo e(app_money($servicio['monto'])); ?>
                        </small>
                    </span>
                </label>
                <div class="form-group mb-0">
                    <label class="mb-1">Bloque del documento</label>
                    <select class="custom-select custom-select-sm rbServicioAdicionalBloque">
                        <option value="Actuales" <?php echo $servicio['bloque_documento'] === 'Actuales' ? 'selected' : ''; ?>>Actuales</option>
                        <option value="Pendientes de pago" <?php echo $servicio['bloque_documento'] === 'Pendientes de pago' ? 'selected' : ''; ?>>Pendientes de pago</option>
                        <option value="Otros servicios o trámites" <?php echo $servicio['bloque_documento'] === 'Otros servicios o trámites' ? 'selected' : ''; ?>>Otros servicios o trámites</option>
                    </select>
                </div>
                <input type="number" class="form-control form-control-sm rbMontoServicioAdicional" min="0" step="0.01" value="<?php echo e(number_format((float)$servicio['monto'], 2, '.', '')); ?>">
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function rb_suma_pagada_proforma($proforma_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(rd.monto_pagado), 0) AS pagado
        FROM ecc_recibo_detalles rd
        INNER JOIN ecc_recibos r ON r.id = rd.recibo_id
        INNER JOIN ecc_proforma_detalles pd ON pd.id = rd.proforma_detalle_id
        WHERE r.estado = 'Emitido'
          AND pd.proforma_id = :proforma_id
    ");

    $stmt->execute(array(':proforma_id' => (int)$proforma_id));
    $row = $stmt->fetch();

    return isset($row['pagado']) ? (float)$row['pagado'] : 0.00;
}

function rb_actualizar_estados_proforma($proforma_id)
{
    $pdo = app_pdo();

    $proforma = rb_obtener_proforma($proforma_id);

    if (!$proforma) {
        return;
    }

    $detalles = rb_listar_detalles_proforma($proforma_id);

    foreach ($detalles as $detalle) {
        $estado_detalle = ((float)$detalle['monto_pagado_acumulado'] >= (float)$detalle['total']) ? 'Pagado' : 'Pendiente';

        $stmt = $pdo->prepare("
            UPDATE ecc_proforma_detalles
            SET estado = :estado
            WHERE id = :id
        ");

        $stmt->execute(array(
            ':estado' => $estado_detalle,
            ':id' => (int)$detalle['id']
        ));

        if ((int)$detalle['cliente_servicio_id'] > 0) {
            $estado_servicio = $estado_detalle === 'Pagado' ? 'Pagado' : 'Pendiente';

            $stmt = $pdo->prepare("
                UPDATE ecc_cliente_servicios
                SET estado = :estado, updated_by_external_id = :updated_by_external_id
                WHERE id = :id
                  AND estado NOT IN ('Anulado')
            ");

            $stmt->execute(array(
                ':estado' => $estado_servicio,
                ':updated_by_external_id' => rb_external_id(),
                ':id' => (int)$detalle['cliente_servicio_id']
            ));
        }
    }

    $pagado = rb_suma_pagada_proforma($proforma_id);
    $total = (float)$proforma['total'];
    $estado = 'Emitida';

    if ($pagado > 0 && $pagado < $total) {
        $estado = 'Parcial';
    }

    if ($total > 0 && $pagado >= $total) {
        $estado = 'Convertida';
    }

    $stmt = $pdo->prepare("
        UPDATE ecc_proformas
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':estado' => $estado,
        ':updated_by_external_id' => rb_external_id(),
        ':id' => (int)$proforma_id
    ));
}

function rb_actualizar_estado_servicio_adicional($cliente_servicio_id, $monto_original, $monto_pagado)
{
    if ((int)$cliente_servicio_id <= 0) {
        return;
    }

    $pdo = app_pdo();
    $estado = ((float)$monto_pagado >= (float)$monto_original) ? 'Pagado' : 'Pendiente';

    $stmt = $pdo->prepare("
        UPDATE ecc_cliente_servicios
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
          AND estado NOT IN ('Anulado')
    ");

    $stmt->execute(array(
        ':estado' => $estado,
        ':updated_by_external_id' => rb_external_id(),
        ':id' => (int)$cliente_servicio_id
    ));
}

function rb_totalizar_bloques_recibo($detalles)
{
    $totales = array(
        'Actuales' => array('original' => 0.00, 'pagado' => 0.00),
        'Pendientes de pago' => array('original' => 0.00, 'pagado' => 0.00),
        'Otros servicios o trámites' => array('original' => 0.00, 'pagado' => 0.00)
    );

    foreach ($detalles as $detalle) {
        $bloque = isset($detalle['bloque']) ? $detalle['bloque'] : 'Actuales';
        if (!isset($totales[$bloque])) {
            $totales[$bloque] = array('original' => 0.00, 'pagado' => 0.00);
        }
        $totales[$bloque]['original'] += isset($detalle['monto_original']) ? (float)$detalle['monto_original'] : 0.00;
        $totales[$bloque]['pagado'] += isset($detalle['monto_pagado']) ? (float)$detalle['monto_pagado'] : 0.00;
    }

    return $totales;
}

function rb_render_documento($recibo_id)
{
    $recibo = rb_obtener($recibo_id);

    if (!$recibo) {
        return '<div class="alert alert-warning mb-0">Recibo no encontrado.</div>';
    }

    $cliente = rb_obtener_cliente($recibo['cliente_id']);
    $plantilla = $recibo['plantilla_id'] ? rb_obtener_plantilla($recibo['plantilla_id']) : null;
    $metodo = $recibo['metodo_pago_id'] ? rb_obtener_metodo_pago($recibo['metodo_pago_id']) : null;
    $proforma = $recibo['proforma_id'] ? rb_obtener_proforma($recibo['proforma_id']) : null;
    $empresa = rb_configuracion_empresa();
    $detalles = rb_obtener_detalles($recibo_id);
    $metodos_visibles = $plantilla ? rb_metodos_plantilla($plantilla['id']) : array();

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

    $totales_bloque = rb_totalizar_bloques_recibo($detalles);

    foreach ($detalles as $detalle) {
        if (!isset($bloques[$detalle['bloque']])) {
            $bloques[$detalle['bloque']] = array();
            $totales_bloque[$detalle['bloque']] = array('original' => 0.00, 'pagado' => 0.00);
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
    <div class="rb-documento rb-documento-<?php echo e(strtolower($plantilla['orientacion'])); ?>" id="rbDocumentoExportable">
        <div class="rb-doc-header" style="<?php echo $header_style; ?>">
            <?php if ((int)$plantilla['logo_visible'] === 1) { ?>
                <div class="rb-doc-logo rb-logo-<?php echo e(strtolower($plantilla['logo_tipo'])); ?>">
                    <?php if ($logo_url !== '') { ?>
                        <img src="<?php echo e($logo_url); ?>" alt="Logo">
                    <?php } else { ?>
                        <div class="rb-doc-logo-empty">
                            <i class="fas fa-image"></i>
                            <span>Logo</span>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ((int)$plantilla['datos_empresa_visible'] === 1) { ?>
                <div class="rb-doc-empresa">
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

            <div class="rb-doc-codigo">
                <span>RECIBO</span>
                <strong><?php echo e($recibo['codigo']); ?></strong>
            </div>
        </div>

        <div class="rb-doc-body">
            <div class="rb-doc-meta">
                <div>
                    <strong>Fecha de emisión:</strong>
                    <?php echo e(date('d/m/Y', strtotime($recibo['fecha_emision']))); ?>
                </div>
                <div>
                    <strong>Fecha de pago:</strong>
                    <?php echo e(date('d/m/Y', strtotime($recibo['fecha_pago']))); ?>
                </div>
                <div>
                    <strong>Proforma:</strong>
                    <?php echo $proforma ? e($proforma['codigo']) : 'Manual'; ?>
                </div>
            </div>

            <?php if ((int)$plantilla['datos_cliente_visible'] === 1 && $cliente) { ?>
                <div class="rb-doc-cliente">
                    <h5>Cliente</h5>
                    <?php $cliente_contacto = trim((string)$cliente['nombres'] . ' ' . (string)$cliente['apellidos']); ?>
                    <?php if ($cliente['tipo_cliente'] === 'Empresa') { ?>
                        <p><strong><?php echo e(trim((string)$cliente['razon_social']) !== '' ? $cliente['razon_social'] : rb_cliente_nombre($cliente)); ?></strong></p>
                        <p>RUC <?php echo e($cliente['numero_documento']); ?></p>
                        <?php if ($cliente_contacto !== '') { ?>
                            <p><strong>Contacto:</strong> <?php echo e($cliente_contacto); ?></p>
                        <?php } ?>
                    <?php } else { ?>
                        <p><strong><?php echo e(rb_cliente_nombre($cliente)); ?></strong></p>
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
                    <div class="rb-doc-bloque">
                        <h5><?php echo e($bloque_nombre); ?></h5>
                        <table>
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th class="text-right">Original</th>
                                    <th class="text-right">Pagado</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) { ?>
                                    <tr>
                                        <td><?php echo nl2br(e($item['descripcion'])); ?></td>
                                        <td class="text-right"><?php echo e(app_money($item['monto_original'])); ?></td>
                                        <td class="text-right"><?php echo e(app_money($item['monto_pagado'])); ?></td>
                                        <td class="text-center"><?php echo e($item['estado_servicio_resultante']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="rb-doc-bloque-total">
                            <div>
                                <span>Total original <?php echo e($bloque_nombre); ?></span>
                                <strong><?php echo e(app_money($totales_bloque[$bloque_nombre]['original'])); ?></strong>
                            </div>
                            <div>
                                <span>Total pagado <?php echo e($bloque_nombre); ?></span>
                                <strong><?php echo e(app_money($totales_bloque[$bloque_nombre]['pagado'])); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

            <div class="rb-doc-totales">
                <div><span>Total proforma</span><strong><?php echo e(app_money($recibo['total_proforma'])); ?></strong></div>
                <div class="rb-doc-total-final"><span>Total pagado</span><strong><?php echo e(app_money($recibo['total_pagado'])); ?></strong></div>
                <div><span>Saldo pendiente</span><strong><?php echo e(app_money($recibo['saldo_pendiente'])); ?></strong></div>
            </div>

            <?php if (!empty($metodos_visibles)) { ?>
                <div class="rb-doc-metodos">
                    <h5>Métodos de pago</h5>
                    <?php foreach ($metodos_visibles as $metodo_visible) { ?>
                        <div class="rb-doc-metodo">
                            <strong><?php echo e($metodo_visible['titulo_visible']); ?></strong>
                            <span>
                                <?php echo e($metodo_visible['tipo']); ?>
                                <?php if ($metodo_visible['tipo'] === 'Cuenta de ahorro') { ?>
                                    | Banco: <?php echo e($metodo_visible['banco']); ?>
                                    | Cuenta: <?php echo e($metodo_visible['numero_cuenta']); ?>
                                    <?php if (trim((string)$metodo_visible['cci']) !== '') { ?>
                                        | CCI: <?php echo e($metodo_visible['cci']); ?>
                                    <?php } ?>
                                <?php } else { ?>
                                    | Celular: <?php echo e($metodo_visible['numero_celular']); ?>
                                <?php } ?>
                            </span>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($metodo) { ?>
                <div class="rb-doc-metodo-pago">
                    <h5>Método usado para el pago</h5>
                    <strong><?php echo e($metodo['titulo_visible']); ?></strong>
                    <span>
                        <?php echo e($metodo['tipo']); ?>
                        <?php if ($metodo['tipo'] === 'Cuenta de ahorro') { ?>
                            | Banco: <?php echo e($metodo['banco']); ?>
                            | Cuenta: <?php echo e($metodo['numero_cuenta']); ?>
                            <?php if (trim((string)$metodo['cci']) !== '') { ?>
                                | CCI: <?php echo e($metodo['cci']); ?>
                            <?php } ?>
                        <?php } else { ?>
                            | Celular: <?php echo e($metodo['numero_celular']); ?>
                        <?php } ?>
                    </span>
                </div>
            <?php } ?>
            <?php if ((int)$plantilla['pie_pagina_visible'] === 1) { ?>
                <?php $pie = trim((string)$plantilla['pie_pagina']) !== '' ? $plantilla['pie_pagina'] : $empresa['pie_pagina']; ?>
                <?php if (trim((string)$pie) !== '') { ?>
                    <div class="rb-doc-footer">
                        <?php echo nl2br(e($pie)); ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function rb_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Recibos de pago',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => rb_external_id(),
        ':created_by_external_id' => rb_external_id()
    ));
}

