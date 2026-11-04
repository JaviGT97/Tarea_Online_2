<?php
/**
 * "Mi pequeño Blog" - Código compartido por las páginas de alta y edición
 * de entradas (formularios del Ejercicio 2.3 + CKEditor Ej. 2.10).
 */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../helpers/saneamiento.php';

/** Devuelve las categorías para el SELECT del formulario. */
function obtenerCategorias(PDO $pdo): array
{
    return $pdo->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
}

/** CKEditor 5 (Ejercicio 2.10) - <script> a incluir en el pie. */
function scriptCKEditor(): void
{
    echo '<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    // Registro global de instancias (útil para sincronizar el textarea / Testing)
    window.ckeditorInstances = window.ckeditorInstances || [];
    ClassicEditor
        .create(document.querySelector("#descripcion"), {
            toolbar: ["heading", "|", "bold", "italic", "link", "bulletedList", "numberedList", "|", "insertTable", "|", "undo", "redo"],
            language: "es"
        })
        .then(function (editor) {
            window.ckeditorInstances.push(editor);
        })
        .catch(function (error) { console.error(error); });
</script>';
}
