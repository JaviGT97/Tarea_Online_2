<?php
/**
 * Clase PDF común del Blog (FPDF) — Ejercicios 2.11 y 2.14.
 * Diapositiva de portada + tabla de registros con estilo del listado web.
 */
require_once __DIR__ . '/../fpdf/fpdf.php';
require_once __DIR__ . '/sesion.php';

class PDF_Listado extends FPDF
{
    public string $tituloListado = 'Listado';
    public string $perfilUsuario = '';

    function header(): void
    {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 8, utf8_decode('Mi pequeño Blog — ' . $this->tituloListado), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(108, 117, 125);
        $this->Cell(0, 6, utf8_decode('Generado: ' . date('d/m/Y H:i') . '   |   Usuario: ' . $this->perfilUsuario), 0, 1, 'R');
        $this->Ln(4);
    }

    function footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    /** Cabecera de la tabla con columnas y colores estilo "thead-dark". */
    function cabeceraTabla(array $anchos, array $titulos): void
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(33, 37, 41);   // #212529 (thead-dark de Bootstrap)
        $this->SetTextColor(255, 255, 255);
        foreach ($titulos as $i => $t) {
            $this->Cell($anchos[$i], 7, utf8_decode($t), 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(33, 37, 41);
    }

    /** Fila de datos, con fondo alternado como en el listado de Bootstrap. */
    function fila(array $anchos, array $valores, bool $fondoAlternado, int $alto = 7): void
    {
        $this->SetFont('Arial', '', 8.5);
        $this->SetFillColor(240, 241, 245);
        if ($fondoAlternado) { $this->SetDrawColor(206, 208, 214); }
        foreach ($valores as $i => $v) {
            $v = mb_substr((string) $v, 0, 110);  // evitar overflow
            $this->Cell($anchos[$i], $alto, utf8_decode($v), 1, 0, 'L', $fondoAlternado);
        }
        $this->Ln();
    }

    function sinRegistros(): void
    {
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, utf8_decode('No hay registros que listar.'), 1, 1, 'C');
    }
}

/** Comprueba sesión y devuelve la conexión (sólo para admin o usuarios autorizados). */
function pdfArrancarApp(bool $soloAdmin = false)
{
    require_once __DIR__ . '/../config/conexion.php';
    if (!estaLogueado()) {
        header('Location: login.php');
        exit;
    }
    if ($soloAdmin && !esAdmin()) {
        http_response_code(403);
        die('Acceso permitido sólo al perfil admin.');
    }
    return conexion();
}
