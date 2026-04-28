<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function app_config($key = null, $default = null)
{
    global $app_config;

    if ($key === null) {
        return $app_config;
    }

    return array_key_exists($key, $app_config) ? $app_config[$key] : $default;
}

function app_modules()
{
    global $app_modules;
    return $app_modules;
}

function app_debug()
{
    $config = app_config();
    return !empty($config['app_debug']);
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function app_url($path = '')
{
    $path = trim((string)$path);

    if ($path === '') {
        return './';
    }

    return './' . ltrim($path, '/');
}

function asset_url($path)
{
    return app_url($path);
}

function module_url($module)
{
    return app_url('index.php?mod=' . urlencode((string)$module));
}

function app_current_module()
{
    $config = app_config();
    $modules = app_modules();

    $module = isset($_GET['mod']) ? trim((string)$_GET['mod']) : $config['default_module'];

    if ($module === '') {
        $module = $config['default_module'];
    }

    if (!array_key_exists($module, $modules)) {
        $module = $config['default_module'];
    }

    return $module;
}

function app_is_active_module($module)
{
    return app_current_module() === $module;
}

function app_active_class($module)
{
    return app_is_active_module($module) ? 'active' : '';
}

function app_menu_open_class($modules)
{
    foreach ($modules as $module) {
        if (app_is_active_module($module)) {
            return 'menu-open';
        }
    }

    return '';
}

function app_money($amount)
{
    return 'S/ ' . number_format((float)$amount, 2, '.', ',');
}

function app_date_time()
{
    return date('d/m/Y H:i');
}

function app_json_response($data, $status_code = 200)
{
    http_response_code((int)$status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function app_is_ajax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function app_request_method()
{
    return isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string)$_SERVER['REQUEST_METHOD']) : 'GET';
}

function app_require_post()
{
    if (app_request_method() !== 'POST') {
        app_json_response(array(
            'ok' => false,
            'message' => 'Método no permitido'
        ), 405);
    }
}

function app_storage_dir()
{
    $config = app_config();
    return $config['paths']['storage_dir'];
}

function app_storage_public()
{
    $config = app_config();
    return $config['paths']['storage_public'];
}