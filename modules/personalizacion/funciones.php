<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function pz_external_id()
{
    $user = auth_user();
    return isset($user['mode']) && $user['mode'] !== '' ? $user['mode'] : 'demo';
}

function pz_obtener_configuracion()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT ce.*, a.ruta_relativa AS logo_ruta, a.nombre_original AS logo_nombre
        FROM ecc_configuracion_empresa ce
        LEFT JOIN ecc_archivos a ON a.id = ce.logo_archivo_id
        WHERE ce.id = 1
        LIMIT 1
    ");

    $row = $stmt->fetch();

    if ($row) {
        return $row;
    }

    $stmt = $pdo->prepare("
        INSERT INTO ecc_configuracion_empresa
        (id, ruc, razon_social, nombre_comercial, rubro, direccion, correo, celular, logo_tipo, logo_zoom, logo_pos_x, logo_pos_y, color_tipo, color_primario, color_secundario, pie_pagina, created_by_external_id)
        VALUES
        (1, '00000000000', 'Estudio Contable Contreras', 'Estudio Contable Contreras', 'Servicios contables y tributarios', '', '', '', 'Rectangular', 1.00, 0.00, 0.00, 'Solido', '#1f4e79', NULL, '', :created_by_external_id)
    ");

    $stmt->execute(array(':created_by_external_id' => pz_external_id()));

    return pz_obtener_configuracion();
}

function pz_logo_url($config)
{
    if (!isset($config['logo_ruta']) || trim((string)$config['logo_ruta']) === '') {
        return '';
    }

    return app_url($config['logo_ruta']);
}

function pz_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Personalización',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => pz_external_id(),
        ':created_by_external_id' => pz_external_id()
    ));
}