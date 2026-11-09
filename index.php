<?php
/**
 * Ejercicio 2.4  - Listado de las entradas del Blog, ordenadas por fecha
 *                  de creación de más a menos reciente, con columna "Operaciones"
 *                  (detalle / editar / eliminar) según perfil de usuario.
 * Ejercicio 2.6  - Eliminación de registros con ventana modal Bootstrap.
 * Ejercicio 2.9  - Sistema de paginación completo (#Págs + RegXPág + Ir a pág).
 * Ejercicio 2.11 - Impresión del listado en PDF.
 * Ejercicio 2.12 - Ordenación asc/desc por fecha mediante iconos en la cabecera.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/funciones/sesion.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/imagenes.php';
require_once __DIR__ . '/plantillas/paginacion.php';

requerirLogin();

// ---------- Orden por fecha (Ej. 2.12): asc|desc ----------
$orden = ($_GET['orden'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

// ---------- Paginación (Ej. 2.9) ----------
$porPagina    = validarEntero($_GET['porPagina'] ?? 5) ?? 5;
$porPagina    = in_array($porPagina, [3, 5, 10, 15], true) ? $porPagina : 5;
$pagina       = max(1, validarEntero($_GET['pagina'] ?? 1) ?? 1);
$offset       = ($pagina - 1) * $porPagina;

// Filtro adicional por autor (los usuarios sólo ven el detalle eius) y por categoría
$filtroCategoria   = validarEntero($_GET['categoria'] ?? null);
$filtroSoloMias    = isset($_GET['mias']) && !esAdmin();

try {
    $pdo = conexion();

    // --------- Consulta con total de registros (para paginación) ---------
    $sqlBase = 'FROM entradas e
                INNER JOIN usuarios u ON e.usuario_id = u.id
                INNER JOIN categorias c ON e.categoria_id = c.id'
              . ($filtroCategoria ? ' WHERE e.categoria_id = :categoria' : '')
              . ($filtroSoloMias ? ' WHERE e.usuario_id = :usuario' : '');

    $params = [];
    if ($filtroCategoria) { $params[':categoria'] = $filtroCategoria; }
    if ($filtroSoloMias)  { $params[':usuario'] = usuarioActual()['id']; }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS total ' . $sqlBase);
    $stmt->execute($params);
    $totalRegistros = (int) $stmt->fetchColumn();
    $totalPaginas   = (int) max(1, ceil($totalRegistros / $porPagina));
    $pagina         = min($pagina, $totalPaginas);
    $offset         = ($pagina - 1) * $porPagina;

    // --------- Listado ordenado por fecha (Ej. 2.4 y 2.12) ---------
    $stmt = $pdo->prepare('SELECT e.*, u.nick, u.nombre, u.apellido, c.nombre AS categoria ' . $sqlBase .
                          " ORDER BY e.fecha $orden, e.id $orden LIMIT :limite OFFSET :offset");
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v, PDO::PARAM_INT); }
    $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $entradas = $stmt->fetchAll();

    // Categorías para el filtro
    $categorias = $pdo->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
} catch (PDOException $e) {
    die('Error al recuperar los datos: ' . htmlspecialchars($e->getMessage()));
}

$paramsUrl = array_filter(['orden' => strtolower($orden), 'categoria' => $filtroCategoria, 'mias' => $filtroSoloMias ? 1 : null],
                          fn($v) => $v !== null && $v !== false);

$tituloPagina = 'Entradas';
require_once __DIR__ . '/plantillas/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="bi bi-journal-text"></i> Entradas del blog (<?= $totalRegistros ?>)</h1>
    <div>
        <!-- Ejercicio 2.11: imprimir el listado en PDF -->
        <a href="pdf_entradas.php?<?= http_build_query(array_merge($paramsUrl, ['pagina' => $pagina, 'porPagina' => $porPagina])) ?>"
           class="btn btn-outline-danger btn-sm" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="entrada_nueva.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Nueva entrada</a>
    </div>
</div>

<!-- Filtros -->
<form method="get" class="form-inline mb-3" action="index.php">
    <label class="mr-2 small">Categor&iacute;a:</label>
    <select name="categoria" class="form-control form-control-sm mr-3">
        <option value="">Todas</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filtroCategoria == $c['id'] ? 'selected' : '' ?>><?= limpiar($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="orden" value="<?= limpiar(strtolower($orden)) ?>">
    <input type="hidden" name="porPagina" value="<?= (int) $porPagina ?>">
    <?php if ($filtroSoloMias): ?><input type="hidden" name="mias" value="1"><?php endif; ?>
    <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
    <?php if (esAdmin()): ?>
        <div class="custom-control custom-checkbox ml-3">
            <input type="checkbox" class="custom-control-input" id="mias" name="mias" value="1" <?= $filtroSoloMias ? 'checked' : '' ?>>
            <label class="custom-control-label small" for="mias">S&oacute;lo mis entradas</label>
        </div>
    <?php endif; ?>
</form>

<!-- ---------- Listado de entradas ---------- -->
<div class="table-responsive card card-borde p-2">
<table class="table table-hover table-sm align-middle mb-0">
    <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Imagen</th>
            <th>T&iacute;tulo</th>
            <th>Categor&iacute;a</th>
            <th>Autor</th>
            <!-- Ejercicio 2.12: icono para ordenar por fecha asc/desc -->
            <th class="fd-fecha" title="Ordenar por fecha">
                <a class="text-decoration-none text-white" href="index.php?<?= http_build_query(array_merge($paramsUrl, ['orden' => $orden === 'ASC' ? 'desc' : 'asc'])) ?>">
                    Fecha
                    <i class="bi <?= $orden === 'ASC' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up-alt' ?>"></i>
                    <small class="d-none d-md-inline">(<?= $orden === 'ASC' ? 'más antiguas primero' : 'más recientes primero' ?>)</small>
                </a>
            </th>
            <!-- Ejercicio 2.5 / 2.6: columna Operaciones -->
            <th>Operaciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!$entradas): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No hay entradas registradas.</td></tr>
    <?php endif; ?>
    <?php foreach ($entradas as $entrada): ?>
        <tr>
            <td><?= (int) $entrada['id'] ?></td>
            <td>
                <?php if (imagenOMarcador($entrada['imagen'])): ?>
                    <img src="<?= imagenOMarcador($entrada['imagen']) ?>" alt="Entrada <?= (int) $entrada['id'] ?>" class="miniatura">
                <?php else: ?>
                    <i class="bi bi-image text-muted" style="font-size:2rem"></i>
                <?php endif; ?>
            </td>
            <td><?= limpiar(mb_substr($entrada['titulo'], 0, 45)) ?></td>
            <td><span class="badge badge-info"><?= limpiar($entrada['categoria']) ?></span></td>
            <td><?= limpiar($entrada['nick']) ?></td>
            <td class="small"><?= date('d/m/Y H:i', strtotime($entrada['fecha'])) ?></td>
            <td class="text-nowrap">
                <!-- Detalle (Ej. 2.7): disponibles para todos -->
                <a href="entrada_detalle.php?id=<?= (int) $entrada['id'] ?>" class="btn btn-sm btn-outline-info" title="Detalle">
                    <i class="bi bi-eye"></i></a>
                <?php if (puedeGestionarEntrada($entrada)): ?>
                    <a href="entrada_editar.php?id=<?= (int) $entrada['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil"></i></a>
                    <!-- Ejercicio 2.6: eliminar con modal de confirmación -->
                    <button class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar"
                            data-url="entrada_eliminar.php?id=<?= (int) $entrada['id'] ?>"
                            data-nombre="la entrada &laquo;<?= limpiar(mb_substr($entrada['titulo'], 0, 30)) ?>&raquo;">
                        <i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php dibujarPaginacion($pagina, $totalPaginas, $porPagina, 'index.php', $paramsUrl); ?>

<?php require_once __DIR__ . '/plantillas/footer.php'; ?>
