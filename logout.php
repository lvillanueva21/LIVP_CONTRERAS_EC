<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

auth_logout();
header('Location: ' . app_url('login.php'));
exit;
