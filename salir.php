<?php
/**
 * Ejercicio 2.2 - Cierre de sesión.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/log.php';

if (estaLogueado()) {
    registrarLog(conexion(), 'cierre de sesión');
    logoutUsuario();
}

header('Location: login.php');
exit;
