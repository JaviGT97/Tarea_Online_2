<?php
/**
 * Ejercicio 2.6 - Eliminación de una entrada, con confirmación previa
 * mediante ventana modal Bootstrap. Con transacción y control de excepciones.
 * Ejercicio 2.13 - Registro de la operación en 'logs' (procedimiento BD).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/imagenes.php';
require_once __DIR__ . '/helpers/log.php';

requerirLogin();

$idEntrada = idDesdePeticion();

try {
    $pdo = conexion();

    $stmt = $pdo->prepare('SELECT * FROM entradas WHERE id = :id');
    $stmt->execute([':id' => $idEntrada]);
    $entrada = $stmt->fetch();

    if (!$entrada) {
        header('Location: index.php?cod=noregistro');
        exit;
    }
    // Permisos: admin elimina cualquier entrada; user sólo las suyas
    if (!puedeGestionarEntrada($entrada)) {
        header('Location: index.php?cod=sinpermisos');
        exit;
    }

    // Transacción: DELETE de la entrada + log (Ej. 2.13) de forma atómica
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('DELETE FROM entradas WHERE id = :id');
    $stmt->execute([':id' => $idEntrada]);
    registrarLog($pdo, 'eliminación de entrada "' . $entrada['titulo'] . '"', true);
    $pdo->commit();

    // La imagen del servidor se borra una vez confirmada la operación
    eliminarImagen($entrada['imagen']);

    header('Location: index.php?cod=eliminado');
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: index.php?cod=error');
    exit;
}
