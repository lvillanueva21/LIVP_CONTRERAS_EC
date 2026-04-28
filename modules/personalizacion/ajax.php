<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/gestion_archivos.php';
require_once __DIR__ . '/funciones.php';

function pz_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    return $default;
}

function pz_clean($value)
{
    return trim((string)$value);
}

function pz_decimal($value, $default = 0)
{
    $value = str_replace(',', '.', (string)$value);

    if (!is_numeric($value)) {
        return $default;
    }

    return round((float)$value, 2);
}

function pz_enum($value, $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function pz_json($data, $status = 200)
{
    app_json_response($data, $status);
}

function pz_color($value, $default)
{
    $value = pz_clean($value);

    if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        return $value;
    }

    return $default;
}

function pz_action_guardar()
{
    app_require_post();

    $pdo = app_pdo();

    $anterior = pz_obtener_configuracion();

    $nombre_comercial = pz_clean(pz_request('nombre_comercial'));
    $ruc = pz_clean(pz_request('ruc'));
    $razon_social = pz_clean(pz_request('razon_social'));
    $rubro = pz_clean(pz_request('rubro'));
    $direccion = pz_clean(pz_request('direccion'));
    $celular = pz_clean(pz_request('celular'));
    $correo = pz_clean(pz_request('correo'));
    $logo_tipo = pz_enum(pz_clean(pz_request('logo_tipo')), array('Cuadrado', 'Rectangular', 'Banner'), 'Rectangular');
    $logo_zoom = pz_decimal(pz_request('logo_zoom'), 1.00);
    $logo_pos_x = pz_decimal(pz_request('logo_pos_x'), 0.00);
    $logo_pos_y = pz_decimal(pz_request('logo_pos_y'), 0.00);
    $color_tipo = pz_enum(pz_clean(pz_request('color_tipo')), array('Solido', 'Degradado'), 'Solido');
    $color_primario = pz_color(pz_request('color_primario'), '#1f4e79');
    $color_secundario = pz_color(pz_request('color_secundario'), '#163a5a');
    $pie_pagina = pz_clean(pz_request('pie_pagina'));

    if ($nombre_comercial === '') {
        pz_json(array(
            'ok' => false,
            'message' => 'Ingrese el nombre comercial.'
        ), 422);
    }

    if ($ruc === '') {
        pz_json(array(
            'ok' => false,
            'message' => 'Ingrese el RUC.'
        ), 422);
    }

    if ($razon_social === '') {
        pz_json(array(
            'ok' => false,
            'message' => 'Ingrese la razón social.'
        ), 422);
    }

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        pz_json(array(
            'ok' => false,
            'message' => 'Ingrese un correo válido.'
        ), 422);
    }

    if ($logo_zoom < 0.50) {
        $logo_zoom = 0.50;
    }

    if ($logo_zoom > 3.00) {
        $logo_zoom = 3.00;
    }

    $logo_archivo_id = isset($anterior['logo_archivo_id']) ? $anterior['logo_archivo_id'] : null;
    $logo_url = isset($anterior['logo_ruta']) && $anterior['logo_ruta'] !== '' ? app_url($anterior['logo_ruta']) : '';

    if (isset($_FILES['logo']) && isset($_FILES['logo']['error']) && (int)$_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $guardado = app_archivo_guardar(
            $_FILES['logo'],
            'logo',
            'ecc_configuracion_empresa',
            1,
            'Logo de empresa',
            array('image/png', 'image/jpeg', 'image/webp')
        );

        if (!$guardado['ok']) {
            pz_json($guardado, 422);
        }

        $logo_archivo_id = $guardado['archivo_id'];
        $logo_url = app_url($guardado['ruta_relativa']);
    }

    $stmt = $pdo->prepare("
        UPDATE ecc_configuracion_empresa
        SET
            nombre_comercial = :nombre_comercial,
            ruc = :ruc,
            razon_social = :razon_social,
            rubro = :rubro,
            direccion = :direccion,
            correo = :correo,
            celular = :celular,
            logo_archivo_id = :logo_archivo_id,
            logo_tipo = :logo_tipo,
            logo_zoom = :logo_zoom,
            logo_pos_x = :logo_pos_x,
            logo_pos_y = :logo_pos_y,
            color_tipo = :color_tipo,
            color_primario = :color_primario,
            color_secundario = :color_secundario,
            pie_pagina = :pie_pagina,
            updated_by_external_id = :updated_by_external_id
        WHERE id = 1
    ");

    $stmt->execute(array(
        ':nombre_comercial' => $nombre_comercial,
        ':ruc' => $ruc,
        ':razon_social' => $razon_social,
        ':rubro' => $rubro !== '' ? $rubro : null,
        ':direccion' => $direccion !== '' ? $direccion : null,
        ':correo' => $correo !== '' ? $correo : null,
        ':celular' => $celular !== '' ? $celular : null,
        ':logo_archivo_id' => $logo_archivo_id !== '' ? $logo_archivo_id : null,
        ':logo_tipo' => $logo_tipo,
        ':logo_zoom' => $logo_zoom,
        ':logo_pos_x' => $logo_pos_x,
        ':logo_pos_y' => $logo_pos_y,
        ':color_tipo' => $color_tipo,
        ':color_primario' => $color_primario,
        ':color_secundario' => $color_tipo === 'Degradado' ? $color_secundario : null,
        ':pie_pagina' => $pie_pagina !== '' ? $pie_pagina : null,
        ':updated_by_external_id' => pz_external_id()
    ));

    pz_auditoria('Actualizar personalización', 'ecc_configuracion_empresa', 1, 'Configuración de empresa actualizada.', $anterior, pz_obtener_configuracion());

    pz_json(array(
        'ok' => true,
        'message' => 'Personalización guardada correctamente.',
        'logo_url' => $logo_url
    ));
}

try {
    $action = pz_clean(pz_request('action'));

    if ($action === 'guardar_personalizacion') {
        pz_action_guardar();
    }

    pz_json(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    pz_json(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del módulo.'
    ), 500);
}