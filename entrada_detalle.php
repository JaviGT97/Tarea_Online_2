<?php
/**
 * Ejercicio 2.7 - Detalle completo de una entrada del Blog.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';

requerirLogin();

$idEntrada = idDesdePeticion();

try {
    $pdo = conexion();
    $stmt = $pdo->prepare(
        'SELECT e.*, u.nick, u.nombre, u.apellido, u.imagen_avatar, c.nombre AS categoria
         FROM entradas e
         INNER JOIN usuarios u ON e.usuario_id = u.id
         INNER JOIN categorias c ON e.categoria_id = c.id
         WHERE e.id = :id'
    );
    $stmt->execute([':id' => $idEntrada]);
    $entrada = $stmt->fetch();

    if (!$entrada) {
        header('Location: index.php?cod=noregistro');
        exit;
    }
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$tituloPagina = 'Detalle de entrada';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-borde">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><i class="bi bi-card-text"></i> Detalle de la entrada #<?= (int) $entrada['id'] ?></span>
                <?php if (puedeGestionarEntrada($entrada)): ?>
                    <span>
                        <a href="entrada_editar.php?id=<?= (int) $entrada['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Editar</a>
                        <button class="btn btn-sm btn-danger btn-eliminar"
                                data-url="entrada_eliminar.php?id=<?= (int) $entrada['id'] ?>"
                                data-nombre="la entrada &laquo;<?= limpiar(mb_substr($entrada['titulo'], 0, 30)) ?>&raquo;">
                            <i class="bi bi-trash"></i> Eliminar</button>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h1 class="h4"><?= limpiar($entrada['titulo']) ?></h1>
                <p class="text-muted small mb-2">
                    <i class="bi bi-person"></i> <?= limpiar($entrada['nombre'] . ' ' . $entrada['apellido']) ?>
                    (<b><?= limpiar($entrada['nick']) ?></b>)
                    &nbsp;&middot;&nbsp; <span class="badge badge-info"><?= limpiar($entrada['categoria']) ?></span>
                    &nbsp;&middot;&nbsp; <i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i', strtotime($entrada['fecha'])) ?>
                </p>
                <hr>
                <?php if (imagenOMarcador($entrada['imagen'])): ?>
                    <img src="<?= imagenOMarcador($entrada['imagen']) ?>" alt="Imagen de la entrada" class="img-detalle">
                <?php endif; ?>
                <!-- Contenido completo generado con CKEditor -->
                <div><?= $entrada['descripcion'] /* HTML del editor */ ?></div>
            </div>
            <div class="card-footer text-right">
                <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver al listado</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
