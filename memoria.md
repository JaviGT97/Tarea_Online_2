# Memoria — «Mi pequeño Blog» (Tarea Online 2)

Curso: 2.º DAW — Desarrollo Web en Entorno Servidor
Aplicación: blog en PHP con PDO, transacciones, control de excepciones y maquetación Bootstrap 4.

Cada apartado explica **qué he hecho** y **cómo lo he comprobado** (todas las
pruebas listadas aquí están hechas sobre XAMPP: Apache 2.4 + MariaDB + PHP 8.2).

---

## Ejercicio 2.1 — Base de datos y conexión PDO

**Qué he hecho.** Con PhpMyAdmin (importando `bd/bdBlog.sql`) he creado la base
de datos `bd_blog` con las tres tablas del esquema:

- `usuarios`: id (PK), nick (único), nombre, apellido, email, password
  (cifrada con `password_hash`), imagen_avatar y perfil (admin/user).
- `entradas`: id (PK), usuario_id (FK→usuarios), categoria_id (FK→categorias),
  titulo, imagen, descripcion (contenido HTML del editor) y fecha.
- `categorias`: id (PK), nombre.
- Además la tabla `logs` (necesaria para los ejercicios 2.13 y 2.14).

Las relaciones están definidas con claves ajenas: `ON DELETE CASCADE` para
borrar las entradas si se borra su autor, y `ON DELETE RESTRICT` en la
categoría, para que NO se pueda borrar una categoría que tiene entradas.

El script de conexión es `config/conexion.php`: función `conexion()` con un
`try/catch` que lanza `die()` con el mensaje si algo falla, y deja PDO
configurado con `ERRMODE_EXCEPTION` y `FETCH_ASSOC`.

**Cómo lo he probado.**
- `SHOW TABLES` → las 4 tablas existen; con `information_schema` compruebo las
  2 claves ajenas (`fk_entradas_usuario`, `fk_entradas_categoria`).
- Cargando cualquier página de la app: si la conexión fallara, veríamos el
  mensaje «Error de conexión a la base de datos».

## Ejercicio 2.2 — Control de acceso con dos perfiles

**Qué he hecho.** `login.php` valida nick y contraseña con
`password_verify()`, y guarda el usuario en `$_SESSION`. El menú y los botones
se dibujan según el perfil (`funciones/sesion.php`):
- `requerirLogin()` → sin sesión, va al login.
- `requerirAdmin()` → si no eres admin te devuelve al listado con un aviso.
- El perfil `user` sólo puede editar/eliminar sus propias entradas
  (`puedeGestionarEntrada()`).

**Cómo lo he probado.**
1. Login con contraseña mal escrita → aviso «Nick o contraseña incorrectos».
2. Login `admin/admin` → entro al listado y el menú muestra Usuarios y Logs.
3. Login `javier/1234` → el menú NO muestra Usuarios ni Logs, y al intentar
   abrir `entrada_editar.php` de una entrada ajena me devuelve
   `index.php?cod=sinpermisos`.

## Ejercicio 2.3 — Tres formularios con validación y sanitización

**Qué he hecho.** Los tres son `usuario_crear.php` (+`registro.php`),
`entrada_nueva.php` y `categoria_crear.php`. En todos:
- en el cliente, atributos HTML5 (`required`, `minlength`, `maxlength`,
  `type=email` y `accept` en las imágenes);
- en el servidor, limpieza con `strip_tags`/`trim` y la funciones de
  `funciones/saneamiento.php` (`validarEmail`, `validarLongitud`,
  `validarEntero`, `filtrarTexto`), y `htmlspecialchars()` al mostrar.

**Cómo lo he probado.**
- Registro con «correo-mal-sin-arroba» → «El email no es válido.»
- Categoría llamada «a» → no se guarda (encontré y arreglé un fallo:
  `validarLongitud()` no tenía en cuenta el mínimo).
- Entrada vacía → tres avisos (título, categoría y descripción).
- Envíos correctos → guardan y vuelven al listado con el aviso
  «Registro creado correctamente».

## Ejercicio 2.4 — Listados y columna Operaciones

**Qué he hecho.** `index.php` (entradas, listado por defecto), `usuarios.php`
(sólo admin), `categorias.php` (admin gestiona, user consulta) y `logs.php`
(sólo admin). Todos llevan la columna **Operaciones** con detalle, editar y
eliminar según el perfil: el botón de eliminar aparece deshabilitado cuando el
registro tiene registros relacionados.

**Cómo lo he probado.**
- El listado por defecto sale ordenado por fecha de más a menos reciente.
- Con javier: en una página llena de sus entradas, 5 detalle + 5 editar +
  5 eliminar; con admin aparecen todas las Operaciones.

## Ejercicio 2.5 — Edición previa de registros

**Qué he hecho.** El enlace *Editar* lleva a la página de edición que primero
**carga el registro** (título, categoría y descripción) y al guardar redirige
al listado con el aviso. Permiten editar entradas (con CKEditor), usuarios y
categorías.

**Cómo lo he probado.** Abro `entrada_editar.php?id=6`: el formulario aparece
rellenado con los datos de la BD; cambio el título, guardo y en phpMyAdmin
compruebo que el UPDATE se hizo (y sale la «edición de entrada ...» en logs).

## Ejercicio 2.6 — Eliminación con confirmación (modal) y FK

**Qué he hecho.** Cada fila tiene un botón de basurita con `data-url` y
`data-nombre`; el script de `plantillas/scripts.php` abre el **modal de
Bootstrap** pidiendo confirmación, y sólo al aceptar va a la página
`*_eliminar.php`. Antes de borrar el script comprueba si hay registros
relacionados → aviso «No se puede eliminar…».

**Cómo lo he probado.**
- Borrar la categoría «Estudiantes» (0 entradas) → modal → «Sí, eliminar» →
  desaparece y queda registrado en logs.
- Intentar borrar la categoría «Tecnología» (tiene 5 entradas) →
  `categorias.php?cod=relacionado» (bloqueado).
- Intentar borrar el usuario javier (tiene entradas) → `cod=relacionado`.
- Con javier no se ven botones de edición en entradas ajenas.

## Ejercicio 2.7 — Páginas de detalle dinámicas

**Qué he hecho.** `entrada_detalle.php` y `usuario_detalle.php` (y también
`categoria_detalle.php`) reciben el `id` por GET, consultan la BD y pintan
todo: la entrada muestra autor, categoría, fecha, imagen y el contenido HTML
del editor; el de usuario muestra sus datos y el listado de sus entradas.

**Cómo lo he probado.** Detalle de la entrada 1 (con su texto formateado),
detalle de javier con sus entradas, y `id=9999` → `cod=noregistro`.

## Ejercicio 2.8 — Subida de imágenes al directorio images/

**Qué he hecho.** En `registro.php` / `usuario_*.php` se sube el **avatar** y
en `entrada_nueva/editar.php` la **imagen de la entrada**.
`funciones/imagenes.php` comprueba el error de PHP, el tamaño (máx. 2 MB), el
tipo MIME real con `mime_content_type()`, la extensión y guarda el fichero con
nombre único (`uniqid`). En la edición puedes mantener la imagen actual o
marcar «Quitar imagen». Si la operación falla y se deshace la transacción, la
imagen subida también se borra.

**Cómo lo he probado.**
- Avatar del usuario → aparece el fichero `img_xxx.png` en `images/`, la
  referencia en la BD y el avatar en el listado.
- Entrada con imagen → ídem; al eliminar la entrada con el modal, el fichero
  físico se borra de `images/`.

## Ejercicio 2.9 — Paginación completa

**Qué he hecho.** Todos los listados usan `plantillas/paginacion.php`:
selector de **registros por página** (3/5/10/15, con autoenvío), la **lista de
páginas** (« 1 2 3 » de N) y el cuadro **«Ir a pág»** con un máximo.

**Cómo lo he probado.** Con 19 entradas y 3 por página → 7 páginas; «Ir a pág»
no deja pasar de 7; la página 6 empieza en la «Entrada de prueba 15» (offset
correcto). El contenido cambia al cambiar RegXPág.

## Ejercicio 2.10 — CKEditor en las entradas

**Qué he hecho.** Los formularios de alta y edición de entradas sustituyen el
`<textarea>` por **CKEditor 5** (build classic, local en `assets/vendor/`).
El HTML generado se guarda en `descripcion`.

**Cómo lo he probado.** Editado una entrada con un `<h2>`, una lista y
negrita; guardo y en la BD queda el HTML; en el detalle se ve con formato.

## Ejercicio 2.11 — Listado en PDF

**Qué he hecho.** `pdf_entradas.php` genera el PDF con la librería **FPDF**,
respetando el orden y los filtros y con el estilo del listado (cabecera oscura
y filas enormes alternadas). Botón «PDF» en la cabecera de `index.php`.

**Cómo lo he probado.** El archivo empieza por `%PDF-` y se abre el visor con
el listado completo; se genera también desde el navegador con la sesión de
usuario normal.

## Ejercicio 2.12 — Ordenar por fecha asc/desc con icono

**Qué he hecho.** En la cabecera de la columna «Fecha» el enlace va alternando
`?orden=asc|desc` y cambia el **icono** de Bootstrap-Icons
(`bi-sort-numeric-down` / `bi-sort-numeric-up-alt`) indicando el sentido.

**Cómo lo he probado.** asc → aparece primero «PHP 8…» (la más antigua);
desc → primero «Entrada con imagen» (la más reciente).

## Ejercicio 2.13 — Procedimiento almacenado de logs

**Qué he hecho.** En `bd/bdBlog.sql` está el procedimiento
`sp_registrar_log(p_usuario, p_operacion)` que inserta fecha/hora (NOW()),
usuario y la operación en la tabla `logs`. `funciones/log.php` lo llama con
`CALL` **dentro de las transacciones** de cada operación del blog.

**Cómo lo he probado.**
- Llamada directa: `CALL bd_blog.sp_registrar_log(...)` → fila nueva.
- Cada alta/edición/borrado que hago desde la app deja su fila en logs.

## Ejercicio 2.14 — Gestión de los logs

**Qué he hecho.** `logs.php` (sólo admin): listado paginado con bucador,
**PDF** (`pdf_logs.php`) y dos opciones de borrado con confirmación modal:
individual (`log_eliminar.php`) y vaciado completo (`log_vaciar.php`).

**Cómo lo he probado.**
- Listado con 10 filas → paginación de 4 páginas a 3/página.
- Eliminar el log nº 19 con el modal → desaparece de la BD.
- Vaciar → queda sólo el registro «vaciado del log» (prueba de que el borado
  se hizo en la misma transacción, con `beginTransaction/commit`).

## Ejercicio 2.15 — Control de versiones con Git

**Qué he hecho.** Repositorio Git en el proyecto (`git init` + rama `main`)
con un **commit por bloque de funcionalidad**:

```
c78d491 Ej 2.1: BD bd_blog (tablas, relaciones, SP logs) y conexión PDO
550d5c6 Ej 2.2-2.3: perfiles admin/user, validación y filtrado
0a4112f Ej 2.8: subida de imágenes al directorio images/
4ffcedb Ej 2.4+2.9+2.12: listado entradas (orden fecha, paginación, PDF...)
8d44d09 Ej 2.5+2.7: detalle y edición; CRUD usuarios y categorías con modales
733df8c Ej 2.10: CKEditor 5 en alta/edición de entradas
11703dc Ej 2.11: impresión del listado de entradas en PDF con FPDF
0040f75 Ej 2.14: gestión de logs (listar, eliminar, vaciar y PDF)
bbd915b librerías locales (funciona sin internet)
8e3176c fixes de cabecera de listados y ruta del PDF
24356c2 estructura final (funciones/, plantillas/) y conexión PDO simple
b8ad8ce Ej 2.3 fix: validarLongitud() comprobar el mínimo
```

---

## Resultado final de la comprobación

Todas las páginas devuelven 200 y sin PHP Warnings/Fatals; las pruebas de cada
ejercicio (login, formularios, modales, imágenes, paginación, CKEditor, PDF y
logs) se han ejecutado sobre la `bd_blog` de XAMPP con buen resultado.
