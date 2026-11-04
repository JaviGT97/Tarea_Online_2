<?php
/**
 * Ejercicio 2.3 - Validación, sanitización y filtrado de datos.
 */

/** Escapa texto para mostrar en HTML de forma segura. */
function limpiar(string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** Sanitiza texto recibido: quita etiquetas, espacios y tabulaciones. */
function filtrarTexto(?string $texto): string
{
    return trim(strip_tags($texto ?? ''), " \t\n\r\0\x0B");
}

/** Valida un correo electrónico (FILTER_VALIDATE_EMAIL). */
function validarEmail(?string $email): ?string
{
    $email = filtrarTexto($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

/** Valida y devuelve un entero (o null si no es válido). */
function validarEntero($valor): ?int
{
    $valor = filter_var($valor, FILTER_VALIDATE_INT);
    return $valor === false ? null : $valor;
}

/** Valida que un texto respete una longitud mínima y máxima. */
function validarLongitud(?string $texto, int $min = 1, int $max = 255): bool
{
    $texto = filtrarTexto($texto);
    return $texto !== '' && mb_strlen($texto) <= $max;
}

/** Convierte '<' '>' en entidades: para previsualizar contenido en formularios. */
function mostrarValor(?string $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
