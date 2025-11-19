DELIMITER //

-- =============================================
-- ### Grupo 1: Registro y Autenticación
-- =============================================

-- Procedimiento para validar el login de un usuario
CREATE PROCEDURE `sp_autenticar_usuario_por_codigo`(
    IN p_codigo_usuario VARCHAR(10)
)
BEGIN
    -- Devuelve los datos necesarios para que el BACKEND verifique la contraseña
    SELECT 
        `id_usuario`,
        `password`,
        `nombre`,
        `apellido`,
        `cargo`
    FROM 
        `Usuarios`
    WHERE 
        `codigo_usuario` = p_codigo_usuario;
END //

-- Procedimiento para registrar un estudiante y matricularlo en un curso
CREATE PROCEDURE `sp_registrar_estudiante_curso`(
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_codigo_usuario VARCHAR(10),
    IN p_password VARCHAR(255),
    IN p_fecha_nacimiento DATE,
    IN p_genero VARCHAR(10),
    IN p_id_curso INT
)
BEGIN
    DECLARE v_id_usuario INT;

    -- 1. Crear el usuario
    INSERT INTO `Usuarios` 
        (`nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`, `fecha_nacimiento`, `genero`)
    VALUES 
        (p_nombre, p_apellido, p_codigo_usuario, p_password, 'Estudiante', p_fecha_nacimiento, p_genero);

    SET v_id_usuario = LAST_INSERT_ID();

    -- 2. Inscribir el usuario en el curso
    INSERT INTO `Usuario_Curso` (`id_usuario`, `id_curso`)
    VALUES (v_id_usuario, p_id_curso);

    SELECT 'Estudiante registrado e inscrito con éxito.' AS Mensaje, v_id_usuario AS Nuevo_ID_Usuario;
END //

-- =============================================
-- ### Grupo 2: Flujo de Aplicación de Tests
-- =============================================

-- Inicia una aplicación de test para un usuario
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

-- Registra o actualiza la respuesta de un ítem
CREATE PROCEDURE `sp_registrar_respuesta`(
    IN p_id_aplicacion INT,
    IN p_id_item INT,
    IN p_id_opcion_seleccionada INT
)
BEGIN
    DECLARE v_puntuacion INT;

    -- Obtener el valor de la opción seleccionada
    SELECT `valor_puntuacion` INTO v_puntuacion
    FROM `Opciones_Respuesta`
    WHERE `id_opcion` = p_id_opcion_seleccionada;

    -- Insertar la respuesta; si ya existe, actualizarla
    INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`)
    VALUES (p_id_aplicacion, p_id_item, p_id_opcion_seleccionada, v_puntuacion)
    ON DUPLICATE KEY UPDATE 
        `id_opcion_seleccionada` = p_id_opcion_seleccionada,
        `puntuacion_obtenida` = v_puntuacion;
        
    SELECT 'Respuesta registrada/actualizada con éxito.' AS Mensaje;
END //

-- Calcula el puntaje total y finaliza la aplicación
CREATE PROCEDURE `sp_finalizar_aplicacion_y_calcular_puntuacion`(
    IN p_id_aplicacion INT
)
BEGIN
    DECLARE v_puntuacion_total INT;
    DECLARE v_id_test INT;
    DECLARE v_resultado_nivel VARCHAR(50);

    -- Obtener el ID del Test
    SELECT `id_test` INTO v_id_test
    FROM `Aplicaciones`
    WHERE `id_aplicacion` = p_id_aplicacion;

    -- Sumar todas las puntuaciones de las respuestas
    SELECT SUM(ra.`puntuacion_obtenida`) INTO v_puntuacion_total
    FROM `Respuestas_Aplicacion` ra
    WHERE ra.`id_aplicacion` = p_id_aplicacion;
    
    IF v_puntuacion_total IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se encontraron respuestas para esta aplicación.';
    END IF;

    -- Lógica de baremos (ejemplo para Test 1 y 2)
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

    -- Actualizar la aplicación con los resultados
    UPDATE `Aplicaciones`
    SET 
        `puntuacion_total` = v_puntuacion_total,
        `resultado_nivel` = v_resultado_nivel
    WHERE `id_aplicacion` = p_id_aplicacion;

    SELECT 'Cálculo finalizado y aplicación actualizada.' AS Mensaje, v_puntuacion_total AS Puntuacion_Final, v_resultado_nivel AS Nivel_Resultado;
END //

-- =============================================
-- ### Grupo 3: Creación de Contenido (Admin)
-- =============================================

-- Crear una nueva escuela
CREATE PROCEDURE `sp_crear_escuela`(
    IN p_nombre_escuela VARCHAR(150),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO `Escuelas` (`nombre_escuela`, `telefono`)
    VALUES (p_nombre_escuela, p_telefono);
    SELECT 'Escuela creada con éxito' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Escuela;
END //

-- Crear un nuevo curso (y asignarlo a escuela y profesor)
CREATE PROCEDURE `sp_crear_curso`(
    IN p_nombre_curso VARCHAR(150),
    IN p_id_escuela INT,
    IN p_id_profesor INT
)
BEGIN
    INSERT INTO `Cursos` (`nombre_curso`, `id_escuela`, `id_profesor`)
    VALUES (p_nombre_curso, p_id_escuela, p_id_profesor);
    SELECT 'Curso creado con éxito' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Curso;
END //

-- Crear la cabecera de un nuevo test
CREATE PROCEDURE `sp_crear_test`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_num_items INT
)
BEGIN
    INSERT INTO `Tests` (`nombre`, `descripcion`, `num_items`)
    VALUES (p_nombre, p_descripcion, p_num_items);
    SELECT 'Test creado con éxito' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Test;
END //

-- Añadir preguntas (items) a un test existente
CREATE PROCEDURE `sp_crear_item`(
    IN p_id_test INT,
    IN p_texto_item TEXT,
    IN p_subescala VARCHAR(50),
    IN p_orden INT
)
BEGIN
    INSERT INTO `Items` (`id_test`, `texto_item`, `subescala`, `orden`)
    VALUES (p_id_test, p_texto_item, p_subescala, p_orden);
    SELECT 'Item añadido al test' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Item;
END //

-- Añadir las opciones de respuesta globales
CREATE PROCEDURE `sp_crear_opcion_respuesta`(
    IN p_texto_opcion VARCHAR(100),
    IN p_valor_puntuacion INT
)
BEGIN
    INSERT INTO `Opciones_Respuesta` (`texto_opcion`, `valor_puntuacion`)
    VALUES (p_texto_opcion, p_valor_puntuacion);
    SELECT 'Opción de respuesta creada' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Opcion;
END //

-- =============================================
-- ### Grupo 4: Reportes del Dashboard (Profesor)
-- =============================================

-- Obtener los cursos que un profesor imparte (para el dropdown)
CREATE PROCEDURE `sp_obtener_cursos_por_profesor`(
    IN p_id_profesor INT
)
BEGIN
    SELECT id_curso, nombre_curso 
    FROM Cursos 
    WHERE id_profesor = p_id_profesor 
    ORDER BY nombre_curso;
END //

-- Contar estudiantes con nivel alto en un curso (para Card 3)
CREATE PROCEDURE `sp_contar_niveles_altos_por_curso`(
    IN p_id_curso INT
)
BEGIN
    SELECT COUNT(DISTINCT a.id_usuario) AS conteo_niveles_altos
    FROM Aplicaciones a
    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
    WHERE uc.id_curso = p_id_curso
      AND a.resultado_nivel IN ('Alto/Severo', 'Alto');
END //

-- Datos para el gráfico de evolución temporal (por CURSO)
CREATE PROCEDURE `sp_obtener_evolucion_temporal_por_curso`(
    IN p_id_curso INT
)
BEGIN
    SELECT 
        DATE_FORMAT(a.fecha_aplicacion, '%Y-%u') AS etiqueta_temporal,
        t.nombre AS nombre_test,
        AVG(a.puntuacion_total) AS promedio_puntuacion
    FROM Aplicaciones a
    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
    JOIN Tests t ON a.id_test = t.id_test
    WHERE uc.id_curso = p_id_curso
      AND a.puntuacion_total IS NOT NULL
      AND t.id_test IN (1, 2)
    GROUP BY etiqueta_temporal, t.nombre
    ORDER BY etiqueta_temporal ASC
    LIMIT 12;
END //

-- Datos para el gráfico de dona de distribución (por CURSO)
CREATE PROCEDURE `sp_obtener_distribucion_riesgo_por_curso`(
    IN p_id_curso INT
)
BEGIN
    SELECT 
        COALESCE(resultado_nivel, 'Sin Nivel') AS nivel_riesgo,
        COUNT(id_aplicacion) AS conteo
    FROM Aplicaciones a
    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
    WHERE uc.id_curso = p_id_curso
    GROUP BY resultado_nivel
    ORDER BY FIELD(nivel_riesgo, 'Mínimo/Bajo', 'Medio/Moderado', 'Alto/Severo', 'Sin Nivel');
END //

-- Datos para el gráfico de barras comparativo (GLOBAL)
CREATE PROCEDURE `sp_obtener_comparativa_escuelas`()
BEGIN
    SELECT 
        e.nombre_escuela,
        t.nombre AS nombre_test,
        AVG(a.puntuacion_total) AS promedio_puntuacion
    FROM Aplicaciones a
    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
    JOIN Cursos c ON uc.id_curso = c.id_curso
    JOIN Escuelas e ON c.id_escuela = e.id_escuela
    JOIN Tests t ON a.id_test = t.id_test
    WHERE a.puntuacion_total IS NOT NULL
      AND t.id_test IN (1, 2) -- Solo Estrés (1) y Ansiedad (2)
    GROUP BY e.nombre_escuela, t.nombre
    ORDER BY e.nombre_escuela;
END //

-- =============================================
-- ### Grupo 5: Lectura de Datos (UI General)
-- =============================================

-- Obtener la lista de tests disponibles para tomar
CREATE PROCEDURE `sp_obtener_tests_disponibles`()
BEGIN
    SELECT `id_test`, `nombre`, `descripcion`, `num_items`
    FROM `Tests`
    ORDER BY `nombre`;
END //

-- Cargar todas las preguntas de un test específico
CREATE PROCEDURE `sp_obtener_items_por_test`(
    IN p_id_test INT
)
BEGIN
    SELECT `id_item`, `id_test`, `texto_item`, `subescala`, `orden`
    FROM `Items`
    WHERE `id_test` = p_id_test
    ORDER BY `orden` ASC;
END //

-- Cargar las opciones de respuesta generales (Nunca, A veces...)
CREATE PROCEDURE `sp_obtener_opciones_respuesta_generales`()
BEGIN
    SELECT `id_opcion`, `texto_opcion`, `valor_puntuacion`
    FROM `Opciones_Respuesta`
    ORDER BY `valor_puntuacion` ASC;
END //

-- =============================================
-- ### Grupo 6: Reportes del Estudiante
-- =============================================

-- Obtener el historial de tests completados de un estudiante
CREATE PROCEDURE `sp_obtener_historial_usuario`(
    IN p_id_usuario INT
)
BEGIN
    SELECT 
        a.`id_aplicacion`,
        t.`nombre` AS Nombre_Test,
        a.`fecha_aplicacion`,
        a.`puntuacion_total`,
        a.`resultado_nivel`
    FROM 
        `Aplicaciones` a
    JOIN 
        `Tests` t ON a.`id_test` = t.`id_test`
    WHERE 
        a.`id_usuario` = p_id_usuario
        AND a.`puntuacion_total` IS NOT NULL -- Asegurarse que esté finalizado
    ORDER BY 
        a.`fecha_aplicacion` DESC;
END //

-- Ver el detalle (pregunta y respuesta) de una aplicación específica
CREATE PROCEDURE `sp_obtener_detalle_aplicacion`(
    IN p_id_aplicacion INT
)
BEGIN
    SELECT
        i.`orden`,
        i.`texto_item`,
        o.`texto_opcion` AS respuesta_seleccionada,
        ra.`puntuacion_obtenida`
    FROM
        `Respuestas_Aplicacion` ra
    JOIN
        `Items` i ON ra.`id_item` = i.`id_item`
    JOIN
        `Opciones_Respuesta` o ON ra.`id_opcion_seleccionada` = o.`id_opcion`
    WHERE
        ra.`id_aplicacion` = p_id_aplicacion
    ORDER BY
        i.`orden` ASC;
END //

DELIMITER ;