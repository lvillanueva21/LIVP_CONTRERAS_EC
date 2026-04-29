<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function app_pdo()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config();
    $db = $config['database'];

    $dsn = 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['name'] . ';charset=' . $db['charset'];

    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    );

    try {
      $pdo = new PDO($dsn, $db['user'], $db['pass'], $options);

      $database_timezone = app_config('database_timezone', '-05:00');
      $pdo->exec("SET time_zone = " . $pdo->quote($database_timezone));

       return $pdo;
    } catch (PDOException $e) {
        if (app_debug()) {
            throw new RuntimeException('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }

        throw new RuntimeException('No se pudo conectar a la base de datos.');
    }
}

function app_db_status()
{
    try {
        $pdo = app_pdo();
        $stmt = $pdo->query('SELECT 1 AS estado');
        $row = $stmt->fetch();

        if (isset($row['estado']) && (int)$row['estado'] === 1) {
            return array(
                'ok' => true,
                'message' => 'Conexión PDO activa'
            );
        }

        return array(
            'ok' => false,
            'message' => 'La conexión respondió, pero no devolvió el estado esperado'
        );
    } catch (Throwable $e) {
        return array(
            'ok' => false,
            'message' => app_debug() ? $e->getMessage() : 'Base de datos pendiente de configurar'
        );
    }
}