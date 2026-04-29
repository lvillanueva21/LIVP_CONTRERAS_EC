<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function dash_request($key, $default = '')
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }

    if (isset($_GET[$key])) {
        return $_GET[$key];
    }

    return $default;
}

try {
    $action = trim((string)dash_request('action'));

    if ($action === 'grafico_ingresos') {
        app_json_response(array(
            'ok' => true,
            'chart' => dash_grafico_ingresos()
        ));
    }

    if ($action === 'avisos') {
        app_json_response(array(
            'ok' => true,
            'resumen' => dash_resumen(),
            'avisos_html' => dash_render_avisos(dash_proximos_avisos()),
            'vencidos_html' => dash_render_vencidos(dash_servicios_vencidos())
        ));
    }

    app_json_response(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    app_json_response(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno del dashboard.'
    ), 500);
}