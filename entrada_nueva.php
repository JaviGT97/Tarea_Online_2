<?php
/**
 * Alta de una entrada del Blog.
 * Ejercicio 2.3 - Formulario con validación y saneamiento de datos.
 * Ejercicio 2.8 - Subida de la imagen de la entrada a images/.
 * Ejercicio 2.10 - Edición de la descripción con CKEditor.
 * Se emplea TRANSACCION + control de excepciones (Ej. 2.1) para que la
 * inserción y el registro del log (Ej. 2.13) se completen o se deshagan juntas.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';
require_once __DIR__ . '/funciones/log.php';
require_once __DIR__ . '/funciones/entradas.php';

requerirLogin();

$errores    = [];
$titulo     = '';
$categoria  = '';
$previo     = '';   // descripción (CKEditor) para rellenar el formulario si hay errores

try {
    $pdo        = conexion();
    $categorias = obtenerCategorias($pdo);
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Sanitización / filtrado (Ej. 2.3) ---
    $titulo    = filtrarTexto($_POST['titulo'] ?? '');
    $categoria = validarEntero($_POST['categoria'] ?? null);
    $previo    = $_POST['descripcion'] ?? '';   // se guarda tal cual (CKEditor produce HTML)

    if (!validarLongitud($titulo, 3, 150)) $errores[] = 'El título debe tener entre 3 y 150 caracteres.';
    if (!$categoria)                       $errores[] = 'Debe seleccionar una categoría.';
    if (trim(strip_tags($previo)) === '')  $errores[] = 'La descripción es obligatoria.';

    // --- Imagen (Ej. 2.8) ---
    $imagen = subirImagen('imagen', $errores);

    if (!$errores) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'INSERT INTO entradas (usuario_id, categoria_id, titulo, imagen, descripcion, fecha)
                 VALUES (:usuario_id, :categoria_id, :titulo, :imagen, :descripcion, NOW())'
            );
            $stmt->execute([
                ':usuario_id'   => usuarioActual()['id'],
                ':categoria_id' => $categoria,
                ':titulo'       => $titulo,
                ':imagen'       => $imagen,
                ':descripcion'  => $previo,
            ]);

            // Ejercicio 2.13: log mediante procedimiento almacenado (dentro de la transacción)
            registrarLog($pdo, 'alta de entrada "' . $titulo . '"', true);

            $pdo->commit();
            header('Location: index.php?cod=creado');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($imagen) { eliminarImagen($imagen); }
            $errores[] = 'Error al guardar la entrada (operación deshecha): ' . $e->getMessage();
        }
    }

    if ($errores && $imagen) { eliminarImagen($imagen); }
}

$tituloPagina = 'Nueva entrada';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="card card-borde">
    <div class="card-header bg-dark text-white"><i class="bi bi-plus-circle"></i> Nueva entrada</div>
    <div class="card-body">
        <?php if ($errores): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e) echo '<li>' . limpiar($e) . '</li>'; ?></ul></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" novalidate>
            <div class="row">
                <div class="col-md-7 form-group">
                    <label for="titulo">T&iacute;tulo *</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" value="<?= limpiar($titulo) ?>"
                           required minlength="3" maxlength="150" placeholder="Título de la entrada">
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
                <label for="descripcion">Descripci&oacute;n (contenido de la entrada) *</label>
                <textarea id="descripcion" name="descripcion" rows="6"
                          class="form-control"><?= limpiar($previo) ?></textarea>
                <small class="form-text text-muted"><i class="bi bi-info-circle"></i> Editor CKEditor 5 (Ejercicio 2.10).</small>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen de la entrada</label>
                <input type="file" id="imagen" name="imagen" class="form-control-file" accept="image/*">
                <small class="form-text text-muted">JPG, PNG, GIF o WEBP &middot; m&aacute;x. 2 MB &middot; se guarda en el directorio <code>images/</code></small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Publicar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
require_once __DIR__ . '/plantillas/footer.php';
scriptCKEditor();
