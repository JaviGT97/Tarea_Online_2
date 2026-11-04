# «Mi pequeño Blog» — Tarea Online 2

Blog sencillo en **PHP 8 + PDO (MySQL/MariaDB)** con **Bootstrap 4** para el diseño,
que demuestra el uso de **transacciones**, **procedimientos almacenados** y el
**control de excepciones** como mecanismo de verificación de las operaciones sobre la BD.

## Puesta en marcha

1. **XAMPP**: Apache y MySQL en marcha.
2. Importar la BD: en phpMyAdmin → *Importar* → `bd/bdBlog.sql` (crea la base
   `bd_blog`, sus tablas, relaciones, el procedimiento `sp_registrar_log` y datos de ejemplo).
   O por consola: `mysql -u root < bd/bdBlog.sql`
3. Abrir `http://localhost/Tarea_Online_2/login.php`.

**Usuarios de ejemplo:**

| Perfil | Nick     | Contraseña |
|--------|----------|------------|
| admin  | `admin`  | `admin`    |
| user   | `javier` | `1234`     |

## Estructura

```
Tarea_Online_2/
├── bd/bdBlog.sql               # Esquema BD + procedimiento almacenado (Ej. 2.1, 2.13)
├── config/conexion.php         # Conexión PDO con try/catch (Ej. 2.1)
├── config/config.php           # Constantes (carpeta images, tamaño máx, extensiones)
├── helpers/
│   ├── sesion.php              # Perfiles admin/user + permisos (Ej. 2.2)
│   ├── saneamiento.php         # Validación, sanitización y filtrado (Ej. 2.3)
│   ├── imagenes.php            # Subida de imágenes a images/ (Ej. 2.8)
│   ├── log.php                 # Registro en logs vía procedimiento BD (Ej. 2.13)
│   └── entradas.php            # Código compartido alta/edición + CKEditor (Ej. 2.10)
├── inc/
│   ├── header.php / footer.php # Plantilla Bootstrap 4 + menú según perfil
│   ├── paginacion.php          # Componente de paginación (Ej. 2.9)
│   ├── pdf.php                 # Clase FPDF común (Ej. 2.11, 2.14)
│   └── scripts.php             # Modal de confirmación + JS paginación (Ej. 2.6)
├── login.php / salir.php / registro.php     # Acceso y registro (Ej. 2.2, 2.3)
├── index.php                   # Listado de ENTRADAS por defecto (Ej. 2.4)
├── entrada_{nueva,editar,detalle,eliminar}.php
├── usuarios.php + usuario_{crear,editar,detalle,eliminar}.php
├── categorias.php + categoria_{crear,editar,detalle,eliminar}.php
├── logs.php + log_{eliminar,vaciar}.php     # Gestión del log (Ej. 2.14)
├── pdf_{entradas,logs}.php     # Impresión en PDF (Ej. 2.11/2.14)
├── fpdf/                       # Librería FPDF 1.86 (no modificar)
└── images/                     # Imágenes subidas por los formularios (Ej. 2.8)
```

## Correspondencia ejercicios ↔ implementación

| Ejercicio | Dónde se resuelve |
|-----------|-------------------|
| 2.1 BD + conexión PDO con excepciones | `bd/bdBlog.sql`, `config/conexion.php` |
| 2.2 Control de acceso, perfiles admin/user | `login.php`, `helpers/sesion.php` (`requerirLogin`, `requerirAdmin`, `puedeGestionarEntrada`) |
| 2.3 Tres formularios con validación/sanitización | `usuario_crear.php` (usuarios), `entrada_nueva.php` (entradas), `categoria_crear.php` (categorías) — helpers en `saneamiento.php` |
| 2.4 Listados con columna *Operaciones* | `index.php` (defecto, ordenado por fecha), `usuarios.php`, `categorias.php`, `logs.php` |
| 2.5 Edición previa confirmación de registros | `*_editar.php` (enlace *Editar* en Operaciones) |
| 2.6 Eliminación previa confirmación + FK | Modales Bootstrap (Botón `btn-eliminar` + `inc/scripts.php`) y comprobación de registros relacionados antes del borrado (`usuario_eliminar.php`, `categoria_eliminar.php`) |
| 2.7 Páginas dinámicas de detalle | `entrada_detalle.php`, `usuario_detalle.php` (+ `categoria_detalle.php`) |
| 2.8 Subir imágenes en listado/creación/edición | `helpers/imagenes.php`, `registro.php`, `usuario_*`, `entrada_*` |
| 2.9 Paginación completa | `index.php`, `usuarios.php`, `logs.php` con `inc/paginacion.php` (#Págs + RegXPág en `<select>` + Ir a pág) |
| 2.10 CKEditor en edición de entradas | `entrada_nueva.php` / `entrada_editar.php` (`helpers/entradas.php`) |
| 2.11 Listado en PDF | `pdf_entradas.php` (botón PDF en `index.php`) |
| 2.12 Ordenar por fecha asc/desc con icono | Cabecera «Fecha» de `index.php` (`?orden=asc|desc`) |
| 2.13 Procedimiento almacenado 'logs' | `sp_registrar_log` en `bd/bdBlog.sql`; llamado por `helpers/log.php` dentro de transacciones |
| 2.14 Gestión del log (listar, eliminar, PDF) | `logs.php`, `log_eliminar.php`, `log_vaciar.php`, `pdf_logs.php` |
| 2.15 Control de versiones con Git | `git log` (un commit por funcionalidad) |

## Transacciones y control de excepciones

Todas las operaciones de modificación (`alta`, `edición`, `eliminación`) siguen el
mismo patrón: `beginTransaction()` → sentencias preparadas → llamada al
procedimiento `sp_registrar_log` → `commit()`; **si algo falla, `rollBack()`**
deshace la operación y la imagen ya subida se elimina del servidor
(`catch (PDOException $e)`).

## Seguridad

- Sentencias **preparadas** (parámetros vinculados) en todas las consultas → *SQL injection*.
- `htmlspecialchars()` en toda la salida → *XSS*.
- `strip_tags`/`filter_var` en la entrada; imágenes validadas por **MIME real**,
  extensión y tamaño, con nombres aleatorios (`uniqid`).
- Contraseñas cifradas con `password_hash()` / `password_verify()`.

## Pruebas manuales adicionales

- Ordenar por fecha: enlace en la cabecera «Fecha» de `index.php`.
- Paginación: cambiar registros por página y usar «Ir a pág» (añade ≥ 10 entradas).
- Eliminar una categoría o usuario con entradas → alerta «tiene registros relacionados».
- PDF: botón rojo *PDF* en el listado de entradas y en logs (perfil admin).
