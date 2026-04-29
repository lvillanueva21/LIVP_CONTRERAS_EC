<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function us_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

function us_clean($value)
{
    return trim((string)$value);
}

function us_json($data, $status = 200)
{
    app_json_response($data, $status);
}

function us_current_user_id()
{
    $user = auth_user();
    return $user && isset($user['id']) ? (int)$user['id'] : 0;
}

function us_action_listar()
{
    us_json(array(
        'ok' => true,
        'html' => us_render_table()
    ));
}

function us_action_obtener()
{
    $id = (int)us_request('id', 0);
    $usuario = us_obtener($id);

    if (!$usuario) {
        us_json(array(
            'ok' => false,
            'message' => 'Usuario no encontrado.'
        ), 404);
    }

    us_json(array(
        'ok' => true,
        'usuario' => $usuario
    ));
}

function us_validar_base($dni, $nombres, $apellidos)
{
    if (!preg_match('/^\d{8}$/', $dni)) {
        us_json(array(
            'ok' => false,
            'message' => 'El DNI debe tener exactamente 8 digitos numericos.'
        ), 422);
    }

    if ($nombres === '') {
        us_json(array(
            'ok' => false,
            'message' => 'Ingrese los nombres.'
        ), 422);
    }

    if ($apellidos === '') {
        us_json(array(
            'ok' => false,
            'message' => 'Ingrese los apellidos.'
        ), 422);
    }
}

function us_action_guardar()
{
    $pdo = app_pdo();

    $id = (int)us_request('id', 0);
    $dni = us_clean(us_request('dni'));
    $nombres = us_clean(us_request('nombres'));
    $apellidos = us_clean(us_request('apellidos'));
    $clave = (string)us_request('clave');
    $estado = (int)us_request('estado', 1) === 1 ? 1 : 0;

    us_validar_base($dni, $nombres, $apellidos);

    $dup = $pdo->prepare("
        SELECT id
        FROM ecc_usuarios
        WHERE dni = :dni
          AND id <> :id
        LIMIT 1
    ");
    $dup->execute(array(
        ':dni' => $dni,
        ':id' => $id
    ));

    if ($dup->fetch()) {
        us_json(array(
            'ok' => false,
            'message' => 'Ya existe un usuario con ese DNI.'
        ), 422);
    }

    if ($id > 0) {
        $anterior = us_obtener($id);

        if (!$anterior) {
            us_json(array(
                'ok' => false,
                'message' => 'Usuario no encontrado.'
            ), 404);
        }

        if ((int)$anterior['id'] === us_current_user_id() && $estado === 0) {
            us_json(array(
                'ok' => false,
                'message' => 'No puedes desactivar tu propio usuario en esta pantalla.'
            ), 422);
        }

        $stmt = $pdo->prepare("
            UPDATE ecc_usuarios
            SET
                dni = :dni,
                usuario = :usuario,
                nombres = :nombres,
                apellidos = :apellidos,
                estado = :estado,
                updated_by_external_id = :updated_by_external_id
            WHERE id = :id
        ");

        $stmt->execute(array(
            ':dni' => $dni,
            ':usuario' => $dni,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':estado' => $estado,
            ':updated_by_external_id' => us_external_id(),
            ':id' => $id
        ));

        us_auditoria('Actualizar usuario', 'ecc_usuarios', $id, 'Usuario actualizado.', $anterior, us_obtener($id));

        us_json(array(
            'ok' => true,
            'message' => 'Usuario actualizado correctamente.',
            'html' => us_render_table()
        ));
    }

    if (strlen($clave) < 8) {
        us_json(array(
            'ok' => false,
            'message' => 'La contraseña debe tener minimo 8 caracteres.'
        ), 422);
    }

    $hash = password_hash($clave, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO ecc_usuarios
        (dni, usuario, nombres, apellidos, clave_hash, rol, estado, created_by_external_id)
        VALUES
        (:dni, :usuario, :nombres, :apellidos, :clave_hash, 'Administrador', :estado, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':dni' => $dni,
        ':usuario' => $dni,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':clave_hash' => $hash,
        ':estado' => $estado,
        ':created_by_external_id' => us_external_id()
    ));

    $nuevo_id = (int)$pdo->lastInsertId();
    us_auditoria('Crear usuario', 'ecc_usuarios', $nuevo_id, 'Usuario creado.', null, us_obtener($nuevo_id));

    us_json(array(
        'ok' => true,
        'message' => 'Usuario creado correctamente.',
        'html' => us_render_table()
    ));
}

function us_action_cambiar_clave()
{
    $pdo = app_pdo();

    $id = (int)us_request('id', 0);
    $clave = (string)us_request('clave');

    if (strlen($clave) < 8) {
        us_json(array(
            'ok' => false,
            'message' => 'La contraseña debe tener minimo 8 caracteres.'
        ), 422);
    }

    $usuario = us_obtener($id);

    if (!$usuario) {
        us_json(array(
            'ok' => false,
            'message' => 'Usuario no encontrado.'
        ), 404);
    }

    $hash = password_hash($clave, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE ecc_usuarios
        SET clave_hash = :clave_hash, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':clave_hash' => $hash,
        ':updated_by_external_id' => us_external_id(),
        ':id' => $id
    ));

    us_auditoria('Cambiar contraseña usuario', 'ecc_usuarios', $id, 'Se actualizo la contraseña del usuario.');

    us_json(array(
        'ok' => true,
        'message' => 'Contraseña actualizada correctamente.',
        'html' => us_render_table()
    ));
}

function us_action_cambiar_estado()
{
    $pdo = app_pdo();

    $id = (int)us_request('id', 0);
    $usuario = us_obtener($id);

    if (!$usuario) {
        us_json(array(
            'ok' => false,
            'message' => 'Usuario no encontrado.'
        ), 404);
    }

    if ((int)$usuario['id'] === us_current_user_id()) {
        us_json(array(
            'ok' => false,
            'message' => 'No puedes desactivar tu propio usuario.'
        ), 422);
    }

    $nuevo_estado = (int)$usuario['estado'] === 1 ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE ecc_usuarios
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':estado' => $nuevo_estado,
        ':updated_by_external_id' => us_external_id(),
        ':id' => $id
    ));

    us_auditoria('Cambiar estado usuario', 'ecc_usuarios', $id, 'Estado de usuario actualizado.', $usuario, us_obtener($id));

    us_json(array(
        'ok' => true,
        'message' => 'Estado actualizado correctamente.',
        'html' => us_render_table()
    ));
}

try {
    $action = us_clean(us_request('action'));

    if ($action === 'listar_usuarios') {
        us_action_listar();
    }

    if ($action === 'obtener_usuario') {
        us_action_obtener();
    }

    if ($action === 'guardar_usuario') {
        app_require_post();
        us_action_guardar();
    }

    if ($action === 'cambiar_clave_usuario') {
        app_require_post();
        us_action_cambiar_clave();
    }

    if ($action === 'cambiar_estado_usuario') {
        app_require_post();
        us_action_cambiar_estado();
    }

    us_json(array(
        'ok' => false,
        'message' => 'Accion no valida.'
    ), 400);
} catch (Throwable $e) {
    us_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del modulo.'
    ), 500);
}
