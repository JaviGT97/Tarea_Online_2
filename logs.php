<?php
/**
 * Ejercicio 2.14 - Gestión de los registros de la tabla 'logs':
 * listado (paginado), eliminación individual/vaciado total y creación de PDF.
 * Sólo perfil 'admin'.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/helpers/sesion.php';
require_once __DIR__ . '/helpers/saneamiento.php';
require_once __DIR__ . '/inc/paginacion.php';

requerirAdmin();  // los usuarios → index.php?cod=sinpermisos
$tituloPagina = 'Logs';

try {
    $pdo = conexion();

    $porPagina = validarEntero($_GET['porPagina'] ?? 10) ?? 10;
    $porPagina = in_array($porPagina, [3, 5, 10, 15], true) ? $porPagina : 10;
    $pagina    = max(1, validarEntero($_GET['pagina'] ?? 1) ?? 1);
    $busqueda  = filtrarTexto($_GET['q'] ?? '');

    $where  = $busqueda !== '' ? ' WHERE usuario LIKE :q OR operacion LIKE :q' : '';
    $params = $busqueda !== '' ? [':q' => '%' . $busqueda . '%'] : [];

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM logs' . $where);
    $stmt->execute($params);
    $totalRegistros = (int) $stmt->fetchColumn();
    $totalPaginas   = (int) max(1, ceil($totalRegistros / $porPagina));
    $pagina         = min($pagina, $totalPaginas);
    $offset         = ($pagina - 1) * $porPagina;

    $stmt = $pdo->prepare('SELECT * FROM logs' . $where . ' ORDER BY fecha DESC, id DESC LIMIT :limite OFFSET :offset');
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

require_once __DIR__ . '/inc/header.php';
?>
<h1 class="h3 mb-3"><i class="bi bi-clock-history"></i> Registro de operaciones &mdash; logs (<?= $totalRegistros ?>) <span class="badge badge-warning">s&oacute;lo admin</span></h1>

<div class="d-flex justify-content-between mb-3">
    <form method="get" class="form-inline">
        <input type="search" name="q" class="form-control form-control-sm mr-2" style="min-width:240px"
               value="<?= limpiar($busqueda) ?>" placeholder="Buscar por usuario u operaci&oacute;n...">
        <input type="hidden" name="porPagina" value="<?= (int) $porPagina ?>">
        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i> Buscar</button>
    </form>
    <div>
        <!-- Ejercicio 2.14: PDF con el contenido del listado de logs -->
        <a href="pdf_logs.php?<?= http_build_query(['q' => $busqueda, 'pagina' => $pagina, 'porPagina' => $porPagina]) ?>"
           class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <!-- Vaciar todo el log -->
        <button class="btn btn-outline-secondary btn-sm btn-eliminar"
                data-url="log_vaciar.php" data-nombre="todo el hist&oacute;rico del log"><i class="bi bi-eraser"></i> Vaciar log</button>
    </div>
</div>

<div class="table-responsive card card-borde p-2">
<table class="table table-hover table-sm align-middle mb-0">
    <thead class="thead-dark">
        <tr><th>ID</th><th>Fecha y hora</th><th>Usuario</th><th>Operaci&oacute;n</th><th>Operaciones</th></tr>
    </thead>
    <tbody>
    <?php if (!$logs): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No hay operaciones registradas.</td></tr>
    <?php endif; ?>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= (int) $log['id'] ?></td>
            <td class="small"><?= date('d/m/Y H:i:s', strtotime($log['fecha'])) ?></td>
            <td><span class="badge badge-dark"><?= limpiar($log['usuario']) ?></span></td>
            <td class="small"><?= limpiar($log['operacion']) ?></td>
            <td>
                <button class="btn btn-sm btn-outline-danger btn-eliminar"
                        data-url="log_eliminar.php?id=<?= (int) $log['id'] ?>"
                        data-nombre="el log #<?= (int) $log['id'] ?> (<?= date('d/m/Y H:i:s', strtotime($log['fecha'])) ?>)">
                    <i class="bi bi-trash"></i></button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php dibujarPaginacion($pagina, $totalPaginas, $porPagina, 'logs.php', ['q' => $busqueda]); ?>
<?php require_once __DIR__ . '/inc/footer.php'; ?>
