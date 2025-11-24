-- Migración: Agregar tipo de escala a Tests
-- Fecha: 2025-11-24
-- Descripción: Agrega campo tipo_escala a la tabla Tests para definir qué escala de respuesta usar

USE `db_tests_estres_ansiedad`;

-- 1. Crear tabla de tipos de escalas (catálogo)
CREATE TABLE IF NOT EXISTS `Tipos_Escalas` (
    `id_tipo_escala` INT NOT NULL AUTO_INCREMENT,
    `nombre_escala` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `opciones_ids` VARCHAR(255) NOT NULL COMMENT 'IDs de opciones separados por comas',
    PRIMARY KEY (`id_tipo_escala`)
);

-- 2. Insertar escalas predefinidas
INSERT INTO `Tipos_Escalas` (`nombre_escala`, `descripcion`, `opciones_ids`) VALUES
('Frecuencia (5 puntos)', 'Escala de frecuencia: Nunca, Casi nunca, A veces, A menudo, Siempre', '5,6,7,8,9'),
('Intensidad (4 puntos)', 'Escala de intensidad: Nada en absoluto, Un poco, Bastante, Mucho', '10,11,12,13'),
('Acuerdo (4 puntos)', 'Escala de acuerdo: Totalmente en desacuerdo, En desacuerdo, De acuerdo, Totalmente de acuerdo', '1,2,3,4'),
('Sí/No (2 puntos)', 'Respuesta binaria: No, Sí', '14,15'),
('Burnout (5 puntos)', 'Escala específica de burnout: 0-Nunca a 4-Una vez a la semana', '16,17,18,19,20'),
('Calidad (5 puntos)', 'Escala de calidad: Muy bueno, Bueno, Regular, Malo, Muy malo', '21,22,23,24,25'),
('Sentimiento (4 puntos)', 'Escala de sentimiento: No me siento así, Raramente, A veces, Casi siempre', '26,27,28,29');

-- 3. Agregar columna tipo_escala a tabla Tests
ALTER TABLE `Tests` 
ADD COLUMN `tipo_escala` INT NULL DEFAULT 1 COMMENT 'FK a Tipos_Escalas, por defecto Frecuencia' AFTER `num_items`;

-- 4. Agregar foreign key
ALTER TABLE `Tests`
ADD CONSTRAINT `fk_tests_tipo_escala` 
FOREIGN KEY (`tipo_escala`) REFERENCES `Tipos_Escalas`(`id_tipo_escala`)
ON DELETE SET NULL;

-- 5. Actualizar tests existentes con escala de frecuencia por defecto
UPDATE `Tests` SET `tipo_escala` = 1 WHERE `tipo_escala` IS NULL;
