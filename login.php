<?php
/**
 * Ejercicio 2.2 - Control de acceso: login con perfil admin / user.
 * Ejercicio 2.3 - Validación, sanitización y filtrado de los datos del formulario.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/saneamiento.php';

$errores  = [];
$nick     = '';

// Si ya hay sesión, al listado
if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nick = filtrarTexto($_POST['nick'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Validación / filtrado ---
    if (!validarLongitud($nick, 3, 50)) {
        $errores[] = 'El nick debe tener entre 3 y 50 caracteres.';
    }
    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }

    if (!$errores) {
        try {
            $pdo  = conexion();
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nick = :nick LIMIT 1');
            $stmt->execute([':nick' => $nick]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                loginUsuario($usuario);
                header('Location: index.php');
                exit;
            }
            $errores[] = 'Nick o contraseña incorrectos.';
        } catch (PDOException $e) {
            $errores[] = 'Error al consultar la base de datos: ' . $e->getMessage();
        }
    }
}

$tituloPagina = 'Acceso';
require_once __DIR__ . '/inc/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card card-borde">
            <div class="card-header bg-dark text-white"><i class="bi bi-box-arrow-in-right"></i> Acceso al blog</div>
            <div class="card-body">
                <?php if ($errores): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errores as $e) echo '<li>' . limpiar($e) . '</li>'; ?></ul></div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="form-group">
                        <label for="nick">Nick</label>
                        <input type="text" id="nick" name="nick" class="form-control <?= $nick && !$errores ? '' : '' ?>"
                               value="<?= limpiar($nick) ?>" required minlength="3" maxlength="50" pattern=".{3,50}"
                               placeholder="Su nick de usuario">
                    </div>
                    <div class="form-group">
                        <label for="password">Contrase&ntilde;a</label>
                        <input type="password" id="password" name="password" class="form-control" required
                               placeholder="Su contraseña">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-unlock"></i> Entrar</button>
                    <p class="text-center mt-3 mb-0 small">¿No tiene cuenta? <a href="registro.php">Reg&iacute;strese aqu&iacute;</a></p>
                </form>
            </div>
            <div class="card-footer text-muted small text-center">
                Usuarios de prueba: <b>admin</b> / admin &nbsp;&middot;&nbsp; <b>javier</b> / 1234
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/inc/footer.php'; ?>
