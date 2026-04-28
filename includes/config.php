<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

date_default_timezone_set('America/Lima');

$app_config = array(
    'app_name' => 'Estudio Contable Contreras',
    'app_short_name' => 'Contreras',
    'app_version' => '0.1.0',
    'app_environment' => 'development',
    'app_debug' => true,

    'auth_mode' => 'demo',
    'auth_modes_available' => array(
        'demo',
        'central_api'
    ),

    'default_module' => 'inicio',

    'database' => array(
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'u517204426_c0nt4ble_contr',
        'user' => 'u517204426_c00nt4door',
        'pass' => 'W@xhEpCEk:L4',
        'charset' => 'utf8mb4'
    ),

    'paths' => array(
        'storage_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'almacen',
        'storage_public' => 'almacen'
    ),

    'company' => array(
        'name' => 'Estudio Contable Contreras',
        'ruc' => '',
        'address' => '',
        'email' => '',
        'phone' => ''
    )
);

$app_modules = array(
    'inicio' => array(
        'label' => 'Inicio',
        'icon' => 'fas fa-tachometer-alt',
        'enabled' => true
    ),
    'clientes_servicios' => array(
        'label' => 'Clientes y servicios',
        'icon' => 'fas fa-users',
        'enabled' => true
    ),
    'proformas' => array(
        'label' => 'Proformas de pago',
        'icon' => 'fas fa-file-invoice-dollar',
        'enabled' => true
    ),
    'recibos' => array(
        'label' => 'Recibos de pago',
        'icon' => 'fas fa-receipt',
        'enabled' => true
    ),
    'plantillas' => array(
        'label' => 'Plantillas',
        'icon' => 'fas fa-layer-group',
        'enabled' => true
    ),
    'metodos_pago' => array(
        'label' => 'Métodos de pago',
        'icon' => 'fas fa-credit-card',
        'enabled' => true
    ),
    'personalizacion' => array(
        'label' => 'Personalización',
        'icon' => 'fas fa-palette',
        'enabled' => true
    ),
    'auditoria' => array(
        'label' => 'Auditoría',
        'icon' => 'fas fa-shield-alt',
        'enabled' => true
    )
);