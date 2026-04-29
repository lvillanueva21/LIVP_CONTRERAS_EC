<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth_stub.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/funciones.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$recibo = rb_obtener($id);
$titulo = $recibo ? 'Recibo ' . $recibo['codigo'] : 'Recibo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo e($titulo); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?php echo e(asset_url('plugins/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset_url('assets/css/app.css')); ?>">
</head>
<body class="rb-documento-body">
    <div class="rb-documento-actions">
        <button type="button" class="btn btn-secondary" onclick="window.close();">
            <i class="fas fa-times mr-1"></i>
            Cerrar
        </button>
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="fas fa-print mr-1"></i>
            Imprimir / guardar PDF
        </button>
    </div>

    <div class="rb-documento-page">
        <?php echo rb_render_documento($id); ?>
    </div>
</body>
</html>