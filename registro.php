<?php
/**
 * Ejercicio 2.3 - Formulario de registro de usuario (perfil 'user'),
 * con validación, sanitización y filtrado de datos.
 * Ejercicio 2.8 - Subida de la imagen-avatar al directorio images/.
 * Ejercicio 2.13 - Se registra la operación en el log (procedimiento BD).
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/saneamiento.php';
require_once __DIR__ . '/helpers/imagenes.php';
require_once __DIR__ . '/helpers/log.php';

$errores = [];
$nombre = $apellido = $email = $nick = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Sanitización y filtrado de la entrada ---
    $nick     = filtrarTexto($_POST['nick'] ?? '');
    $nombre   = filtrarTexto($_POST['nombre'] ?? '');
    $apellido = filtrarTexto($_POST['apellido'] ?? '');
    $email    = filtrarTexto($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!validarLongitud($nick, 3, 50))        $errores[] = 'El nick debe tener entre 3 y 50 caracteres.';
    if (!validarLongitud($nombre, 2, 100))     $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
    if ($apellido !== '' && !validarLongitud($apellido, 2, 100)) $errores[] = 'Los apellidos no pueden superar los 100 caracteres.';
    if (!validarEmail($email))                 $errores[] = 'El email no es válido.';
    if (mb_strlen($password) < 4)              $errores[] = 'La contraseña debe tener al menos 4 caracteres.';
    if ($password !== $password2)              $errores[] = 'Las contraseñas no coinciden.';

    // --- Subida de la imagen avatar (Ej. 2.8) ---
    $avatar = subirImagen('imagen_avatar', $errores);

    if (!$errores) {
        try {
            $pdo = conexion();

            // Comprobamos que el nick / email no existan ya
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE nick = :nick OR email = :email');
            $stmt->execute([':nick' => $nick, ':email' => $email]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errores[] = 'Ya existe un usuario con ese nick o ese email.';
            } else {
                // --- Transacción: INSERT del usuario + registro de log ---
                $pdo->beginTransaction();
                $stmt = $pdo->prepare(
                    'INSERT INTO usuarios (nick, nombre, apellido, email, password, imagen_avatar, perfil)
                     VALUES (:nick, :nombre, :apellido, :email, :password, :avatar, :perfil)'
                );
                $stmt->execute([
                    ':nick'     => $nick,
                    ':nombre'   => $nombre,
                    ':apellido' => $apellido,
                    ':email'    => $email,
                    // Ciframos la contraseña (nunca en claro)
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':avatar'   => $avatar,
                    ':perfil'   => 'user',          // registro público → siempre perfil 'user'
                ]);
                registrarLog($pdo, 'alta de usuario (' . $nick . ')', true);
                $pdo->commit();

                header('Location: login.php?cod=creado');
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($avatar) { eliminarImagen($avatar); }
            $errores[] = 'Error al registrar el usuario: ' . $e->getMessage();
        }
    }

    if ($errores && $avatar) {
        eliminarImagen($avatar);
    }
}

$tituloPagina = 'Registro';
require_once __DIR__ . '/inc/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-borde">
            <div class="card-header bg-dark text-white"><i class="bi bi-person-plus"></i> Registro de nuevo usuario</div>
            <div class="card-body">
                <?php if ($errores): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e) echo '<li>' . limpiar($e) . '</li>'; ?></ul></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" novalidate>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nick">Nick *</label>
                            <input type="text" id="nick" name="nick" class="form-control" value="<?= limpiar($nick) ?>"
                                   required minlength="3" maxlength="50" placeholder="nick">
                        </div>
                        <!-- Ejercicio 2.3 + 2.8: email con validación nativa -->
                        <div class="col-md-6 form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= limpiar($email) ?>"
                                   required maxlength="100" placeholder="usuario@servidor.es">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?= limpiar($nombre) ?>"
                                   required maxlength="100">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="apellido">Apellidos</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" value="<?= limpiar($apellido) ?>"
                                   maxlength="100">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="password">Contrase&ntilde;a *</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="4">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="password2">Repita la contrase&ntilde;a *</label>
                            <input type="password" id="password2" name="password2" class="form-control" required minlength="4">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="imagen_avatar">Imagen avatar</label>
                            <input type="file" id="imagen_avatar" name="imagen_avatar" class="form-control-file"
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">JPG, PNG, GIF o WEBP &middot; m&aacute;x. 2 MB</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Registrarme</button>
                    <a href="login.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/inc/footer.php'; ?>
