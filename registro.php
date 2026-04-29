<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/conexion.php';

/* Archivo temporal para crear usuarios. Puede eliminarse o desactivarse cuando ya no sea necesario. */
if (!auth_registro_publico_habilitado()) {
    http_response_code(403);
    exit('Registro temporal deshabilitado.');
}

$errores = array();
$ok = '';
$datos = array(
    'dni' => '',
    'nombres' => '',
    'apellidos' => ''
);

if (app_request_method() === 'POST') {
    $datos['dni'] = trim((string)($_POST['dni'] ?? ''));
    $datos['nombres'] = trim((string)($_POST['nombres'] ?? ''));
    $datos['apellidos'] = trim((string)($_POST['apellidos'] ?? ''));
    $clave = (string)($_POST['clave'] ?? '');
    $clave2 = (string)($_POST['clave_repetir'] ?? '');

    if (!preg_match('/^\d{8}$/', $datos['dni'])) {
        $errores[] = 'El DNI debe tener exactamente 8 dígitos numéricos.';
    }

    if ($datos['nombres'] === '') {
        $errores[] = 'Ingresa los nombres.';
    }

    if ($datos['apellidos'] === '') {
        $errores[] = 'Ingresa los apellidos.';
    }

    if (strlen($clave) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($clave !== $clave2) {
        $errores[] = 'La confirmación de contraseña no coincide.';
    }

    if (empty($errores)) {
        $pdo = app_pdo();

        $stmt = $pdo->prepare("SELECT id FROM ecc_usuarios WHERE dni = :dni LIMIT 1");
        $stmt->execute(array(
            ':dni' => $datos['dni']
        ));

        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un usuario con ese DNI.';
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $usuario_login = $datos['dni'];

            $ins = $pdo->prepare("\n                INSERT INTO ecc_usuarios\n                (dni, nombres, apellidos, usuario, clave_hash, rol, estado, created_by_external_id)\n                VALUES\n                (:dni, :nombres, :apellidos, :usuario, :clave_hash, 'Administrador', 1, :created_by_external_id)\n            ");
            $ins->execute(array(
                ':dni' => $datos['dni'],
                ':nombres' => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':usuario' => $usuario_login,
                ':clave_hash' => $hash,
                ':created_by_external_id' => $usuario_login
            ));

            $ok = 'Usuario creado correctamente. Ya puedes iniciar sesión.';
            $datos = array('dni' => '', 'nombres' => '', 'apellidos' => '');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro temporal | <?php echo e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset_url('plugins/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('assets/css/app.css')); ?>">
</head>
<body class="hold-transition app-auth-page">
<section class="app-auth-section">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 text-center">
                <h2 class="app-auth-heading">Registro temporal de usuario</h2>
                <p class="app-auth-subheading">Crea un usuario administrador para usar el sistema.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="app-auth-wrap">
                    <div class="app-auth-cover app-auth-cover-register">
                        <div class="app-auth-cover-mark">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h4>Registro inicial de acceso</h4>
                    </div>

                    <div class="app-auth-form p-4 p-md-5">
                        <?php if (!empty($errores)) { ?>
                            <div class="alert alert-danger py-2 mb-3">
                                <?php foreach ($errores as $error) { ?>
                                    <div><?php echo e($error); ?></div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if ($ok !== '') { ?>
                            <div class="alert alert-success py-2 mb-3"><?php echo e($ok); ?></div>
                        <?php } ?>

                        <div class="alert alert-danger d-none mb-3" id="registroErrorCliente"></div>

                        <form method="post" action="<?php echo e(app_url('registro.php')); ?>" autocomplete="off" id="formRegistroTemporal">
                            <div class="form-group mt-3">
                                <label class="app-auth-label">DNI</label>
                                <input type="text" class="form-control app-auth-control" name="dni" maxlength="8" inputmode="numeric" pattern="\d{8}" value="<?php echo e($datos['dni']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="app-auth-label">Nombres</label>
                                <input type="text" class="form-control app-auth-control" name="nombres" value="<?php echo e($datos['nombres']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="app-auth-label">Apellidos</label>
                                <input type="text" class="form-control app-auth-control" name="apellidos" value="<?php echo e($datos['apellidos']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="registroClave" class="app-auth-label">Contraseña</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control app-auth-control pr-5" name="clave" id="registroClave" required>
                                    <button type="button" class="btn btn-link app-auth-eye" data-toggle-pass="#registroClave" aria-label="Mostrar u ocultar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="registroClaveRepetir" class="app-auth-label">Repetir contraseña</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control app-auth-control pr-5" name="clave_repetir" id="registroClaveRepetir" required>
                                    <button type="button" class="btn btn-link app-auth-eye" data-toggle-pass="#registroClaveRepetir" aria-label="Mostrar u ocultar contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <button type="submit" class="form-control btn btn-primary rounded submit px-3 app-auth-btn">Crear usuario</button>
                            </div>
                        </form>

                        <p class="text-center mt-3 mb-0 small">
                            <a href="<?php echo e(app_url('login.php')); ?>" class="app-auth-link">Volver al login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo e(asset_url('plugins/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/registro.js')); ?>"></script>
</body>
</html>
