<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

$config = app_config();
?>

<footer class="main-footer">
    <strong><?php echo e($config['app_name']); ?></strong>
    <span class="d-none d-sm-inline"> | Sistema administrativo contable</span>
    <div class="float-right d-none d-sm-inline-block">
        <b>Versión</b> <?php echo e($config['app_version']); ?>
    </div>
</footer>

<aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script src="<?php echo e(asset_url('plugins/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('plugins/toastr/toastr.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('dist/js/adminlte.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/app.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/ui.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/ajax.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/tablas.js')); ?>"></script>
</body>
</html>