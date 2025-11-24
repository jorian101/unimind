-- Script completo para aplicar cambios de tipo_escala
-- Fecha: 2025-11-24
-- Descripción: Aplica todos los cambios necesarios para el sistema de tipos de escala

USE `db_tests_estres_ansiedad`;

-- 1. Crear tabla de tipos de escalas (si no existe)
CREATE TABLE IF NOT EXISTS `Tipos_Escalas` (
    `id_tipo_escala` INT NOT NULL AUTO_INCREMENT,
    `nombre_escala` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `opciones_ids` VARCHAR(255) NOT NULL COMMENT 'IDs de opciones separados por comas',
    PRIMARY KEY (`id_tipo_escala`)
);

-- 2. Insertar escalas predefinidas (solo si la tabla está vacía)
INSERT INTO `Tipos_Escalas` (`nombre_escala`, `descripcion`, `opciones_ids`) 
SELECT * FROM (
    SELECT 'Frecuencia (5 puntos)' as nombre_escala, 'Escala de frecuencia: Nunca, Casi nunca, A veces, A menudo, Siempre' as descripcion, '5,6,7,8,9' as opciones_ids UNION ALL
    SELECT 'Intensidad (4 puntos)', 'Escala de intensidad: Nada en absoluto, Un poco, Bastante, Mucho', '10,11,12,13' UNION ALL
    SELECT 'Acuerdo (4 puntos)', 'Escala de acuerdo: Totalmente en desacuerdo, En desacuerdo, De acuerdo, Totalmente de acuerdo', '1,2,3,4' UNION ALL
    SELECT 'Sí/No (2 puntos)', 'Respuesta binaria: No, Sí', '14,15' UNION ALL
    SELECT 'Burnout (5 puntos)', 'Escala específica de burnout: 0-Nunca a 4-Una vez a la semana', '16,17,18,19,20' UNION ALL
    SELECT 'Calidad (5 puntos)', 'Escala de calidad: Muy bueno, Bueno, Regular, Malo, Muy malo', '21,22,23,24,25' UNION ALL
    SELECT 'Sentimiento (4 puntos)', 'Escala de sentimiento: No me siento así, Raramente, A veces, Casi siempre', '26,27,28,29'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `Tipos_Escalas` LIMIT 1);

-- 3. Agregar columna tipo_escala a Tests (si no existe)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'db_tests_estres_ansiedad' 
AND TABLE_NAME = 'Tests' 
AND COLUMN_NAME = 'tipo_escala';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `Tests` ADD COLUMN `tipo_escala` INT NULL DEFAULT 1 COMMENT "FK a Tipos_Escalas" AFTER `num_items`',
    'SELECT "La columna tipo_escala ya existe" as mensaje');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Actualizar tests existentes con escala de frecuencia por defecto
UPDATE `Tests` SET `tipo_escala` = 1 WHERE `tipo_escala` IS NULL;

-- 5. Agregar foreign key (si no existe)
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE CONSTRAINT_SCHEMA = 'db_tests_estres_ansiedad' 
AND TABLE_NAME = 'Tests' 
AND CONSTRAINT_NAME = 'fk_tests_tipo_escala';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `Tests` ADD CONSTRAINT `fk_tests_tipo_escala` FOREIGN KEY (`tipo_escala`) REFERENCES `Tipos_Escalas`(`id_tipo_escala`) ON DELETE SET NULL',
    'SELECT "El foreign key fk_tests_tipo_escala ya existe" as mensaje');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Actualizar procedimientos almacenados
DELIMITER //

-- Actualizar sp_crear_test
DROP PROCEDURE IF EXISTS `sp_crear_test` //
CREATE PROCEDURE `sp_crear_test`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_num_items INT,
    IN p_tipo_escala INT
)
BEGIN
    INSERT INTO `Tests` (`nombre`, `descripcion`, `num_items`, `tipo_escala`, `created_at`, `updated_at`)
    VALUES (p_nombre, p_descripcion, p_num_items, p_tipo_escala, NOW(), NOW());
    SELECT 'Test creado con éxito' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Test;
END //

-- Crear sp_obtener_tipos_escalas
DROP PROCEDURE IF EXISTS `sp_obtener_tipos_escalas` //
CREATE PROCEDURE `sp_obtener_tipos_escalas`()
BEGIN
    SELECT `id_tipo_escala`, `nombre_escala`, `descripcion`, `opciones_ids`
    FROM `Tipos_Escalas`
    ORDER BY `id_tipo_escala` ASC;
END //

-- Crear sp_obtener_opciones_por_tipo_escala
DROP PROCEDURE IF EXISTS `sp_obtener_opciones_por_tipo_escala` //
CREATE PROCEDURE `sp_obtener_opciones_por_tipo_escala`(
    IN p_tipo_escala INT
)
BEGIN
    DECLARE v_opciones_ids VARCHAR(255);
    
    SELECT `opciones_ids` INTO v_opciones_ids
    FROM `Tipos_Escalas`
    WHERE `id_tipo_escala` = p_tipo_escala;
    
    IF v_opciones_ids IS NOT NULL THEN
        SET @sql = CONCAT('SELECT `id_opcion`, `texto_opcion`, `valor_puntuacion` 
                           FROM `Opciones_Respuesta` 
                           WHERE `id_opcion` IN (', v_opciones_ids, ') 
                           ORDER BY `valor_puntuacion` ASC');
        
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //

-- Actualizar sp_obtener_tests_disponibles
DROP PROCEDURE IF EXISTS `sp_obtener_tests_disponibles` //
CREATE PROCEDURE `sp_obtener_tests_disponibles`()
BEGIN
    SELECT t.`id_test`, t.`nombre`, t.`descripcion`, t.`num_items`, 
           t.`tipo_escala`, te.`nombre_escala`, te.`opciones_ids`,
           t.`created_at`, t.`updated_at`
    FROM `Tests` t
    LEFT JOIN `Tipos_Escalas` te ON t.`tipo_escala` = te.`id_tipo_escala`
    ORDER BY t.`nombre`;
END //

DELIMITER ;

-- 7. Verificación final
SELECT 'Migración completada exitosamente' as Estado;
SELECT COUNT(*) as 'Total Tipos de Escalas' FROM Tipos_Escalas;
SELECT COUNT(*) as 'Total Tests con tipo_escala' FROM Tests WHERE tipo_escala IS NOT NULL;
