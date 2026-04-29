<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

require_once __DIR__ . '/funciones.php';

$tabla_usuarios = us_render_table();
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-user-cog mr-1"></i>
                    Usuarios
                </h3>

                <button type="button" class="btn btn-sm btn-primary ml-auto" id="btnNuevoUsuario">
                    <i class="fas fa-plus mr-1"></i>
                    Nuevo usuario
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Administra accesos: crea usuarios, actualiza datos, cambia contraseña y activa o desactiva cuentas.
                </div>

                <div id="usuariosTablaContainer">
                    <?php echo $tabla_usuarios; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog" aria-labelledby="modalUsuarioTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formUsuario" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioTitulo">Nuevo usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="guardar_usuario">
                <input type="hidden" name="id" id="usuarioId" value="0">

                <div class="form-group">
                    <label>DNI <span class="app-required">*</span></label>
                    <input type="text" name="dni" id="usuarioDni" class="form-control" maxlength="8" inputmode="numeric" pattern="\d{8}" required>
                </div>

                <div class="form-group">
                    <label>Nombres <span class="app-required">*</span></label>
                    <input type="text" name="nombres" id="usuarioNombres" class="form-control" maxlength="120" data-char-counter="true" required>
                </div>

                <div class="form-group">
                    <label>Apellidos <span class="app-required">*</span></label>
                    <input type="text" name="apellidos" id="usuarioApellidos" class="form-control" maxlength="120" data-char-counter="true" required>
                </div>

                <div class="form-group usuario-clave-row">
                    <label>Contraseña <span class="app-required">*</span></label>
                    <div class="position-relative">
                        <input type="password" name="clave" id="usuarioClave" class="form-control pr-5" minlength="8">
                        <button type="button" class="btn btn-link app-auth-eye" data-toggle-pass="#usuarioClave" aria-label="Mostrar u ocultar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Minimo 8 caracteres.</small>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado" id="usuarioEstado" class="custom-select">
                        <option value="1">Activo</option>
                        <option value="0">Desactivado</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Guardar usuario
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUsuarioClave" tabindex="-1" role="dialog" aria-labelledby="modalUsuarioClaveTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formUsuarioClave" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioClaveTitulo">Cambiar contraseña</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="action" value="cambiar_clave_usuario">
                <input type="hidden" name="id" id="usuarioClaveId" value="0">

                <div class="form-group">
                    <label>Nueva contraseña <span class="app-required">*</span></label>
                    <div class="position-relative">
                        <input type="password" name="clave" id="usuarioNuevaClave" class="form-control pr-5" minlength="8" required>
                        <button type="button" class="btn btn-link app-auth-eye" data-toggle-pass="#usuarioNuevaClave" aria-label="Mostrar u ocultar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">Minimo 8 caracteres.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key mr-1"></i>
                    Actualizar contraseña
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalConfirmarUsuarioEstado" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarUsuarioEstadoTitulo" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalConfirmarUsuarioEstadoTitulo">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar accion
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p id="modalConfirmarUsuarioEstadoTexto" class="mb-0">Confirma la accion seleccionada.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnConfirmarUsuarioEstado">
                    <i class="fas fa-check mr-1"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
