<?php
/**
 * Ejercicio 2.13 - Registro de operaciones en la tabla 'logs'
 * mediante el procedimiento almacenado sp_registrar_log().
 * Las llamadas se incluyen dentro de las transacciones de las
 * operaciones del Blog para que se registre sólo si la
 * operación principal se completó correctamente.
 */
require_once __DIR__ . '/../config/conexion.php';

/**
 * Llama al procedimiento almacenado de la BD.
 *
 * @param PDO    $pdo        Conexión activa.
 * @param string $operacion  Tipo de operación realizada ("alta entrada", "edición usuario"...).
 * @param bool   $enTransaccion  true cuando la llamada va dentro de una transacción.
 */
function registrarLog(PDO $pdo, string $operacion, bool $enTransaccion = false): void
{
    try {
        $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario']['nick'] : 'anónimo';

        // Ejercicio 2.2: si no estamos ya dentro de una transacción,
        // envolvemos la llamada en su propia transacción.
        if (!$enTransaccion) {
            if ($pdo->inTransaction()) {
                $enTransaccion = true;
            } else {
                $pdo->beginTransaction();
            }
        }

        $sql  = 'CALL sp_registrar_log(:usuario, :operacion)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':usuario' => $usuario, ':operacion' => $operacion]);

        // Consumir los posibles resultsets del CALL para no bloquear la conexión
        while ($stmt->nextRowset()) { /* vacío */ }
        $stmt->closeCursor();

        if (!$enTransaccion && $pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (PDOException $e) {
        if (!$enTransaccion && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // No interrumpimos la navegación: sólo informamos en pantalla
        error_log('Error registrando log: ' . $e->getMessage());
    }
}
