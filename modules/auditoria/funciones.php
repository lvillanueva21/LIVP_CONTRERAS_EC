<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function au_clean($value)
{
    return trim((string)$value);
}

function au_filtros($source = null)
{
    if ($source === null) {
        $source = $_REQUEST;
    }

    return array(
        'modulo' => isset($source['modulo']) ? au_clean($source['modulo']) : '',
        'accion' => isset($source['accion']) ? au_clean($source['accion']) : '',
        'tabla' => isset($source['tabla']) ? au_clean($source['tabla']) : '',
        'fecha_desde' => isset($source['fecha_desde']) ? au_clean($source['fecha_desde']) : '',
        'fecha_hasta' => isset($source['fecha_hasta']) ? au_clean($source['fecha_hasta']) : '',
        'q' => isset($source['q']) ? au_clean($source['q']) : ''
    );
}

function au_distinct($campo)
{
    $permitidos = array('modulo', 'accion', 'tabla_afectada');

    if (!in_array($campo, $permitidos, true)) {
        return array();
    }

    $pdo = app_pdo();
    $stmt = $pdo->query("
        SELECT DISTINCT {$campo} AS valor
        FROM ecc_auditoria
        WHERE {$campo} IS NOT NULL
          AND {$campo} <> ''
        ORDER BY {$campo} ASC
    ");

    return $stmt->fetchAll();
}

function au_kpis()
{
    return array(
        'total' => (int)au_scalar("SELECT COUNT(*) FROM ecc_auditoria"),
        'hoy' => (int)au_scalar("SELECT COUNT(*) FROM ecc_auditoria WHERE DATE(created_at) = CURDATE()"),
        'descargas' => (int)au_scalar("SELECT COUNT(*) FROM ecc_auditoria WHERE accion LIKE 'Descargar%'"),
        'cambios' => (int)au_scalar("SELECT COUNT(*) FROM ecc_auditoria WHERE accion NOT LIKE 'Descargar%'")
    );
}

function au_scalar($sql, $params = array())
{
    $pdo = app_pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();

    return $value === false || $value === null ? 0 : $value;
}

function au_listar($filtros = array())
{
    $pdo = app_pdo();

    $where = array();
    $params = array();

    if (!empty($filtros['modulo'])) {
        $where[] = 'modulo = :modulo';
        $params[':modulo'] = $filtros['modulo'];
    }

    if (!empty($filtros['accion'])) {
        $where[] = 'accion = :accion';
        $params[':accion'] = $filtros['accion'];
    }

    if (!empty($filtros['tabla'])) {
        $where[] = 'tabla_afectada = :tabla';
        $params[':tabla'] = $filtros['tabla'];
    }

    if (!empty($filtros['fecha_desde'])) {
        $where[] = 'created_at >= :fecha_desde';
        $params[':fecha_desde'] = $filtros['fecha_desde'] . ' 00:00:00';
    }

    if (!empty($filtros['fecha_hasta'])) {
        $where[] = 'created_at <= :fecha_hasta';
        $params[':fecha_hasta'] = $filtros['fecha_hasta'] . ' 23:59:59';
    }

    if (!empty($filtros['q'])) {
        $where[] = '(descripcion LIKE :q OR modulo LIKE :q OR accion LIKE :q OR tabla_afectada LIKE :q OR usuario_externo_id LIKE :q)';
        $params[':q'] = '%' . $filtros['q'] . '%';
    }

    $sql = "
        SELECT *
        FROM ecc_auditoria
    ";

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " ORDER BY id DESC LIMIT 500";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function au_badge_accion($accion)
{
    $accion = (string)$accion;
    $tipo = 'secondary';

    if (stripos($accion, 'Crear') !== false || stripos($accion, 'Generar') !== false) {
        $tipo = 'success';
    } elseif (stripos($accion, 'Actualizar') !== false || stripos($accion, 'Editar') !== false || stripos($accion, 'modificada') !== false) {
        $tipo = 'info';
    } elseif (stripos($accion, 'Descargar') !== false) {
        $tipo = 'primary';
    } elseif (stripos($accion, 'Anular') !== false || stripos($accion, 'Desactivar') !== false) {
        $tipo = 'danger';
    }

    return '<span class="badge badge-' . e($tipo) . '">' . e($accion) . '</span>';
}

function au_options($items, $selected = '', $label = 'Todos')
{
    $html = '<option value="">' . e($label) . '</option>';

    foreach ($items as $item) {
        $valor = $item['valor'];
        $is_selected = $valor === $selected ? ' selected' : '';
        $html .= '<option value="' . e($valor) . '"' . $is_selected . '>' . e($valor) . '</option>';
    }

    return $html;
}

function au_render_tabla($filtros = array())
{
    $items = au_listar($filtros);

    ob_start();
    ?>
    <table class="table table-sm table-bordered table-hover" data-app-table="true" data-page-length="10" data-empty-text="No hay registros de auditoría.">
        <thead>
            <tr>
                <th width="145">Fecha</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>Tabla</th>
                <th>Registro</th>
                <th>Descripción</th>
                <th>Usuario</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item) { ?>
                <tr>
                    <td>
                        <strong><?php echo e(date('d/m/Y', strtotime($item['created_at']))); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo e(date('H:i:s', strtotime($item['created_at']))); ?></small>
                    </td>
                    <td><?php echo e($item['modulo']); ?></td>
                    <td><?php echo au_badge_accion($item['accion']); ?></td>
                    <td><?php echo trim((string)$item['tabla_afectada']) !== '' ? e($item['tabla_afectada']) : '<span class="text-muted">---</span>'; ?></td>
                    <td><?php echo $item['registro_id'] !== null ? e($item['registro_id']) : '<span class="text-muted">---</span>'; ?></td>
                    <td><?php echo e($item['descripcion']); ?></td>
                    <td><?php echo trim((string)$item['usuario_externo_id']) !== '' ? e($item['usuario_externo_id']) : '<span class="text-muted">demo</span>'; ?></td>
                    <td><?php echo trim((string)$item['ip']) !== '' ? e($item['ip']) : '<span class="text-muted">---</span>'; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}