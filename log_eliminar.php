<?php
/**
 * Ejercicio 2.14 - Eliminación de un registro del log (con modal de confirmación).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/log.php';

requerirAdmin();

$idLog = idDesdePeticion();

try {
    $pdo = conexion();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('DELETE FROM logs WHERE id = :id');
    $stmt->execute([':id' => $idLog]);
    registrarLog($pdo, 'eliminación de log #' . $idLog, true);
    $pdo->commit();

    header('Location: logs.php?cod=eliminado');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: logs.php?cod=error');
    exit;
}
