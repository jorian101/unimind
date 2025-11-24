CREATE DATABASE IF NOT EXISTS `db_tests_estres_ansiedad`;
USE `db_tests_estres_ansiedad`;

-- 1. Tablas Principales (Sin dependencias)

CREATE TABLE `Escuelas` (
    `id_escuela` INT NOT NULL AUTO_INCREMENT,
    `nombre_escuela` VARCHAR(150) UNIQUE NOT NULL,
    `telefono` VARCHAR(20),
    PRIMARY KEY (`id_escuela`)
);

CREATE TABLE `Usuarios` (
    `id_usuario` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `codigo_usuario` VARCHAR(10) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `cargo` VARCHAR(30) NOT NULL CHECK (`cargo` IN ('Estudiante', 'Docente', 'Administrador')),
    `fecha_nacimiento` DATE,
    `genero` VARCHAR(10),
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`)
);

CREATE TABLE `Tests` (                             
    `id_test` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `num_items` INT NOT NULL,
    PRIMARY KEY (`id_test`)
);

CREATE TABLE `Opciones_Respuesta` (
    `id_opcion` INT NOT NULL AUTO_INCREMENT,
    `texto_opcion` VARCHAR(100) NOT NULL,
    `valor_puntuacion` INT NOT NULL,
    PRIMARY KEY (`id_opcion`)
);

-- 2. Tablas Dependientes (Con Foreign Keys)

CREATE TABLE `Cursos` (
    `id_curso` INT NOT NULL AUTO_INCREMENT,
    `nombre_curso` VARCHAR(150) NOT NULL,
    `id_escuela` INT NOT NULL,
    `id_profesor` INT NOT NULL, -- FK a Usuarios
    PRIMARY KEY (`id_curso`),
    FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas`(`id_escuela`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_profesor`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE RESTRICT
);

CREATE TABLE `Usuario_Curso` (
    `id_usuario_curso` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL, -- FK al estudiante
    `id_curso` INT NOT NULL,   -- FK al curso
    `fecha_inscripcion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario_curso`),
    UNIQUE KEY `uk_usuario_curso` (`id_usuario`, `id_curso`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_curso`) REFERENCES `Cursos`(`id_curso`)
        ON DELETE CASCADE
);

CREATE TABLE `Usuario_Escuela` (
    `id_usuario_escuela` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL,
    `id_escuela` INT NOT NULL,
    `fecha_vinculo` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario_escuela`),
    UNIQUE KEY `uk_usuario_escuela` (`id_usuario`, `id_escuela`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_escuela`) REFERENCES `Escuelas`(`id_escuela`)
        ON DELETE CASCADE
);

CREATE TABLE `Items` (
    `id_item` INT NOT NULL AUTO_INCREMENT,
    `id_test` INT NOT NULL,
    `texto_item` TEXT NOT NULL,
    `subescala` VARCHAR(50),
    `orden` INT NOT NULL,
    PRIMARY KEY (`id_item`),
    FOREIGN KEY (`id_test`) REFERENCES `Tests`(`id_test`)
        ON DELETE CASCADE
);

CREATE TABLE `Aplicaciones` (
    `id_aplicacion` INT NOT NULL AUTO_INCREMENT,
    `id_usuario` INT NOT NULL,
    `id_test` INT NOT NULL,
    `client_uuid` VARCHAR(36) NULL DEFAULT NULL,
    `fecha_aplicacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `puntuacion_total` INT,
    `resultado_nivel` VARCHAR(50),
    PRIMARY KEY (`id_aplicacion`),
    UNIQUE KEY `idx_aplicaciones_client_uuid` (`client_uuid`),
    FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios`(`id_usuario`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_test`) REFERENCES `Tests`(`id_test`)
        ON DELETE RESTRICT
);

CREATE TABLE `Respuestas_Aplicacion` (
    `id_respuesta` INT NOT NULL AUTO_INCREMENT,
    `id_aplicacion` INT NOT NULL,
    `id_item` INT NOT NULL,
    `id_opcion_seleccionada` INT NOT NULL,
    `puntuacion_obtenida` INT NOT NULL,
    PRIMARY KEY (`id_respuesta`),
    FOREIGN KEY (`id_aplicacion`) REFERENCES `Aplicaciones`(`id_aplicacion`)
        ON DELETE CASCADE,
    FOREIGN KEY (`id_item`) REFERENCES `Items`(`id_item`)
        ON DELETE RESTRICT,
    FOREIGN KEY (`id_opcion_seleccionada`) REFERENCES `Opciones_Respuesta`(`id_opcion`)
        ON DELETE RESTRICT,
    UNIQUE KEY `uk_aplicacion_item` (`id_aplicacion`, `id_item`)
);

-- Tabla para registrar intentos de sincronización desde PWA
CREATE TABLE IF NOT EXISTS `sync_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_uuid` VARCHAR(36) NULL,
    `request_payload` LONGTEXT NULL,
    `response_payload` LONGTEXT NULL,
    `status` VARCHAR(32) NULL,
    `duration_ms` INT NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX (`client_uuid`)
);