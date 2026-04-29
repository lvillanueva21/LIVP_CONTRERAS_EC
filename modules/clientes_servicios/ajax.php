<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function cs_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

function cs_json($data, $status_code = 200)
{
    app_json_response($data, $status_code);
}

function cs_clean_string($value)
{
    return trim((string)$value);
}

function cs_clean_money($value)
{
    $value = str_replace(',', '.', (string)$value);
    return round((float)$value, 2);
}

function cs_clean_int_or_null($value)
{
    $value = trim((string)$value);

    if ($value === '') {
        return null;
    }

    $int = (int)$value;

    return $int >= 0 ? $int : null;
}

function cs_clean_datetime_or_null($value)
{
    $value = trim((string)$value);

    if ($value === '') {
        return null;
    }

    $value = str_replace('T', ' ', $value);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        $value .= ' 00:00:00';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        $value .= ':00';
    }

    return $value;
}

function cs_validate_enum($value, $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function cs_action_listar_clientes()
{
    cs_json(array(
        'ok' => true,
        'html' => cs_render_clientes_table()
    ));
}

function cs_action_obtener_cliente()
{
    $id = (int)cs_request('id', 0);
    $cliente = cs_obtener_cliente($id);

    if (!$cliente) {
        cs_json(array(
            'ok' => false,
            'message' => 'Cliente no encontrado.'
        ), 404);
    }

    cs_json(array(
        'ok' => true,
        'cliente' => $cliente
    ));
}

function cs_action_guardar_cliente()
{
    $pdo = app_pdo();

    $id = (int)cs_request('id', 0);
    $tipo_cliente = cs_validate_enum(cs_clean_string(cs_request('tipo_cliente')), array('Empresa', 'Persona natural'), 'Empresa');
    $documento_tipo = cs_validate_enum(cs_clean_string(cs_request('documento_tipo')), array('RUC', 'DNI'), 'RUC');
    $numero_documento = cs_clean_string(cs_request('numero_documento'));
    $razon_social = cs_clean_string(cs_request('razon_social'));
    $nombre_comercial = cs_clean_string(cs_request('nombre_comercial'));
    $nombres = cs_clean_string(cs_request('nombres'));
    $apellidos = cs_clean_string(cs_request('apellidos'));
    $direccion = cs_clean_string(cs_request('direccion'));
    $correo = cs_clean_string(cs_request('correo'));
    $celular = cs_clean_string(cs_request('celular'));
    $observacion = cs_clean_string(cs_request('observacion'));
    $estado = (int)cs_request('estado', 1) === 1 ? 1 : 0;

    if ($numero_documento === '') {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese el número de documento.'
        ), 422);
    }

    if ($tipo_cliente === 'Empresa') {
        $documento_tipo = 'RUC';

        if ($razon_social === '') {
            cs_json(array(
                'ok' => false,
                'message' => 'Ingrese la razón social.'
            ), 422);
        }

        if (!preg_match('/^\\d{11}$/', $numero_documento)) {
            cs_json(array(
                'ok' => false,
                'message' => 'El RUC debe tener 11 dígitos numéricos.'
            ), 422);
        }
    }

    if ($tipo_cliente === 'Persona natural') {
        $documento_tipo = 'DNI';

        if ($nombres === '' || $apellidos === '') {
            cs_json(array(
                'ok' => false,
                'message' => 'Ingrese nombres y apellidos.'
            ), 422);
        }

        if (!preg_match('/^\\d{8}$/', $numero_documento)) {
            cs_json(array(
                'ok' => false,
                'message' => 'El DNI debe tener 8 dígitos numéricos.'
            ), 422);
        }
    }

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese un correo válido.'
        ), 422);
    }

    try {
        if ($id > 0) {
            $anterior = cs_obtener_cliente($id);

            if (!$anterior) {
                cs_json(array(
                    'ok' => false,
                    'message' => 'Cliente no encontrado.'
                ), 404);
            }

            $stmt = $pdo->prepare("
                UPDATE ecc_clientes
                SET
                    tipo_cliente = :tipo_cliente,
                    documento_tipo = :documento_tipo,
                    numero_documento = :numero_documento,
                    razon_social = :razon_social,
                    nombre_comercial = :nombre_comercial,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    direccion = :direccion,
                    correo = :correo,
                    celular = :celular,
                    observacion = :observacion,
                    estado = :estado,
                    updated_by_external_id = :updated_by_external_id
                WHERE id = :id
            ");

            $stmt->execute(array(
                ':tipo_cliente' => $tipo_cliente,
                ':documento_tipo' => $documento_tipo,
                ':numero_documento' => $numero_documento,
                ':razon_social' => $razon_social !== '' ? $razon_social : null,
                ':nombre_comercial' => $nombre_comercial !== '' ? $nombre_comercial : null,
                ':nombres' => $nombres !== '' ? $nombres : null,
                ':apellidos' => $apellidos !== '' ? $apellidos : null,
                ':direccion' => $direccion !== '' ? $direccion : null,
                ':correo' => $correo !== '' ? $correo : null,
                ':celular' => $celular !== '' ? $celular : null,
                ':observacion' => $observacion !== '' ? $observacion : null,
                ':estado' => $estado,
                ':updated_by_external_id' => cs_external_id(),
                ':id' => $id
            ));

            cs_auditoria('Actualizar cliente', 'ecc_clientes', $id, 'Cliente actualizado.', $anterior, cs_obtener_cliente($id));

            cs_json(array(
                'ok' => true,
                'message' => 'Cliente actualizado correctamente.',
                'html' => cs_render_clientes_table()
            ));
        }

        $stmt = $pdo->prepare("
            INSERT INTO ecc_clientes
            (tipo_cliente, documento_tipo, numero_documento, razon_social, nombre_comercial, nombres, apellidos, direccion, correo, celular, observacion, estado, created_by_external_id)
            VALUES
            (:tipo_cliente, :documento_tipo, :numero_documento, :razon_social, :nombre_comercial, :nombres, :apellidos, :direccion, :correo, :celular, :observacion, :estado, :created_by_external_id)
        ");

        $stmt->execute(array(
            ':tipo_cliente' => $tipo_cliente,
            ':documento_tipo' => $documento_tipo,
            ':numero_documento' => $numero_documento,
            ':razon_social' => $razon_social !== '' ? $razon_social : null,
            ':nombre_comercial' => $nombre_comercial !== '' ? $nombre_comercial : null,
            ':nombres' => $nombres !== '' ? $nombres : null,
            ':apellidos' => $apellidos !== '' ? $apellidos : null,
            ':direccion' => $direccion !== '' ? $direccion : null,
            ':correo' => $correo !== '' ? $correo : null,
            ':celular' => $celular !== '' ? $celular : null,
            ':observacion' => $observacion !== '' ? $observacion : null,
            ':estado' => $estado,
            ':created_by_external_id' => cs_external_id()
        ));

        $nuevo_id = (int)$pdo->lastInsertId();
        cs_auditoria('Crear cliente', 'ecc_clientes', $nuevo_id, 'Cliente creado.', null, cs_obtener_cliente($nuevo_id));

        cs_json(array(
            'ok' => true,
            'message' => 'Cliente creado correctamente.',
            'html' => cs_render_clientes_table()
        ));
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            cs_json(array(
                'ok' => false,
                'message' => 'Ya existe un cliente con ese documento.'
            ), 409);
        }

        throw $e;
    }
}

function cs_action_desactivar_cliente()
{
    $pdo = app_pdo();

    $id = (int)cs_request('id', 0);
    $cliente = cs_obtener_cliente($id);

    if (!$cliente) {
        cs_json(array(
            'ok' => false,
            'message' => 'Cliente no encontrado.'
        ), 404);
    }

    $stmt = $pdo->prepare("
        UPDATE ecc_clientes
        SET estado = 0, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':updated_by_external_id' => cs_external_id(),
        ':id' => $id
    ));

    cs_auditoria('Desactivar cliente', 'ecc_clientes', $id, 'Cliente desactivado.', $cliente, cs_obtener_cliente($id));

    cs_json(array(
        'ok' => true,
        'message' => 'Cliente desactivado correctamente.',
        'html' => cs_render_clientes_table()
    ));
}

function cs_action_detalle_cliente()
{
    $id = (int)cs_request('id', 0);

    cs_json(array(
        'ok' => true,
        'html' => cs_render_cliente_detalle($id)
    ));
}

function cs_action_timeline_cliente()
{
    $cliente_id = (int)cs_request('cliente_id', cs_request('id', 0));
    $cliente = cs_obtener_cliente($cliente_id);

    if (!$cliente) {
        cs_json(array(
            'ok' => false,
            'message' => 'Cliente no encontrado.'
        ), 404);
    }

    cs_json(array(
        'ok' => true,
        'html' => cs_render_timeline_cliente($cliente_id),
        'resumen' => cs_resumen_financiero_cliente($cliente_id)
    ));
}

function cs_action_obtener_servicio_cliente()
{
    $id = (int)cs_request('id', 0);
    $servicio = cs_obtener_servicio_asignado($id);

    if (!$servicio) {
        cs_json(array(
            'ok' => false,
            'message' => 'Servicio asignado no encontrado.'
        ), 404);
    }

    $servicio['etiquetas_ids'] = cs_etiquetas_servicio_ids($servicio['servicio_id']);

    cs_json(array(
        'ok' => true,
        'servicio' => $servicio
    ));
}

function cs_action_guardar_servicio_cliente()
{
    $pdo = app_pdo();

    $id = (int)cs_request('id', 0);
    $cliente_id = (int)cs_request('cliente_id', 0);
    $servicio_id = (int)cs_request('servicio_id', 0);
    $descripcion = cs_clean_string(cs_request('descripcion_personalizada'));
    $periodo = cs_clean_string(cs_request('periodo'));
    $monto = cs_clean_money(cs_request('monto', 0));
    $bloque_documento = 'Actuales';
    $estado = cs_validate_enum(cs_clean_string(cs_request('estado')), array('Pendiente', 'En proforma', 'Pagado', 'Anulado'), 'Pendiente');
    $fecha_vencimiento = cs_clean_string(cs_request('fecha_vencimiento'));
    $fecha_aviso = cs_clean_datetime_or_null(cs_request('fecha_aviso'));
    $modo_aviso = cs_validate_enum(
        cs_clean_string(cs_request('modo_aviso')),
        array('Sin aviso', 'Fecha exacta', 'Faltando X días', 'Faltando X horas', 'Faltando X minutos', 'Antes de vencer', 'Manual'),
        'Sin aviso'
    );
    $aviso_valor = cs_clean_int_or_null(cs_request('aviso_valor'));
    $etiquetas = isset($_POST['etiquetas']) && is_array($_POST['etiquetas']) ? $_POST['etiquetas'] : array();

    if ($cliente_id <= 0) {
        cs_json(array(
            'ok' => false,
            'message' => 'Cliente inválido.'
        ), 422);
    }

    if ($servicio_id <= 0) {
        cs_json(array(
            'ok' => false,
            'message' => 'Seleccione un servicio.'
        ), 422);
    }

    if ($monto <= 0) {
        cs_json(array(
            'ok' => false,
            'message' => 'El monto debe ser mayor a cero.'
        ), 422);
    }

    if ($modo_aviso === 'Fecha exacta' && $fecha_aviso === null) {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese la fecha y hora exacta de aviso.'
        ), 422);
    }

    if (in_array($modo_aviso, array('Faltando X días', 'Faltando X horas', 'Faltando X minutos'), true)) {
        if ($fecha_vencimiento === '') {
            cs_json(array(
                'ok' => false,
                'message' => 'Ingrese la fecha de vencimiento para calcular el aviso.'
            ), 422);
        }

        if ($aviso_valor === null || $aviso_valor <= 0) {
            cs_json(array(
                'ok' => false,
                'message' => 'Ingrese el valor del aviso.'
            ), 422);
        }
    }

    if ($modo_aviso === 'Antes de vencer' && $fecha_vencimiento === '') {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese la fecha de vencimiento.'
        ), 422);
    }

    if ($modo_aviso === 'Manual' && $fecha_aviso === null) {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese la fecha y hora manual del aviso.'
        ), 422);
    }

    if ($modo_aviso === 'Sin aviso') {
        $fecha_aviso = null;
        $aviso_valor = null;
    }

    if (!cs_obtener_cliente($cliente_id)) {
        cs_json(array(
            'ok' => false,
            'message' => 'Cliente no encontrado.'
        ), 404);
    }

    if ($id > 0) {
        $anterior = cs_obtener_servicio_asignado($id);

        if (!$anterior) {
            cs_json(array(
                'ok' => false,
                'message' => 'Servicio asignado no encontrado.'
            ), 404);
        }

        $stmt = $pdo->prepare("
            UPDATE ecc_cliente_servicios
            SET
                servicio_id = :servicio_id,
                descripcion_personalizada = :descripcion_personalizada,
                periodo = :periodo,
                monto = :monto,
                bloque_documento = :bloque_documento,
                estado = :estado,
                fecha_vencimiento = :fecha_vencimiento,
                fecha_aviso = :fecha_aviso,
                modo_aviso = :modo_aviso,
                aviso_valor = :aviso_valor,
                updated_by_external_id = :updated_by_external_id
            WHERE id = :id AND cliente_id = :cliente_id
        ");

        $stmt->execute(array(
            ':servicio_id' => $servicio_id,
            ':descripcion_personalizada' => $descripcion !== '' ? $descripcion : null,
            ':periodo' => $periodo !== '' ? $periodo : null,
            ':monto' => $monto,
            ':bloque_documento' => $bloque_documento,
            ':estado' => $estado,
            ':fecha_vencimiento' => $fecha_vencimiento !== '' ? $fecha_vencimiento : null,
            ':fecha_aviso' => $fecha_aviso,
            ':modo_aviso' => $modo_aviso,
            ':aviso_valor' => $aviso_valor,
            ':updated_by_external_id' => cs_external_id(),
            ':id' => $id,
            ':cliente_id' => $cliente_id
        ));

        cs_sync_servicio_etiquetas($servicio_id, $etiquetas);
        cs_auditoria('Actualizar servicio asignado', 'ecc_cliente_servicios', $id, 'Servicio asignado actualizado.', $anterior, cs_obtener_servicio_asignado($id));

        cs_json(array(
            'ok' => true,
            'message' => 'Servicio actualizado correctamente.',
            'detalle_html' => cs_render_cliente_detalle($cliente_id),
            'clientes_html' => cs_render_clientes_table()
        ));
    }

    $stmt = $pdo->prepare("
        INSERT INTO ecc_cliente_servicios
        (cliente_id, servicio_id, descripcion_personalizada, periodo, monto, bloque_documento, estado, fecha_asignacion, fecha_vencimiento, fecha_aviso, modo_aviso, aviso_valor, created_by_external_id)
        VALUES
        (:cliente_id, :servicio_id, :descripcion_personalizada, :periodo, :monto, :bloque_documento, :estado, CURDATE(), :fecha_vencimiento, :fecha_aviso, :modo_aviso, :aviso_valor, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':cliente_id' => $cliente_id,
        ':servicio_id' => $servicio_id,
        ':descripcion_personalizada' => $descripcion !== '' ? $descripcion : null,
        ':periodo' => $periodo !== '' ? $periodo : null,
        ':monto' => $monto,
        ':bloque_documento' => $bloque_documento,
        ':estado' => $estado,
        ':fecha_vencimiento' => $fecha_vencimiento !== '' ? $fecha_vencimiento : null,
        ':fecha_aviso' => $fecha_aviso,
        ':modo_aviso' => $modo_aviso,
        ':aviso_valor' => $aviso_valor,
        ':created_by_external_id' => cs_external_id()
    ));

    $nuevo_id = (int)$pdo->lastInsertId();

    cs_sync_servicio_etiquetas($servicio_id, $etiquetas);
    cs_auditoria('Asignar servicio', 'ecc_cliente_servicios', $nuevo_id, 'Servicio asignado al cliente.', null, cs_obtener_servicio_asignado($nuevo_id));

    cs_json(array(
        'ok' => true,
        'message' => 'Servicio asignado correctamente.',
        'detalle_html' => cs_render_cliente_detalle($cliente_id),
        'clientes_html' => cs_render_clientes_table()
    ));
}

function cs_action_obtener_servicio_base()
{
    $id = (int)cs_request('id', 0);
    $servicio = cs_obtener_servicio_general($id);

    if (!$servicio) {
        cs_json(array(
            'ok' => false,
            'message' => 'Servicio base no encontrado.'
        ), 404);
    }

    $servicio['etiquetas_ids'] = cs_etiquetas_servicio_ids($id);

    cs_json(array(
        'ok' => true,
        'servicio' => $servicio
    ));
}

function cs_action_guardar_servicio_base()
{
    $pdo = app_pdo();

    $id = (int)cs_request('id', 0);
    $nombre = cs_clean_string(cs_request('nombre'));
    $descripcion = cs_clean_string(cs_request('descripcion'));
    $precio_base = cs_clean_money(cs_request('precio_base', 0));
    $estado = (int)cs_request('estado', 1) === 1 ? 1 : 0;
    $etiquetas = isset($_POST['etiquetas']) && is_array($_POST['etiquetas']) ? $_POST['etiquetas'] : array();

    if ($nombre === '') {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese el nombre del servicio base.'
        ), 422);
    }

    if ($precio_base < 0) {
        cs_json(array(
            'ok' => false,
            'message' => 'El precio base debe ser mayor o igual a cero.'
        ), 422);
    }

    $params = array(':nombre' => $nombre);
    $sql_duplicado = "SELECT id FROM ecc_servicios WHERE nombre = :nombre";

    if ($id > 0) {
        $sql_duplicado .= " AND id <> :id";
        $params[':id'] = $id;
    }

    $sql_duplicado .= " LIMIT 1";
    $stmt = $pdo->prepare($sql_duplicado);
    $stmt->execute($params);

    if ($stmt->fetch()) {
        cs_json(array(
            'ok' => false,
            'message' => 'Ya existe un servicio base con ese nombre.'
        ), 409);
    }

    if ($id > 0) {
        $anterior = cs_obtener_servicio_general($id);

        if (!$anterior) {
            cs_json(array(
                'ok' => false,
                'message' => 'Servicio base no encontrado.'
            ), 404);
        }

        $upd = $pdo->prepare("
            UPDATE ecc_servicios
            SET
                nombre = :nombre,
                descripcion = :descripcion,
                precio_base = :precio_base,
                estado = :estado,
                updated_by_external_id = :updated_by_external_id
            WHERE id = :id
        ");

        $upd->execute(array(
            ':nombre' => $nombre,
            ':descripcion' => $descripcion !== '' ? $descripcion : null,
            ':precio_base' => $precio_base,
            ':estado' => $estado,
            ':updated_by_external_id' => cs_external_id(),
            ':id' => $id
        ));

        cs_sync_servicio_etiquetas($id, $etiquetas);
        cs_auditoria('Actualizar servicio base', 'ecc_servicios', $id, 'Servicio base actualizado.', $anterior, cs_obtener_servicio_general($id));

        cs_json(array(
            'ok' => true,
            'message' => 'Servicio base actualizado correctamente.',
            'tabla_html' => cs_render_catalogo_servicios_table(),
            'servicios_options' => cs_render_servicios_options($id)
        ));
    }

    $ins = $pdo->prepare("
        INSERT INTO ecc_servicios
        (nombre, descripcion, precio_base, estado, created_by_external_id)
        VALUES
        (:nombre, :descripcion, :precio_base, :estado, :created_by_external_id)
    ");

    $ins->execute(array(
        ':nombre' => $nombre,
        ':descripcion' => $descripcion !== '' ? $descripcion : null,
        ':precio_base' => $precio_base,
        ':estado' => $estado,
        ':created_by_external_id' => cs_external_id()
    ));

    $nuevo_id = (int)$pdo->lastInsertId();
    cs_sync_servicio_etiquetas($nuevo_id, $etiquetas);
    cs_auditoria('Crear servicio base', 'ecc_servicios', $nuevo_id, 'Servicio base creado.', null, cs_obtener_servicio_general($nuevo_id));

    cs_json(array(
        'ok' => true,
        'message' => 'Servicio base creado correctamente.',
        'tabla_html' => cs_render_catalogo_servicios_table(),
        'servicios_options' => cs_render_servicios_options($nuevo_id)
    ));
}

function cs_action_toggle_servicio_base()
{
    $pdo = app_pdo();

    $id = (int)cs_request('id', 0);
    $estado = (int)cs_request('estado', 0) === 1 ? 1 : 0;
    $servicio = cs_obtener_servicio_general($id);

    if (!$servicio) {
        cs_json(array(
            'ok' => false,
            'message' => 'Servicio base no encontrado.'
        ), 404);
    }

    $upd = $pdo->prepare("
        UPDATE ecc_servicios
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");
    $upd->execute(array(
        ':estado' => $estado,
        ':updated_by_external_id' => cs_external_id(),
        ':id' => $id
    ));

    $nuevo = cs_obtener_servicio_general($id);
    cs_auditoria(($estado === 1 ? 'Activar' : 'Inactivar') . ' servicio base', 'ecc_servicios', $id, 'Se cambió estado de servicio base.', $servicio, $nuevo);

    cs_json(array(
        'ok' => true,
        'message' => $estado === 1 ? 'Servicio base activado correctamente.' : 'Servicio base inactivado correctamente.',
        'tabla_html' => cs_render_catalogo_servicios_table(),
        'servicios_options' => cs_render_servicios_options()
    ));
}

function cs_action_anular_servicio_cliente()
{
    $pdo = app_pdo();

    $id = (int)cs_request('id', 0);
    $servicio = cs_obtener_servicio_asignado($id);

    if (!$servicio) {
        cs_json(array(
            'ok' => false,
            'message' => 'Servicio asignado no encontrado.'
        ), 404);
    }

    if ($servicio['estado'] === 'Pagado') {
        cs_json(array(
            'ok' => false,
            'message' => 'No se puede anular un servicio pagado desde este módulo.'
        ), 422);
    }

    $stmt = $pdo->prepare("
        UPDATE ecc_cliente_servicios
        SET estado = 'Anulado', updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':updated_by_external_id' => cs_external_id(),
        ':id' => $id
    ));

    cs_auditoria('Anular servicio asignado', 'ecc_cliente_servicios', $id, 'Servicio asignado anulado.', $servicio, cs_obtener_servicio_asignado($id));

    cs_json(array(
        'ok' => true,
        'message' => 'Servicio anulado correctamente.',
        'detalle_html' => cs_render_cliente_detalle($servicio['cliente_id']),
        'clientes_html' => cs_render_clientes_table()
    ));
}

function cs_action_crear_etiqueta()
{
    $pdo = app_pdo();

    $nombre = cs_clean_string(cs_request('nombre'));
    $color = cs_clean_string(cs_request('color', '#6c757d'));

    if ($nombre === '') {
        cs_json(array(
            'ok' => false,
            'message' => 'Ingrese el nombre de la etiqueta.'
        ), 422);
    }

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#6c757d';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO ecc_etiquetas
            (nombre, color, estado, created_by_external_id)
            VALUES
            (:nombre, :color, 1, :created_by_external_id)
        ");

        $stmt->execute(array(
            ':nombre' => $nombre,
            ':color' => $color,
            ':created_by_external_id' => cs_external_id()
        ));

        $id = (int)$pdo->lastInsertId();
        cs_auditoria('Crear etiqueta', 'ecc_etiquetas', $id, 'Etiqueta creada.', null, array('nombre' => $nombre, 'color' => $color));
    } catch (PDOException $e) {
        if ($e->getCode() !== '23000') {
            throw $e;
        }

        $stmt = $pdo->prepare("SELECT id FROM ecc_etiquetas WHERE nombre = :nombre LIMIT 1");
        $stmt->execute(array(':nombre' => $nombre));
        $row = $stmt->fetch();
        $id = $row ? (int)$row['id'] : 0;
    }

    cs_json(array(
        'ok' => true,
        'message' => 'Etiqueta disponible.',
        'etiqueta' => array(
            'id' => $id,
            'nombre' => $nombre,
            'color' => $color
        ),
        'options_html' => cs_render_etiquetas_options(array($id))
    ));
}

try {
    $action = cs_clean_string(cs_request('action'));

    if ($action === 'listar_clientes') {
        cs_action_listar_clientes();
    }

    if ($action === 'obtener_cliente') {
        cs_action_obtener_cliente();
    }

    if ($action === 'guardar_cliente') {
        app_require_post();
        cs_action_guardar_cliente();
    }

    if ($action === 'desactivar_cliente') {
        app_require_post();
        cs_action_desactivar_cliente();
    }

    if ($action === 'detalle_cliente') {
        cs_action_detalle_cliente();
    }

    if ($action === 'timeline_cliente') {
        cs_action_timeline_cliente();
    }

    if ($action === 'obtener_servicio_cliente') {
        cs_action_obtener_servicio_cliente();
    }

    if ($action === 'guardar_servicio_cliente') {
        app_require_post();
        cs_action_guardar_servicio_cliente();
    }

    if ($action === 'anular_servicio_cliente') {
        app_require_post();
        cs_action_anular_servicio_cliente();
    }

    if ($action === 'crear_etiqueta') {
        app_require_post();
        cs_action_crear_etiqueta();
    }

    if ($action === 'obtener_servicio_base') {
        cs_action_obtener_servicio_base();
    }

    if ($action === 'guardar_servicio_base') {
        app_require_post();
        cs_action_guardar_servicio_base();
    }

    if ($action === 'toggle_servicio_base') {
        app_require_post();
        cs_action_toggle_servicio_base();
    }

    cs_json(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    cs_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del módulo.'
    ), 500);
}

