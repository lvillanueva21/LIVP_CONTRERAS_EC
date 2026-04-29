<?php
if (!defined('APP_BOOTSTRAP')) {
    http_response_code(403);
    exit('Acceso no permitido');
}

function app_archivo_normalizar_categoria($categoria)
{
    $categoria = strtolower(trim((string)$categoria));
    $categoria = preg_replace('/[^a-z0-9_-]/', '_', $categoria);
    $categoria = preg_replace('/_+/', '_', $categoria);
    $categoria = trim($categoria, '_');

    return $categoria !== '' ? $categoria : 'general';
}

function app_archivo_extension($nombre)
{
    $extension = strtolower(pathinfo((string)$nombre, PATHINFO_EXTENSION));
    return preg_replace('/[^a-z0-9]/', '', $extension);
}

function app_archivo_mime($tmp_name)
{
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo) {
            $mime = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);

            if ($mime) {
                return $mime;
            }
        }
    }

    return 'application/octet-stream';
}

function app_archivo_crear_directorio($ruta)
{
    if (is_dir($ruta)) {
        return true;
    }

    return mkdir($ruta, 0755, true);
}

function app_archivo_nombre_seguro($extension)
{
    try {
        $base = bin2hex(random_bytes(12));
    } catch (Throwable $e) {
        $base = uniqid('archivo_', true);
    }

    $extension = app_archivo_extension('x.' . $extension);

    if ($extension === '') {
        return $base;
    }

    return $base . '.' . $extension;
}

function app_archivo_guardar($file, $categoria, $tabla_referencia = null, $registro_id = null, $descripcion = null, $allowed_mimes = array())
{
    if (!isset($file) || !is_array($file)) {
        return array(
            'ok' => false,
            'message' => 'Archivo no recibido.'
        );
    }

    if (!isset($file['error']) || (int)$file['error'] !== UPLOAD_ERR_OK) {
        return array(
            'ok' => false,
            'message' => 'No se pudo recibir el archivo.'
        );
    }

    $categoria = app_archivo_normalizar_categoria($categoria);
    $nombre_original = isset($file['name']) ? (string)$file['name'] : 'archivo';
    $tmp_name = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
    $tamanio = isset($file['size']) ? (int)$file['size'] : 0;
    $extension = app_archivo_extension($nombre_original);
    $mime = app_archivo_mime($tmp_name);

    if ($tamanio <= 0) {
        return array(
            'ok' => false,
            'message' => 'El archivo está vacío.'
        );
    }

    if ($tamanio > 5242880) {
        return array(
            'ok' => false,
            'message' => 'El archivo supera el tamaño máximo permitido de 5 MB.'
        );
    }

    if (!empty($allowed_mimes) && !in_array($mime, $allowed_mimes, true)) {
        return array(
            'ok' => false,
            'message' => 'Tipo de archivo no permitido.'
        );
    }

    $fecha = date('Y/m/d');
    $storage_dir = app_storage_dir();
    $ruta_relativa_dir = app_storage_public() . '/' . $fecha . '/' . $categoria;
    $ruta_fisica_dir = $storage_dir . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR . $categoria;

    if (!app_archivo_crear_directorio($ruta_fisica_dir)) {
        return array(
            'ok' => false,
            'message' => 'No se pudo crear el directorio de almacenamiento.'
        );
    }

    $nombre_guardado = app_archivo_nombre_seguro($extension);
    $ruta_fisica = $ruta_fisica_dir . DIRECTORY_SEPARATOR . $nombre_guardado;
    $ruta_relativa = $ruta_relativa_dir . '/' . $nombre_guardado;

    if (!move_uploaded_file($tmp_name, $ruta_fisica)) {
        return array(
            'ok' => false,
            'message' => 'No se pudo guardar el archivo.'
        );
    }

    $pdo = app_pdo();

    $stmt = $pdo->prepare("
        INSERT INTO ecc_archivos
        (categoria, nombre_original, nombre_guardado, extension, mime_type, tamanio_bytes, ruta_relativa, tabla_referencia, registro_id, descripcion, estado, created_by_external_id)
        VALUES
        (:categoria, :nombre_original, :nombre_guardado, :extension, :mime_type, :tamanio_bytes, :ruta_relativa, :tabla_referencia, :registro_id, :descripcion, 1, :created_by_external_id)
    ");

    $user = auth_user();
    $external_id = isset($user['mode']) ? $user['mode'] : 'demo';

    $stmt->execute(array(
        ':categoria' => $categoria,
        ':nombre_original' => $nombre_original,
        ':nombre_guardado' => $nombre_guardado,
        ':extension' => $extension,
        ':mime_type' => $mime,
        ':tamanio_bytes' => $tamanio,
        ':ruta_relativa' => $ruta_relativa,
        ':tabla_referencia' => $tabla_referencia,
        ':registro_id' => $registro_id,
        ':descripcion' => $descripcion,
        ':created_by_external_id' => $external_id
    ));

    $archivo_id = (int)$pdo->lastInsertId();

    return array(
        'ok' => true,
        'message' => 'Archivo guardado correctamente.',
        'archivo_id' => $archivo_id,
        'ruta_relativa' => $ruta_relativa,
        'nombre_original' => $nombre_original,
        'mime_type' => $mime
    );
}

function app_archivo_obtener($archivo_id)
{
    $pdo = app_pdo();

    $stmt = $pdo->prepare("SELECT * FROM ecc_archivos WHERE id = :id AND estado = 1 LIMIT 1");
    $stmt->execute(array(':id' => (int)$archivo_id));

    return $stmt->fetch();
}

function app_archivo_url($archivo_id)
{
    $archivo = app_archivo_obtener($archivo_id);

    if (!$archivo) {
        return '';
    }

    return app_url($archivo['ruta_relativa']);
}
