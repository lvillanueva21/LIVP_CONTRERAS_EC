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
<body class="hold-transition app-auth-page">
<section class="app-auth-section">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 text-center">
                <h2 class="app-auth-heading">Bienvenido al Estudio Contable Contreras</h2>
                <p class="app-auth-subheading">Ingresa con tu usuario o DNI para continuar.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="app-auth-wrap">
                    <div class="app-auth-cover">
                        <div class="app-auth-cover-mark">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h4>Estudio Contable Contreras</h4>
                    </div>

                    <div class="app-auth-form p-4 p-md-5">
                        <div class="d-flex align-items-center mb-3">
                            <h4 class="mb-0">Iniciar sesión</h4>
                        </div>

                        <?php if ($error !== '') { ?>
                            <div class="alert alert-danger py-2 mb-3"><?php echo e($error); ?></div>
                        <?php } ?>

                        <form method="post" action="<?php echo e(app_url('login.php')); ?>" autocomplete="off">
                            <div class="form-group mt-3">
                                <label for="loginUsuario" class="app-auth-label">Usuario o DNI</label>
                                <input type="text" class="form-control app-auth-control" name="login" id="loginUsuario" value="<?php echo e($login); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="loginClave" class="app-auth-label">Contraseña</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control app-auth-control pr-5" name="clave" id="loginClave" required>
                                    <button type="button" class="btn btn-link app-auth-eye" data-toggle-pass="#loginClave" aria-label="Mostrar u ocultar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="form-control btn btn-primary rounded submit px-3 app-auth-btn">
                                    Ingresar
                                </button>
                            </div>
                        </form>

                        <?php if (auth_registro_publico_habilitado()) { ?>
                            <p class="text-center mt-3 mb-0 small">
                                <a href="<?php echo e(app_url('registro.php')); ?>" class="app-auth-link">Crear usuario temporal</a>
                            </p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo e(asset_url('plugins/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/login.js')); ?>"></script>
</body>
</html>
