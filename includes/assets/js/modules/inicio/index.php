<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

$db_status = app_db_status();
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info app-dashboard-card">
            <div class="inner">
                <h3>0</h3>
                <p>Proformas emitidas</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <a href="<?php echo e(module_url('proformas')); ?>" class="small-box-footer">
                Ver módulo <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success app-dashboard-card">
            <div class="inner">
                <h3><?php echo e(app_money(0)); ?></h3>
                <p>Ingresos del mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <a href="<?php echo e(module_url('recibos')); ?>" class="small-box-footer">
                Ver módulo <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning app-dashboard-card">
            <div class="inner">
                <h3>0</h3>
                <p>Pendientes por cobrar</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="<?php echo e(module_url('clientes_servicios')); ?>" class="small-box-footer">
                Ver módulo <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger app-dashboard-card">
            <div class="inner">
                <h3>0</h3>
                <p>Avisos vencidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
            <a href="<?php echo e(module_url('auditoria')); ?>" class="small-box-footer">
                Ver módulo <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-project-diagram mr-1"></i>
                    Flujo principal del sistema
                </h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                            <div>Cliente</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <i class="fas fa-tasks fa-2x text-info mb-2"></i>
                            <div>Servicios asignados</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <i class="fas fa-file-invoice-dollar fa-2x text-warning mb-2"></i>
                            <div>Proforma de pago</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <div>Confirmación de pago</div>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="border rounded p-3 mb-2">
                            <i class="fas fa-receipt fa-2x text-danger mb-2"></i>
                            <div>Recibo de pago</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mb-0 mt-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Base del sistema creada. Los módulos CRUD se implementarán en las siguientes fases.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-server mr-1"></i>
                    Estado técnico
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Autenticación:</strong>
                    <span class="badge badge-info"><?php echo e(strtoupper(auth_mode())); ?></span>
                </p>

                <p class="mb-2">
                    <strong>Base de datos:</strong>
                    <?php if ($db_status['ok']) { ?>
                        <span class="app-status-pill app-status-ok">
                            <i class="fas fa-check-circle mr-1"></i>
                            Activa
                        </span>
                    <?php } else { ?>
                        <span class="app-status-pill app-status-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Pendiente
                        </span>
                    <?php } ?>
                </p>

                <p class="text-muted mb-3">
                    <?php echo e($db_status['message']); ?>
                </p>

                <hr>

                <p class="mb-1">
                    <strong>AdminLTE:</strong> Local
                </p>
                <p class="mb-1">
                    <strong>PDO:</strong> Preparado
                </p>
                <p class="mb-0">
                    <strong>CRUD:</strong> Pendiente
                </p>
            </div>
        </div>
    </div>
</div>