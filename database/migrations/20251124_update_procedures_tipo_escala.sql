-- Procedimientos actualizados para soportar tipo_escala
-- Fecha: 2025-11-24

USE `db_tests_estres_ansiedad`;

DELIMITER //

-- Actualizar procedimiento para crear test (incluye tipo_escala)
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

-- Nuevo: Obtener todos los tipos de escalas disponibles
CREATE PROCEDURE IF NOT EXISTS `sp_obtener_tipos_escalas`()
BEGIN
    SELECT `id_tipo_escala`, `nombre_escala`, `descripcion`, `opciones_ids`
    FROM `Tipos_Escalas`
    ORDER BY `id_tipo_escala` ASC;
END //

-- Nuevo: Obtener opciones de respuesta por tipo de escala
CREATE PROCEDURE IF NOT EXISTS `sp_obtener_opciones_por_tipo_escala`(
    IN p_tipo_escala INT
)
BEGIN
    DECLARE v_opciones_ids VARCHAR(255);
    
    -- Obtener los IDs de opciones para este tipo de escala
    SELECT `opciones_ids` INTO v_opciones_ids
    FROM `Tipos_Escalas`
    WHERE `id_tipo_escala` = p_tipo_escala;
    
    -- Devolver las opciones correspondientes
    SET @sql = CONCAT('SELECT `id_opcion`, `texto_opcion`, `valor_puntuacion` 
                       FROM `Opciones_Respuesta` 
                       WHERE `id_opcion` IN (', v_opciones_ids, ') 
                       ORDER BY `valor_puntuacion` ASC');
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //

-- Actualizar procedimiento para obtener tests disponibles (incluye tipo_escala)
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
