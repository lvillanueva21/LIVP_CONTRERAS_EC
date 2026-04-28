<?php
define('APP_BOOTSTRAP', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth_stub.php';
require_once __DIR__ . '/includes/conexion.php';

$app_current_module = app_current_module();
$app_modules = app_modules();
$app_page_title = isset($app_modules[$app_current_module]) ? $app_modules[$app_current_module]['label'] : 'Módulo no encontrado';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

$module_file = __DIR__ . '/modules/' . $app_current_module . '/index.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo e($app_page_title); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(app_url('index.php')); ?>">Inicio</a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo e($app_page_title); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php
            if (is_file($module_file)) {
                require $module_file;
            } else {
                ?>
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="<?php echo e($app_modules[$app_current_module]['icon']); ?> mr-1"></i>
                            <?php echo e($app_page_title); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="empty-state-box">
                            <div class="empty-state-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h4>Módulo en preparación</h4>
                            <p class="mb-0">
                                Este módulo ya está reservado en el menú final, pero su programación corresponde a una fase posterior.
                            </p>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';