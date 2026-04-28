<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_mode()
{
    $config = app_config();
    return $config['auth_mode'];
}

function auth_incoming_token()
{
    $headers = function_exists('getallheaders') ? getallheaders() : array();

    if (isset($headers['Authorization'])) {
        return trim((string)$headers['Authorization']);
    }

    if (isset($headers['authorization'])) {
        return trim((string)$headers['authorization']);
    }

    if (isset($headers['X-App-Token'])) {
        return trim((string)$headers['X-App-Token']);
    }

    if (isset($headers['x-app-token'])) {
        return trim((string)$headers['x-app-token']);
    }

    if (isset($_GET['token'])) {
        return trim((string)$_GET['token']);
    }

    return '';
}

function auth_demo_user()
{
    return array(
        'id' => 1,
        'name' => 'Usuario demo',
        'email' => 'demo@contreras.local',
        'role' => 'Administrador',
        'mode' => 'demo',
        'token' => null
    );
}

function auth_central_api_user()
{
    $token = auth_incoming_token();

    return array(
        'id' => 0,
        'name' => 'Usuario API pendiente',
        'email' => '',
        'role' => 'Pendiente',
        'mode' => 'central_api',
        'token' => $token
    );
}

function auth_user()
{
    if (auth_mode() === 'central_api') {
        return auth_central_api_user();
    }

    return auth_demo_user();
}

function auth_check()
{
    $user = auth_user();

    if (auth_mode() === 'demo') {
        return true;
    }

    if (auth_mode() === 'central_api') {
        return $user['token'] !== '';
    }

    return false;
}

function auth_require()
{
    if (!auth_check()) {
        http_response_code(401);
        exit('Acceso no autorizado');
    }
}

function auth_user_name()
{
    $user = auth_user();
    return $user['name'];
}

function auth_user_role()
{
    $user = auth_user();
    return $user['role'];
}