<?php
/**
 * "Mi pequeño Blog" - Plantilla común: cabecera + menú (Bootstrap 4).
 * Variables opcionales antes del include: $titulo or $_tituloPagina, $ocultarMenu
 */
require_once __DIR__ . '/../helpers/sesion.php';
require_once __DIR__ . '/../helpers/saneamiento.php';

$tituloPagina = $tituloPagina ?? 'Mi pequeño Blog';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= limpiar($tituloPagina) ?> | Mi pequeño Blog</title>
    <!-- Bootstrap 4 CSS (local, funciona sin internet) -->
    <link rel="stylesheet" href="assets/vendor/css/bootstrap.min.css">
    <!-- Bootstrap Icons (para los iconos de ordenación, Ej. 2.12) -->
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body { background-color: #f5f6fa; padding-top: 70px; }
        .navbar-tarea { background-color: #343a40; }
        .card-borde { border-radius: .35rem; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
        .miniatura { max-width: 120px; height: auto; border-radius: .25rem; }
        .fd-fecha { cursor: pointer; white-space: nowrap; }
        .pie { color: #6c757d; font-size: .875rem; }
        .img-detalle { max-width: 100%; height: auto; border-radius: .35rem; margin-bottom: 1rem; }
        .avatar { width: 38px; height: 38px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand mb-0" href="index.php">
            <i class="bi bi-journal-richtext"></i> Mi pequeño Blog
            <span class="small text-warning"><?= limpiar($tituloPagina === 'Mi pequeño Blog' ? '' : '· ' . $tituloPagina) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ml-auto">
                <?php if (estaLogueado()): ?>
                    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                        <a class="nav-link" href="index.php"><i class="bi bi-list-ul"></i> Entradas</a>
                    </li>
                    <?php if (esAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="usuarios.php"><i class="bi bi-people"></i> Usuarios</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="categorias.php"><i class="bi bi-tags"></i> Categorías</a></li>
                    <?php if (esAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="logs.php"><i class="bi bi-clock-history"></i> Logs</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="entrada_nueva.php"><i class="bi bi-plus-circle"></i> Nueva entrada</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ddUsuario" data-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= limpiar(usuarioActual()['nick']) ?>
                            <span class="badge badge-<?= esAdmin() ? 'warning' : 'secondary' ?>">
                                <?= limpiar(usuarioActual()['perfil']) ?>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php if (esAdmin()): ?>
                                <a class="dropdown-item" href="usuario_crear.php"><i class="bi bi-person-plus"></i> Crear usuario</a>
                            <?php endif; ?>
                            <a class="dropdown-item" href="salir.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Entrar</a></li>
                    <li class="nav-item"><a class="nav-link" href="registro.php"><i class="bi bi-person-plus"></i> Registro</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container mt-4">
<?php
/** Mensajes de resultado (códigos por GET) */
$codigoMensaje = isset($_GET['cod']) ? $_GET['cod'] : null;
$mensajes = [
    'creado'       => ['success', 'Registro creado correctamente.'],
    'editado'      => ['success', 'Registro editado correctamente.'],
    'eliminado'    => ['success', 'Registro eliminado correctamente.'],
    'logsEliminados' => ['success', 'Registros del log eliminados correctamente.'],
    'sinpermisos'  => ['warning', 'No tiene permisos para realizar esa operación (perfil insuficiente).'],
    'noregistro'   => ['danger',  'No existe el registro solicitado o ha sido eliminado.'],
    'relacionado'  => ['danger',  'No se puede eliminar: el registro tiene registros relacionados en otras tablas.'],
    'error'        => ['danger',  'Se ha producido un error en la operación.'],
];
if ($codigoMensaje && isset($mensajes[$codigoMensaje])) {
    echo '<div class="alert alert-' . $mensajes[$codigoMensaje][0] . ' alert-dismissible fade show">'
        . '<i class="bi bi-info-circle"></i> ' . limpiar($mensajes[$codigoMensaje][1])
        . '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
}
