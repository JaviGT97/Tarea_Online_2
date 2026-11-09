<?php
/**
 * Ejercicio 2.4 - Listado de los registros de la tabla usuarios con la
 * columna "Operaciones" (detalle / editar / eliminar) — sólo administradores.
 * Ejercicio 2.6 - Eliminación con modal y comprobación de registros relacionados.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';
require_once __DIR__ . '/plantillas/paginacion.php';

requerirAdmin();
$tituloPagina = 'Usuarios';

try {
    $pdo = conexion();

    $porPagina  = validarEntero($_GET['porPagina'] ?? 5) ?? 5;
    $porPagina  = in_array($porPagina, [3, 5, 10, 15], true) ? $porPagina : 5;
    $pagina     = max(1, validarEntero($_GET['pagina'] ?? 1) ?? 1);
    $busqueda   = filtrarTexto($_GET['q'] ?? '');

    $where  = $busqueda !== '' ? ' WHERE nick LIKE :q OR nombre LIKE :q OR apellido LIKE :q OR email LIKE :q' : '';
    $params = $busqueda !== '' ? [':q' => '%' . $busqueda . '%'] : [];

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios' . $where);
    $stmt->execute($params);
    $totalRegistros = (int) $stmt->fetchColumn();
    $totalPaginas   = (int) max(1, ceil($totalRegistros / $porPagina));
    $pagina         = min($pagina, $totalPaginas);
    $offset         = ($pagina - 1) * $porPagina;

    $stmt = $pdo->prepare(
        'SELECT u.*, (SELECT COUNT(*) FROM entradas e WHERE e.usuario_id = u.id) AS numEntradas
         FROM usuarios u' . $where . ' ORDER BY u.id LIMIT :limite OFFSET :offset'
    );
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/plantillas/header.php';
?>
<h1 class="h3 mb-3"><i class="bi bi-people"></i> Usuarios (<?= $totalRegistros ?>) <span class="badge badge-warning">s&oacute;lo admin</span></h1>

<form method="get" class="form-inline mb-3">
    <input type="search" name="q" class="form-control form-control-sm mr-2" style="min-width:240px"
           value="<?= limpiar($busqueda) ?>" placeholder="Buscar por nick, nombre o email...">
    <input type="hidden" name="porPagina" value="<?= (int) $porPagina ?>">
    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i> Buscar</button>
    <a href="usuario_crear.php" class="btn btn-sm btn-primary ml-2"><i class="bi bi-person-plus"></i> Crear usuario</a>
</form>

<div class="table-responsive card card-borde p-2">
<table class="table table-hover table-sm align-middle mb-0">
    <thead class="thead-dark">
        <tr>
            <th>ID</th><th>Avatar</th><th>Nick</th><th>Nombre</th><th>Email</th><th>Perfil</th><th>Entradas</th><th>Operaciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$usuarios): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios que coincidan con la b&uacute;squeda.</td></tr>
    <?php endif; ?>
    <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= (int) $usuario['id'] ?></td>
            <td>
                <?php if (imagenOMarcador($usuario['imagen_avatar'])): ?>
                    <img src="<?= imagenOMarcador($usuario['imagen_avatar']) ?>" alt="Avatar" class="avatar">
                <?php else: ?>
                    <i class="bi bi-person-circle text-muted" style="font-size:1.6rem"></i>
                <?php endif; ?>
            </td>
            <td><?= limpiar($usuario['nick']) ?></td>
            <td><?= limpiar($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
            <td class="small"><?= limpiar($usuario['email']) ?></td>
            <td><span class="badge badge-<?= $usuario['perfil'] === 'admin' ? 'warning' : 'secondary' ?>"><?= limpiar($usuario['perfil']) ?></span></td>
            <td class="text-center"><?= (int) $usuario['numEntradas'] ?></td>
            <td class="text-nowrap">
                <a href="usuario_detalle.php?id=<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-outline-info" title="Detalle"><i class="bi bi-eye"></i></a>
                <a href="usuario_editar.php?id=<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                <?php // Ej. 2.6: sólo se permite eliminar si no tiene entradas relacionadas ?>
                <?php if ((int) $usuario['numEntradas'] === 0): ?>
                    <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-url="usuario_eliminar.php?id=<?= (int) $usuario['id'] ?>"
                            data-nombre="al usuario &laquo;<?= limpiar($usuario['nick']) ?>&raquo;">
                        <i class="bi bi-trash"></i></button>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline-danger" disabled title="Tiene entradas relacionadas: no se puede eliminar">
                        <i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php dibujarPaginacion($pagina, $totalPaginas, $porPagina, 'usuarios.php', ['q' => $busqueda]); ?>
<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
