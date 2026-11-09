<?php
/**
 * Ejercicio 2.7 - Detalle de una categoría con sus entradas asociadas.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';

requerirLogin();

$idCategoria = idDesdePeticion();

try {
    $pdo = conexion();
    $stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = :id');
    $stmt->execute([':id' => $idCategoria]);
    $categoria = $stmt->fetch();

    if (!$categoria) { header('Location: categorias.php?cod=noregistro'); exit; }

    $stmt = $pdo->prepare(
        'SELECT e.*, u.nick FROM entradas e
         INNER JOIN usuarios u ON e.usuario_id = u.id
         WHERE e.categoria_id = :id ORDER BY e.fecha DESC'
    );
    $stmt->execute([':id' => $idCategoria]);
    $entradas = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$tituloPagina = 'Detalle de categoría';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-borde">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><i class="bi bi-tag"></i> Categor&iacute;a: <b><?= limpiar($categoria['nombre']) ?></b></span>
                <?php if (esAdmin()): ?>
                    <a href="categoria_editar.php?id=<?= (int) $categoria['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Editar</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p><span class="badge badge-secondary p-2">ID <?= (int) $categoria['id'] ?></span>
                   <span class="text-muted ml-2"><?= count($entradas) ?> entrada(s) asociadas</span></p>
                <?php if (!$entradas): ?>
                    <p class="text-muted">No hay entradas registradas en esta categor&iacute;a.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($entradas as $e): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-journal-text"></i> <?= limpiar($e['titulo']) ?>
                                    <span class="text-muted">— <?= limpiar($e['nick']) ?></span></span>
                                <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($e['fecha'])) ?>
                                    <a href="entrada_detalle.php?id=<?= (int) $e['id'] ?>" class="btn btn-sm btn-outline-info ml-2"><i class="bi bi-eye"></i></a>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="categorias.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver al listado</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
