<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function cs_external_id()
{
    $user = auth_user();

    if (isset($user['mode']) && $user['mode'] !== '') {
        return $user['mode'];
    }

    return 'demo';
}

function cs_cliente_nombre($cliente)
{
    if ($cliente['tipo_cliente'] === 'Empresa') {
        return trim((string)$cliente['razon_social']) !== '' ? $cliente['razon_social'] : $cliente['numero_documento'];
    }

    $nombre = trim((string)$cliente['nombres'] . ' ' . (string)$cliente['apellidos']);
    return $nombre !== '' ? $nombre : $cliente['numero_documento'];
}

function cs_estado_cliente_badge($estado)
{
    if ((int)$estado === 1) {
        return '<span class="badge badge-success">Activo</span>';
    }

    return '<span class="badge badge-secondary">Inactivo</span>';
}

function cs_estado_servicio_badge($estado)
{
    $map = array(
        'Pendiente' => 'warning',
        'En proforma' => 'info',
        'Pagado' => 'success',
        'Anulado' => 'danger'
    );

    $type = isset($map[$estado]) ? $map[$estado] : 'secondary';

    return '<span class="badge badge-' . e($type) . '">' . e($estado) . '</span>';
}

function cs_modo_aviso_badge($modo)
{
    $map = array(
        'Sin aviso' => 'secondary',
        'Fecha exacta' => 'primary',
        'Antes de vencer' => 'info',
        'Manual' => 'warning'
    );

    $type = isset($map[$modo]) ? $map[$modo] : 'secondary';

    return '<span class="badge badge-' . e($type) . '">' . e($modo) . '</span>';
}

function cs_listar_clientes()
{
    $pdo = app_pdo();

    $sql = "
        SELECT
            c.*,
            COALESCE(s.servicios_total, 0) AS servicios_total,
            COALESCE(s.servicios_pendientes, 0) AS servicios_pendientes,
            COALESCE(s.servicios_en_proforma, 0) AS servicios_en_proforma,
            COALESCE(s.servicios_pagados, 0) AS servicios_pagados
        FROM ecc_clientes c
        LEFT JOIN (
            SELECT
                cliente_id,
                COUNT(*) AS servicios_total,
                SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS servicios_pendientes,
                SUM(CASE WHEN estado = 'En proforma' THEN 1 ELSE 0 END) AS servicios_en_proforma,
                SUM(CASE WHEN estado = 'Pagado' THEN 1 ELSE 0 END) AS servicios_pagados
            FROM ecc_cliente_servicios
            GROUP BY cliente_id
        ) s ON s.cliente_id = c.id
        ORDER BY c.id DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function cs_obtener_cliente($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_clientes WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function cs_listar_servicios_generales()
{
    $pdo = app_pdo();

    $sql = "
        SELECT
            s.*,
            GROUP_CONCAT(DISTINCT e.nombre ORDER BY e.nombre SEPARATOR ', ') AS etiquetas
        FROM ecc_servicios s
        LEFT JOIN ecc_servicio_etiquetas se ON se.servicio_id = s.id
        LEFT JOIN ecc_etiquetas e ON e.id = se.etiqueta_id AND e.estado = 1
        WHERE s.estado = 1
        GROUP BY s.id
        ORDER BY s.nombre ASC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function cs_listar_etiquetas()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("SELECT * FROM ecc_etiquetas WHERE estado = 1 ORDER BY nombre ASC");
    return $stmt->fetchAll();
}

function cs_etiquetas_servicio_ids($servicio_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT etiqueta_id FROM ecc_servicio_etiquetas WHERE servicio_id = :servicio_id");
    $stmt->execute(array(':servicio_id' => (int)$servicio_id));

    $ids = array();

    foreach ($stmt->fetchAll() as $row) {
        $ids[] = (int)$row['etiqueta_id'];
    }

    return $ids;
}

function cs_render_servicios_options($selected_id = 0)
{
    $html = '<option value="">Seleccione servicio</option>';
    $servicios = cs_listar_servicios_generales();

    foreach ($servicios as $servicio) {
        $selected = ((int)$selected_id === (int)$servicio['id']) ? ' selected' : '';
        $label = $servicio['nombre'];

        if (trim((string)$servicio['etiquetas']) !== '') {
            $label .= ' | ' . $servicio['etiquetas'];
        }

        $html .= '<option value="' . e($servicio['id']) . '"' . $selected . ' data-precio="' . e($servicio['precio_base']) . '">' . e($label) . '</option>';
    }

    return $html;
}

function cs_render_etiquetas_options($selected_ids = array())
{
    $html = '';
    $etiquetas = cs_listar_etiquetas();

    foreach ($etiquetas as $etiqueta) {
        $selected = in_array((int)$etiqueta['id'], $selected_ids, true) ? ' selected' : '';
        $html .= '<option value="' . e($etiqueta['id']) . '"' . $selected . '>' . e($etiqueta['nombre']) . '</option>';
    }

    return $html;
}

function cs_render_etiquetas_badges($etiquetas)
{
    $texto = trim((string)$etiquetas);

    if ($texto === '') {
        return '<span class="text-muted">Sin etiquetas</span>';
    }

    $partes = explode(',', $texto);
    $html = '';

    foreach ($partes as $parte) {
        $nombre = trim($parte);

        if ($nombre !== '') {
            $html .= '<span class="badge badge-light border mr-1">' . e($nombre) . '</span>';
        }
    }

    return $html !== '' ? $html : '<span class="text-muted">Sin etiquetas</span>';
}

function cs_render_clientes_table()
{
    $clientes = cs_listar_clientes();

    ob_start();
    ?>
    <table class="table table-sm" data-app-table="true" data-page-length="10" data-empty-text="No hay clientes registrados.">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Contacto</th>
                <th>Servicios</th>
                <th>Estado</th>
                <th width="150">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $cliente) { ?>
                <tr data-id="<?php echo e($cliente['id']); ?>">
                    <td>
                        <strong><?php echo e(cs_cliente_nombre($cliente)); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo e($cliente['tipo_cliente']); ?></small>
                    </td>
                    <td>
                        <span class="badge badge-light border"><?php echo e($cliente['documento_tipo']); ?></span>
                        <?php echo e($cliente['numero_documento']); ?>
                    </td>
                    <td>
                        <?php if (trim((string)$cliente['celular']) !== '') { ?>
                            <div><i class="fas fa-phone-alt mr-1 text-muted"></i><?php echo e($cliente['celular']); ?></div>
                        <?php } ?>
                        <?php if (trim((string)$cliente['correo']) !== '') { ?>
                            <div><i class="fas fa-envelope mr-1 text-muted"></i><?php echo e($cliente['correo']); ?></div>
                        <?php } ?>
                        <?php if (trim((string)$cliente['celular']) === '' && trim((string)$cliente['correo']) === '') { ?>
                            <span class="text-muted">Sin contacto</span>
                        <?php } ?>
                    </td>
                    <td>
                        <span class="badge badge-secondary">Total: <?php echo e($cliente['servicios_total']); ?></span>
                        <span class="badge badge-warning">Pendientes: <?php echo e($cliente['servicios_pendientes']); ?></span>
                    </td>
                    <td><?php echo cs_estado_cliente_badge($cliente['estado']); ?></td>
                    <td>
                        <div class="app-action-buttons">
                            <button type="button" class="btn btn-sm btn-info btnVerCliente" data-id="<?php echo e($cliente['id']); ?>" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary btnEditarCliente" data-id="<?php echo e($cliente['id']); ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ((int)$cliente['estado'] === 1) { ?>
                                <button type="button" class="btn btn-sm btn-danger btnDesactivarCliente" data-id="<?php echo e($cliente['id']); ?>" data-nombre="<?php echo e(cs_cliente_nombre($cliente)); ?>" title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function cs_listar_servicios_cliente($cliente_id)
{
    $pdo = app_pdo();

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
        GROUP BY cs.id
        ORDER BY cs.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':cliente_id' => (int)$cliente_id));

    return $stmt->fetchAll();
}

function cs_obtener_servicio_asignado($id)
{
    $pdo = app_pdo();

    $sql = "
        SELECT
            cs.*,
            s.nombre AS servicio_nombre
        FROM ecc_cliente_servicios cs
        INNER JOIN ecc_servicios s ON s.id = cs.servicio_id
        WHERE cs.id = :id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function cs_render_cliente_detalle($cliente_id)
{
    $cliente = cs_obtener_cliente($cliente_id);

    if (!$cliente) {
        return '<div class="alert alert-warning mb-0">Cliente no encontrado.</div>';
    }

    $servicios = cs_listar_servicios_cliente($cliente_id);

    ob_start();
    ?>
    <div class="row">
        <div class="col-md-5">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-1"></i>
                        Datos del cliente
                    </h3>
                </div>
                <div class="card-body">
                    <h5 class="mb-1"><?php echo e(cs_cliente_nombre($cliente)); ?></h5>
                    <p class="text-muted mb-2"><?php echo e($cliente['tipo_cliente']); ?></p>

                    <p class="mb-1">
                        <strong>Documento:</strong>
                        <?php echo e($cliente['documento_tipo']); ?> <?php echo e($cliente['numero_documento']); ?>
                    </p>
                    <p class="mb-1">
                        <strong>Celular:</strong>
                        <?php echo trim((string)$cliente['celular']) !== '' ? e($cliente['celular']) : '<span class="text-muted">No registrado</span>'; ?>
                    </p>
                    <p class="mb-1">
                        <strong>Correo:</strong>
                        <?php echo trim((string)$cliente['correo']) !== '' ? e($cliente['correo']) : '<span class="text-muted">No registrado</span>'; ?>
                    </p>
                    <p class="mb-1">
                        <strong>Dirección:</strong>
                        <?php echo trim((string)$cliente['direccion']) !== '' ? e($cliente['direccion']) : '<span class="text-muted">No registrada</span>'; ?>
                    </p>
                    <p class="mb-0">
                        <strong>Estado:</strong>
                        <?php echo cs_estado_cliente_badge($cliente['estado']); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-tasks mr-1"></i>
                        Servicios asignados
                    </h3>
                    <button type="button" class="btn btn-sm btn-primary ml-auto btnNuevoServicioCliente" data-cliente-id="<?php echo e($cliente['id']); ?>">
                        <i class="fas fa-plus mr-1"></i>
                        Asignar servicio
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered" data-app-table="true" data-page-length="10" data-empty-text="Este cliente no tiene servicios asignados.">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Periodo</th>
                                <th>Monto</th>
                                <th>Aviso</th>
                                <th>Estado</th>
                                <th width="115">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio) { ?>
                                <tr data-id="<?php echo e($servicio['id']); ?>">
                                    <td>
                                        <strong><?php echo e($servicio['servicio_nombre']); ?></strong>
                                        <br>
                                        <?php echo cs_render_etiquetas_badges($servicio['etiquetas']); ?>
                                        <?php if (trim((string)$servicio['descripcion_personalizada']) !== '') { ?>
                                            <br>
                                            <small class="text-muted"><?php echo e($servicio['descripcion_personalizada']); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo trim((string)$servicio['periodo']) !== '' ? e($servicio['periodo']) : '<span class="text-muted">Sin periodo</span>'; ?></td>
                                    <td><?php echo e(app_money($servicio['monto'])); ?></td>
                                    <td>
                                        <?php echo cs_modo_aviso_badge($servicio['modo_aviso']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $servicio['fecha_aviso'] ? e(date('d/m/Y', strtotime($servicio['fecha_aviso']))) : 'Sin fecha'; ?>
                                        </small>
                                    </td>
                                    <td><?php echo cs_estado_servicio_badge($servicio['estado']); ?></td>
                                    <td>
                                        <div class="app-action-buttons">
                                            <?php if ($servicio['estado'] !== 'Pagado' && $servicio['estado'] !== 'Anulado') { ?>
                                                <button type="button" class="btn btn-sm btn-primary btnEditarServicioCliente" data-id="<?php echo e($servicio['id']); ?>" title="Editar servicio">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger btnAnularServicioCliente" data-id="<?php echo e($servicio['id']); ?>" data-nombre="<?php echo e($servicio['servicio_nombre']); ?>" title="Anular servicio">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php } else { ?>
                                                <span class="text-muted">Sin acciones</span>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function cs_sync_servicio_etiquetas($servicio_id, $etiqueta_ids)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("DELETE FROM ecc_servicio_etiquetas WHERE servicio_id = :servicio_id");
    $stmt->execute(array(':servicio_id' => (int)$servicio_id));

    if (!is_array($etiqueta_ids)) {
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO ecc_servicio_etiquetas (servicio_id, etiqueta_id)
        VALUES (:servicio_id, :etiqueta_id)
    ");

    foreach ($etiqueta_ids as $etiqueta_id) {
        $etiqueta_id = (int)$etiqueta_id;

        if ($etiqueta_id > 0) {
            $insert->execute(array(
                ':servicio_id' => (int)$servicio_id,
                ':etiqueta_id' => $etiqueta_id
            ));
        }
    }
}

function cs_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Clientes y servicios',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => cs_external_id(),
        ':created_by_external_id' => cs_external_id()
    ));
}