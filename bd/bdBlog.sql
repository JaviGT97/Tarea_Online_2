-- ============================================================
-- Ejercicio 2.1 - "Mi pequeño Blog"
-- Creación de la Base de Datos bd_blog (MySQL / PhpMyAdmin)
-- Esquema: usuarios, entradas, categorías (+ tabla logs p/ Ej. 2.13)
-- ============================================================
DROP DATABASE IF EXISTS bd_blog;
CREATE DATABASE bd_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bd_blog;

-- --------------------- TABLA USUARIOS -----------------------
CREATE TABLE usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nick          VARCHAR(50)  NOT NULL UNIQUE,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100) DEFAULT NULL,
    email         VARCHAR(100) NOT NULL,
    password      VARCHAR(255) NOT NULL,
    imagen_avatar VARCHAR(255) DEFAULT NULL,
    perfil        ENUM('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB;

-- --------------------- TABLA CATEGORÍAS ---------------------
CREATE TABLE categorias (
    id     INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ---------------------- TABLA ENTRADAS ----------------------
CREATE TABLE entradas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    categoria_id INT NOT NULL,
    titulo      VARCHAR(150) NOT NULL,
    imagen      VARCHAR(255) DEFAULT NULL,
    descripcion TEXT,
    fecha       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_entradas_usuario   FOREIGN KEY (usuario_id)   REFERENCES usuarios(id)   ON DELETE CASCADE,
    CONSTRAINT fk_entradas_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------- TABLA LOGS (Ej. 2.13) ---------------
CREATE TABLE logs (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    fecha     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario   VARCHAR(50)  NOT NULL,
    operacion VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ============================================================
-- Ejercicio 2.13: Procedimiento almacenado
-- Inserta en 'logs' fecha/hora, usuario y tipo de operación.
-- ============================================================
DELIMITER //
CREATE PROCEDURE sp_registrar_log(
    IN p_usuario   VARCHAR(50),
    IN p_operacion VARCHAR(255)
)
BEGIN
    INSERT INTO logs (fecha, usuario, operacion)
    VALUES (NOW(), p_usuario, p_operacion);
END//
DELIMITER ;

-- ============================================================
-- Datos de ejemplo
-- admin / admin  |  javier / 1234
-- ============================================================
INSERT INTO usuarios (nick, nombre, apellido, email, password, imagen_avatar, perfil) VALUES
('admin',  'Administrador', 'Del Blog', 'admin@micuentablogdev.es',   '$2y$10$wkG1EmkvbokoN4MSBeB55OSj/AIbjWkeDStLZqAdDyfC/1ScTSpma', NULL, 'admin'),
('javier', 'Javier',        'Pérez',    'javier@micuentablogdev.es',  '$2y$10$4Ti0dST20o5x7GAmX.eSDeeuO46sXrrGpLfFVEDXLIrY3YG9yYcda', NULL, 'user');

INSERT INTO categorias (nombre) VALUES
('Tecnología'), ('Viajes'), ('Gastronomía'), ('Deportes');

INSERT INTO entradas (usuario_id, categoria_id, titulo, imagen, descripcion, fecha) VALUES
(1, 1, 'PHP 8: qué nos trae la nueva versión', NULL, '<p>PHP 8 llega con el compilador JIT y mejoras de rendimiento de hasta un 30% en aplicaciones ligeras.</p>', NOW() - INTERVAL 2 DAY),
(2, 2, 'Mi viaje por la costa norte', NULL, '<p>Un recorrido de diez días por la costa norte: acantilados, pueblos de pescadores y atardeceres imposibles.</p>', NOW() - INTERVAL 1 DAY),
(2, 3, 'Tres recetas fáciles para principiantes', NULL, '<p>Nada de técnicas complicadas: tres recetas con menos de cinco ingredientes y listo en media hora.</p>', NOW());
