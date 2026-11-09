<?php
/**
 * Ejercicio 2.14 - PDF con el contenido de los logs de la aplicación.
 */
require_once __DIR__ . '/plantillas/pdf.php';
require_once __DIR__ . '/funciones/saneamiento.php';
require_once __DIR__ . '/funciones/sesion.php';

$pdo = pdfArrancarApp(true);   // sólo perfil admin

$busqueda = filtrarTexto($_GET['q'] ?? '');
$where    = $busqueda !== '' ? ' WHERE usuario LIKE :q OR operacion LIKE :q' : '';
$params   = $busqueda !== '' ? [':q' => '%' . $busqueda . '%'] : [];

try {
    $stmt = $pdo->prepare('SELECT id, fecha, usuario, operacion FROM logs' . $where . ' ORDER BY fecha DESC, id DESC');
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->execute();
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$pdf = new PDF_Listado();
$pdf->tituloListado = 'Registro de operaciones (logs)';  // la cabecera aplica utf8_decode()
$pdf->perfilUsuario = usuarioActual()['nick'];
$pdf->AliasNbPages();
$pdf->AddPage();

$anchos  = [12, 36, 30, 112];
$titulos = ['ID', 'Fecha / hora', 'Usuario', utf8_decode('Operación realizada')];

$pdf->cabeceraTabla($anchos, $titulos);
if (!$logs) {
    $pdf->sinRegistros();
} else {
    foreach (array_values($logs) as $i => $l) {
        $pdf->fila($anchos, [
            $l['id'],
            date('d/m/Y H:i:s', strtotime($l['fecha'])),
            $l['usuario'],
            $l['operacion'],
        ], $i % 2 === 1);
    }
}

$pdf->Output('I', 'logs_bd_blog.pdf');
