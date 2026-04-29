<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexion.php';

if (auth_check()) {
    header('Location: ' . app_url('index.php'));
    exit;
}

$error = '';
$login = '';

if (app_request_method() === 'POST') {
    $login = trim((string)($_POST['login'] ?? ''));
    $clave = (string)($_POST['clave'] ?? '');

    $resultado = auth_login_attempt($login, $clave);

    if (!empty($resultado['ok'])) {
        header('Location: ' . app_url('index.php'));
        exit;
    }

    $error = (string)($resultado['message'] ?? 'No se pudo iniciar sesión.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?php echo e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset_url('plugins/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('assets/css/app.css')); ?>">
</head>
<body class="hold-transition login-page app-auth-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="<?php echo e(app_url('login.php')); ?>" class="h1"><b>Estudio Contable</b> Contreras</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Inicia sesión con tu usuario o DNI</p>

            <?php if ($error !== '') { ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php } ?>

            <form method="post" action="<?php echo e(app_url('login.php')); ?>" autocomplete="off">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="login" id="loginUsuario" placeholder="Usuario o DNI" value="<?php echo e($login); ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="clave" id="loginClave" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary app-btn-eye" data-toggle-pass="#loginClave" aria-label="Mostrar u ocultar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </div>
                </div>
            </form>

            <?php if (auth_registro_publico_habilitado()) { ?>
                <p class="mt-3 mb-0 text-center">
                    <a href="<?php echo e(app_url('registro.php')); ?>">Crear usuario temporal</a>
                </p>
            <?php } ?>
        </div>
    </div>
</div>

<script src="<?php echo e(asset_url('plugins/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/login.js')); ?>"></script>
</body>
</html>
