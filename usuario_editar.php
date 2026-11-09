<?php
/**
 * Edición de usuarios (Ejercicio 2.5) — el administrador edita cualquier usuario.
 * La contraseña es opcional (sólo se cambia si se escriben caracteres).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';
require_once __DIR__ . '/funciones/log.php';

requerirAdmin();

$errores = [];
$idUsuario = idDesdePeticion();

try {
    $pdo = conexion();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) { header('Location: usuarios.php?cod=noregistro'); exit; }
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nick     = filtrarTexto($_POST['nick'] ?? '');
    $nombre   = filtrarTexto($_POST['nombre'] ?? '');
    $apellido = filtrarTexto($_POST['apellido'] ?? '');
    $email    = filtrarTexto($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $perfil   = ($_POST['perfil'] ?? 'user') === 'admin' ? 'admin' : 'user';
    $quitarAvatar = isset($_POST['quitarImagen']);

    if (!validarLongitud($nick, 3, 50))    $errores[] = 'El nick debe tener entre 3 y 50 caracteres.';
    if (!validarLongitud($nombre, 2, 100)) $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
    if ($apellido !== '' && !validarLongitud($apellido, 2, 100)) $errores[] = 'Los apellidos no pueden superar los 100 caracteres.';
    if (!validarEmail($email))             $errores[] = 'El email no es válido.';
    if ($password !== '' && mb_strlen($password) < 4) $errores[] = 'La contraseña debe tener al menos 4 caracteres.';

    $avatarNuevo = subirImagen('imagen_avatar', $errores);

    if (!$errores) {
        $avatarFinal = $avatarNuevo ?? ($quitarAvatar ? null : $usuario['imagen_avatar']);

        try {
            $pdo->beginTransaction();

            // La contraseña se actualiza sólo si se ha escrito una nueva
            $sql = 'UPDATE usuarios SET nick = :nick, nombre = :nombre, apellido = :apellido,
                    email = :email, perfil = :perfil, imagen_avatar = :avatar' .
                   ($password !== '' ? ', password = :password' : '') . ' WHERE id = :id';
            $params = [
                ':nick' => $nick, ':nombre' => $nombre, ':apellido' => $apellido,
                ':email' => $email, ':perfil' => $perfil, ':avatar' => $avatarFinal, ':id' => $idUsuario,
            ];
            if ($password !== '') { $params[':password'] = password_hash($password, PASSWORD_DEFAULT); }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            registrarLog($pdo, 'edición de usuario (' . $nick . ')', true);
            $pdo->commit();

            if ($avatarNuevo && $usuario['imagen_avatar'] && $usuario['imagen_avatar'] !== $avatarNuevo) {
                eliminarImagen($usuario['imagen_avatar']);
            }
            if ($quitarAvatar && !$avatarNuevo) { eliminarImagen($usuario['imagen_avatar']); }

            header('Location: usuarios.php?cod=editado');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($avatarNuevo) { eliminarImagen($avatarNuevo); }
            $errores[] = 'Error al actualizar el usuario: ' . $e->getMessage();
        }
    }
    if ($errores && $avatarNuevo) { eliminarImagen($avatarNuevo); }
} else {
    $nick = $usuario['nick']; $nombre = $usuario['nombre']; $apellido = $usuario['apellido'];
    $email = $usuario['email']; $perfil = $usuario['perfil'];
}

$tituloPagina = 'Editar usuario';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="card card-borde">
    <div class="card-header bg-dark text-white"><i class="bi bi-pencil-square"></i> Editar usuario #<?= (int) $idUsuario ?></div>
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
                    <label for="password">Contrase&ntilde;a</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="4"
                           placeholder="(en blanco para conservar la actual)">
                </div>
                <div class="col-md-6 form-group">
                    <label for="perfil">Perfil *</label>
                    <select id="perfil" name="perfil" class="form-control" required>
                        <option value="user"  <?= $perfil === 'user' ? 'selected' : '' ?>>user</option>
                        <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                </div>
                <div class="col-md-5 form-group">
                    <label for="imagen_avatar">Cambiar avatar</label>
                    <input type="file" id="imagen_avatar" name="imagen_avatar" class="form-control-file" accept="image/*">
                </div>
                <div class="col-md-4 form-group text-md-center">
                    <?php if (imagenOMarcador($usuario['imagen_avatar'])): ?>
                        <img src="<?= imagenOMarcador($usuario['imagen_avatar']) ?>" alt="Avatar actual" class="avatar" style="width:64px;height:64px">
                        <div class="custom-control custom-checkbox mt-1">
                            <input type="checkbox" class="custom-control-input" id="quitarImagen" name="quitarImagen" value="1">
                            <label class="custom-control-label small" for="quitarImagen">Quitar avatar</label>
                        </div>
                    <?php else: ?>
                        <span class="text-muted small"><i class="bi bi-person-circle"></i> Sin avatar</span>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
