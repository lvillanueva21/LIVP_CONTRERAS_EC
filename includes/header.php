<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

$config = app_config();
$user = auth_user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($app_page_title); ?> | <?php echo e($config['app_name']); ?></title>

    <link rel="stylesheet" href="<?php echo e(asset_url('plugins/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('plugins/toastr/toastr.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('assets/css/app.css')); ?>">
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?php echo e(app_url('index.php')); ?>" class="nav-link">Inicio</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item d-none d-md-flex align-items-center mr-2">
            <span class="badge badge-light border">
                <i class="fas fa-user-circle mr-1"></i>
                <?php echo e($user ? $user['name'] : 'Sin sesión'); ?>
            </span>
        </li>
        <li class="nav-item d-none d-md-flex align-items-center mr-3">
            <span class="badge badge-secondary">
                <?php echo e($user ? $user['role'] : '---'); ?>
            </span>
        </li>
        <li class="nav-item">
            <span class="nav-link app-clock" id="appClock">
                <?php echo e(app_date_time()); ?>
            </span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo e(app_url('logout.php')); ?>" title="Salir">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </li>
    </ul>
</nav>
