<?php
/**
 * Alta de usuarios (administradores) — formulario 1 (Ejercicio 2.3).
 * Validación + saneamiento + subida de avatar (Ej. 2.8).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';
require_once __DIR__ . '/funciones/log.php';

requerirAdmin();

$errores = $nick = $nombre = $apellido = $email = $perfil = '';
$perfil = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nick     = filtrarTexto($_POST['nick'] ?? '');
    $nombre   = filtrarTexto($_POST['nombre'] ?? '');
    $apellido = filtrarTexto($_POST['apellido'] ?? '');
    $email    = filtrarTexto($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $perfil   = ($_POST['perfil'] ?? 'user') === 'admin' ? 'admin' : 'user';

    if (!validarLongitud($nick, 3, 50))    $errores[] = 'El nick debe tener entre 3 y 50 caracteres.';
    if (!validarLongitud($nombre, 2, 100)) $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
    if ($apellido !== '' && !validarLongitud($apellido, 2, 100)) $errores[] = 'Los apellidos no pueden superar los 100 caracteres.';
    if (!validarEmail($email))             $errores[] = 'El email no es válido.';
    if (mb_strlen($password) < 4)          $errores[] = 'La contraseña debe tener al menos 4 caracteres.';

    $avatar = subirImagen('imagen_avatar', $errores);

    if (!$errores) {
        try {
            $pdo = conexion();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE nick = :nick OR email = :email');
            $stmt->execute([':nick' => $nick, ':email' => $email]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errores[] = 'Ya existe un usuario con ese nick o ese email.';
            } else {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare(
                    'INSERT INTO usuarios (nick, nombre, apellido, email, password, imagen_avatar, perfil)
                     VALUES (:nick, :nombre, :apellido, :email, :password, :avatar, :perfil)'
                );
                $stmt->execute([
                    ':nick' => $nick, ':nombre' => $nombre, ':apellido' => $apellido,
                    ':email' => $email, ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':avatar' => $avatar, ':perfil' => $perfil,
                ]);
                registrarLog($pdo, 'alta de usuario (' . $nick . ', perfil ' . $perfil . ')', true);
                $pdo->commit();
                header('Location: usuarios.php?cod=creado');
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($avatar) { eliminarImagen($avatar); }
            $errores[] = 'Error al crear el usuario: ' . $e->getMessage();
        }
    }
    if ($errores && $avatar) { eliminarImagen($avatar); }
}

$tituloPagina = 'Crear usuario';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="card card-borde">
    <div class="card-header bg-dark text-white"><i class="bi bi-person-plus"></i> Crear usuario</div>
    <div class="card-body">
        <?php if ($errores): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e) echo '<li>' . limpiar($e) . '</li>'; ?></ul></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" novalidate>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="nick">Nick *</label>
                    <input type="text" id="nick" name="nick" class="form-control" value="<?= limpiar($nick) ?>" required minlength="3" maxlength="50">
                </div>
                <div class="col-md-6 form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= limpiar($email) ?>" required maxlength="100">
                </div>
                <div class="col-md-6 form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?= limpiar($nombre) ?>" required maxlength="100">
                </div>
                <div class="col-md-6 form-group">
                    <label for="apellido">Apellidos</label>
                    <input type="text" id="apellido" name="apellido" class="form-control" value="<?= limpiar($apellido) ?>" maxlength="100">
                </div>
                <div class="col-md-6 form-group">
                    <label for="password">Contrase&ntilde;a *</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="4">
                </div>
                <div class="col-md-6 form-group">
                    <label for="perfil">Perfil *</label>
                    <select id="perfil" name="perfil" class="form-control" required>
                        <option value="user"  <?= $perfil === 'user' ? 'selected' : '' ?>>user</option>
                        <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                </div>
                <div class="col-md-12 form-group">
                    <label for="imagen_avatar">Imagen avatar</label>
                    <input type="file" id="imagen_avatar" name="imagen_avatar" class="form-control-file" accept="image/*">
                    <small class="form-text text-muted">JPG, PNG, GIF o WEBP &middot; m&aacute;x. 2 MB</small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Crear</button>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
