<?php
/**
 * Ejercicio 2.4/2.5/2.6 - Listado de categorías con columna Operaciones.
 * Los usuarios no administradores sólo pueden consultar.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';

requerirLogin();

try {
    $pdo = conexion();
    $categorias = $pdo->query(
        'SELECT c.*, (SELECT COUNT(*) FROM entradas e WHERE e.categoria_id = c.id) AS numEntradas
         FROM categorias c ORDER BY c.id'
    )->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$tituloPagina = 'Categorías';
require_once __DIR__ . '/plantillas/header.php';
?>
<h1 class="h3 mb-3"><i class="bi bi-tags"></i> Categor&iacute;as (<?= count($categorias) ?>)</h1>

<?php if (esAdmin()): ?>
<div class="text-right mb-2"><a href="categoria_crear.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Nueva categor&iacute;a</a></div>
<?php endif; ?>

<div class="table-responsive card card-borde p-2">
<table class="table table-hover table-sm align-middle mb-0">
    <thead class="thead-dark">
        <tr><th>ID</th><th>Nombre</th><th>N&ordm; entradas</th><th>Operaciones</th></tr>
    </thead>
    <tbody>
    <?php foreach ($categorias as $c): ?>
        <tr>
            <td><?= (int) $c['id'] ?></td>
            <td><?= limpiar($c['nombre']) ?></td>
            <td class="text-center"><?= (int) $c['numEntradas'] ?></td>
            <td class="text-nowrap">
                <a href="categoria_detalle.php?id=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline-info" title="Detalle"><i class="bi bi-eye"></i></a>
                <?php if (esAdmin()): ?>
                    <a href="categoria_editar.php?id=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                    <?php if ((int) $c['numEntradas'] === 0): ?>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                                data-url="categoria_eliminar.php?id=<?= (int) $c['id'] ?>"
                                data-nombre="la categor&iacute;a &laquo;<?= limpiar($c['nombre']) ?>&raquo;">
                            <i class="bi bi-trash"></i></button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-danger" disabled title="Tiene entradas relacionadas: no se puede eliminar"><i class="bi bi-trash"></i></button>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<p class="text-muted small"><i class="bi bi-shield-lock"></i> S&oacute;lo el perfil <b>admin</b> puede crear, editar o eliminar categor&iacute;as.</p>
<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
