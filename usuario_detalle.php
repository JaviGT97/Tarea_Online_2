<?php
/**
 * Ejercicio 2.7 - Detalle completo de un usuario (con listado de sus entradas).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/saneamiento.php';
require_once __DIR__ . '/helpers/imagenes.php';

requerirAdmin();

$idUsuario = idDesdePeticion();

try {
    $pdo = conexion();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) { header('Location: usuarios.php?cod=noregistro'); exit; }

    $stmt = $pdo->prepare(
        'SELECT e.*, c.nombre AS categoria FROM entradas e
         INNER JOIN categorias c ON e.categoria_id = c.id
         WHERE e.usuario_id = :id ORDER BY e.fecha DESC'
    );
    $stmt->execute([':id' => $idUsuario]);
    $entradas = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$tituloPagina = 'Detalle de usuario';
require_once __DIR__ . '/inc/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-borde">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><i class="bi bi-person"></i> Detalle del usuario #<?= (int) $usuario['id'] ?></span>
                <span>
                    <a href="usuario_editar.php?id=<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Editar</a>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <?php if (imagenOMarcador($usuario['imagen_avatar'])): ?>
                            <img src="<?= imagenOMarcador($usuario['imagen_avatar']) ?>" alt="Avatar" class="img-detalle" style="max-width:150px;border-radius:50%">
                        <?php else: ?>
                            <i class="bi bi-person-circle text-muted" style="font-size:6rem"></i>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th style="width:130px">Nick</th><td><b><?= limpiar($usuario['nick']) ?></b></td></tr>
                            <tr><th>Nombre</th><td><?= limpiar($usuario['nombre']) ?></td></tr>
                            <tr><th>Apellidos</th><td><?= limpiar($usuario['apellido']) ?></td></tr>
                            <tr><th>Email</th><td><?= limpiar($usuario['email']) ?></td></tr>
                            <tr><th>Perfil</th><td><span class="badge badge-<?= $usuario['perfil'] === 'admin' ? 'warning' : 'secondary' ?>"><?= limpiar($usuario['perfil']) ?></span></td></tr>
                            <tr><th>N&ordm; entradas</th><td><?= count($entradas) ?></td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <h5 class="h6">Entradas publicadas</h5>
                <?php if (!$entradas): ?>
                    <p class="text-muted small">Este usuario no ha publicado entradas.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($entradas as $e): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-journal-text"></i> <?= limpiar($e['titulo']) ?>
                                    <span class="badge badge-info"><?= limpiar($e['categoria']) ?></span></span>
                                <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($e['fecha'])) ?>
                                    <a href="entrada_detalle.php?id=<?= (int) $e['id'] ?>" class="btn btn-sm btn-outline-info ml-2"><i class="bi bi-eye"></i></a>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer text-right">
                <a href="usuarios.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver al listado</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/inc/footer.php'; ?>
