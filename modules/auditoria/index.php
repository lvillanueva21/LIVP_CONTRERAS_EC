<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$filtros = au_filtros();
$kpis = au_kpis();
$modulos = au_distinct('modulo');
$acciones = au_distinct('accion');
$tablas = au_distinct('tabla_afectada');
$tabla_html = au_render_tabla($filtros);
?>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-shield-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total registros</span>
                <span class="info-box-number" id="audKpiTotal"><?php echo e($kpis['total']); ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-calendar-day"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Hoy</span>
                <span class="info-box-number" id="audKpiHoy"><?php echo e($kpis['hoy']); ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-download"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Descargas</span>
                <span class="info-box-number" id="audKpiDescargas"><?php echo e($kpis['descargas']); ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-edit"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cambios</span>
                <span class="info-box-number" id="audKpiCambios"><?php echo e($kpis['cambios']); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-1"></i>
            Filtros de auditoría
        </h3>
    </div>

    <form id="formAuditoriaFiltros" class="card-body" autocomplete="off">
        <input type="hidden" name="action" value="listar">

        <div class="form-row">
            <div class="form-group col-md-2">
                <label>Módulo</label>
                <select name="modulo" id="auditoriaModulo" class="custom-select">
                    <?php echo au_options($modulos, $filtros['modulo'], 'Todos'); ?>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label>Acción</label>
                <select name="accion" id="auditoriaAccion" class="custom-select">
                    <?php echo au_options($acciones, $filtros['accion'], 'Todas'); ?>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label>Tabla</label>
                <select name="tabla" id="auditoriaTabla" class="custom-select">
                    <?php echo au_options($tablas, $filtros['tabla'], 'Todas'); ?>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label>Desde</label>
                <input type="date" name="fecha_desde" id="auditoriaFechaDesde" class="form-control" value="<?php echo e($filtros['fecha_desde']); ?>">
            </div>

            <div class="form-group col-md-2">
                <label>Hasta</label>
                <input type="date" name="fecha_hasta" id="auditoriaFechaHasta" class="form-control" value="<?php echo e($filtros['fecha_hasta']); ?>">
            </div>

            <div class="form-group col-md-2">
                <label>Buscar</label>
                <input type="text" name="q" id="auditoriaQ" class="form-control" value="<?php echo e($filtros['q']); ?>" placeholder="Texto">
            </div>
        </div>

        <div class="text-right">
            <button type="button" class="btn btn-secondary" id="btnLimpiarAuditoria">
                <i class="fas fa-eraser mr-1"></i>
                Limpiar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i>
                Filtrar
            </button>
        </div>
    </form>
</div>

<div class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Registros de auditoría
        </h3>
    </div>
    <div class="card-body" id="auditoriaTablaContainer">
        <?php echo $tabla_html; ?>
    </div>
</div>