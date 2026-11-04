<?php
/**
 * Ejercicio 2.6 - Eliminación de un usuario (previa confirmación con modal).
 * NO se podrá eliminar si tiene registros relacionados (entradas):
 * se controla con comprobación previa y con la restricción FK de la BD.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/imagenes.php';
require_once __DIR__ . '/helpers/log.php';

requerirAdmin();

$idUsuario = idDesdePeticion();

try {
    $pdo = conexion();

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) { header('Location: usuarios.php?cod=noregistro'); exit; }

    // Impedimos que el admin se elimine a sí mismo (se quedaría sin administrador)
    if ($usuario['id'] === (int) usuarioActual()['id']) {
        header('Location: usuarios.php?cod=sinpermisos');
        exit;
    }

    // Comprobación de registros relacionados (Ej. 2.6)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM entradas WHERE usuario_id = :id');
    $stmt->execute([':id' => $idUsuario]);
    if ((int) $stmt->fetchColumn() > 0) {
        header('Location: usuarios.php?cod=relacionado');
        exit;
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $idUsuario]);
    registrarLog($pdo, 'eliminación de usuario (' . $usuario['nick'] . ')', true);
    $pdo->commit();

    eliminarImagen($usuario['imagen_avatar']);

    header('Location: usuarios.php?cod=eliminado');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: usuarios.php?cod=error');
    exit;
}
