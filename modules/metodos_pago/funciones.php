<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function mp_external_id()
{
    $user = auth_user();
    return isset($user['mode']) && $user['mode'] !== '' ? $user['mode'] : 'demo';
}

function mp_listar()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT *
        FROM ecc_metodos_pago
        ORDER BY orden ASC, id DESC
    ");

    return $stmt->fetchAll();
}

function mp_obtener($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_metodos_pago WHERE id = :id LIMIT 1");
    $stmt->execute(array(':id' => (int)$id));

    return $stmt->fetch();
}

function mp_badge_tipo($tipo)
{
    $map = array(
        'Cuenta de ahorro' => 'primary',
        'Yape' => 'success',
        'Plin' => 'info'
    );

    $badge = isset($map[$tipo]) ? $map[$tipo] : 'secondary';

    return '<span class="badge badge-' . e($badge) . '">' . e($tipo) . '</span>';
}

function mp_badge_estado($estado)
{
    if ((int)$estado === 1) {
        return '<span class="badge badge-success">Activo</span>';
    }

    return '<span class="badge badge-secondary">Inactivo</span>';
}

function mp_render_table()
{
    $metodos = mp_listar();

    ob_start();
    ?>
    <table class="table table-sm" data-app-table="true" data-page-length="10" data-empty-text="No hay métodos de pago registrados.">
        <thead>
            <tr>
                <th>Título visible</th>
                <th>Tipo</th>
                <th>Datos</th>
                <th>Titular</th>
                <th>Estado</th>
                <th width="130">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($metodos as $metodo) { ?>
                <tr data-id="<?php echo e($metodo['id']); ?>">
                    <td>
                        <strong><?php echo e($metodo['titulo_visible']); ?></strong>
                        <?php if (trim((string)$metodo['descripcion']) !== '') { ?>
                            <br>
                            <small class="text-muted"><?php echo e($metodo['descripcion']); ?></small>
                        <?php } ?>
                    </td>
                    <td><?php echo mp_badge_tipo($metodo['tipo']); ?></td>
                    <td>
                        <?php if ($metodo['tipo'] === 'Cuenta de ahorro') { ?>
                            <div><strong>Banco:</strong> <?php echo e($metodo['banco']); ?></div>
                            <div><strong>Cuenta:</strong> <?php echo e($metodo['numero_cuenta']); ?></div>
                            <div><strong>CCI:</strong> <?php echo trim((string)$metodo['cci']) !== '' ? e($metodo['cci']) : '<span class="text-muted">No registrado</span>'; ?></div>
                        <?php } else { ?>
                            <div><strong>Celular:</strong> <?php echo e($metodo['numero_celular']); ?></div>
                        <?php } ?>
                    </td>
                    <td><?php echo e($metodo['titular']); ?></td>
                    <td><?php echo mp_badge_estado($metodo['estado']); ?></td>
                    <td>
                        <div class="app-action-buttons">
                            <button type="button" class="btn btn-sm btn-primary btnEditarMetodoPago" data-id="<?php echo e($metodo['id']); ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm <?php echo (int)$metodo['estado'] === 1 ? 'btn-danger' : 'btn-success'; ?> btnCambiarEstadoMetodoPago" data-id="<?php echo e($metodo['id']); ?>" data-estado="<?php echo e($metodo['estado']); ?>" title="Cambiar estado">
                                <i class="fas <?php echo (int)$metodo['estado'] === 1 ? 'fa-ban' : 'fa-check'; ?>"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function mp_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Métodos de pago',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => mp_external_id(),
        ':created_by_external_id' => mp_external_id()
    ));
}