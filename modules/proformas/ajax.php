<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function pf_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

function pf_clean($value)
{
    return trim((string)$value);
}

function pf_money($value)
{
    $value = str_replace(',', '.', (string)$value);
    return round((float)$value, 2);
}

function pf_enum($value, $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function pf_json($data, $status = 200)
{
    app_json_response($data, $status);
}

function pf_action_servicios_cliente()
{
    $cliente_id = (int)pf_request('cliente_id', 0);
    $proforma_id = (int)pf_request('proforma_id', 0);

    if ($cliente_id <= 0) {
        pf_json(array(
            'ok' => true,
            'html' => '<div class="app-empty-state"><div class="app-empty-state-icon"><i class="fas fa-user"></i></div><h5>Seleccione un cliente</h5><p>Los servicios pendientes aparecerán aquí.</p></div>'
        ));
    }

    pf_json(array(
        'ok' => true,
        'html' => pf_render_servicios_cliente($cliente_id, $proforma_id)
    ));
}

function pf_action_obtener()
{
    $id = (int)pf_request('id', 0);
    $proforma = pf_obtener($id);

    if (!$proforma) {
        pf_json(array(
            'ok' => false,
            'message' => 'Proforma no encontrada.'
        ), 404);
    }

    $detalles = pf_obtener_detalles($id);

    pf_json(array(
        'ok' => true,
        'proforma' => $proforma,
        'detalles' => $detalles
    ));
}

function pf_normalizar_items($items_raw)
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

        $tipo_item = pf_enum(isset($item['tipo_item']) ? $item['tipo_item'] : '', array('Servicio', 'Manual'), 'Manual');
        $bloque = pf_enum(isset($item['bloque']) ? $item['bloque'] : '', array('Actuales', 'Pendientes de pago', 'Otros servicios o trámites'), 'Actuales');
        $descripcion = pf_clean(isset($item['descripcion']) ? $item['descripcion'] : '');
        $cantidad = pf_money(isset($item['cantidad']) ? $item['cantidad'] : 1);
        $precio_unitario = pf_money(isset($item['precio_unitario']) ? $item['precio_unitario'] : 0);
        $cliente_servicio_id = isset($item['cliente_servicio_id']) ? (int)$item['cliente_servicio_id'] : 0;

        if ($descripcion === '') {
            continue;
        }

        if ($cantidad <= 0) {
            $cantidad = 1;
        }

        if ($precio_unitario < 0) {
            $precio_unitario = 0;
        }

        if ($tipo_item === 'Manual') {
            $cliente_servicio_id = 0;
        }

        $total = round($cantidad * $precio_unitario, 2);

        $normalizados[] = array(
            'tipo_item' => $tipo_item,
            'cliente_servicio_id' => $cliente_servicio_id > 0 ? $cliente_servicio_id : null,
            'bloque' => $bloque,
            'descripcion' => $descripcion,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_unitario,
            'total' => $total,
            'orden' => $orden
        );

        $orden++;
    }

    return $normalizados;
}

function pf_validar_servicios_items($cliente_id, $items)
{
    $pdo = app_pdo();

    foreach ($items as $item) {
        if ($item['tipo_item'] !== 'Servicio') {
            continue;
        }

        if ((int)$item['cliente_servicio_id'] <= 0) {
            pf_json(array(
                'ok' => false,
                'message' => 'Hay un servicio seleccionado inválido.'
            ), 422);
        }

        $stmt = $pdo->prepare("
            SELECT id
            FROM ecc_cliente_servicios
            WHERE id = :id
              AND cliente_id = :cliente_id
              AND estado NOT IN ('Pagado','Anulado')
            LIMIT 1
        ");

        $stmt->execute(array(
            ':id' => (int)$item['cliente_servicio_id'],
            ':cliente_id' => (int)$cliente_id
        ));

        if (!$stmt->fetch()) {
            pf_json(array(
                'ok' => false,
                'message' => 'Uno de los servicios seleccionados no pertenece al cliente o no puede proformarse.'
            ), 422);
        }
    }
}

function pf_action_guardar()
{
    app_require_post();

    $pdo = app_pdo();

    $id = (int)pf_request('id', 0);
    $cliente_id = (int)pf_request('cliente_id', 0);
    $plantilla_id = (int)pf_request('plantilla_id', 0);
    $fecha_emision = pf_clean(pf_request('fecha_emision'));
    $fecha_vencimiento = pf_clean(pf_request('fecha_vencimiento'));
    $observacion = pf_clean(pf_request('observacion'));
    $descuento = pf_money(pf_request('descuento', 0));
    $items = pf_normalizar_items(pf_request('items_json', '[]'));

    if ($cliente_id <= 0) {
        pf_json(array(
            'ok' => false,
            'message' => 'Seleccione un cliente.'
        ), 422);
    }

    if ($plantilla_id <= 0) {
        pf_json(array(
            'ok' => false,
            'message' => 'Seleccione una plantilla.'
        ), 422);
    }

    if ($fecha_emision === '') {
        pf_json(array(
            'ok' => false,
            'message' => 'Ingrese la fecha de emisión.'
        ), 422);
    }

    if (empty($items)) {
        pf_json(array(
            'ok' => false,
            'message' => 'Agregue al menos un ítem a la proforma.'
        ), 422);
    }

    if (!pf_obtener_cliente($cliente_id)) {
        pf_json(array(
            'ok' => false,
            'message' => 'Cliente no encontrado.'
        ), 404);
    }

    if (!pf_obtener_plantilla($plantilla_id)) {
        pf_json(array(
            'ok' => false,
            'message' => 'Plantilla no encontrada.'
        ), 404);
    }

    pf_validar_servicios_items($cliente_id, $items);

    $subtotal = 0;

    foreach ($items as $item) {
        $subtotal += (float)$item['total'];
    }

    if ($descuento < 0) {
        $descuento = 0;
    }

    if ($descuento > $subtotal) {
        $descuento = $subtotal;
    }

    $total = round($subtotal - $descuento, 2);

    try {
        $pdo->beginTransaction();

        $servicios_anteriores = array();
        $anterior = null;

        if ($id > 0) {
            $anterior = pf_obtener($id);

            if (!$anterior) {
                $pdo->rollBack();
                pf_json(array(
                    'ok' => false,
                    'message' => 'Proforma no encontrada.'
                ), 404);
            }

            foreach (pf_obtener_detalles($id) as $detalle_anterior) {
                if ((int)$detalle_anterior['cliente_servicio_id'] > 0) {
                    $servicios_anteriores[] = (int)$detalle_anterior['cliente_servicio_id'];
                }
            }

            $stmt = $pdo->prepare("
                UPDATE ecc_proformas
                SET
                    cliente_id = :cliente_id,
                    plantilla_id = :plantilla_id,
                    fecha_emision = :fecha_emision,
                    fecha_vencimiento = :fecha_vencimiento,
                    subtotal = :subtotal,
                    descuento = :descuento,
                    total = :total,
                    estado = :estado,
                    observacion = :observacion,
                    updated_by_external_id = :updated_by_external_id
                WHERE id = :id
            ");

            $stmt->execute(array(
                ':cliente_id' => $cliente_id,
                ':plantilla_id' => $plantilla_id,
                ':fecha_emision' => $fecha_emision,
                ':fecha_vencimiento' => $fecha_vencimiento !== '' ? $fecha_vencimiento : null,
                ':subtotal' => $subtotal,
                ':descuento' => $descuento,
                ':total' => $total,
                ':estado' => 'Emitida',
                ':observacion' => $observacion !== '' ? $observacion : null,
                ':updated_by_external_id' => pf_external_id(),
                ':id' => $id
            ));

            $stmt = $pdo->prepare("DELETE FROM ecc_proforma_detalles WHERE proforma_id = :proforma_id");
            $stmt->execute(array(':proforma_id' => $id));

            $proforma_id = $id;
            $codigo = $anterior['codigo'];
        } else {
            $codigo = pf_generar_codigo();

            $stmt = $pdo->prepare("
                INSERT INTO ecc_proformas
                (codigo, cliente_id, plantilla_id, fecha_emision, fecha_vencimiento, subtotal, descuento, total, estado, observacion, created_by_external_id)
                VALUES
                (:codigo, :cliente_id, :plantilla_id, :fecha_emision, :fecha_vencimiento, :subtotal, :descuento, :total, 'Emitida', :observacion, :created_by_external_id)
            ");

            $stmt->execute(array(
                ':codigo' => $codigo,
                ':cliente_id' => $cliente_id,
                ':plantilla_id' => $plantilla_id,
                ':fecha_emision' => $fecha_emision,
                ':fecha_vencimiento' => $fecha_vencimiento !== '' ? $fecha_vencimiento : null,
                ':subtotal' => $subtotal,
                ':descuento' => $descuento,
                ':total' => $total,
                ':observacion' => $observacion !== '' ? $observacion : null,
                ':created_by_external_id' => pf_external_id()
            ));

            $proforma_id = (int)$pdo->lastInsertId();
        }

        $insert = $pdo->prepare("
            INSERT INTO ecc_proforma_detalles
            (proforma_id, cliente_servicio_id, tipo_item, bloque, descripcion, cantidad, precio_unitario, total, estado, orden)
            VALUES
            (:proforma_id, :cliente_servicio_id, :tipo_item, :bloque, :descripcion, :cantidad, :precio_unitario, :total, 'Pendiente', :orden)
        ");

        $servicios_actuales = array();

        foreach ($items as $item) {
            $insert->execute(array(
                ':proforma_id' => $proforma_id,
                ':cliente_servicio_id' => $item['cliente_servicio_id'],
                ':tipo_item' => $item['tipo_item'],
                ':bloque' => $item['bloque'],
                ':descripcion' => $item['descripcion'],
                ':cantidad' => $item['cantidad'],
                ':precio_unitario' => $item['precio_unitario'],
                ':total' => $item['total'],
                ':orden' => $item['orden']
            ));

            if ((int)$item['cliente_servicio_id'] > 0) {
                $servicios_actuales[] = (int)$item['cliente_servicio_id'];
            }
        }

        $servicios_revisar = array_unique(array_merge($servicios_anteriores, $servicios_actuales));

        foreach ($servicios_revisar as $cliente_servicio_id) {
            pf_recalcular_estado_servicio($cliente_servicio_id);
        }

        pf_auditoria(
            $id > 0 ? 'Actualizar proforma' : 'Crear proforma',
            'ecc_proformas',
            $proforma_id,
            $id > 0 ? 'Proforma actualizada.' : 'Proforma creada.',
            $anterior,
            pf_obtener($proforma_id)
        );

        $pdo->commit();

        pf_json(array(
            'ok' => true,
            'message' => 'Proforma guardada correctamente.',
            'id' => $proforma_id,
            'codigo' => $codigo,
            'html' => pf_render_tabla()
        ));
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

function pf_action_documento()
{
    $id = (int)pf_request('id', 0);

    pf_json(array(
        'ok' => true,
        'html' => pf_render_documento($id)
    ));
}

function pf_action_exportar()
{
    app_require_post();

    $id = (int)pf_request('id', 0);
    $tipo = pf_enum(pf_clean(pf_request('tipo')), array('jpg', 'pdf'), 'pdf');
    $proforma = pf_obtener($id);

    if (!$proforma) {
        pf_json(array(
            'ok' => false,
            'message' => 'Proforma no encontrada.'
        ), 404);
    }

    pf_auditoria(
        'Descargar proforma ' . strtoupper($tipo),
        'ecc_proformas',
        $id,
        'Se descargó la proforma ' . $proforma['codigo'] . ' en formato ' . strtoupper($tipo) . '.',
        null,
        array(
            'codigo' => $proforma['codigo'],
            'tipo' => $tipo
        )
    );

    pf_json(array(
        'ok' => true,
        'message' => 'Descarga registrada.'
    ));
}

try {
    $action = pf_clean(pf_request('action'));

    if ($action === 'servicios_cliente') {
        pf_action_servicios_cliente();
    }

    if ($action === 'obtener_proforma') {
        pf_action_obtener();
    }

    if ($action === 'guardar_proforma') {
        pf_action_guardar();
    }

    if ($action === 'documento_proforma') {
        pf_action_documento();
    }

    if ($action === 'exportar_proforma') {
        pf_action_exportar();
    }

    pf_json(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    pf_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del módulo.'
    ), 500);
}