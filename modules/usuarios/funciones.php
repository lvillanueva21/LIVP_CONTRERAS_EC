<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function us_external_id()
{
    $user = auth_user();
    return isset($user['mode']) && $user['mode'] !== '' ? $user['mode'] : 'sistema';
}

function us_listar()
{
    $pdo = app_pdo();

    $stmt = $pdo->query("
        SELECT
            id,
            dni,
            nombres,
            apellidos,
            usuario,
            rol,
            estado,
            ultimo_login_at,
            created_at,
            updated_at
        FROM ecc_usuarios
        ORDER BY id DESC
    ");

    return $stmt->fetchAll();
}

function us_obtener($id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        SELECT
            id,
            dni,
            nombres,
            apellidos,
            usuario,
            rol,
            estado,
            ultimo_login_at,
            created_at,
            updated_at
        FROM ecc_usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute(array(':id' => (int)$id));
    return $stmt->fetch();
}

function us_badge_estado($estado)
{
    if ((int)$estado === 1) {
        return '<span class="badge badge-success">Activo</span>';
    }

    return '<span class="badge badge-secondary">Desactivado</span>';
}

function us_fecha_hora($value)
{
    $value = trim((string)$value);

    if ($value === '' || $value === '0000-00-00 00:00:00') {
        return '<span class="text-muted">Sin acceso</span>';
    }

    return e(date('d/m/Y H:i', strtotime($value)));
}

function us_nombre_completo($usuario)
{
    return trim((string)$usuario['nombres'] . ' ' . (string)$usuario['apellidos']);
}

function us_render_table()
{
    $usuarios = us_listar();

    ob_start();
    ?>
    <table class="table table-sm" data-app-table="true" data-page-length="10" data-empty-text="No hay usuarios registrados.">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creacion</th>
                <th>Ultimo acceso</th>
                <th width="140">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario) { ?>
                <tr data-id="<?php echo e($usuario['id']); ?>">
                    <td>
                        <strong><?php echo e(us_nombre_completo($usuario)); ?></strong>
                        <br>
                        <small class="text-muted">DNI: <?php echo e($usuario['dni']); ?></small>
                    </td>
                    <td><?php echo e($usuario['rol']); ?></td>
                    <td><?php echo us_badge_estado($usuario['estado']); ?></td>
                    <td><?php echo us_fecha_hora($usuario['created_at']); ?></td>
                    <td><?php echo us_fecha_hora($usuario['ultimo_login_at']); ?></td>
                    <td>
                        <div class="app-action-buttons">
                            <button type="button" class="btn btn-sm btn-primary btnEditarUsuario" data-id="<?php echo e($usuario['id']); ?>" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-dark btnCambiarClaveUsuario" data-id="<?php echo e($usuario['id']); ?>" title="Cambiar contraseña">
                                <i class="fas fa-key"></i>
                            </button>
                            <button type="button" class="btn btn-sm <?php echo (int)$usuario['estado'] === 1 ? 'btn-danger' : 'btn-success'; ?> btnCambiarEstadoUsuario" data-id="<?php echo e($usuario['id']); ?>" data-estado="<?php echo e($usuario['estado']); ?>" title="Cambiar estado">
                                <i class="fas <?php echo (int)$usuario['estado'] === 1 ? 'fa-toggle-off' : 'fa-toggle-on'; ?>"></i>
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

function us_auditoria($accion, $tabla, $registro_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_auditoria
        (modulo, accion, tabla_afectada, registro_id, descripcion, datos_anteriores, datos_nuevos, ip, user_agent, usuario_externo_id, created_by_external_id)
        VALUES
        (:modulo, :accion, :tabla_afectada, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip, :user_agent, :usuario_externo_id, :created_by_external_id)
    ");

    $stmt->execute(array(
        ':modulo' => 'Usuarios',
        ':accion' => $accion,
        ':tabla_afectada' => $tabla,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':datos_anteriores' => $datos_anteriores !== null ? json_encode($datos_anteriores, JSON_UNESCAPED_UNICODE) : null,
        ':datos_nuevos' => $datos_nuevos !== null ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
        ':ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
        ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ':usuario_externo_id' => us_external_id(),
        ':created_by_external_id' => us_external_id()
    ));
}
