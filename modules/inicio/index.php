<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$db_status = app_db_status();
$resumen = dash_resumen();
$avisos = dash_proximos_avisos();
$vencidos = dash_servicios_vencidos();
$auditoria = dash_auditoria_reciente();
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success app-dashboard-card">
            <div class="inner">
                <h3 id="dashIngresosHoy"><?php echo e(app_money($resumen['ingresos_hoy'])); ?></h3>
                <p>Ingresos de hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <a href="<?php echo e(module_url('recibos')); ?>" class="small-box-footer">
                Ver recibos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info app-dashboard-card">
            <div class="inner">
                <h3 id="dashIngresosMes"><?php echo e(app_money($resumen['ingresos_mes'])); ?></h3>
                <p>Ingresos del mes</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <a href="<?php echo e(module_url('recibos')); ?>" class="small-box-footer">
                Ver ingresos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning app-dashboard-card">
            <div class="inner">
                <h3 id="dashPendientes"><?php echo e(app_money($resumen['pendientes'])); ?></h3>
                <p>Pendientes por cobrar</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="<?php echo e(module_url('proformas')); ?>" class="small-box-footer">
                Ver proformas <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger app-dashboard-card">
            <div class="inner">
                <h3 id="dashVencidos"><?php echo e($resumen['vencidos']); ?></h3>
                <p>Vencidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="<?php echo e(module_url('clientes_servicios')); ?>" class="small-box-footer">
                Ver servicios <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Proformas emitidas</span>
                <span class="info-box-number" id="dashProformasEmitidas"><?php echo e($resumen['proformas_emitidas']); ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Recibos emitidos</span>
                <span class="info-box-number" id="dashRecibosEmitidos"><?php echo e($resumen['recibos_emitidos']); ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-indigo"><i class="fas fa-bell"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Próximos avisos</span>
                <span class="info-box-number" id="dashProximosAvisos"><?php echo e($resumen['proximos_avisos']); ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-secondary"><i class="fas fa-database"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Base de datos</span>
                <span class="info-box-number">
                    <?php echo $db_status['ok'] ? 'Activa' : 'Revisar'; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-chart-area mr-1"></i>
                    Gráfico de ingresos
                </h3>
                <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="btnActualizarDashboard">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Actualizar
                </button>
            </div>
            <div class="card-body">
                <canvas id="dashboardIngresosChart" height="115"></canvas>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-1"></i>
                    Auditoría reciente
                </h3>
            </div>
            <div class="card-body" id="dashboardAuditoriaContainer">
                <?php echo dash_render_auditoria($auditoria); ?>
            </div>
            <div class="card-footer text-right">
                <a href="<?php echo e(module_url('auditoria')); ?>" class="btn btn-sm btn-secondary">
                    Ver auditoría completa
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bell mr-1"></i>
                    Próximos avisos de cobro
                </h3>
            </div>
            <div class="card-body p-0" id="dashboardAvisosContainer">
                <?php echo dash_render_avisos($avisos); ?>
            </div>
        </div>

        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Vencidos
                </h3>
            </div>
            <div class="card-body p-0" id="dashboardVencidosContainer">
                <?php echo dash_render_vencidos($vencidos); ?>
            </div>
        </div>

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

                <p class="text-muted mb-0">
                    <?php echo e($db_status['message']); ?>
                </p>
            </div>
        </div>
    </div>
</div>