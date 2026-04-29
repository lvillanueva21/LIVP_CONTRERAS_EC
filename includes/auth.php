<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

if (!function_exists('auth_boot_session')) {
    function auth_boot_session()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $session_name = (string)app_config('app_session_name', 'ecc_session');

        if ($session_name !== '') {
            session_name($session_name);
        }

        session_start();
    }
}

auth_boot_session();

if (!defined('AUTH_SESSION_KEY')) {
    define('AUTH_SESSION_KEY', 'ecc_auth_user');
}

function auth_mode()
{
    return 'session';
}

function auth_is_ajax_request()
{
    if (function_exists('app_is_ajax') && app_is_ajax()) {
        return true;
    }

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower((string)$_SERVER['HTTP_ACCEPT']) : '';

    if (strpos($accept, 'application/json') !== false) {
        return true;
    }

    $script = isset($_SERVER['SCRIPT_NAME']) ? strtolower((string)$_SERVER['SCRIPT_NAME']) : '';
    return substr($script, -8) === 'ajax.php';
}

function auth_current_user_from_session()
{
    if (!isset($_SESSION[AUTH_SESSION_KEY]) || !is_array($_SESSION[AUTH_SESSION_KEY])) {
        return null;
    }

    $user = $_SESSION[AUTH_SESSION_KEY];

    if (!isset($user['id']) || (int)$user['id'] <= 0) {
        return null;
    }

    $nombres = trim((string)($user['nombres'] ?? ''));
    $apellidos = trim((string)($user['apellidos'] ?? ''));
    $display_name = trim($nombres . ' ' . $apellidos);

    if ($display_name === '') {
        $display_name = (string)($user['dni'] ?? 'Usuario');
    }

    return array(
        'id' => (int)$user['id'],
        'dni' => (string)($user['dni'] ?? ''),
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'usuario' => (string)($user['usuario'] ?? ''),
        'rol' => (string)($user['rol'] ?? 'Administrador'),
        'estado' => (int)($user['estado'] ?? 1),
        'name' => $display_name,
        'role' => (string)($user['rol'] ?? 'Administrador'),
        'mode' => (string)($user['dni'] ?? 'sistema')
    );
}

function auth_user()
{
    return auth_current_user_from_session();
}

function auth_check()
{
    return auth_user() !== null;
}

function auth_require()
{
    if (auth_check()) {
        return;
    }

    if (auth_is_ajax_request()) {
        app_json_response(array(
            'ok' => false,
            'message' => 'Sesión no válida. Inicia sesión nuevamente.'
        ), 401);
    }

    header('Location: ' . app_url('login.php'));
    exit;
}

function auth_user_name()
{
    $user = auth_user();
    return $user ? $user['name'] : 'Invitado';
}

function auth_user_role()
{
    $user = auth_user();
    return $user ? $user['role'] : 'Sin sesión';
}

function auth_set_session_user($db_user)
{
    $_SESSION[AUTH_SESSION_KEY] = array(
        'id' => (int)$db_user['id'],
        'dni' => (string)$db_user['dni'],
        'nombres' => (string)$db_user['nombres'],
        'apellidos' => (string)$db_user['apellidos'],
        'usuario' => (string)$db_user['usuario'],
        'rol' => (string)$db_user['rol'],
        'estado' => (int)$db_user['estado']
    );
}

function auth_logout()
{
    if (session_status() === PHP_SESSION_NONE) {
        return;
    }

    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function auth_login_attempt($dni, $clave)
{
    $dni = trim((string)$dni);
    $clave = (string)$clave;

    if (!preg_match('/^\d{8}$/', $dni) || $clave === '') {
        return array('ok' => false, 'message' => 'Ingresa DNI y contraseña válidos.');
    }

    $pdo = app_pdo();

    $stmt = $pdo->prepare("\n        SELECT *\n        FROM ecc_usuarios\n        WHERE estado = 1\n          AND dni = :dni\n        LIMIT 1\n    ");
    $stmt->execute(array(':dni' => $dni));
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($clave, (string)$usuario['clave_hash'])) {
        return array('ok' => false, 'message' => 'Credenciales inválidas.');
    }

    auth_set_session_user($usuario);

    $updated_by = (string)$usuario['dni'];
    $upd = $pdo->prepare("\n        UPDATE ecc_usuarios\n        SET ultimo_login_at = NOW(), updated_by_external_id = :updated_by_external_id\n        WHERE id = :id\n    ");
    $upd->execute(array(
        ':updated_by_external_id' => $updated_by,
        ':id' => (int)$usuario['id']
    ));

    return array('ok' => true, 'message' => 'Sesión iniciada.');
}

function auth_registro_publico_habilitado()
{
    return (bool)app_config('app_registro_publico', true);
}
