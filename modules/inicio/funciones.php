<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function dash_scalar($sql, $params = array(), $default = 0)
{
    $pdo = app_pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();

    return $value === false || $value === null ? $default : $value;
}

function dash_aviso_programado_sql()
{
    return "
        CASE
            WHEN cs.modo_aviso = 'Fecha exacta' THEN cs.fecha_aviso
            WHEN cs.modo_aviso = 'Faltando X días' AND cs.fecha_vencimiento IS NOT NULL THEN DATE_SUB(CONCAT(cs.fecha_vencimiento, ' 00:00:00'), INTERVAL COALESCE(cs.aviso_valor, 0) DAY)
            WHEN cs.modo_aviso = 'Faltando X horas' AND cs.fecha_vencimiento IS NOT NULL THEN DATE_SUB(CONCAT(cs.fecha_vencimiento, ' 00:00:00'), INTERVAL COALESCE(cs.aviso_valor, 0) HOUR)
            WHEN cs.modo_aviso = 'Faltando X minutos' AND cs.fecha_vencimiento IS NOT NULL THEN DATE_SUB(CONCAT(cs.fecha_vencimiento, ' 00:00:00'), INTERVAL COALESCE(cs.aviso_valor, 0) MINUTE)
            WHEN cs.modo_aviso = 'Antes de vencer' AND cs.fecha_vencimiento IS NOT NULL THEN COALESCE(cs.fecha_aviso, CONCAT(cs.fecha_vencimiento, ' 00:00:00'))
            WHEN cs.modo_aviso = 'Manual' THEN cs.fecha_aviso
            ELSE NULL
        END
    ";
}

function dash_cliente_nombre($row)
{
    if (!$row) {
        return '';
    }

    if (isset($row['tipo_cliente']) && $row['tipo_cliente'] === 'Empresa') {
        return trim((string)$row['razon_social']) !== '' ? $row['razon_social'] : $row['numero_documento'];
    }

    $nombre = trim((string)$row['nombres'] . ' ' . (string)$row['apellidos']);

    return $nombre !== '' ? $nombre : $row['numero_documento'];
}

function dash_resumen()
{
    $ingresos_hoy = dash_scalar("
        SELECT COALESCE(SUM(total_pagado), 0)
        FROM ecc_recibos
        WHERE estado = 'Emitido'
          AND DATE(fecha_pago) = CURDATE()
    ");

    $ingresos_mes = dash_scalar("
        SELECT COALESCE(SUM(total_pagado), 0)
        FROM ecc_recibos
        WHERE estado = 'Emitido'
          AND YEAR(fecha_pago) = YEAR(CURDATE())
          AND MONTH(fecha_pago) = MONTH(CURDATE())
    ");

    $pendientes = dash_scalar("
        SELECT COALESCE(SUM(GREATEST(p.total - COALESCE(r.pagado, 0), 0)), 0)
        FROM ecc_proformas p
        LEFT JOIN (
            SELECT proforma_id, SUM(total_pagado) AS pagado
            FROM ecc_recibos
            WHERE estado = 'Emitido'
              AND proforma_id IS NOT NULL
            GROUP BY proforma_id
        ) r ON r.proforma_id = p.id
        WHERE p.estado IN ('Emitida','Parcial')
    ");

    $proformas_emitidas = dash_scalar("
        SELECT COUNT(*)
        FROM ecc_proformas
        WHERE estado IN ('Emitida','Parcial','Convertida')
          AND YEAR(fecha_emision) = YEAR(CURDATE())
          AND MONTH(fecha_emision) = MONTH(CURDATE())
    ");

    $recibos_emitidos = dash_scalar("
        SELECT COUNT(*)
        FROM ecc_recibos
        WHERE estado = 'Emitido'
          AND YEAR(fecha_pago) = YEAR(CURDATE())
          AND MONTH(fecha_pago) = MONTH(CURDATE())
    ");

    $vencidos = dash_scalar("
        SELECT COUNT(*)
        FROM ecc_cliente_servicios
        WHERE estado IN ('Pendiente','En proforma')
          AND fecha_vencimiento IS NOT NULL
          AND fecha_vencimiento < CURDATE()
    ");

    $aviso_sql = dash_aviso_programado_sql();

    $proximos_avisos = dash_scalar("
        SELECT COUNT(*)
        FROM (
            SELECT {$aviso_sql} AS aviso_programado
            FROM ecc_cliente_servicios cs
            WHERE cs.estado IN ('Pendiente','En proforma')
              AND cs.modo_aviso <> 'Sin aviso'
        ) x
        WHERE x.aviso_programado IS NOT NULL
          AND x.aviso_programado >= NOW()
          AND x.aviso_programado <= DATE_ADD(NOW(), INTERVAL 7 DAY)
    ");

    return array(
        'ingresos_hoy' => (float)$ingresos_hoy,
        'ingresos_mes' => (float)$ingresos_mes,
        'pendientes' => (float)$pendientes,
        'proformas_emitidas' => (int)$proformas_emitidas,
        'recibos_emitidos' => (int)$recibos_emitidos,
        'proximos_avisos' => (int)$proximos_avisos,
        'vencidos' => (int)$vencidos
    );
}

function dash_grafico_ingresos()
{
    $pdo = app_pdo();

    $inicio = new DateTime('first day of this month');
    $inicio->modify('-5 months');
    $fin = new DateTime('first day of next month');

    $labels = array();
    $map = array();

    $cursor = clone $inicio;

    while ($cursor < $fin) {
        $key = $cursor->format('Y-m');
        $labels[] = $cursor->format('m/Y');
        $map[$key] = 0.00;
        $cursor->modify('+1 month');
    }

    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS periodo, COALESCE(SUM(total_pagado), 0) AS total
        FROM ecc_recibos
        WHERE estado = 'Emitido'
          AND fecha_pago >= :inicio
          AND fecha_pago < :fin
        GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
        ORDER BY periodo ASC
    ");

    $stmt->execute(array(
        ':inicio' => $inicio->format('Y-m-d'),
        ':fin' => $fin->format('Y-m-d')
    ));

    foreach ($stmt->fetchAll() as $row) {
        if (array_key_exists($row['periodo'], $map)) {
            $map[$row['periodo']] = (float)$row['total'];
        }
    }

    return array(
        'labels' => $labels,
        'data' => array_values($map)
    );
}

function dash_tiempo_relativo($datetime)
{
    if (!$datetime) {
        return 'Sin fecha';
    }

    $timestamp = strtotime($datetime);

    if (!$timestamp) {
        return 'Sin fecha';
    }

    $diff = $timestamp - time();
    $abs = abs($diff);

    if ($abs < 60) {
        $texto = $abs . ' min';
    } elseif ($abs < 3600) {
        $texto = floor($abs / 60) . ' min';
    } elseif ($abs < 86400) {
        $texto = floor($abs / 3600) . ' h';
    } else {
        $texto = floor($abs / 86400) . ' d';
    }

    return $diff < 0 ? 'Vencido hace ' . $texto : 'Falta ' . $texto;
}

function dash_proximos_avisos($limit = 8)
{
    $pdo = app_pdo();
    $aviso_sql = dash_aviso_programado_sql();

    $stmt = $pdo->prepare("
        SELECT
            cs.id,
            cs.periodo,
            cs.monto,
            cs.estado,
            cs.fecha_vencimiento,
            cs.fecha_aviso,
            cs.modo_aviso,
            cs.aviso_valor,
            {$aviso_sql} AS aviso_programado,
            s.nombre AS servicio_nombre,
            c.tipo_cliente,
            c.documento_tipo,
            c.numero_documento,
            c.razon_social,
            c.nombres,
            c.apellidos
        FROM ecc_cliente_servicios cs
        INNER JOIN ecc_servicios s ON s.id = cs.servicio_id
        INNER JOIN ecc_clientes c ON c.id = cs.cliente_id
        WHERE cs.estado IN ('Pendiente','En proforma')
          AND cs.modo_aviso <> 'Sin aviso'
        HAVING aviso_programado IS NOT NULL
           AND aviso_programado >= NOW()
           AND aviso_programado <= DATE_ADD(NOW(), INTERVAL 7 DAY)
        ORDER BY aviso_programado ASC
        LIMIT " . (int)$limit
    );

    $stmt->execute();

    return $stmt->fetchAll();
}

function dash_servicios_vencidos($limit = 8)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT
            cs.id,
            cs.periodo,
            cs.monto,
            cs.estado,
            cs.fecha_vencimiento,
            s.nombre AS servicio_nombre,
            c.tipo_cliente,
            c.documento_tipo,
            c.numero_documento,
            c.razon_social,
            c.nombres,
            c.apellidos
        FROM ecc_cliente_servicios cs
        INNER JOIN ecc_servicios s ON s.id = cs.servicio_id
        INNER JOIN ecc_clientes c ON c.id = cs.cliente_id
        WHERE cs.estado IN ('Pendiente','En proforma')
          AND cs.fecha_vencimiento IS NOT NULL
          AND cs.fecha_vencimiento < CURDATE()
        ORDER BY cs.fecha_vencimiento ASC
        LIMIT " . (int)$limit
    );

    $stmt->execute();

    return $stmt->fetchAll();
}

function dash_auditoria_reciente($limit = 8)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT *
        FROM ecc_auditoria
        ORDER BY id DESC
        LIMIT " . (int)$limit
    );

    $stmt->execute();

    return $stmt->fetchAll();
}

function dash_render_avisos($avisos)
{
    if (empty($avisos)) {
        return '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-bell-slash"></i></div><h5>Sin avisos próximos</h5><p>No hay avisos programados para los próximos 7 días.</p></div>';
    }

    ob_start();
    ?>
    <div class="dash-list">
        <?php foreach ($avisos as $aviso) { ?>
            <div class="dash-list-item">
                <div>
                    <strong><?php echo e($aviso['servicio_nombre']); ?></strong>
                    <small><?php echo e(dash_cliente_nombre($aviso)); ?> | <?php echo e($aviso['documento_tipo']); ?> <?php echo e($aviso['numero_documento']); ?></small>
                    <small>
                        <?php echo e($aviso['modo_aviso']); ?>
                        <?php if ($aviso['aviso_valor'] !== null) { ?>
                            | <?php echo e($aviso['aviso_valor']); ?>
                        <?php } ?>
                    </small>
                </div>
                <div class="text-right">
                    <span class="badge badge-info"><?php echo e(dash_tiempo_relativo($aviso['aviso_programado'])); ?></span>
                    <small><?php echo e(date('d/m/Y H:i', strtotime($aviso['aviso_programado']))); ?></small>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function dash_render_vencidos($vencidos)
{
    if (empty($vencidos)) {
        return '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-check-circle"></i></div><h5>Sin vencidos</h5><p>No hay servicios vencidos pendientes de cobro.</p></div>';
    }

    ob_start();
    ?>
    <div class="dash-list">
        <?php foreach ($vencidos as $item) { ?>
            <div class="dash-list-item dash-list-item-danger">
                <div>
                    <strong><?php echo e($item['servicio_nombre']); ?></strong>
                    <small><?php echo e(dash_cliente_nombre($item)); ?> | <?php echo e($item['documento_tipo']); ?> <?php echo e($item['numero_documento']); ?></small>
                    <small>Periodo: <?php echo trim((string)$item['periodo']) !== '' ? e($item['periodo']) : 'Sin periodo'; ?></small>
                </div>
                <div class="text-right">
                    <span class="badge badge-danger">Vencido</span>
                    <small><?php echo e(date('d/m/Y', strtotime($item['fecha_vencimiento']))); ?></small>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}

function dash_render_auditoria($items)
{
    if (empty($items)) {
        return '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-shield-alt"></i></div><h5>Sin auditoría</h5><p>Aún no hay registros de auditoría.</p></div>';
    }

    ob_start();
    ?>
    <div class="timeline timeline-sm">
        <?php foreach ($items as $item) { ?>
            <div>
                <i class="fas fa-history bg-primary"></i>
                <div class="timeline-item">
                    <span class="time"><i class="far fa-clock"></i> <?php echo e(date('d/m/Y H:i', strtotime($item['created_at']))); ?></span>
                    <h3 class="timeline-header">
                        <strong><?php echo e($item['accion']); ?></strong>
                        <span class="text-muted"> | <?php echo e($item['modulo']); ?></span>
                    </h3>
                    <div class="timeline-body">
                        <?php echo e($item['descripcion']); ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
    return ob_get_clean();
}