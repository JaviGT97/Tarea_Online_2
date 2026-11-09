<?php
/**
 * Edición de una entrada del Blog (previa carga del registro).
 * Permisos (Ej. 2.6): admin → cualquier entrada; user → sólo las suyas.
 * Ejercicio 2.8: subida de imagen con opción de conservar la actual.
 * Ejercicio 2.10: descripción con CKEditor.
 * TRANSACCION: UPDATE de la entrada + log en el mismo bloque.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';
require_once __DIR__ . '/funciones/log.php';
require_once __DIR__ . '/funciones/entradas.php';

requerirLogin();

$errores = [];
$idEntrada = idDesdePeticion();

try {
    $pdo = conexion();

    $stmt = $pdo->prepare('SELECT * FROM entradas WHERE id = :id');
    $stmt->execute([':id' => $idEntrada]);
    $entrada = $stmt->fetch();

    if (!$entrada) {
        header('Location: index.php?cod=noregistro');
        exit;
    }
    if (!puedeGestionarEntrada($entrada)) {
        header('Location: index.php?cod=sinpermisos');
        exit;
    }

    $categorias = obtenerCategorias($pdo);
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = filtrarTexto($_POST['titulo'] ?? '');
    $categoria = validarEntero($_POST['categoria'] ?? null);
    $previo    = $_POST['descripcion'] ?? '';
    $quitarImagen = isset($_POST['quitarImagen']);

    if (!validarLongitud($titulo, 3, 150)) $errores[] = 'El título debe tener entre 3 y 150 caracteres.';
    if (!$categoria)                       $errores[] = 'Debe seleccionar una categoría.';
    if (trim(strip_tags($previo)) === '')  $errores[] = 'La descripción es obligatoria.';

    $imagenNueva = subirImagen('imagen', $errores);

    if (!$errores) {
        // Imagen final: la nueva, ninguna (si se marca quitar) o la actual
        $imagenFinal = $imagenNueva
            ?? ($quitarImagen ? null : $entrada['imagen']);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'UPDATE entradas
                 SET titulo = :titulo, categoria_id = :categoria_id, imagen = :imagen, descripcion = :descripcion
                 WHERE id = :id'
            );
            $stmt->execute([
                ':titulo'       => $titulo,
                ':categoria_id' => $categoria,
                ':imagen'       => $imagenFinal,
                ':descripcion'  => $previo,
                ':id'           => $idEntrada,
            ]);

            registrarLog($pdo, 'edición de entrada "' . $titulo . '"', true);

            $pdo->commit();

            // Eliminamos del servidor la imagen reemplazada (una vez confirmada la operación)
            if ($imagenNueva && $entrada['imagen'] && $entrada['imagen'] !== $imagenNueva) {
                eliminarImagen($entrada['imagen']);
            }
            if ($quitarImagen && !$imagenNueva) {
                eliminarImagen($entrada['imagen']);
            }

            header('Location: index.php?cod=editado');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($imagenNueva) { eliminarImagen($imagenNueva); }
            $errores[] = 'Error al actualizar la entrada (operación deshecha): ' . $e->getMessage();
        }
    }

    if ($errores && $imagenNueva) { eliminarImagen($imagenNueva); }
} else {
    // Primera carga del formulario: valores actuales de la BD
    $titulo    = $entrada['titulo'];
    $categoria = $entrada['categoria_id'];
    $previo    = $entrada['descripcion'];
}

$tituloPagina = 'Editar entrada';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="card card-borde">
    <div class="card-header bg-dark text-white"><i class="bi bi-pencil"></i> Editar entrada #<?= (int) $idEntrada ?></div>
    <div class="card-body">
        <?php if ($errores): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e) echo '<li>' . limpiar($e) . '</li>'; ?></ul></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="<?= (int) $idEntrada ?>">
            <div class="row">
                <div class="col-md-7 form-group">
                    <label for="titulo">T&iacute;tulo *</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" value="<?= limpiar($titulo) ?>"
                           required minlength="3" maxlength="150">
                </div>
                <div class="col-md-5 form-group">
                    <label for="categoria">Categor&iacute;a *</label>
                    <select id="categoria" name="categoria" class="form-control" required>
                        <option value="">-- Elija una categoría --</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= $categoria == $c['id'] ? 'selected' : '' ?>><?= limpiar($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripci&oacute;n *</label>
                <textarea id="descripcion" name="descripcion" rows="6"
                          class="form-control"><?= limpiar($previo) ?></textarea>
                <small class="form-text text-muted"><i class="bi bi-info-circle"></i> Editor CKEditor 5 (Ejercicio 2.10).</small>
            </div>
            <div class="row align-items-center">
                <div class="col-md-5 form-group">
                    <label for="imagen">Cambiar imagen</label>
                    <input type="file" id="imagen" name="imagen" class="form-control-file" accept="image/*">
                </div>
                <div class="col-md-4 form-group text-md-center">
                    <?php if (imagenOMarcador($entrada['imagen'])): ?>
                        <img src="<?= imagenOMarcador($entrada['imagen']) ?>" alt="Imagen actual" class="miniatura">
                        <div class="custom-control custom-checkbox mt-1">
                            <input type="checkbox" class="custom-control-input" id="quitarImagen" name="quitarImagen" value="1">
                            <label class="custom-control-label small" for="quitarImagen">Quitar imagen</label>
                        </div>
                    <?php else: ?>
                        <span class="text-muted small"><i class="bi bi-image"></i> Sin imagen</span>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
require_once __DIR__ . '/plantillas/footer.php';
scriptCKEditor();
