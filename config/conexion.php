<?php
/**
 * Ejercicio 2.1 - "Mi pequeño Blog"
 * Script de conexión a la Base de Datos mediante PDO, utilizando
 * el control de excepciones (try/catch) como mecanismo para
 * comprobar que la conexión se realizó correctamente.
 */
class Conexion
{
    /** @var PDO|null Instancia única (patrón Singleton) */
    private static ?PDO $conexion = null;

    private const HOST     = 'localhost';
    private const BD       = 'bd_blog';
    private const USUARIO  = 'root';
    private const PASSWORD = '';
    private const CHARSET  = 'utf8mb4';

    /**
     * Devuelve la conexión PDO. Si no existe todavía la crea
     * lanzando/las capturando las excepciones correspondientes.
     */
    public static function obtenerConexion(): PDO
    {
        if (self::$conexion === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::BD . ';charset=' . self::CHARSET;

            try {
                self::$conexion = new PDO($dsn, self::USUARIO, self::PASSWORD, [
                    // Lanza PDOException ante cualquier error SQL
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    // Arrays asociativos por defecto
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Sentencias preparadas reales en el servidor
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Control de excepciones: informamos y detenemos la ejecución
                die('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
            }
        }

        return self::$conexion;
    }
}

/** Función de acceso rápido a la conexión */
function conexion(): PDO
{
    return Conexion::obtenerConexion();
}
