<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function rb_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

function rb_clean($value)
{
    return trim((string)$value);
}

function rb_money($value)
{
    $value = str_replace(',', '.', (string)$value);
    return round((float)$value, 2);
}

function rb_enum($value, $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function rb_json($data, $status = 200)
{
    app_json_response($data, $status);
}

function rb_action_cargar_proforma()
{
    $proforma_id = (int)rb_request('proforma_id', 0);
    $proforma = rb_obtener_proforma($proforma_id);

    if (!$proforma) {
        rb_json(array(
            'ok' => false,
            'message' => 'Proforma no encontrada.'
        ), 404);
    }

    rb_json(array(
        'ok' => true,
        'proforma' => $proforma,
        'detalles_html' => rb_render_detalles_proforma($proforma_id),
        'servicios_html' => rb_render_servicios_adicionales($proforma['cliente_id'], $proforma_id)
    ));
}

function rb_action_servicios_cliente()
{
    $cliente_id = (int)rb_request('cliente_id', 0);
    $proforma_id = (int)rb_request('proforma_id', 0);

    if ($cliente_id <= 0) {
        rb_json(array(
            'ok' => true,
            'html' => '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-user"></i></div><h5>Seleccione cliente</h5><p>Los servicios adicionales disponibles aparecerán aquí.</p></div>'
        ));
    }

    rb_json(array(
        'ok' => true,
        'html' => rb_render_servicios_adicionales($cliente_id, $proforma_id)
    ));
}

function rb_normalizar_items($items_raw)
{
    $items = json_decode((string)$items_raw, true);

    if (!is_array($items)) {
        return array();
    }

    $normalizados = array();
    $orden = 1;

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $origen = rb_enum(isset($item['origen']) ? $item['origen'] : '', array('Proforma', 'Servicio adicional', 'Manual'), 'Manual');
        $proforma_detalle_id = isset($item['proforma_detalle_id']) ? (int)$item['proforma_detalle_id'] : 0;
        $cliente_servicio_id = isset($item['cliente_servicio_id']) ? (int)$item['cliente_servicio_id'] : 0;
        $bloque = rb_enum(isset($item['bloque']) ? $item['bloque'] : '', array('Actuales', 'Pendientes de pago', 'Otros servicios o trámites'), 'Actuales');
        $descripcion = rb_clean(isset($item['descripcion']) ? $item['descripcion'] : '');
        $monto_original = rb_money(isset($item['monto_original']) ? $item['monto_original'] : 0);
        $monto_pagado = rb_money(isset($item['monto_pagado']) ? $item['monto_pagado'] : 0);

        if ($descripcion === '') {
            continue;
        }

        if ($monto_original < 0) {
            $monto_original = 0;
        }

        if ($monto_pagado <= 0) {
            continue;
        }

        if ($monto_pagado > $monto_original && $monto_original > 0) {
            $monto_pagado = $monto_original;
        }

        if ($origen === 'Manual') {
            $proforma_detalle_id = 0;
            $cliente_servicio_id = 0;
            $monto_original = $monto_pagado;
        }

        $estado_resultante = ($monto_original > 0 && $monto_pagado >= $monto_original) ? 'Pagado' : 'Pendiente';

        $normalizados[] = array(
            'origen' => $origen,
            'proforma_detalle_id' => $proforma_detalle_id > 0 ? $proforma_detalle_id : null,
            'cliente_servicio_id' => $cliente_servicio_id > 0 ? $cliente_servicio_id : null,
            'bloque' => $bloque,
            'descripcion' => $descripcion,
            'monto_original' => $monto_original,
            'monto_pagado' => $monto_pagado,
            'estado_servicio_resultante' => $estado_resultante,
            'orden' => $orden
        );

        $orden++;
    }

    return $normalizados;
}

function rb_validar_items($cliente_id, $proforma_id, $items)
{
    $pdo = app_pdo();

    foreach ($items as $item) {
        if ($item['origen'] === 'Proforma') {
            if ((int)$item['proforma_detalle_id'] <= 0) {
                rb_json(array(
                    'ok' => false,
                    'message' => 'Hay un detalle de proforma inválido.'
                ), 422);
            }

            $stmt = $pdo->prepare("
                SELECT pd.id
                FROM ecc_proforma_detalles pd
                INNER JOIN ecc_proformas p ON p.id = pd.proforma_id
                WHERE pd.id = :id
                  AND p.id = :proforma_id
                  AND p.cliente_id = :cliente_id
                LIMIT 1
            ");

            $stmt->execute(array(
                ':id' => (int)$item['proforma_detalle_id'],
                ':proforma_id' => (int)$proforma_id,
                ':cliente_id' => (int)$cliente_id
            ));

            if (!$stmt->fetch()) {
                rb_json(array(
                    'ok' => false,
                    'message' => 'Uno de los detalles no pertenece a la proforma seleccionada.'
                ), 422);
            }
        }

        if ($item['origen'] === 'Servicio adicional') {
            if ((int)$item['cliente_servicio_id'] <= 0) {
                rb_json(array(
                    'ok' => false,
                    'message' => 'Hay un servicio adicional inválido.'
                ), 422);
            }

            $stmt = $pdo->prepare("
                SELECT id
                FROM ecc_cliente_servicios
                WHERE id = :id
                  AND cliente_id = :cliente_id
                  AND estado = 'Pendiente'
                LIMIT 1
            ");

            $stmt->execute(array(
                ':id' => (int)$item['cliente_servicio_id'],
                ':cliente_id' => (int)$cliente_id
            ));

            if (!$stmt->fetch()) {
                rb_json(array(
                    'ok' => false,
                    'message' => 'Uno de los servicios adicionales no pertenece al cliente o no está pendiente.'
                ), 422);
            }
        }
    }
}

function rb_action_guardar()
{
    app_require_post();

    $pdo = app_pdo();

    $manual_emergencia = (int)rb_request('manual_emergencia', 0) === 1;
    $proforma_id = $manual_emergencia ? 0 : (int)rb_request('proforma_id', 0);
    $cliente_id = (int)rb_request('cliente_id', 0);
    $plantilla_id = (int)rb_request('plantilla_id', 0);
    $metodo_pago_id = (int)rb_request('metodo_pago_id', 0);
    $fecha_emision = rb_clean(rb_request('fecha_emision'));
    $fecha_pago = rb_clean(rb_request('fecha_pago'));
    $observacion = rb_clean(rb_request('observacion'));
    $items = rb_normalizar_items(rb_request('items_json', '[]'));

    if (!$manual_emergencia && $proforma_id <= 0) {
        rb_json(array(
            'ok' => false,
            'message' => 'Seleccione una proforma.'
        ), 422);
    }

    if ($cliente_id <= 0) {
        rb_json(array(
            'ok' => false,
            'message' => 'Seleccione un cliente.'
        ), 422);
    }

    if ($plantilla_id <= 0) {
        rb_json(array(
            'ok' => false,
            'message' => 'Seleccione una plantilla.'
        ), 422);
    }

    if ($metodo_pago_id <= 0) {
        rb_json(array(
            'ok' => false,
            'message' => 'Seleccione el método de pago.'
        ), 422);
    }

    if ($fecha_emision === '' || $fecha_pago === '') {
        rb_json(array(
            'ok' => false,
            'message' => 'Ingrese las fechas del recibo.'
        ), 422);
    }

    if (empty($items)) {
        rb_json(array(
            'ok' => false,
            'message' => 'Agregue al menos un ítem pagado.'
        ), 422);
    }

    if (!rb_obtener_cliente($cliente_id)) {
        rb_json(array(
            'ok' => false,
            'message' => 'Cliente no encontrado.'
        ), 404);
    }

    if (!rb_obtener_plantilla($plantilla_id)) {
        rb_json(array(
            'ok' => false,
            'message' => 'Plantilla no encontrada.'
        ), 404);
    }

    if (!rb_obtener_metodo_pago($metodo_pago_id)) {
        rb_json(array(
            'ok' => false,
            'message' => 'Método de pago no encontrado.'
        ), 404);
    }

    $proforma = null;

    if (!$manual_emergencia) {
        $proforma = rb_obtener_proforma($proforma_id);

        if (!$proforma) {
            rb_json(array(
                'ok' => false,
                'message' => 'Proforma no encontrada.'
            ), 404);
        }

        if ((int)$proforma['cliente_id'] !== $cliente_id) {
            rb_json(array(
                'ok' => false,
                'message' => 'La proforma no pertenece al cliente seleccionado.'
            ), 422);
        }
    }

    rb_validar_items($cliente_id, $proforma_id, $items);

    $total_pagado = 0;

    foreach ($items as $item) {
        $total_pagado += (float)$item['monto_pagado'];
    }

    $total_proforma = $proforma ? (float)$proforma['total'] : $total_pagado;
    $pagado_proforma_antes = $proforma ? rb_suma_pagada_proforma($proforma_id) : 0.00;
    $pagado_proforma_actual = 0.00;

    foreach ($items as $item) {
        if ($item['origen'] === 'Proforma') {
            $pagado_proforma_actual += (float)$item['monto_pagado'];
        }
    }

    $saldo_pendiente = max($total_proforma - $pagado_proforma_antes - $pagado_proforma_actual, 0);

    try {
        $pdo->beginTransaction();

        $codigo = rb_generar_codigo();

        $stmt = $pdo->prepare("
            INSERT INTO ecc_recibos
            (codigo, proforma_id, cliente_id, plantilla_id, metodo_pago_id, fecha_emision, fecha_pago, total_proforma, total_pagado, saldo_pendiente, estado, observacion, created_by_external_id)
            VALUES
            (:codigo, :proforma_id, :cliente_id, :plantilla_id, :metodo_pago_id, :fecha_emision, :fecha_pago, :total_proforma, :total_pagado, :saldo_pendiente, 'Emitido', :observacion, :created_by_external_id)
        ");

        $stmt->execute(array(
            ':codigo' => $codigo,
            ':proforma_id' => $proforma ? $proforma_id : null,
            ':cliente_id' => $cliente_id,
            ':plantilla_id' => $plantilla_id,
            ':metodo_pago_id' => $metodo_pago_id,
            ':fecha_emision' => $fecha_emision,
            ':fecha_pago' => $fecha_pago,
            ':total_proforma' => $total_proforma,
            ':total_pagado' => $total_pagado,
            ':saldo_pendiente' => $saldo_pendiente,
            ':observacion' => $observacion !== '' ? $observacion : null,
            ':created_by_external_id' => rb_external_id()
        ));

        $recibo_id = (int)$pdo->lastInsertId();

        $insert = $pdo->prepare("
            INSERT INTO ecc_recibo_detalles
            (recibo_id, proforma_detalle_id, cliente_servicio_id, bloque, descripcion, monto_original, monto_pagado, estado_servicio_resultante, orden)
            VALUES
            (:recibo_id, :proforma_detalle_id, :cliente_servicio_id, :bloque, :descripcion, :monto_original, :monto_pagado, :estado_servicio_resultante, :orden)
        ");

        foreach ($items as $item) {
            $insert->execute(array(
                ':recibo_id' => $recibo_id,
                ':proforma_detalle_id' => $item['proforma_detalle_id'],
                ':cliente_servicio_id' => $item['cliente_servicio_id'],
                ':bloque' => $item['bloque'],
                ':descripcion' => $item['descripcion'],
                ':monto_original' => $item['monto_original'],
                ':monto_pagado' => $item['monto_pagado'],
                ':estado_servicio_resultante' => $item['estado_servicio_resultante'],
                ':orden' => $item['orden']
            ));

            if ($item['origen'] === 'Servicio adicional' && (int)$item['cliente_servicio_id'] > 0) {
                rb_actualizar_estado_servicio_adicional($item['cliente_servicio_id'], $item['monto_original'], $item['monto_pagado']);
            }
        }

        if ($proforma) {
            rb_actualizar_estados_proforma($proforma_id);
        }

        rb_auditoria('Generar recibo', 'ecc_recibos', $recibo_id, 'Recibo generado.', null, rb_obtener($recibo_id));

        $pdo->commit();

        rb_json(array(
            'ok' => true,
            'message' => 'Recibo generado correctamente.',
            'id' => $recibo_id,
            'codigo' => $codigo,
            'html' => rb_render_tabla()
        ));
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

function rb_action_documento()
{
    $id = (int)rb_request('id', 0);

    rb_json(array(
        'ok' => true,
        'html' => rb_render_documento($id)
    ));
}

function rb_action_exportar()
{
    $tipo = rb_enum(rb_clean(rb_request('tipo')), array('jpg', 'pdf'), 'pdf');

    rb_json(array(
        'ok' => false,
        'message' => 'La exportación ' . strtoupper($tipo) . ' local se implementará en Fase 10. El documento ya puede verse desde Ver documento.'
    ), 501);
}

try {
    $action = rb_clean(rb_request('action'));

    if ($action === 'cargar_proforma') {
        rb_action_cargar_proforma();
    }

    if ($action === 'servicios_cliente') {
        rb_action_servicios_cliente();
    }

    if ($action === 'guardar_recibo') {
        rb_action_guardar();
    }

    if ($action === 'documento_recibo') {
        rb_action_documento();
    }

    if ($action === 'exportar_recibo') {
        rb_action_exportar();
    }

    rb_json(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    rb_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del módulo.'
    ), 500);
}