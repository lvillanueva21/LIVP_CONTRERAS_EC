<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

function au_request($key, $default = '')
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
    $action = trim((string)au_request('action'));

    if ($action === 'listar') {
        $filtros = au_filtros($_POST);

        app_json_response(array(
            'ok' => true,
            'html' => au_render_tabla($filtros),
            'kpis' => au_kpis()
        ));
    }

    app_json_response(array(
        'ok' => false,
        'message' => 'Acción no válida.'
    ), 400);
} catch (Throwable $e) {
    app_json_response(array(
        'ok' => false,
        'message' => app_debug() ? $e->getMessage() : 'Error interno de auditoría.'
    ), 500);
}