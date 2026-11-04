<?php
/**
 * Ejercicio 2.9 - Componente de paginación completo:
 *   selector de nº de páginas/regímenes: #Págs + RegXPág + "Ir a pág".
 * Parámetros: $pagina, $totalPaginas, $porPagina, $urlBase y $params (array de query string).
 */
require_once __DIR__ . '/../helpers/saneamiento.php';

function dibujarPaginacion(int $pagina, int $totalPaginas, int $porPagina, string $urlBase, array $params = []): void
{
    if ($totalPaginas === 0) { return; }

    // Función para construir el enlace conservando los filtros actuales
    $enlace = function (int $p) use ($urlBase, $params, $porPagina) {
        $qs = http_build_query(array_merge($params, ['pagina' => $p, 'porPagina' => $porPagina]));
        return $urlBase . '?' . $qs;
    };
    ?>
    <nav class="mt-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <!-- Selector RegXPág -->
            <form method="get" action="<?= limpiar($urlBase) ?>" class="form-inline mb-2" id="formPorPagina">
                <?php foreach (array_diff_key($params, ['pagina' => 1, 'porPagina' => 1]) as $k => $v): ?>
                    <input type="hidden" name="<?= limpiar($k) ?>" value="<?= limpiar((string)$v) ?>">
                <?php endforeach; ?>
                <label class="mr-2 small">Reg. por p&aacute;gina:</label>
                <select name="porPagina" class="form-control form-control-sm js-autoenvio">
                    <?php foreach ([3, 5, 10, 15] as $n): ?>
                        <option value="<?= $n ?>" <?= $porPagina == $n ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Flechas #Págs -->
            <ul class="pagination pagination-sm mb-2">
                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pagina <= 1 ? '#' : $enlace(1) ?>">&laquo;</a>
                </li>
                <?php
                for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $enlace($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pagina >= $totalPaginas ? '#' : $enlace($totalPaginas) ?>">&raquo;</a>
                </li>
                <li class="page-item disabled"><span class="page-link">de <?= $totalPaginas ?></span></li>
            </ul>

            <!-- Ir a pág -->
            <form method="get" action="<?= limpiar($urlBase) ?>" class="form-inline mb-2">
                <?php foreach (array_diff_key($params, ['pagina' => 1, 'porPagina' => 1]) as $k => $v): ?>
                    <input type="hidden" name="<?= limpiar($k) ?>" value="<?= limpiar((string)$v) ?>">
                <?php endforeach; ?>
                <input type="hidden" name="porPagina" value="<?= (int) $porPagina ?>">
                <label class="mr-2 small">Ir a p&aacute;</label>
                <input type="number" name="pagina" min="1" max="<?= $totalPaginas ?>" value="<?= $pagina ?>"
                       class="form-control form-control-sm js-ir-pagina" style="width:90px">
                <button class="btn btn-sm btn-outline-secondary ml-1" type="submit">Ir</button>
            </form>
        </div>
    </nav>
    <?php
}
