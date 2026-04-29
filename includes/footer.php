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
<?php if (isset($app_current_module) && $app_current_module === 'inicio') { ?>
<script src="<?php echo e(asset_url('plugins/chart.js/Chart.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/dashboard.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && in_array($app_current_module, array('proformas', 'recibos'), true)) { ?>
<script src="<?php echo e(asset_url('plugins/html2canvas/html2canvas.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('plugins/jspdf/jspdf.umd.min.js')); ?>"></script>
<script src="<?php echo e(asset_url('assets/js/exportador.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'clientes_servicios') { ?>
<script src="<?php echo e(asset_url('assets/js/clientes_servicios.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'metodos_pago') { ?>
<script src="<?php echo e(asset_url('assets/js/metodos_pago.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'personalizacion') { ?>
<script src="<?php echo e(asset_url('assets/js/personalizacion.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'plantillas') { ?>
<script src="<?php echo e(asset_url('assets/js/plantillas.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'proformas') { ?>
<script src="<?php echo e(asset_url('assets/js/proformas.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'recibos') { ?>
<script src="<?php echo e(asset_url('assets/js/recibos.js')); ?>"></script>
<?php } ?>
<?php if (isset($app_current_module) && $app_current_module === 'auditoria') { ?>
<script src="<?php echo e(asset_url('assets/js/auditoria.js')); ?>"></script>
<?php } ?>
</body>
</html>