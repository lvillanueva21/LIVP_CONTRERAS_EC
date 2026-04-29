/* ============================================================
   ESTUDIO CONTABLE CONTRERAS
   Archivo maestro de base de datos
   Proyecto: LIVP_CONTRERAS_EC
   Motor objetivo: MySQL / MariaDB
   Codificación recomendada: utf8mb4
   Fecha de creación inicial: 2026-04-28
   ============================================================ */


/* ============================================================
   1. ENCABEZADO DEL PROYECTO
   ============================================================

   Sistema final del Estudio Contable Contreras.

   Stack definido:
   - PHP
   - MySQL
   - PDO
   - AJAX
   - AdminLTE V3 local
   - Sin Composer
   - Sin frameworks
   - Sin CDN para estilos ni librerías críticas

   Flujo principal:
   Cliente -> Servicios asignados -> Proforma de pago -> Confirmación de pago -> Recibo de pago

   Reglas SQL:
   - Todo CREATE, ALTER, INSERT, UPDATE, DELETE o cambio de estructura/datos debe registrarse en este archivo.
   - Si una tabla cambia, también debe actualizarse su CREATE TABLE completo en el esquema actual completo.
   - Las querys del sistema y las querys de testeo deben mantenerse separadas.
   - No ejecutar datos de prueba en producción real salvo decisión explícita.
*/


/* ============================================================
   2. ESQUEMA ACTUAL COMPLETO
   QUERYS PARA EL SISTEMA
   ============================================================ */

CREATE TABLE IF NOT EXISTS `ecc_clientes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo_cliente` ENUM('Empresa','Persona natural') NOT NULL DEFAULT 'Empresa',
    `documento_tipo` ENUM('RUC','DNI','CE','PASAPORTE') NOT NULL DEFAULT 'RUC',
    `numero_documento` VARCHAR(20) NOT NULL,
    `razon_social` VARCHAR(180) NULL,
    `nombre_comercial` VARCHAR(180) NULL,
    `nombres` VARCHAR(120) NULL,
    `apellidos` VARCHAR(120) NULL,
    `direccion` VARCHAR(255) NULL,
    `correo` VARCHAR(120) NULL,
    `celular` VARCHAR(30) NULL,
    `observacion` TEXT NULL,
    `estado` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_clientes_documento` (`documento_tipo`,`numero_documento`),
    KEY `idx_ecc_clientes_tipo_cliente` (`tipo_cliente`),
    KEY `idx_ecc_clientes_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_servicios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(180) NOT NULL,
    `descripcion` TEXT NULL,
    `precio_base` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_servicios_nombre` (`nombre`),
    KEY `idx_ecc_servicios_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_etiquetas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(80) NOT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#6c757d',
    `estado` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_etiquetas_nombre` (`nombre`),
    KEY `idx_ecc_etiquetas_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_servicio_etiquetas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `servicio_id` INT UNSIGNED NOT NULL,
    `etiqueta_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_servicio_etiquetas` (`servicio_id`,`etiqueta_id`),
    KEY `idx_ecc_servicio_etiquetas_servicio` (`servicio_id`),
    KEY `idx_ecc_servicio_etiquetas_etiqueta` (`etiqueta_id`),
    CONSTRAINT `fk_ecc_servicio_etiquetas_servicio`
        FOREIGN KEY (`servicio_id`) REFERENCES `ecc_servicios` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ecc_servicio_etiquetas_etiqueta`
        FOREIGN KEY (`etiqueta_id`) REFERENCES `ecc_etiquetas` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_metodos_pago` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo_visible` VARCHAR(120) NOT NULL DEFAULT '',
    `tipo` ENUM('Cuenta de ahorro','Yape','Plin') NOT NULL,
    `titular` VARCHAR(180) NOT NULL,
    `banco` VARCHAR(120) NULL,
    `numero_cuenta` VARCHAR(80) NULL,
    `cci` VARCHAR(80) NULL,
    `numero_celular` VARCHAR(30) NULL,
    `descripcion` VARCHAR(180) NULL,
    `orden` INT UNSIGNED NOT NULL DEFAULT 1,
    `estado` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_metodos_pago_tipo` (`tipo`),
    KEY `idx_ecc_metodos_pago_estado` (`estado`),
    KEY `idx_ecc_metodos_pago_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_archivos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `categoria` VARCHAR(80) NOT NULL,
    `nombre_original` VARCHAR(255) NOT NULL,
    `nombre_guardado` VARCHAR(255) NOT NULL,
    `extension` VARCHAR(20) NULL,
    `mime_type` VARCHAR(120) NULL,
    `tamanio_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `ruta_relativa` VARCHAR(255) NOT NULL,
    `tabla_referencia` VARCHAR(80) NULL,
    `registro_id` INT UNSIGNED NULL,
    `descripcion` VARCHAR(255) NULL,
    `estado` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_archivos_categoria` (`categoria`),
    KEY `idx_ecc_archivos_referencia` (`tabla_referencia`,`registro_id`),
    KEY `idx_ecc_archivos_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_configuracion_empresa` (
    `id` TINYINT UNSIGNED NOT NULL,
    `ruc` VARCHAR(20) NOT NULL,
    `razon_social` VARCHAR(180) NOT NULL,
    `nombre_comercial` VARCHAR(180) NULL,
    `rubro` VARCHAR(180) NULL,
    `direccion` VARCHAR(255) NULL,
    `correo` VARCHAR(120) NULL,
    `celular` VARCHAR(30) NULL,
    `logo_archivo_id` INT UNSIGNED NULL,
    `logo_tipo` ENUM('Cuadrado','Rectangular','Banner') NOT NULL DEFAULT 'Rectangular',
    `logo_zoom` DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    `logo_pos_x` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `logo_pos_y` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `color_tipo` ENUM('Solido','Degradado') NOT NULL DEFAULT 'Solido',
    `color_primario` VARCHAR(20) NOT NULL DEFAULT '#1f4e79',
    `color_secundario` VARCHAR(20) NULL,
    `pie_pagina` TEXT NULL,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_configuracion_empresa_logo` (`logo_archivo_id`),
    CONSTRAINT `fk_ecc_configuracion_empresa_logo`
        FOREIGN KEY (`logo_archivo_id`) REFERENCES `ecc_archivos` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ecc_plantillas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(120) NOT NULL,
    `descripcion` TEXT NULL,
    `orientacion` ENUM('Vertical','Horizontal') NOT NULL DEFAULT 'Vertical',
    `logo_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `logo_tipo` ENUM('Cuadrado','Rectangular','Banner') NOT NULL DEFAULT 'Rectangular',
    `datos_empresa_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `datos_cliente_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `color_tipo` ENUM('Solido','Degradado') NOT NULL DEFAULT 'Solido',
    `color_primario` VARCHAR(20) NOT NULL DEFAULT '#1f4e79',
    `color_secundario` VARCHAR(20) NULL,
    `pie_pagina_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `pie_pagina` TEXT NULL,
    `es_predeterminada` TINYINT(1) NOT NULL DEFAULT 0,
    `estado` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_plantillas_nombre` (`nombre`),
    KEY `idx_ecc_plantillas_estado` (`estado`),
    KEY `idx_ecc_plantillas_predeterminada` (`es_predeterminada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_plantilla_metodos_pago` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `plantilla_id` INT UNSIGNED NOT NULL,
    `metodo_pago_id` INT UNSIGNED NOT NULL,
    `mostrar` TINYINT(1) NOT NULL DEFAULT 1,
    `orden` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_plantilla_metodos_pago` (`plantilla_id`,`metodo_pago_id`),
    KEY `idx_ecc_plantilla_metodos_pago_plantilla` (`plantilla_id`),
    KEY `idx_ecc_plantilla_metodos_pago_metodo` (`metodo_pago_id`),
    CONSTRAINT `fk_ecc_plantilla_metodos_pago_plantilla`
        FOREIGN KEY (`plantilla_id`) REFERENCES `ecc_plantillas` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ecc_plantilla_metodos_pago_metodo`
        FOREIGN KEY (`metodo_pago_id`) REFERENCES `ecc_metodos_pago` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_cliente_servicios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cliente_id` INT UNSIGNED NOT NULL,
    `servicio_id` INT UNSIGNED NOT NULL,
    `descripcion_personalizada` TEXT NULL,
    `periodo` VARCHAR(60) NULL,
    `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `bloque_documento` ENUM('Actuales','Pendientes de pago','Otros servicios o trámites') NOT NULL DEFAULT 'Actuales',
    `estado` ENUM('Pendiente','En proforma','Pagado','Anulado') NOT NULL DEFAULT 'Pendiente',
    `fecha_asignacion` DATE NOT NULL,
    `fecha_vencimiento` DATE NULL,
    `fecha_aviso` DATE NULL,
    `modo_aviso` ENUM('Sin aviso','Fecha exacta','Antes de vencer','Manual') NOT NULL DEFAULT 'Sin aviso',
    `observacion` TEXT NULL,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_cliente_servicios_cliente` (`cliente_id`),
    KEY `idx_ecc_cliente_servicios_servicio` (`servicio_id`),
    KEY `idx_ecc_cliente_servicios_estado` (`estado`),
    KEY `idx_ecc_cliente_servicios_vencimiento` (`fecha_vencimiento`),
    KEY `idx_ecc_cliente_servicios_fecha_aviso` (`fecha_aviso`),
    KEY `idx_ecc_cliente_servicios_modo_aviso` (`modo_aviso`),
    CONSTRAINT `fk_ecc_cliente_servicios_cliente`
        FOREIGN KEY (`cliente_id`) REFERENCES `ecc_clientes` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_ecc_cliente_servicios_servicio`
        FOREIGN KEY (`servicio_id`) REFERENCES `ecc_servicios` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_proformas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL,
    `cliente_id` INT UNSIGNED NOT NULL,
    `plantilla_id` INT UNSIGNED NULL,
    `fecha_emision` DATE NOT NULL,
    `fecha_vencimiento` DATE NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `descuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('Borrador','Emitida','Parcial','Convertida','Anulada') NOT NULL DEFAULT 'Emitida',
    `observacion` TEXT NULL,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_proformas_codigo` (`codigo`),
    KEY `idx_ecc_proformas_cliente` (`cliente_id`),
    KEY `idx_ecc_proformas_plantilla` (`plantilla_id`),
    KEY `idx_ecc_proformas_estado` (`estado`),
    KEY `idx_ecc_proformas_fecha_emision` (`fecha_emision`),
    CONSTRAINT `fk_ecc_proformas_cliente`
        FOREIGN KEY (`cliente_id`) REFERENCES `ecc_clientes` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_ecc_proformas_plantilla`
        FOREIGN KEY (`plantilla_id`) REFERENCES `ecc_plantillas` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_proforma_detalles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `proforma_id` INT UNSIGNED NOT NULL,
    `cliente_servicio_id` INT UNSIGNED NULL,
    `tipo_item` ENUM('Servicio','Manual') NOT NULL DEFAULT 'Servicio',
    `bloque` ENUM('Actuales','Pendientes de pago','Otros servicios o trámites') NOT NULL DEFAULT 'Actuales',
    `descripcion` TEXT NOT NULL,
    `cantidad` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    `precio_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('Pendiente','Pagado','Anulado') NOT NULL DEFAULT 'Pendiente',
    `orden` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_proforma_detalles_proforma` (`proforma_id`),
    KEY `idx_ecc_proforma_detalles_cliente_servicio` (`cliente_servicio_id`),
    KEY `idx_ecc_proforma_detalles_bloque` (`bloque`),
    KEY `idx_ecc_proforma_detalles_estado` (`estado`),
    CONSTRAINT `fk_ecc_proforma_detalles_proforma`
        FOREIGN KEY (`proforma_id`) REFERENCES `ecc_proformas` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ecc_proforma_detalles_cliente_servicio`
        FOREIGN KEY (`cliente_servicio_id`) REFERENCES `ecc_cliente_servicios` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_recibos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL,
    `proforma_id` INT UNSIGNED NULL,
    `cliente_id` INT UNSIGNED NOT NULL,
    `plantilla_id` INT UNSIGNED NULL,
    `metodo_pago_id` INT UNSIGNED NULL,
    `fecha_emision` DATE NOT NULL,
    `fecha_pago` DATE NOT NULL,
    `total_proforma` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_pagado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `saldo_pendiente` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('Emitido','Anulado') NOT NULL DEFAULT 'Emitido',
    `observacion` TEXT NULL,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `updated_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_ecc_recibos_codigo` (`codigo`),
    KEY `idx_ecc_recibos_proforma` (`proforma_id`),
    KEY `idx_ecc_recibos_cliente` (`cliente_id`),
    KEY `idx_ecc_recibos_plantilla` (`plantilla_id`),
    KEY `idx_ecc_recibos_metodo_pago` (`metodo_pago_id`),
    KEY `idx_ecc_recibos_estado` (`estado`),
    KEY `idx_ecc_recibos_fecha_pago` (`fecha_pago`),
    CONSTRAINT `fk_ecc_recibos_proforma`
        FOREIGN KEY (`proforma_id`) REFERENCES `ecc_proformas` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_ecc_recibos_cliente`
        FOREIGN KEY (`cliente_id`) REFERENCES `ecc_clientes` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_ecc_recibos_plantilla`
        FOREIGN KEY (`plantilla_id`) REFERENCES `ecc_plantillas` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_ecc_recibos_metodo_pago`
        FOREIGN KEY (`metodo_pago_id`) REFERENCES `ecc_metodos_pago` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_recibo_detalles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `recibo_id` INT UNSIGNED NOT NULL,
    `proforma_detalle_id` INT UNSIGNED NULL,
    `cliente_servicio_id` INT UNSIGNED NULL,
    `bloque` ENUM('Actuales','Pendientes de pago','Otros servicios o trámites') NOT NULL DEFAULT 'Actuales',
    `descripcion` TEXT NOT NULL,
    `monto_original` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `monto_pagado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado_servicio_resultante` ENUM('Pendiente','Pagado','Anulado') NOT NULL DEFAULT 'Pagado',
    `orden` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_recibo_detalles_recibo` (`recibo_id`),
    KEY `idx_ecc_recibo_detalles_proforma_detalle` (`proforma_detalle_id`),
    KEY `idx_ecc_recibo_detalles_cliente_servicio` (`cliente_servicio_id`),
    CONSTRAINT `fk_ecc_recibo_detalles_recibo`
        FOREIGN KEY (`recibo_id`) REFERENCES `ecc_recibos` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_ecc_recibo_detalles_proforma_detalle`
        FOREIGN KEY (`proforma_detalle_id`) REFERENCES `ecc_proforma_detalles` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_ecc_recibo_detalles_cliente_servicio`
        FOREIGN KEY (`cliente_servicio_id`) REFERENCES `ecc_cliente_servicios` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ecc_auditoria` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `modulo` VARCHAR(80) NOT NULL,
    `accion` VARCHAR(80) NOT NULL,
    `tabla_afectada` VARCHAR(80) NULL,
    `registro_id` INT UNSIGNED NULL,
    `descripcion` TEXT NULL,
    `datos_anteriores` LONGTEXT NULL,
    `datos_nuevos` LONGTEXT NULL,
    `ip` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `usuario_externo_id` VARCHAR(80) NULL,
    `created_by_external_id` VARCHAR(80) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ecc_auditoria_modulo` (`modulo`),
    KEY `idx_ecc_auditoria_accion` (`accion`),
    KEY `idx_ecc_auditoria_tabla_registro` (`tabla_afectada`,`registro_id`),
    KEY `idx_ecc_auditoria_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/* ============================================================
   3. HISTORIAL DE CAMBIOS SQL
   ============================================================

   2026-04-28 - FASE 2
   - Se crea el archivo maestro base_datos.sql.
   - Se deja preparada la estructura obligatoria del archivo.
   - No se crean tablas.
   - No se insertan datos.

   2026-04-28 - FASE 3
   - Se crea la base de datos inicial del sistema.
   - Se agregan las tablas:
     ecc_clientes
     ecc_servicios
     ecc_etiquetas
     ecc_servicio_etiquetas
     ecc_cliente_servicios
     ecc_metodos_pago
     ecc_configuracion_empresa
     ecc_plantillas
     ecc_plantilla_metodos_pago
     ecc_proformas
     ecc_proforma_detalles
     ecc_recibos
     ecc_recibo_detalles
     ecc_auditoria
     ecc_archivos
   - Se agregan claves primarias, índices y relaciones principales.
   - Se agregan campos de preparación para usuario externo futuro.
   - Se agregan datos de prueba separados.


      2026-04-28 - FASE 5
   - Se implementa el módulo Clientes y servicios.
   - Se actualiza ecc_cliente_servicios con:
     periodo
     fecha_aviso
     modo_aviso
   - Se agregan los índices:
     idx_ecc_cliente_servicios_fecha_aviso
     idx_ecc_cliente_servicios_modo_aviso
   - Se mantiene el flujo de estados:
     Pendiente
     En proforma
     Pagado
     Anulado
   - Se registran datos demo de aviso y periodo para servicios asignados.


      2026-04-28 - FASE 6
   - Se implementan los módulos Métodos de pago y Personalización.
   - Se agrega ecc_metodos_pago.titulo_visible.
   - Se agrega ecc_configuracion_empresa.nombre_comercial.
   - Se crea gestión local de archivos para logos usando almacen/AAAA/MM/DD/categoria/.
   - Se registran logos en ecc_archivos.
   - Se mantienen los métodos Cuenta de ahorro, Yape y Plin.


      2026-04-28 - FASE 7
   - Se implementa el módulo Plantillas.
   - Se agrega ecc_plantillas.logo_tipo.
   - Se permite crear, editar, activar e inactivar plantillas.
   - Se agrega vista previa de plantilla.
   - Se permite controlar orientación, logo, datos empresa, datos cliente, métodos de pago, color y pie de página.
   - Se confirma que la plantilla no controla bloques de servicios.
   - Los bloques Actuales, Pendientes de pago y Otros servicios o trámites se mostrarán automáticamente si tienen ítems.

      2026-04-28 - FASE 8
   - Se implementa el módulo Proformas de pago.
   - Se reutilizan las tablas ecc_proformas y ecc_proforma_detalles.
   - No se crean tablas nuevas.
   - No se agregan columnas nuevas.
   - Se permite crear, editar y ver documento de proforma.
   - Se permite documento manual de emergencia usando detalles tipo Manual.
   - Los servicios seleccionados pasan a estado En proforma.
   - La proforma no registra ingreso.
   - Los botones Descargar JPG y Descargar PDF quedan preparados para la exportación local de Fase 10.

   2026-04-28 - FASE 9
   - Se implementa el módulo Recibos de pago.
   - Se reutilizan las tablas ecc_recibos y ecc_recibo_detalles.
   - No se crean tablas nuevas.
   - No se agregan columnas nuevas.
   - Se permite confirmar pago desde proforma.
   - Se permite pago parcial.
   - Se permite generar recibo manual de emergencia.
   - El recibo sí registra ingreso.
   - Los servicios completamente pagados pasan a Pagado.
   - Los servicios no pagados o parcialmente pagados quedan como Pendiente.
   - Se permite añadir servicio adicional al confirmar pago.
   - Los botones Descargar JPG y Descargar PDF quedan preparados para la exportación local de Fase 10.
*/


*/


/* ============================================================
   4. DATOS DE PRUEBA
   QUERYS PARA TESTEO DEL SISTEMA
   ============================================================ */

START TRANSACTION;

INSERT INTO `ecc_clientes`
(`id`, `tipo_cliente`, `documento_tipo`, `numero_documento`, `razon_social`, `nombre_comercial`, `nombres`, `apellidos`, `direccion`, `correo`, `celular`, `observacion`, `estado`, `created_by_external_id`)
VALUES
(1, 'Empresa', 'RUC', '20600000001', 'Comercial Demo SAC', 'Comercial Demo', NULL, NULL, 'Av. Los Contadores 123', 'contacto@demoempresa.pe', '999111222', 'Cliente demo empresa', 1, 'demo'),
(2, 'Persona natural', 'DNI', '70000001', NULL, NULL, 'Juan Carlos', 'Pérez Rojas', 'Jr. Los Servicios 456', 'juan.demo@correo.pe', '999333444', 'Cliente demo persona natural', 1, 'demo')
ON DUPLICATE KEY UPDATE
`tipo_cliente` = VALUES(`tipo_cliente`),
`razon_social` = VALUES(`razon_social`),
`nombre_comercial` = VALUES(`nombre_comercial`),
`nombres` = VALUES(`nombres`),
`apellidos` = VALUES(`apellidos`),
`direccion` = VALUES(`direccion`),
`correo` = VALUES(`correo`),
`celular` = VALUES(`celular`),
`observacion` = VALUES(`observacion`),
`estado` = VALUES(`estado`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_servicios`
(`id`, `nombre`, `descripcion`, `precio_base`, `estado`, `created_by_external_id`)
VALUES
(1, 'Declaración mensual', 'Servicio mensual de declaración tributaria.', 150.00, 1, 'demo'),
(2, 'Planilla mensual', 'Servicio mensual de cálculo y presentación de planilla.', 120.00, 1, 'demo'),
(3, 'Asesoría contable', 'Servicio de asesoría contable general.', 200.00, 1, 'demo'),
(4, 'Trámite SUNAT', 'Gestión de trámite administrativo ante SUNAT.', 80.00, 1, 'demo')
ON DUPLICATE KEY UPDATE
`descripcion` = VALUES(`descripcion`),
`precio_base` = VALUES(`precio_base`),
`estado` = VALUES(`estado`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_etiquetas`
(`id`, `nombre`, `color`, `estado`, `created_by_external_id`)
VALUES
(1, 'SUNAT', '#007bff', 1, 'demo'),
(2, 'Laboral', '#28a745', 1, 'demo'),
(3, 'Mensual', '#17a2b8', 1, 'demo'),
(4, 'Urgente', '#dc3545', 1, 'demo')
ON DUPLICATE KEY UPDATE
`color` = VALUES(`color`),
`estado` = VALUES(`estado`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_servicio_etiquetas`
(`id`, `servicio_id`, `etiqueta_id`)
VALUES
(1, 1, 1),
(2, 1, 3),
(3, 2, 2),
(4, 2, 3),
(5, 4, 1),
(6, 4, 4)
ON DUPLICATE KEY UPDATE
`servicio_id` = VALUES(`servicio_id`),
`etiqueta_id` = VALUES(`etiqueta_id`);


INSERT INTO `ecc_metodos_pago`
(`id`, `titulo_visible`, `tipo`, `titular`, `banco`, `numero_cuenta`, `cci`, `numero_celular`, `descripcion`, `orden`, `estado`, `created_by_external_id`)
VALUES
(1, 'Cuenta de ahorro BCP', 'Cuenta de ahorro', 'Gerente Demo', 'BCP', '19100000000001', '0021910000000000000001', NULL, 'Cuenta de ahorro principal', 1, 1, 'demo'),
(2, 'Yape', 'Yape', 'Gerente Demo', NULL, NULL, NULL, '999888777', 'Yape principal', 2, 1, 'demo'),
(3, 'Plin', 'Plin', 'Gerente Demo', NULL, NULL, NULL, '999888777', 'Plin principal', 3, 1, 'demo')
ON DUPLICATE KEY UPDATE
`titulo_visible` = VALUES(`titulo_visible`),
`tipo` = VALUES(`tipo`),
`titular` = VALUES(`titular`),
`banco` = VALUES(`banco`),
`numero_cuenta` = VALUES(`numero_cuenta`),
`cci` = VALUES(`cci`),
`numero_celular` = VALUES(`numero_celular`),
`descripcion` = VALUES(`descripcion`),
`orden` = VALUES(`orden`),
`estado` = VALUES(`estado`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_configuracion_empresa`
(`id`, `ruc`, `razon_social`, `nombre_comercial`, `rubro`, `direccion`, `correo`, `celular`, `logo_archivo_id`, `logo_tipo`, `logo_zoom`, `logo_pos_x`, `logo_pos_y`, `color_tipo`, `color_primario`, `color_secundario`, `pie_pagina`, `created_by_external_id`)
VALUES
(1, '00000000000', 'Estudio Contable Contreras', 'Estudio Contable Contreras', 'Servicios contables y tributarios', 'Dirección pendiente de configurar', 'correo@contreras.local', '999999999', NULL, 'Rectangular', 1.00, 0.00, 0.00, 'Solido', '#1f4e79', NULL, 'Gracias por confiar en Estudio Contable Contreras.', 'demo')
ON DUPLICATE KEY UPDATE
`ruc` = VALUES(`ruc`),
`razon_social` = VALUES(`razon_social`),
`nombre_comercial` = VALUES(`nombre_comercial`),
`rubro` = VALUES(`rubro`),
`direccion` = VALUES(`direccion`),
`correo` = VALUES(`correo`),
`celular` = VALUES(`celular`),
`logo_tipo` = VALUES(`logo_tipo`),
`logo_zoom` = VALUES(`logo_zoom`),
`logo_pos_x` = VALUES(`logo_pos_x`),
`logo_pos_y` = VALUES(`logo_pos_y`),
`color_tipo` = VALUES(`color_tipo`),
`color_primario` = VALUES(`color_primario`),
`color_secundario` = VALUES(`color_secundario`),
`pie_pagina` = VALUES(`pie_pagina`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_plantillas`
(`id`, `nombre`, `descripcion`, `orientacion`, `logo_visible`, `logo_tipo`, `datos_empresa_visible`, `datos_cliente_visible`, `color_tipo`, `color_primario`, `color_secundario`, `pie_pagina_visible`, `pie_pagina`, `es_predeterminada`, `estado`, `created_by_external_id`)
VALUES
(1, 'Plantilla principal', 'Plantilla demo principal para proformas y recibos.', 'Vertical', 1, 'Rectangular', 1, 1, 'Solido', '#1f4e79', NULL, 1, 'Documento generado por Estudio Contable Contreras.', 1, 1, 'demo'),
(2, 'Plantilla simple', 'Plantilla demo simple sin diseño avanzado.', 'Vertical', 0, 'Cuadrado', 1, 1, 'Solido', '#343a40', NULL, 1, 'Documento informativo.', 0, 1, 'demo')
ON DUPLICATE KEY UPDATE
`descripcion` = VALUES(`descripcion`),
`orientacion` = VALUES(`orientacion`),
`logo_visible` = VALUES(`logo_visible`),
`logo_tipo` = VALUES(`logo_tipo`),
`datos_empresa_visible` = VALUES(`datos_empresa_visible`),
`datos_cliente_visible` = VALUES(`datos_cliente_visible`),
`color_tipo` = VALUES(`color_tipo`),
`color_primario` = VALUES(`color_primario`),
`color_secundario` = VALUES(`color_secundario`),
`pie_pagina_visible` = VALUES(`pie_pagina_visible`),
`pie_pagina` = VALUES(`pie_pagina`),
`es_predeterminada` = VALUES(`es_predeterminada`),
`estado` = VALUES(`estado`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_plantilla_metodos_pago`
(`id`, `plantilla_id`, `metodo_pago_id`, `mostrar`, `orden`)
VALUES
(1, 1, 1, 1, 1),
(2, 1, 2, 1, 2),
(3, 1, 3, 1, 3),
(4, 2, 1, 1, 1),
(5, 2, 2, 0, 2),
(6, 2, 3, 0, 3)
ON DUPLICATE KEY UPDATE
`mostrar` = VALUES(`mostrar`),
`orden` = VALUES(`orden`);


INSERT INTO `ecc_cliente_servicios`
(`id`, `cliente_id`, `servicio_id`, `descripcion_personalizada`, `periodo`, `monto`, `bloque_documento`, `estado`, `fecha_asignacion`, `fecha_vencimiento`, `fecha_aviso`, `modo_aviso`, `observacion`, `created_by_external_id`)
VALUES
(1, 1, 1, 'Declaración mensual abril 2026', 'Abril 2026', 150.00, 'Actuales', 'Pagado', '2026-04-01', '2026-04-30', '2026-04-29', 'Fecha exacta', 'Servicio pagado parcialmente desde proforma demo.', 'demo'),
(2, 1, 2, 'Planilla mensual abril 2026', 'Abril 2026', 120.00, 'Pendientes de pago', 'Pendiente', '2026-04-01', '2026-04-30', '2026-04-29', 'Fecha exacta', 'Servicio pendiente luego de pago parcial.', 'demo'),
(3, 1, 3, 'Asesoría contable especial', 'Abril 2026', 200.00, 'Otros servicios o trámites', 'Pendiente', '2026-04-10', NULL, NULL, 'Sin aviso', 'Servicio pendiente para próxima proforma.', 'demo'),
(4, 2, 4, 'Trámite SUNAT persona natural', 'Abril 2026', 80.00, 'Actuales', 'Pendiente', '2026-04-15', '2026-04-29', '2026-04-28', 'Fecha exacta', 'Servicio pendiente demo.', 'demo')
ON DUPLICATE KEY UPDATE
`cliente_id` = VALUES(`cliente_id`),
`servicio_id` = VALUES(`servicio_id`),
`descripcion_personalizada` = VALUES(`descripcion_personalizada`),
`periodo` = VALUES(`periodo`),
`monto` = VALUES(`monto`),
`bloque_documento` = VALUES(`bloque_documento`),
`estado` = VALUES(`estado`),
`fecha_asignacion` = VALUES(`fecha_asignacion`),
`fecha_vencimiento` = VALUES(`fecha_vencimiento`),
`fecha_aviso` = VALUES(`fecha_aviso`),
`modo_aviso` = VALUES(`modo_aviso`),
`observacion` = VALUES(`observacion`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_proformas`
(`id`, `codigo`, `cliente_id`, `plantilla_id`, `fecha_emision`, `fecha_vencimiento`, `subtotal`, `descuento`, `total`, `estado`, `observacion`, `created_by_external_id`)
VALUES
(1, 'P26-000008', 1, 1, '2026-04-28', '2026-04-30', 270.00, 0.00, 270.00, 'Parcial', 'Proforma demo con pago parcial.', 'demo')
ON DUPLICATE KEY UPDATE
`cliente_id` = VALUES(`cliente_id`),
`plantilla_id` = VALUES(`plantilla_id`),
`fecha_emision` = VALUES(`fecha_emision`),
`fecha_vencimiento` = VALUES(`fecha_vencimiento`),
`subtotal` = VALUES(`subtotal`),
`descuento` = VALUES(`descuento`),
`total` = VALUES(`total`),
`estado` = VALUES(`estado`),
`observacion` = VALUES(`observacion`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_proforma_detalles`
(`id`, `proforma_id`, `cliente_servicio_id`, `tipo_item`, `bloque`, `descripcion`, `cantidad`, `precio_unitario`, `total`, `estado`, `orden`)
VALUES
(1, 1, 1, 'Servicio', 'Actuales', 'Declaración mensual abril 2026', 1.00, 150.00, 150.00, 'Pagado', 1),
(2, 1, 2, 'Servicio', 'Pendientes de pago', 'Planilla mensual abril 2026', 1.00, 120.00, 120.00, 'Pendiente', 2)
ON DUPLICATE KEY UPDATE
`proforma_id` = VALUES(`proforma_id`),
`cliente_servicio_id` = VALUES(`cliente_servicio_id`),
`tipo_item` = VALUES(`tipo_item`),
`bloque` = VALUES(`bloque`),
`descripcion` = VALUES(`descripcion`),
`cantidad` = VALUES(`cantidad`),
`precio_unitario` = VALUES(`precio_unitario`),
`total` = VALUES(`total`),
`estado` = VALUES(`estado`),
`orden` = VALUES(`orden`);



INSERT INTO `ecc_recibos`
(`id`, `codigo`, `proforma_id`, `cliente_id`, `plantilla_id`, `metodo_pago_id`, `fecha_emision`, `fecha_pago`, `total_proforma`, `total_pagado`, `saldo_pendiente`, `estado`, `observacion`, `created_by_external_id`)
VALUES
(1, 'R26-000596', 1, 1, 1, 1, '2026-04-28', '2026-04-28', 270.00, 150.00, 120.00, 'Emitido', 'Recibo demo por pago parcial de proforma P26-000008.', 'demo')
ON DUPLICATE KEY UPDATE
`proforma_id` = VALUES(`proforma_id`),
`cliente_id` = VALUES(`cliente_id`),
`plantilla_id` = VALUES(`plantilla_id`),
`metodo_pago_id` = VALUES(`metodo_pago_id`),
`fecha_emision` = VALUES(`fecha_emision`),
`fecha_pago` = VALUES(`fecha_pago`),
`total_proforma` = VALUES(`total_proforma`),
`total_pagado` = VALUES(`total_pagado`),
`saldo_pendiente` = VALUES(`saldo_pendiente`),
`estado` = VALUES(`estado`),
`observacion` = VALUES(`observacion`),
`updated_by_external_id` = 'demo';

INSERT INTO `ecc_recibo_detalles`
(`id`, `recibo_id`, `proforma_detalle_id`, `cliente_servicio_id`, `bloque`, `descripcion`, `monto_original`, `monto_pagado`, `estado_servicio_resultante`, `orden`)
VALUES
(1, 1, 1, 1, 'Actuales', 'Declaración mensual abril 2026', 150.00, 150.00, 'Pagado', 1)
ON DUPLICATE KEY UPDATE
`recibo_id` = VALUES(`recibo_id`),
`proforma_detalle_id` = VALUES(`proforma_detalle_id`),
`cliente_servicio_id` = VALUES(`cliente_servicio_id`),
`bloque` = VALUES(`bloque`),
`descripcion` = VALUES(`descripcion`),
`monto_original` = VALUES(`monto_original`),
`monto_pagado` = VALUES(`monto_pagado`),
`estado_servicio_resultante` = VALUES(`estado_servicio_resultante`),
`orden` = VALUES(`orden`);

UPDATE `ecc_proforma_detalles`
SET `estado` = 'Pagado'
WHERE `id` = 1;

UPDATE `ecc_proforma_detalles`
SET `estado` = 'Pendiente'
WHERE `id` = 2;

UPDATE `ecc_cliente_servicios`
SET `estado` = 'Pagado'
WHERE `id` = 1;

UPDATE `ecc_cliente_servicios`
SET `estado` = 'Pendiente'
WHERE `id` = 2;

UPDATE `ecc_proformas`
SET
    `estado` = 'Parcial',
    `updated_by_external_id` = 'demo'
WHERE `id` = 1;


INSERT INTO `ecc_recibos`
(`id`, `codigo`, `proforma_id`, `cliente_id`, `plantilla_id`, `metodo_pago_id`, `fecha_emision`, `fecha_pago`, `total_proforma`, `total_pagado`, `saldo_pendiente`, `estado`, `observacion`, `created_by_external_id`)
VALUES
(1, 'R26-000596', 1, 1, 1, 2, '2026-04-28', '2026-04-28', 270.00, 150.00, 120.00, 'Emitido', 'Recibo demo generado desde proforma con pago parcial.', 'demo')
ON DUPLICATE KEY UPDATE
`proforma_id` = VALUES(`proforma_id`),
`cliente_id` = VALUES(`cliente_id`),
`plantilla_id` = VALUES(`plantilla_id`),
`metodo_pago_id` = VALUES(`metodo_pago_id`),
`fecha_emision` = VALUES(`fecha_emision`),
`fecha_pago` = VALUES(`fecha_pago`),
`total_proforma` = VALUES(`total_proforma`),
`total_pagado` = VALUES(`total_pagado`),
`saldo_pendiente` = VALUES(`saldo_pendiente`),
`estado` = VALUES(`estado`),
`observacion` = VALUES(`observacion`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_recibo_detalles`
(`id`, `recibo_id`, `proforma_detalle_id`, `cliente_servicio_id`, `bloque`, `descripcion`, `monto_original`, `monto_pagado`, `estado_servicio_resultante`, `orden`)
VALUES
(1, 1, 1, 1, 'Actuales', 'Declaración mensual abril 2026', 150.00, 150.00, 'Pagado', 1)
ON DUPLICATE KEY UPDATE
`recibo_id` = VALUES(`recibo_id`),
`proforma_detalle_id` = VALUES(`proforma_detalle_id`),
`cliente_servicio_id` = VALUES(`cliente_servicio_id`),
`bloque` = VALUES(`bloque`),
`descripcion` = VALUES(`descripcion`),
`monto_original` = VALUES(`monto_original`),
`monto_pagado` = VALUES(`monto_pagado`),
`estado_servicio_resultante` = VALUES(`estado_servicio_resultante`),
`orden` = VALUES(`orden`);


INSERT INTO `ecc_archivos`
(`id`, `categoria`, `nombre_original`, `nombre_guardado`, `extension`, `mime_type`, `tamanio_bytes`, `ruta_relativa`, `tabla_referencia`, `registro_id`, `descripcion`, `estado`, `created_by_external_id`)
VALUES
(1, 'demo', 'archivo-demo.txt', 'archivo-demo.txt', 'txt', 'text/plain', 0, 'almacen/2026/04/28/demo/archivo-demo.txt', 'ecc_archivos', 1, 'Registro demo de archivo. No representa un archivo físico obligatorio.', 0, 'demo')
ON DUPLICATE KEY UPDATE
`categoria` = VALUES(`categoria`),
`nombre_original` = VALUES(`nombre_original`),
`nombre_guardado` = VALUES(`nombre_guardado`),
`extension` = VALUES(`extension`),
`mime_type` = VALUES(`mime_type`),
`tamanio_bytes` = VALUES(`tamanio_bytes`),
`ruta_relativa` = VALUES(`ruta_relativa`),
`tabla_referencia` = VALUES(`tabla_referencia`),
`registro_id` = VALUES(`registro_id`),
`descripcion` = VALUES(`descripcion`),
`estado` = VALUES(`estado`),
`updated_by_external_id` = 'demo';


INSERT INTO `ecc_auditoria`
(`id`, `modulo`, `accion`, `tabla_afectada`, `registro_id`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `ip`, `user_agent`, `usuario_externo_id`, `created_by_external_id`)
VALUES
(1, 'Base de datos', 'FASE 3', 'base_datos.sql', NULL, 'Carga inicial de estructura y datos demo.', NULL, '{"fase":"3","estado":"demo"}', '127.0.0.1', 'Carga inicial', 'demo', 'demo')
ON DUPLICATE KEY UPDATE
`modulo` = VALUES(`modulo`),
`accion` = VALUES(`accion`),
`tabla_afectada` = VALUES(`tabla_afectada`),
`registro_id` = VALUES(`registro_id`),
`descripcion` = VALUES(`descripcion`),
`datos_anteriores` = VALUES(`datos_anteriores`),
`datos_nuevos` = VALUES(`datos_nuevos`),
`ip` = VALUES(`ip`),
`user_agent` = VALUES(`user_agent`),
`usuario_externo_id` = VALUES(`usuario_externo_id`),
`created_by_external_id` = VALUES(`created_by_external_id`);

COMMIT;


/* ============================================================
   5. NOTAS DE EJECUCIÓN
   ============================================================

   Orden recomendado:
   1. Seleccionar la base de datos real en phpMyAdmin.
   2. Ejecutar solo las querys de la sección 2 para crear estructura.
   3. Ejecutar la sección 4 solo si se desean datos demo.
   4. Confirmar que todas las tablas usan InnoDB y utf8mb4.
   5. Confirmar que includes/config.php apunta a la misma base de datos.

   Para entorno real:
   - Ejecutar QUERYS PARA EL SISTEMA.
   - Ejecutar QUERYS PARA TESTEO DEL SISTEMA solo en ambiente de prueba.
   - No hay tablas de usuarios porque no existe login todavía.
   - Los campos usuario_externo_id, created_by_external_id y updated_by_external_id quedan listos para integración futura por API/token.
*/