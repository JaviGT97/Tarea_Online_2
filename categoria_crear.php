<?php
/**
 * Alta / edición de categorías (Ejercicios 2.3, 2.5 y 2.6) — formulario 3.
 * Se usa la misma página para crear (sin id) y editar (con id).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/saneamiento.php';
require_once __DIR__ . '/helpers/log.php';

requerirAdmin();

$error = '';
$nombre = '';
$idCategoria = idDesdePeticion();
$editando = $idCategoria > 0;

try {
    $pdo = conexion();
    if ($editando) {
        $stmt = $pdo->prepare('SELECT * FROM categorias WHERE id = :id');
        $stmt->execute([':id' => $idCategoria]);
        $categoria = $stmt->fetch();
        if (!$categoria) { header('Location: categorias.php?cod=noregistro'); exit; }
        $nombre = $categoria['nombre'];
    }
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = filtrarTexto($_POST['nombre'] ?? '');

    if (!validarLongitud($nombre, 2, 100)) {
        $error = 'El nombre debe tener entre 2 y 100 caracteres.';
    } else {
        try {
            $pdo->beginTransaction();
            if ($editando) {
                $stmt = $pdo->prepare('UPDATE categorias SET nombre = :nombre WHERE id = :id');
                $stmt->execute([':nombre' => $nombre, ':id' => $idCategoria]);
                registrarLog($pdo, 'edición de categoría (' . $nombre . ')', true);
                $cod = 'editado';
            } else {
                $stmt = $pdo->prepare('INSERT INTO categorias (nombre) VALUES (:nombre)');
                $stmt->execute([':nombre' => $nombre]);
                registrarLog($pdo, 'alta de categoría (' . $nombre . ')', true);
                $cod = 'creado';
            }
            $pdo->commit();
            header('Location: categorias.php?cod=' . $cod);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error al guardar la categoría: ' . $e->getMessage();
        }
    }
}

$tituloPagina = $editando ? 'Editar categoría' : 'Nueva categoría';
require_once __DIR__ . '/inc/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card card-borde">
            <div class="card-header bg-dark text-white"><i class="bi bi-tag"></i> <?= $editando ? 'Editar categoría #' . (int) $idCategoria : 'Nueva categoría' ?></div>
            <div class="card-body">
                <?php if ($error): ?><div class="alert alert-danger"><?= limpiar($error) ?></div><?php endif; ?>
                <form method="post" novalidate>
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?= limpiar($nombre) ?>"
                               required minlength="2" maxlength="100" placeholder="Nombre de la categoría">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Guardar</button>
                    <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/inc/footer.php'; ?>
