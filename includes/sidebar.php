<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

$config = app_config();
$modules = app_modules();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?php echo e(app_url('index.php')); ?>" class="brand-link">
        <span class="brand-image app-brand-icon elevation-2">
            <i class="fas fa-calculator"></i>
        </span>
        <span class="brand-text font-weight-light"><?php echo e($config['app_short_name']); ?></span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <span class="app-user-avatar">
                    <i class="fas fa-user"></i>
                </span>
            </div>
            <div class="info">
                <a href="<?php echo e(app_url('index.php')); ?>" class="d-block"><?php echo e(auth_user_name()); ?></a>
                <small class="text-muted"><?php echo e(auth_user_role()); ?></small>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <?php foreach ($modules as $module_key => $module_data) { ?>
                    <li class="nav-item">
                        <a href="<?php echo e(module_url($module_key)); ?>" class="nav-link <?php echo e(app_active_class($module_key)); ?>">
                            <i class="nav-icon <?php echo e($module_data['icon']); ?>"></i>
                            <p><?php echo e($module_data['label']); ?></p>
                        </a>
                    </li>
                <?php } ?>
                <li class="nav-item mt-2">
                    <a href="<?php echo e(app_url('logout.php')); ?>" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Salir</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
