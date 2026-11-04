<?php
/**
 * Ejercicio 2.11 - Impresión del listado de entradas en PDF (FPDF).
 * Respeta la ordenación (asc/desc) y los filtros del listado de la página index.php.
 */
require_once __DIR__ . '/inc/pdf.php';
require_once __DIR__ . '/helpers/saneamiento.php';
require_once __DIR__ . '/helpers/sesion.php';

$pdo = pdfArrancarApp();

$orden           = ($_GET['orden'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$filtroCategoria = filter_var($_GET['categoria'] ?? null, FILTER_VALIDATE_INT);

try {
    $sql    = 'SELECT e.id, e.titulo, c.nombre AS categoria, u.nick, e.fecha, e.imagen
               FROM entradas e
               INNER JOIN usuarios u ON e.usuario_id = u.id
               INNER JOIN categorias c ON e.categoria_id = c.id';
    $params = [];
    if ($filtroCategoria) { $sql .= ' WHERE e.categoria_id = :cat'; $params[':cat'] = $filtroCategoria; }
    $sql .= " ORDER BY e.fecha $orden, e.id $orden";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v, PDO::PARAM_INT); }
    $stmt->execute();
    $entradas = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$pdf = new PDF_Listado('P', 'mm', 'A4');
$pdf->tituloListado = 'Listado de entradas';
$pdf->perfilUsuario = usuarioActual()['nick'];
$pdf->AliasNbPages();
$pdf->AddPage();

$anchos  = [12, 70, 32, 25, 28, 23];
$titulos = ['ID', utf8_decode('Título'), 'Categoría', 'Autor', 'Fecha', 'Imagen'];

$pdf->cabeceraTabla($anchos, $titulos);
if (!$entradas) {
    $pdf->sinRegistros();
} else {
    foreach (array_values($entradas) as $i => $e) {
        $pdf->fila($anchos, [
            $e['id'],
            $e['titulo'],
            $e['categoria'],
            $e['nick'],
            date('d/m/Y H:i', strtotime($e['fecha'])),
            $e['imagen'] ? 'Sí: ' . $e['imagen'] : '—',
        ], $i % 2 === 1);
    }
}

$pdf->Output('I', 'entradas_blogs.pdf');
