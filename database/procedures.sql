DELIMITER //
CREATE PROCEDURE `sp_registrar_usuario_escuela`(
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_email VARCHAR(150),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_genero VARCHAR(10),
    IN p_id_escuela INT
)
BEGIN
    DECLARE v_id_usuario INT;

    INSERT INTO `Usuarios` (`nombre`, `apellido`, `email`, `cargo`, `fecha_nacimiento`, `genero`)
    VALUES (p_nombre, p_apellido, p_email, p_cargo, p_fecha_nacimiento, p_genero);

    SET v_id_usuario = LAST_INSERT_ID();

    INSERT INTO `Usuario_Escuela` (`id_usuario`, `id_escuela`)
    VALUES (v_id_usuario, p_id_escuela);

    SELECT 'Usuario registrado y afiliado con éxito.' AS Mensaje, v_id_usuario AS Nuevo_ID_Usuario;
END //

CREATE PROCEDURE `sp_iniciar_aplicacion`(
    IN p_id_usuario INT,
    IN p_id_test INT,
    OUT p_id_aplicacion INT
)
BEGIN
    INSERT INTO `Aplicaciones` (`id_usuario`, `id_test`)
    VALUES (p_id_usuario, p_id_test);

    SET p_id_aplicacion = LAST_INSERT_ID();
    
    SELECT 'Aplicación de test iniciada.' AS Mensaje, p_id_aplicacion AS Nuevo_ID_Aplicacion;
END //

CREATE PROCEDURE `sp_registrar_respuesta`(
    IN p_id_aplicacion INT,
    IN p_id_item INT,
    IN p_id_opcion_seleccionada INT
)
BEGIN
    DECLARE v_puntuacion INT;

    SELECT `valor_puntuacion` INTO v_puntuacion
    FROM `Opciones_Respuesta`
    WHERE `id_opcion` = p_id_opcion_seleccionada;

    INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`)
    VALUES (p_id_aplicacion, p_id_item, p_id_opcion_seleccionada, v_puntuacion)
    ON DUPLICATE KEY UPDATE 
        `id_opcion_seleccionada` = p_id_opcion_seleccionada,
        `puntuacion_obtenida` = v_puntuacion;
        
    SELECT 'Respuesta registrada/actualizada con éxito.' AS Mensaje;
END //

CREATE PROCEDURE `sp_finalizar_aplicacion_y_calcular_puntuacion`(
    IN p_id_aplicacion INT
)
BEGIN
    DECLARE v_puntuacion_total INT;
    DECLARE v_id_test INT;
    DECLARE v_resultado_nivel VARCHAR(50);

    SELECT `id_test` INTO v_id_test
    FROM `Aplicaciones`
    WHERE `id_aplicacion` = p_id_aplicacion;

    SELECT SUM(ra.`puntuacion_obtenida`) INTO v_puntuacion_total
    FROM `Respuestas_Aplicacion` ra
    WHERE ra.`id_aplicacion` = p_id_aplicacion;
    
    IF v_puntuacion_total IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se encontraron respuestas para esta aplicación.';
    END IF;

    CASE v_id_test
        WHEN 1 THEN 
            SET v_resultado_nivel = 
                CASE
                    WHEN v_puntuacion_total <= 15 THEN 'Mínimo/Bajo'
                    WHEN v_puntuacion_total <= 25 THEN 'Medio/Moderado'
                    ELSE 'Alto/Severo'
                END;
        WHEN 2 THEN 
            SET v_resultado_nivel = 
                CASE
                    WHEN v_puntuacion_total <= 10 THEN 'Mínimo/Bajo'
                    WHEN v_puntuacion_total <= 18 THEN 'Medio/Moderado'
                    ELSE 'Alto/Severo'
                END;
        ELSE 
            SET v_resultado_nivel = 'No Definido';
    END CASE;

    UPDATE `Aplicaciones`
    SET 
        `puntuacion_total` = v_puntuacion_total,
        `resultado_nivel` = v_resultado_nivel
    WHERE `id_aplicacion` = p_id_aplicacion;

    SELECT 'Cálculo finalizado y aplicación actualizada.' AS Mensaje, v_puntuacion_total AS Puntuacion_Final, v_resultado_nivel AS Nivel_Resultado;
END //

CREATE PROCEDURE `sp_obtener_resultados_por_escuela`(
    IN p_id_escuela INT
)
BEGIN
    SELECT
        u.`nombre`,
        u.`apellido`,
        t.`nombre` AS Nombre_Test,
        a.`fecha_aplicacion`,
        a.`puntuacion_total`,
        a.`resultado_nivel`
    FROM
        `Aplicaciones` a
    JOIN
        `Usuarios` u ON a.`id_usuario` = u.`id_usuario`
    JOIN
        `Tests` t ON a.`id_test` = t.`id_test`
    JOIN
        `Usuario_Escuela` ue ON u.`id_usuario` = ue.`id_usuario`
    WHERE
        ue.`id_escuela` = p_id_escuela
    ORDER BY
        a.`fecha_aplicacion` DESC;
END //

DELIMITER ;