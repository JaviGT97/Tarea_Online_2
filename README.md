# «Mi pequeño Blog» — Tarea Online 2 (DAW 2)

Blog sencillo en **PHP 8 + PDO sobre MySQL**, maquetado con **Bootstrap 4**.
La idea de la tarea es practicar: PDO con **sentencias preparadas**,
**transacciones** y **control de excepciones**, además de subir **imágenes**,
generar **PDF**, usar **CKEditor** y el **procedimiento almacenado** de los logs.

## Cómo montarlo (XAMPP)

1. Arranca Apache y MySQL desde el Panel de Control de XAMPP.
2. Importa el script SQL: phpMyAdmin → Importar → `bd/bdBlog.sql`
   (crea la base `bd_blog`, las 4 tablas, las claves ajenas, el procedimiento
   `sp_registrar_log` y unos datos de ejemplo).
   Por consola también vale: `mysql -u root < bd/bdBlog.sql`
3. Abre `http://localhost/Tarea_Online_2/login.php`

**Cuentas de ejemplo:**

| Perfil | Nick     | Contraseña |
|--------|----------|------------|
| admin  | `admin`  | `admin`    |
| user   | `javier` | `1234`     |

## Estructura del proyecto

```
Tarea_Online_2/
├── bd/bdBlog.sql               # Ej. 2.1 y 2.13: esquema BD + procedimiento almacenado
├── config/
│   ├── conexion.php            # Ej. 2.1: conexión PDO con try/catch
│   └── config.php              # Constantes (ruta images/, máximo 2 MB, extensiones)
├── funciones/
│   ├── sesion.php              # Ej. 2.2: perfiles admin/user y permisos
│   ├── saneamiento.php         # Ej. 2.3: validación, sanitización y filtrado
│   ├── imagenes.php            # Ej. 2.8: subida de imágenes a images/
│   ├── log.php                 # Ej. 2.13: llama al procedimiento sp_registrar_log
│   └── entradas.php            # Ej. 2.10: CKEditor en alta/edición de entradas
├── plantillas/
│   ├── header.php / footer.php # Cabecera, menú por perfil y pie
│   ├── scripts.php             # Ej. 2.6: modal de confirmación + autoenvío paginación
│   ├── paginacion.php          # Ej. 2.9: componente de paginación
│   └── pdf.php                 # Ej. 2.11/2.14: clase PDF_Listado (FPDF)
├── *.php (raíz)                # Todas las páginas (login, index, CRUDs, detailles, PDFs)
├── assets/vendor/              # Librerías locales: Bootstrap 4.6.2, jQuery, Icons, CKEditor
├── fpdf/                       # Librería FPDF 1.86
├── images/                     # Carpeta de imágenes subidas por los formularios
├── README.md                   # Este fichero
└── memoria.md                  # Memoria de la tarea (ejercicios y pruebas)
```

## Correspondencia ejercicios ↔ ficheros

| Ejercicio | Dónde está |
|-----------|------------|
| 2.1 BD + conexión PDO con excepciones | `bd/bdBlog.sql`, `config/conexion.php` |
| 2.2 Control de acceso (admin y user) | `login.php`, `funciones/sesion.php` |
| 2.3 Tres formularios validados | `usuario_crear.php` (usuarios), `entrada_nueva.php` (entradas), `categoria_crear.php` (categorías) |
| 2.4 Listados + columna Operaciones | `index.php` (por defecto), `usuarios.php`, `categorias.php` |
| 2.5 Edición previa de registros | `entrada_editar.php`, `usuario_editar.php`, `categoria_editar.php` |
| 2.6 Eliminación con modal + FK | Botones `.btn-eliminar` + `plantillas/scripts.php`; control de relaciones en `usuario_eliminar.php` y `categoria_eliminar.php` |
| 2.7 Detalle completo dinámico | `entrada_detalle.php`, `usuario_detalle.php` |
| 2.8 Imágenes en images/ | `funciones/imagenes.php` + todos los formularios |
| 2.9 Paginación completa | `plantillas/paginacion.php` (RegXPág + nº de páginas + ir a page) |
| 2.10 CKEditor | `funciones/entradas.php` + `entrada_nueva/editar.php` |
| 2.11 PDF del listado | `pdf_entradas.php` (botón PDF en `index.php`) |
| 2.12 Orden por fecha con icono | Cabecera «Fecha» de `index.php` (`?orden=asc|desc`) |
| 2.13 Procedimiento almacenado | `sp_registrar_log` en `bd/bdBlog.sql`, usado por `funciones/log.php` dentro de las transacciones |
| 2.14 Gestión del log | `logs.php`, `log_eliminar.php`, `log_vaciar.php`, `pdf_logs.php` |
| 2.15 Git | `git log --oneline` (un commit por bloque de funcionalidad) |

## Transacciones y control de excepciones (resumen)

Todas las operaciones de escritura (altas, ediciones y borrados) siguen el mismo
patrón sencillo:

```php
$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT / UPDATE / DELETE ...');
$stmt->execute([...]);
registrarLog($pdo, 'operación ...', true);   // Ej. 2.13, dentro de la transacción
$pdo->commit();
// Si falla cualquier cosa → catch PDOException → rollBack
```

Si la operación se deshace, la imagen que se hubiera subido también se elimina
del directorio `images/`.

## Seguridad (lo básico que hay que hacer siempre)

- Sentencias **preparadas** en todas las consultas → evita SQL injection.
- `htmlspecialchars()` al mostrar → evita XSS.
- `strip_tags` / `filter_var` al recibir los datos del formulario.
- Imágenes: tipo MIME real, extensión y tamaño comprobados, y nombre único con `uniqid()`.
- Contraseñas cifradas con `password_hash()` y comprobadas con `password_verify()`.
