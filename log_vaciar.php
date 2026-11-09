<?php
/**
 * Ejercicio 2.14 - Vaciado completo del registro de logs (con confirmación modal).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/log.php';

requerirAdmin();

try {
    $pdo = conexion();
    $pdo->beginTransaction();
    $pdo->exec('DELETE FROM logs');
    registrarLog($pdo, 'vaciado del log', true);
    $pdo->commit();

    header('Location: logs.php?cod=logsEliminados');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: logs.php?cod=error');
    exit;
}
