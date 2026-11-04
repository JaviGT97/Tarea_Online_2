<?php
/**
 * Ejercicio 2.8 - Subida de imágenes al directorio 'images' del servidor.
 * Control de error, tamaño, extensión y tipo real (MIME).
 */
require_once __DIR__ . '/../config/config.php';

/**
 * Sube la imagen recibida por $_FILES[$campo] al directorio images/.
 *
 * @param string $campo  Nombre del campo del formulario (<input type="file">).
 * @param array  &$errores Array donde se añadirán los mensajes de error.
 * @return string|null Nombre del archivo guardado (o null si no se subió nada / error).
 */
function subirImagen(string $campo, array &$errores = []): ?string
{
    // No se envió ningún archivo (no es obligatorio en todos los formularios)
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $archivo = $_FILES[$campo];

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'Error al subir la imagen (código ' . $archivo['error'] . ').';
        return null;
    }

    if ($archivo['size'] > TAM_MAX_IMAGEN) {
        $errores[] = 'La imagen no puede superar los ' . (TAM_MAX_IMAGEN / (1024 * 1024)) . ' MB.';
        return null;
    }

    // Comprobación del tipo real del archivo (no confiar en la extensión)
    $mime = mime_content_type($archivo['tmp_name']);
    $mimesValidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $mimesValidos, true)) {
        $errores[] = 'El archivo no es una imagen válida.';
        return null;
    }

    // Filtrado de la extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, EXT_VALIDAS, true)) {
        $errores[] = 'Extensión no permitida. Válidas: ' . implode(', ', EXT_VALIDAS) . '.';
        return null;
    }

    // Nombre único para evitar colisiones y/o rutas maliciosas
    if (!is_dir(APP_DIR_IMAGENES)) {
        mkdir(APP_DIR_IMAGENES, 0777, true);
    }

    $nombre = uniqid('img_') . '.' . $extension;

    if (!move_uploaded_file($archivo['tmp_name'], APP_DIR_IMAGENES . $nombre)) {
        $errores[] = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }

    return $nombre;
}

/** Elimina (si existe) la imagen indicada del directorio images/. */
function eliminarImagen(?string $nombre): void
{
    if ($nombre && is_file(APP_DIR_IMAGENES . $nombre)) {
        @unlink(APP_DIR_IMAGENES . $nombre);
    }
}

/** Ruta de imagen o icono por defecto cuando el registro no la tiene. */
function imagenOMarcador(?string $nombre): string
{
    return $nombre && is_file(APP_DIR_IMAGENES . $nombre)
        ? RUTA_IMAGENES . rawurlencode($nombre)
        : '';
}
