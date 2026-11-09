<?php
/**
 * Ejercicio 2.6 - Eliminación de categoría (con confirmación modal).
 * No se podra eliminar si tiene entradas relacionadas (FK RESTRICT).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/log.php';

requerirAdmin();

$idCategoria = idDesdePeticion();

try {
    $pdo = conexion();

    $stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = :id');
    $stmt->execute([':id' => $idCategoria]);
    $categoria = $stmt->fetch();
    if (!$categoria) { header('Location: categorias.php?cod=noregistro'); exit; }

    // Comprobación de registros relacionados (Ej. 2.6)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM entradas WHERE categoria_id = :id');
    $stmt->execute([':id' => $idCategoria]);
    if ((int) $stmt->fetchColumn() > 0) {
        header('Location: categorias.php?cod=relacionado');
        exit;
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = :id');
    $stmt->execute([':id' => $idCategoria]);
    registrarLog($pdo, 'eliminación de categoría (' . $categoria['nombre'] . ')', true);
    $pdo->commit();

    header('Location: categorias.php?cod=eliminado');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: categorias.php?cod=error');
    exit;
}
