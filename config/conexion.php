<?php
/**
 * Ejercicio 2.1 - «Mi pequeño Blog»
 * Script de conexión a la Base de Datos bd_blog mediante PDO.
 * Utilizo el control de excepciones (try/catch) para comprobar
 * que la conexión se ha realizado correctamente.
 */
function conexion()
{
    // Guardo la conexión en una variable estática para no
    // crear una conexión nueva cada vez que llamo a la función.
    static $pdo = null;

    if ($pdo === null) {
        $host     = 'localhost';
        $bd       = 'bd_blog';
        $usuario  = 'root';
        $password = '';
        $charset  = 'utf8mb4';

        try {
            // Conexión por PDO (mejor que mysqli, me sirve para MySQL)
            $pdo = new PDO("mysql:host=$host;dbname=$bd;charset=$charset", $usuario, $password);

            // Configuro PDO para que lance excepciones si hay algún error de SQL
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Que los resultados me vengan en arrays asociativos
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Si falla la conexión aviso y corto la ejecución del script
            die('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}
