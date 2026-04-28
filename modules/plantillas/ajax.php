<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function pl_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

function pl_clean($value)
{
    return trim((string)$value);
}

function pl_enum($value, $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function pl_bool_value($value)
{
    return (int)$value === 1 ? 1 : 0;
}

function pl_color($value, $default)
{
    $value = pl_clean($value);

    if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        return $value;
    }

    return $default;
}

function pl_json($data, $status = 200)
{
    app_json_response($data, $status);
}

function pl_action_obtener()
{
    $id = (int)pl_request('id', 0);
    $plantilla = pl_obtener($id);

    if (!$plantilla) {
        pl_json(array(
            'ok' => false,
            'message' => 'Plantilla no encontrada.'
        ), 404);
    }

    $plantilla['metodos_pago_ids'] = pl_metodos_visibles_ids($id);

    pl_json(array(
        'ok' => true,
        'plantilla' => $plantilla
    ));
}

function pl_action_guardar()
{
    app_require_post();

    $pdo = app_pdo();

    $id = (int)pl_request('id', 0);
    $nombre = pl_clean(pl_request('nombre'));
    $descripcion = pl_clean(pl_request('descripcion'));
    $orientacion = pl_enum(pl_clean(pl_request('orientacion')), array('Vertical', 'Horizontal'), 'Vertical');
    $logo_visible = pl_bool_value(pl_request('logo_visible', 1));
    $logo_tipo = pl_enum(pl_clean(pl_request('logo_tipo')), array('Cuadrado', 'Rectangular', 'Banner'), 'Rectangular');
    $datos_empresa_visible = pl_bool_value(pl_request('datos_empresa_visible', 1));
    $datos_cliente_visible = pl_bool_value(pl_request('datos_cliente_visible', 1));
    $color_tipo = pl_enum(pl_clean(pl_request('color_tipo')), array('Solido', 'Degradado'), 'Solido');
    $color_primario = pl_color(pl_request('color_primario'), '#1f4e79');
    $color_secundario = pl_color(pl_request('color_secundario'), '#163a5a');
    $pie_pagina_visible = pl_bool_value(pl_request('pie_pagina_visible', 1));
    $pie_pagina = pl_clean(pl_request('pie_pagina'));
    $es_predeterminada = pl_bool_value(pl_request('es_predeterminada', 0));
    $estado = pl_bool_value(pl_request('estado', 1));
    $metodos_pago = isset($_POST['metodos_pago']) && is_array($_POST['metodos_pago']) ? $_POST['metodos_pago'] : array();

    if ($nombre === '') {
        pl_json(array(
            'ok' => false,
            'message' => 'Ingrese el nombre de la plantilla.'
        ), 422);
    }

    if ($color_tipo === 'Solido') {
        $color_secundario = null;
    }

    try {
        $pdo->beginTransaction();

        if ($es_predeterminada === 1) {
            $pdo->exec("UPDATE ecc_plantillas SET es_predeterminada = 0");
        }

        if ($id > 0) {
            $anterior = pl_obtener($id);

            if (!$anterior) {
                $pdo->rollBack();
                pl_json(array(
                    'ok' => false,
                    'message' => 'Plantilla no encontrada.'
                ), 404);
            }

            $stmt = $pdo->prepare("
                UPDATE ecc_plantillas
                SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    orientacion = :orientacion,
                    logo_visible = :logo_visible,
                    logo_tipo = :logo_tipo,
                    datos_empresa_visible = :datos_empresa_visible,
                    datos_cliente_visible = :datos_cliente_visible,
                    color_tipo = :color_tipo,
                    color_primario = :color_primario,
                    color_secundario = :color_secundario,
                    pie_pagina_visible = :pie_pagina_visible,
                    pie_pagina = :pie_pagina,
                    es_predeterminada = :es_predeterminada,
                    estado = :estado,
                    updated_by_external_id = :updated_by_external_id
                WHERE id = :id
            ");

            $stmt->execute(array(
                ':nombre' => $nombre,
                ':descripcion' => $descripcion !== '' ? $descripcion : null,
                ':orientacion' => $orientacion,
                ':logo_visible' => $logo_visible,
                ':logo_tipo' => $logo_tipo,
                ':datos_empresa_visible' => $datos_empresa_visible,
                ':datos_cliente_visible' => $datos_cliente_visible,
                ':color_tipo' => $color_tipo,
                ':color_primario' => $color_primario,
                ':color_secundario' => $color_secundario,
                ':pie_pagina_visible' => $pie_pagina_visible,
                ':pie_pagina' => $pie_pagina !== '' ? $pie_pagina : null,
                ':es_predeterminada' => $es_predeterminada,
                ':estado' => $estado,
                ':updated_by_external_id' => pl_external_id(),
                ':id' => $id
            ));

            pl_sync_metodos_pago($id, $metodos_pago);

            $actual = pl_obtener($id);
            pl_auditoria('Actualizar plantilla', 'ecc_plantillas', $id, 'Plantilla actualizada.', $anterior, $actual);

            $pdo->commit();

            pl_json(array(
                'ok' => true,
                'message' => 'Plantilla actualizada correctamente.',
                'html' => pl_render_table()
            ));
        }

        $stmt = $pdo->prepare("
            INSERT INTO ecc_plantillas
            (nombre, descripcion, orientacion, logo_visible, logo_tipo, datos_empresa_visible, datos_cliente_visible, color_tipo, color_primario, color_secundario, pie_pagina_visible, pie_pagina, es_predeterminada, estado, created_by_external_id)
            VALUES
            (:nombre, :descripcion, :orientacion, :logo_visible, :logo_tipo, :datos_empresa_visible, :datos_cliente_visible, :color_tipo, :color_primario, :color_secundario, :pie_pagina_visible, :pie_pagina, :es_predeterminada, :estado, :created_by_external_id)
        ");

        $stmt->execute(array(
            ':nombre' => $nombre,
            ':descripcion' => $descripcion !== '' ? $descripcion : null,
            ':orientacion' => $orientacion,
            ':logo_visible' => $logo_visible,
            ':logo_tipo' => $logo_tipo,
            ':datos_empresa_visible' => $datos_empresa_visible,
            ':datos_cliente_visible' => $datos_cliente_visible,
            ':color_tipo' => $color_tipo,
            ':color_primario' => $color_primario,
            ':color_secundario' => $color_secundario,
            ':pie_pagina_visible' => $pie_pagina_visible,
            ':pie_pagina' => $pie_pagina !== '' ? $pie_pagina : null,
            ':es_predeterminada' => $es_predeterminada,
            ':estado' => $estado,
            ':created_by_external_id' => pl_external_id()
        ));

        $nuevo_id = (int)$pdo->lastInsertId();

        pl_sync_metodos_pago($nuevo_id, $metodos_pago);
        pl_auditoria('Crear plantilla', 'ecc_plantillas', $nuevo_id, 'Plantilla creada.', null, pl_obtener($nuevo_id));

        $pdo->commit();

        pl_json(array(
            'ok' => true,
            'message' => 'Plantilla creada correctamente.',
            'html' => pl_render_table()
        ));
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e->getCode() === '23000') {
            pl_json(array(
                'ok' => false,
                'message' => 'Ya existe una plantilla con ese nombre.'
            ), 409);
        }

        throw $e;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

function pl_action_cambiar_estado()
{
    app_require_post();

    $pdo = app_pdo();

    $id = (int)pl_request('id', 0);
    $plantilla = pl_obtener($id);

    if (!$plantilla) {
        pl_json(array(
            'ok' => false,
            'message' => 'Plantilla no encontrada.'
        ), 404);
    }

    $nuevo_estado = (int)$plantilla['estado'] === 1 ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE ecc_plantillas
        SET estado = :estado, updated_by_external_id = :updated_by_external_id
        WHERE id = :id
    ");

    $stmt->execute(array(
        ':estado' => $nuevo_estado,
        ':updated_by_external_id' => pl_external_id(),
        ':id' => $id
    ));

    pl_auditoria('Cambiar estado plantilla', 'ecc_plantillas', $id, 'Estado de plantilla actualizado.', $plantilla, pl_obtener($id));

    pl_json(array(
        'ok' => true,
        'message' => 'Estado actualizado correctamente.',
        'html' => pl_render_table()
    ));
}

function pl_action_vista_previa()
{
    $id = (int)pl_request('id', 0);

    pl_json(array(
        'ok' => true,
        'html' => pl_render_preview($id)
    ));
}

try {
    $action = pl_clean(pl_request('action'));

    if ($action === 'obtener_plantilla') {
        pl_action_obtener();
    }

    if ($action === 'guardar_plantilla') {
        pl_action_guardar();
    }

    if ($action === 'cambiar_estado_plantilla') {
        pl_action_cambiar_estado();
    }

    if ($action === 'vista_previa_plantilla') {
        pl_action_vista_previa();
    }

    pl_json(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    pl_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del módulo.'
    ), 500);
}