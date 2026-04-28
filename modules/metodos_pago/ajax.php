<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function mp_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

function mp_clean($value)
{
    return trim((string)$value);
}

function mp_enum($value, $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function mp_json($data, $status = 200)
{
    app_json_response($data, $status);
}

function mp_action_listar()
{
    mp_json(array(
        'ok' => true,
        'html' => mp_render_table()
    ));
}

function mp_action_obtener()
{
    $id = (int)mp_request('id', 0);
    $metodo = mp_obtener($id);

    if (!$metodo) {
        mp_json(array(
            'ok' => false,
            'message' => 'Método de pago no encontrado.'
        ), 404);
    }

    mp_json(array(
        'ok' => true,
        'metodo' => $metodo
    ));
}

function mp_action_guardar()
{
    $pdo = app_pdo();

    $id = (int)mp_request('id', 0);
    $titulo_visible = mp_clean(mp_request('titulo_visible'));
    $tipo = mp_enum(mp_clean(mp_request('tipo')), array('Cuenta de ahorro', 'Yape', 'Plin'), 'Cuenta de ahorro');
    $titular = mp_clean(mp_request('titular'));
    $banco = mp_clean(mp_request('banco'));
    $numero_cuenta = mp_clean(mp_request('numero_cuenta'));
    $cci = mp_clean(mp_request('cci'));
    $numero_celular = mp_clean(mp_request('numero_celular'));
    $descripcion = mp_clean(mp_request('descripcion'));
    $orden = (int)mp_request('orden', 1);
    $estado = (int)mp_request('estado', 1) === 1 ? 1 : 0;

    if ($titulo_visible === '') {
        mp_json(array(
            'ok' => false,
            'message' => 'Ingrese el título visible.'
        ), 422);
    }

    if ($titular === '') {
        mp_json(array(
            'ok' => false,
            'message' => 'Ingrese el titular.'
        ), 422);
    }

    if ($tipo === 'Cuenta de ahorro' && ($banco === '' || $numero_cuenta === '')) {
        mp_json(array(
            'ok' => false,
            'message' => 'Ingrese banco y número de cuenta.'
        ), 422);
    }

    if (($tipo === 'Yape' || $tipo === 'Plin') && $numero_celular === '') {
        mp_json(array(
            'ok' => false,
            'message' => 'Ingrese el número de celular.'
        ), 422);
    }

    if ($orden <= 0) {
        $orden = 1;
    }

    if ($tipo !== 'Cuenta de ahorro') {
        $banco = '';
        $numero_cuenta = '';
        $cci = '';
    }

    if ($tipo === 'Cuenta de ahorro') {
        $numero_celular = '';
    }

    if ($id > 0) {
        $anterior = mp_obtener($id);

        if (!$anterior) {
            mp_json(array(
                'ok' => false,
                'message' => 'Método de pago no encontrado.'
            ), 404);
        }

        $stmt = $pdo->prepare("
            UPDATE ecc_metodos_pago
            SET
                titulo_visible = :titulo_visible,
                tipo = :tipo,
                titular = :titular,
                banco = :banco,
                numero_cuenta = :numero_cuenta,
                cci = :cci,
                numero_celular = :numero_celular,
                descripcion = :descripcion,
                orden = :orden,
                estado = :estado,
                updated_by_external_id = :updated_by_external_id
            WHERE id = :id
        ");

        $stmt->execute(array(
            ':titulo_visible' => $titulo_visible,
            ':tipo' => $tipo,
            ':titular' => $titular,
            ':banco' => $banco !== '' ? $banco : null,
            ':numero_cuenta' => $numero_cuenta !== '' ? $numero_cuenta : null,
            ':cci' => $cci !== '' ? $cci : null,
            ':numero_celular' => $numero_celular !== '' ? $numero_celular : null,
            ':descripcion' => $descripcion !== '' ? $descripcion : null,
            ':orden' => $orden,
            ':estado' => $estado,
            ':updated_by_external_id' => mp_external_id(),
            ':id' => $id
        ));

        mp_auditoria('Actualizar método de pago', 'ecc_metodos_pago', $id, 'Método de pago actualizado.', $anterior, mp_obtener($id));

        mp_json(array(
            'ok' => true,
            'message' => 'Método de pago actualizado correctamente.',
            'html' => mp_render_table()
        ));
    }

    $stmt = $pdo->prepare("
        INSERT INTO ecc_metodos_pago
        (titulo_visible, tipo, titular, banco, numero_cuenta, cci, numero_celular, descripcion, orden, estado, created_by_external_id)
        VALUES
        (:titulo_visible, :tipo, :titular, :banco, :numero_cuenta, :cci, :numero_celular, :descripcion, :orden, :estado, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':titulo_visible' => $titulo_visible,
        ':tipo' => $tipo,
        ':titular' => $titular,
        ':banco' => $banco !== '' ? $banco : null,
        ':numero_cuenta' => $numero_cuenta !== '' ? $numero_cuenta : null,
        ':cci' => $cci !== '' ? $cci : null,
        ':numero_celular' => $numero_celular !== '' ? $numero_celular : null,
        ':descripcion' => $descripcion !== '' ? $descripcion : null,
        ':orden' => $orden,
        ':estado' => $estado,
        ':created_by_external_id' => mp_external_id()
    ));

    $nuevo_id = (int)$pdo->lastInsertId();

    mp_auditoria('Crear método de pago', 'ecc_metodos_pago', $nuevo_id, 'Método de pago creado.', null, mp_obtener($nuevo_id));

    mp_json(array(
        'ok' => true,
        'message' => 'Método de pago creado correctamente.',
        'html' => mp_render_table()
    ));
}

function mp_action_cambiar_estado()
{
    $pdo = app_pdo();

    $id = (int)mp_request('id', 0);
    $metodo = mp_obtener($id);

    if (!$metodo) {
        mp_json(array(
            'ok' => false,
            'message' => 'Método de pago no encontrado.'
        ), 404);
    }

    $nuevo_estado = (int)$metodo['estado'] === 1 ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE ecc_metodos_pago
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':estado' => $nuevo_estado,
        ':updated_by_external_id' => mp_external_id(),
        ':id' => $id
    ));

    mp_auditoria('Cambiar estado método de pago', 'ecc_metodos_pago', $id, 'Estado del método de pago actualizado.', $metodo, mp_obtener($id));

    mp_json(array(
        'ok' => true,
        'message' => 'Estado actualizado correctamente.',
        'html' => mp_render_table()
    ));
}

try {
    $action = mp_clean(mp_request('action'));

    if ($action === 'listar_metodos') {
        mp_action_listar();
    }

    if ($action === 'obtener_metodo_pago') {
        mp_action_obtener();
    }

    if ($action === 'guardar_metodo_pago') {
        app_require_post();
        mp_action_guardar();
    }

    if ($action === 'cambiar_estado_metodo_pago') {
        app_require_post();
        mp_action_cambiar_estado();
    }

    mp_json(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    mp_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del módulo.'
    ), 500);
}