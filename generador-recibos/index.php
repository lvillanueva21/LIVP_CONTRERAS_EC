<?php
$nombreSistema = 'Generador de Recibos Personalizable';
$subtituloSistema = 'Estudio Contable Contreras';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($nombreSistema, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($subtituloSistema, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>

<div class="app-layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar__brand">
            <div class="sidebar__logo">EC</div>
            <div>
                <h1>Contreras</h1>
                <p>Recibos</p>
            </div>
        </div>

        <nav class="sidebar__nav">
            <button type="button" class="sidebar__link activo" data-section="inicio">
                <span>🏠</span>
                <strong>Inicio</strong>
            </button>

            <button type="button" class="sidebar__link" data-section="generar-recibo">
                <span>🧾</span>
                <strong>Generar recibo</strong>
            </button>

            <button type="button" class="sidebar__link" data-section="plantillas">
                <span>🎨</span>
                <strong>Plantillas</strong>
            </button>

            <button type="button" class="sidebar__link" data-section="datos-base">
                <span>📚</span>
                <strong>Datos base</strong>
            </button>

            <button type="button" class="sidebar__link" data-section="personalizacion">
                <span>⚙️</span>
                <strong>Personalización</strong>
            </button>

            <button type="button" class="sidebar__link" data-section="auditoria">
                <span>🕒</span>
                <strong>Auditoría</strong>
            </button>
        </nav>

        <div class="sidebar__footer">
            <span class="badge badge--success">Demo temporal</span>
            <p>Sin login, sin MySQL, sin persistencia real.</p>
        </div>
    </aside>

    <main class="main">

        <header class="topbar">
            <button type="button" class="btn-icon solo-mobile" id="btnAbrirSidebar" aria-label="Abrir menú">
                ☰
            </button>

            <div>
                <h2><?php echo htmlspecialchars($nombreSistema, ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars($subtituloSistema, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="topbar__actions">
                <button
                    type="button"
                    class="btn-icon btn-icon--topbar"
                    id="btnColapsarSidebar"
                    title="Contraer menú"
                    aria-label="Contraer menú"
                    aria-pressed="false"
                >
                    <span class="sidebar-collapse-icon" aria-hidden="true">‹</span>
                </button>

                <button type="button" class="btn btn--light" id="btnDemoNotificacion">
                    Probar notificación
                </button>
            </div>
        </header>

        <section class="content">

            <?php include __DIR__ . '/modulos/inicio.php'; ?>

            <?php include __DIR__ . '/modulos/generar_recibo.php'; ?>

            <?php include __DIR__ . '/modulos/plantillas.php'; ?>

            <?php include __DIR__ . '/modulos/datos_base.php'; ?>

            <?php include __DIR__ . '/modulos/personalizacion.php'; ?>

            <?php include __DIR__ . '/modulos/auditoria.php'; ?>

        </section>

    </main>

</div>

<div class="overlay-mobile" id="overlayMobile"></div>

<div class="toast-container" id="toastContainer"></div>

<div class="modal" id="modalDemo">
    <div class="modal__dialog">
        <div class="modal__header">
            <div>
                <h3>Ventana modal demo</h3>
                <p>Este es el estilo base que se usará en todo el sistema.</p>
            </div>

            <button type="button" class="modal__close" data-close-modal="modalDemo">
                ×
            </button>
        </div>

        <div class="modal__body">
            <p>
                Más adelante esta misma ventana se usará para crear clientes, servicios,
                cuentas, plantillas y configuraciones del recibo.
            </p>

            <div class="form-grid">
                <div class="form-group">
                    <label>Campo de ejemplo</label>
                    <input type="text" class="form-control" value="Dato temporal">
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select class="form-control">
                        <option>Activo</option>
                        <option>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="modal__footer">
            <button type="button" class="btn btn--light" data-close-modal="modalDemo">
                Cancelar
            </button>
            <button type="button" class="btn btn--primary" data-close-modal="modalDemo">
                Guardar demo
            </button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/datos_base.js"></script>
<script src="assets/js/personalizacion_plantillas.js"></script>
<script src="assets/js/sidebar_colapsable.js"></script>
<script src="assets/js/generador_recibos.js"></script>
<script src="assets/js/exportacion_recibos.js"></script>
</body>
</html>