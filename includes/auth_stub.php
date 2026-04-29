<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/auth.php';

if (!defined('AUTH_ALLOW_GUEST') || AUTH_ALLOW_GUEST !== true) {
    auth_require();
}
