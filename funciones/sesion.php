<?php
/**
 * Ejercicio 2.2 - Sistema de control de acceso básico
 * Dos perfiles: 'admin' y 'user'.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';

/** Guarda en sesión los datos del usuario validado. */
function loginUsuario(array $usuario): void
{
    $_SESSION['usuario'] = [
        'id'     => (int) $usuario['id'],
        'nick'   => $usuario['nick'],
        'nombre' => $usuario['nombre'],
        'perfil' => $usuario['perfil'],
    ];
}

function logoutUsuario(): void
{
    unset($_SESSION['usuario']);
    session_destroy();
}

function estaLogueado(): bool
{
    return isset($_SESSION['usuario']);
}

/** Devuelve el usuario de la sesión o null. */
function usuarioActual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** ¿Perfil administrador? */
function esAdmin(): bool
{
    return estaLogueado() && usuarioActual()['perfil'] === 'admin';
}

/** ¿El usuario de sesión es el propietario de la entrada indicada? */
function esPropietarioDeEntrada(array $entrada): bool
{
    return estaLogueado() && usuarioActual()['id'] === (int) $entrada['usuario_id'];
}

/** ¿Puede editar/eliminar la entrada indicada?
 *  admin → cualquier entrada; user → solo las suyas. */
function puedeGestionarEntrada(array $entrada): bool
{
    return esAdmin() || esPropietarioDeEntrada($entrada);
}

/** Fuerza que exista sesión; si no, redirige al login. */
function requerirLogin(): void
{
    if (!estaLogueado()) {
        header('Location: login.php');
        exit;
    }
}

/** Fuerza el perfil de administrador. */
function requerirAdmin(string $url = 'index.php'): void
{
    requerirLogin();
    if (!esAdmin()) {
        header('Location: ' . $url . '?cod=sinpermisos');
        exit;
    }
}

/** Convertidor seguro de id por GET/POST. */
function idDesdePeticion(string $campo = 'id'): int
{
    return isset($_GET[$campo]) ? (int) $_GET[$campo] : (isset($_POST[$campo]) ? (int) $_POST[$campo] : 0);
}
