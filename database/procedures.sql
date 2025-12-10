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
-- Ahora acepta un parámetro opcional de origen para tracking
CREATE PROCEDURE `sp_iniciar_aplicacion`(
    IN p_id_usuario INT,
    IN p_id_test INT,
    OUT p_id_aplicacion INT
)
BEGIN
    DECLARE v_origen ENUM('estudiante_voluntario','profesor_sugerencia','sistema_automatico');
    
    -- Determinar origen: si existe una sugerencia pendiente, es por sugerencia de profesor
    SET v_origen = 'estudiante_voluntario';
    
    IF EXISTS (
        SELECT 1 FROM Sugerencias 
        WHERE id_estudiante = p_id_usuario 
        AND id_test = p_id_test 
        AND estado = 'pendiente'
    ) THEN
        SET v_origen = 'profesor_sugerencia';
    END IF;
    
    INSERT INTO `Aplicaciones` (`id_usuario`, `id_test`, `origen`)
    VALUES (p_id_usuario, p_id_test, v_origen);

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
    DECLARE v_id_usuario INT;
    DECLARE v_resultado_nivel VARCHAR(50);

    -- Obtener el ID del Test y el ID del Usuario
    SELECT `id_test`, `id_usuario` INTO v_id_test, v_id_usuario
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

    -- Marcar la sugerencia como completada si existe
    -- Nota: La tabla Sugerencias no tiene columna fecha_completado, solo estado
    -- Se puede usar fecha_ultima_sugerencia para registrar cuando se completó si es necesario
    UPDATE `Sugerencias`
    SET 
        `estado` = 'visto'
    WHERE `id_estudiante` = v_id_usuario
        AND `id_test` = v_id_test
        AND `estado` = 'pendiente';

    SELECT 
        'Cálculo finalizado y aplicación actualizada.' AS Mensaje, 
        v_puntuacion_total AS Puntuacion_Final, 
        v_resultado_nivel AS Nivel_Resultado;
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

CREATE PROCEDURE `sp_crear_test`(
    IN p_nombre VARCHAR(100),
    IN p_descripcion TEXT,
    IN p_num_items INT,
    IN p_tipo_test ENUM('estres','ansiedad'),
    IN p_estado_test ENUM('activo','inactivo'),
    IN p_id_tipo_escala INT
)
BEGIN
    INSERT INTO `Tests` (`nombre`, `descripcion`, `num_items`, `tipo_test`, `estado_test`, `id_tipo_escala`, `created_at`, `updated_at`)
    VALUES (p_nombre, p_descripcion, p_num_items, p_tipo_test, p_estado_test, p_id_tipo_escala, NOW(), NOW());
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
    SELECT `id_test`, `nombre`, `descripcion`, `num_items`, `created_at`, `updated_at`
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

-- =============================================
-- Grupo 7: Gestión de Usuarios (moved from procedures2.sql)
-- =============================================

CREATE PROCEDURE `sp_crear_usuario`(
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_codigo_usuario VARCHAR(10),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_genero VARCHAR(10),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO `Usuarios` (nombre, apellido, codigo_usuario, cargo, fecha_nacimiento, genero, password, fecha_registro)
    VALUES (p_nombre, p_apellido, p_codigo_usuario, p_cargo, p_fecha_nacimiento, p_genero, p_password, NOW());
    SELECT 'Usuario creado' AS Mensaje, LAST_INSERT_ID() AS Nuevo_ID_Usuario;
END //

CREATE PROCEDURE `sp_actualizar_usuario`(
    IN p_id_usuario INT,
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_codigo_usuario VARCHAR(10),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_genero VARCHAR(10),
    IN p_password VARCHAR(255)
)
BEGIN
    UPDATE `Usuarios`
    SET nombre = p_nombre,
        apellido = p_apellido,
        codigo_usuario = p_codigo_usuario,
        cargo = p_cargo,
        fecha_nacimiento = p_fecha_nacimiento,
        genero = p_genero,
        password = p_password
    WHERE id_usuario = p_id_usuario;
    SELECT 'Usuario actualizado' AS Mensaje;
END //

CREATE PROCEDURE `sp_eliminar_usuario`(
    IN p_id_usuario INT
)
BEGIN
    DELETE FROM `Usuarios` WHERE id_usuario = p_id_usuario;
    SELECT 'Usuario eliminado' AS Mensaje;
END //

DELIMITER ;

-- =============================================
-- ### Grupo 8: Gestión de Citas
-- =============================================

-- Agendar una cita (alumno)
CREATE PROCEDURE sp_agendar_cita(
    IN p_id_alumno INT,
    IN p_fecha_cita DATETIME,
    IN p_motivo VARCHAR(255)
)
BEGIN
    INSERT INTO Citas (id_alumno, fecha_cita, motivo, estado, created_at)
    VALUES (p_id_alumno, p_fecha_cita, p_motivo, 'pendiente', NOW());
    SELECT 'Cita agendada correctamente' AS Mensaje, LAST_INSERT_ID() AS id_cita;
END //

-- Consultar citas de un alumno
CREATE PROCEDURE sp_obtener_citas_por_alumno(
    IN p_id_alumno INT
)
BEGIN
    SELECT id_cita, fecha_cita, motivo, estado, created_at
    FROM Citas
    WHERE id_alumno = p_id_alumno
    ORDER BY fecha_cita DESC;
END //

-- Consultar todas las citas (administrador)
CREATE PROCEDURE sp_obtener_todas_citas()
BEGIN
    SELECT c.id_cita, c.fecha_cita, c.motivo, c.estado, c.created_at,
           u.id_usuario AS id_alumno, u.nombre, u.apellido, u.codigo_usuario
    FROM Citas c
    JOIN Usuarios u ON c.id_alumno = u.id_usuario
    ORDER BY c.fecha_cita DESC;
END //

-- =============================================
-- ### Grupo 9: Gestión de Sugerencias de Tests
-- =============================================

-- Sugerir un test a un curso (profesor)
-- Ahora crea sugerencias individuales por cada estudiante del curso
-- Si ya existe la sugerencia Y está dentro de 2 meses, agrega profesor/curso a los arrays JSON
-- Si pasaron más de 2 meses, crea una nueva sugerencia (reinicia el ciclo)
CREATE PROCEDURE sp_sugerir_test(
    IN p_id_curso INT,
    IN p_id_test INT,
    IN p_id_profesor INT
)
BEGIN
    DECLARE v_estudiantes_afectados INT DEFAULT 0;
    DECLARE v_estudiantes_reusados INT DEFAULT 0;

    -- Insertar o actualizar sugerencias para todos los estudiantes del curso
    -- Utiliza INSERT ... ON DUPLICATE KEY UPDATE para manejar sugerencias nuevas y existentes
    INSERT INTO Sugerencias (id_estudiante, id_test, profesores_ids, cursos_ids, fecha_sugerencia, fecha_ultima_sugerencia, estado)
    SELECT 
        uc.id_usuario,
        p_id_test,
        JSON_ARRAY(p_id_profesor),
        JSON_ARRAY(p_id_curso),
        NOW(),
        NOW(),
        'pendiente'
    FROM Usuario_Curso uc
    WHERE uc.id_curso = p_id_curso
    ON DUPLICATE KEY UPDATE
        profesores_ids = JSON_MERGE_PRESERVE(profesores_ids, JSON_ARRAY(p_id_profesor)),
        cursos_ids = JSON_MERGE_PRESERVE(cursos_ids, JSON_ARRAY(p_id_curso)),
        fecha_ultima_sugerencia = NOW(),
        estado = 'pendiente';
    
    -- Contar estudiantes afectados
    SELECT COUNT(*) INTO v_estudiantes_afectados
    FROM Usuario_Curso
    WHERE id_curso = p_id_curso;
    
    SELECT 
        'Test sugerido correctamente' AS Mensaje, 
        v_estudiantes_afectados AS estudiantes_afectados,
        v_estudiantes_afectados AS estudiantes_nuevos,
        0 AS estudiantes_reusados;
END //

-- Obtener tests sugeridos para un estudiante
-- Ahora es más simple: solo buscar por id_estudiante
-- Extrae nombres de profesores y cursos desde los JSON arrays
CREATE PROCEDURE sp_obtener_tests_sugeridos_estudiante(
    IN p_id_usuario INT
)
BEGIN
    SELECT 
        t.id_test,
        t.nombre,
        t.descripcion,
        t.num_items,
        t.tipo_test,
        t.created_at,
        t.updated_at,
        s.id_sugerencia,
        s.fecha_sugerencia,
        s.fecha_ultima_sugerencia,
        s.estado,
        s.profesores_ids,
        s.cursos_ids,
        -- Obtener nombres de los profesores (primer profesor del array para simplificar)
        (SELECT CONCAT(u.nombre, ' ', u.apellido) 
         FROM Usuarios u 
         WHERE u.id_usuario = JSON_UNQUOTE(JSON_EXTRACT(s.profesores_ids, '$[0]'))
         LIMIT 1) AS nombre_profesor,
        -- Obtener nombre del curso (primer curso del array)
        (SELECT c.nombre_curso 
         FROM Cursos c 
         WHERE c.id_curso = JSON_UNQUOTE(JSON_EXTRACT(s.cursos_ids, '$[0]'))
         LIMIT 1) AS nombre_curso,
        -- Verificar si el estudiante ya completó este test
        CASE 
            WHEN EXISTS (
                SELECT 1 FROM Aplicaciones a 
                WHERE a.id_usuario = p_id_usuario 
                AND a.id_test = t.id_test 
                AND a.puntuacion_total IS NOT NULL
            ) THEN 1
            ELSE 0
        END AS completado
    FROM Sugerencias s
    INNER JOIN Tests t ON s.id_test = t.id_test
    WHERE s.id_estudiante = p_id_usuario
        AND t.estado_test = 'activo'
    ORDER BY s.fecha_ultima_sugerencia DESC;
END //

-- Obtener todos los tests disponibles incluyendo sugeridos
-- Simplificado: busca sugerencias directamente por id_estudiante
CREATE PROCEDURE sp_obtener_todos_tests_estudiante(
    IN p_id_usuario INT
)
BEGIN
    -- Tests sugeridos por profesores
    SELECT 
        t.id_test,
        t.nombre,
        t.descripcion,
        t.num_items,
        t.tipo_test,
        t.created_at,
        t.updated_at,
        1 AS es_sugerido,
        s.id_sugerencia,
        s.fecha_sugerencia,
        s.fecha_ultima_sugerencia,
        -- Obtener nombres de los profesores (primer profesor del array)
        (SELECT CONCAT(u.nombre, ' ', u.apellido) 
         FROM Usuarios u 
         WHERE u.id_usuario = JSON_UNQUOTE(JSON_EXTRACT(s.profesores_ids, '$[0]'))
         LIMIT 1) AS nombre_profesor,
        -- Obtener nombre del curso (primer curso del array)
        (SELECT c.nombre_curso 
         FROM Cursos c 
         WHERE c.id_curso = JSON_UNQUOTE(JSON_EXTRACT(s.cursos_ids, '$[0]'))
         LIMIT 1) AS nombre_curso,
        CASE 
            WHEN EXISTS (
                SELECT 1 FROM Aplicaciones a 
                WHERE a.id_usuario = p_id_usuario 
                AND a.id_test = t.id_test 
                AND a.puntuacion_total IS NOT NULL
            ) THEN 1
            ELSE 0
        END AS completado
    FROM Sugerencias s
    INNER JOIN Tests t ON s.id_test = t.id_test
    WHERE s.id_estudiante = p_id_usuario
        AND t.estado_test = 'activo'
    
    UNION
    
    -- Tests generales disponibles (no sugeridos para este estudiante)
    SELECT 
        t.id_test,
        t.nombre,
        t.descripcion,
        t.num_items,
        t.tipo_test,
        t.created_at,
        t.updated_at,
        0 AS es_sugerido,
        NULL AS id_sugerencia,
        NULL AS fecha_sugerencia,
        NULL AS fecha_ultima_sugerencia,
        NULL AS nombre_profesor,
        NULL AS nombre_curso,
        CASE 
            WHEN EXISTS (
                SELECT 1 FROM Aplicaciones a 
                WHERE a.id_usuario = p_id_usuario 
                AND a.id_test = t.id_test 
                AND a.puntuacion_total IS NOT NULL
            ) THEN 1
            ELSE 0
        END AS completado
    FROM Tests t
    WHERE t.estado_test = 'activo'
        AND NOT EXISTS (
            SELECT 1 FROM Sugerencias s
            WHERE s.id_estudiante = p_id_usuario
                AND s.id_test = t.id_test
        )
    
    ORDER BY es_sugerido DESC, fecha_ultima_sugerencia DESC, nombre ASC;
END //

-- =============================================
-- ### PROCEDIMIENTOS PARA SISTEMA DE MÉTRICAS PSICOMÉTRICAS
-- =============================================

-- Función auxiliar para calcular percentil desde z-score
-- Aproximación usando distribución normal: percentil ≈ 50 + (z × 19.1)
DROP FUNCTION IF EXISTS `fn_calcular_percentil` //
CREATE FUNCTION `fn_calcular_percentil`(p_z_score DECIMAL(10,4))
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE v_percentil DECIMAL(5,2);
    
    IF p_z_score IS NULL THEN
        RETURN NULL;
    END IF;
    
    -- Fórmula de aproximación normal
    SET v_percentil = 50 + (p_z_score * 19.1);
    
    -- Limitar entre 0.01 y 99.99
    IF v_percentil < 0.01 THEN
        SET v_percentil = 0.01;
    ELSEIF v_percentil > 99.99 THEN
        SET v_percentil = 99.99;
    END IF;
    
    RETURN ROUND(v_percentil, 2);
END //

-- Procedimiento principal: Procesa una aplicación completada
-- Reemplaza a: sp_finalizar_aplicacion_y_calcular_puntuacion
DROP PROCEDURE IF EXISTS `sp_procesar_aplicacion` //
CREATE PROCEDURE `sp_procesar_aplicacion`(
    IN p_id_aplicacion INT
)
BEGIN
    -- Variables para datos base
    DECLARE v_id_test INT;
    DECLARE v_id_usuario INT;
    DECLARE v_tipo_test ENUM('estres','ansiedad');
    DECLARE v_num_items INT;
    DECLARE v_id_tipo_escala INT;
    DECLARE v_max_valor_escala INT;
    
    -- Variables para cálculos
    DECLARE v_puntuacion_bruta INT;
    DECLARE v_puntuacion_maxima INT;
    DECLARE v_porcentaje_score DECIMAL(5,2);
    DECLARE v_nivel_calculado ENUM('normal','leve','moderado','alto','severo');
    DECLARE v_resultado_nivel_texto VARCHAR(50);
    DECLARE v_orden_nivel INT;
    
    -- Variables para estadísticas
    DECLARE v_media DECIMAL(10,2);
    DECLARE v_desviacion DECIMAL(10,2);
    DECLARE v_z_score DECIMAL(10,4);
    DECLARE v_percentil DECIMAL(5,2);
    
    -- Variables para cambios
    DECLARE v_punt_anterior INT;
    DECLARE v_pct_anterior DECIMAL(5,2);
    DECLARE v_nivel_anterior ENUM('normal','leve','moderado','alto','severo');
    DECLARE v_orden_anterior INT;
    DECLARE v_fecha_anterior DATETIME;
    DECLARE v_cambio_absoluto INT;
    DECLARE v_cambio_pct DECIMAL(6,2);
    DECLARE v_dias_diferencia INT;
    DECLARE v_es_primera BOOLEAN;
    
    -- Variables para notas
    DECLARE v_notas TEXT;
    DECLARE v_tiene_riesgo_emergente BOOLEAN DEFAULT FALSE;
    
    -- PASO 1: Obtener datos base
    SELECT a.id_test, a.id_usuario, t.tipo_test, t.num_items, t.id_tipo_escala
    INTO v_id_test, v_id_usuario, v_tipo_test, v_num_items, v_id_tipo_escala
    FROM Aplicaciones a
    JOIN Tests t ON a.id_test = t.id_test
    WHERE a.id_aplicacion = p_id_aplicacion;
    
    IF v_id_test IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Aplicación no encontrada';
    END IF;
    
    -- Obtener valor máximo de la escala
    SELECT MAX(o.valor_puntuacion) INTO v_max_valor_escala
    FROM TiposEscala_Opciones teo
    JOIN Opciones_Respuesta o ON teo.id_opcion = o.id_opcion
    WHERE teo.id_tipo_escala = v_id_tipo_escala;
    
    IF v_max_valor_escala IS NULL THEN
        SET v_max_valor_escala = 4; -- Fallback por si no hay escala definida
    END IF;
    
    -- PASO 2: Calcular puntuación bruta y porcentaje
    SELECT SUM(ra.puntuacion_obtenida) INTO v_puntuacion_bruta
    FROM Respuestas_Aplicacion ra
    WHERE ra.id_aplicacion = p_id_aplicacion;
    
    IF v_puntuacion_bruta IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se encontraron respuestas para esta aplicación';
    END IF;
    
    SET v_puntuacion_maxima = v_num_items * v_max_valor_escala;
    SET v_porcentaje_score = ROUND((v_puntuacion_bruta / v_puntuacion_maxima) * 100, 2);
    
    -- PASO 3: Determinar nivel por baremo (intervalo semiabierto)
    SELECT b.nivel, b.orden 
    INTO v_nivel_calculado, v_orden_nivel
    FROM Baremos b
    WHERE b.tipo_test = v_tipo_test
      AND b.activo = TRUE
      AND v_porcentaje_score >= b.pct_min 
      AND v_porcentaje_score < b.pct_max
    ORDER BY b.pct_min ASC
    LIMIT 1;
    
    -- Si es exactamente 100.00, buscar el baremo con pct_max = 100.01
    IF v_nivel_calculado IS NULL AND v_porcentaje_score >= 100.00 THEN
        SELECT b.nivel, b.orden 
        INTO v_nivel_calculado, v_orden_nivel
        FROM Baremos b
        WHERE b.tipo_test = v_tipo_test
          AND b.activo = TRUE
          AND b.pct_max >= 100.00
        ORDER BY b.orden DESC
        LIMIT 1;
    END IF;
    
    -- Convertir ENUM a texto para compatibilidad
    SET v_resultado_nivel_texto = v_nivel_calculado;
    
    -- PASO 4: Calcular z-score y percentil
    SELECT e.media, e.desviacion INTO v_media, v_desviacion
    FROM Estadisticas_Poblacionales e
    WHERE e.tipo_test = v_tipo_test
      AND e.id_escuela IS NULL  -- Usar estadística global
      AND e.activo = TRUE
    LIMIT 1;
    
    IF v_media IS NOT NULL AND v_desviacion IS NOT NULL AND v_desviacion >= 0.01 THEN
        SET v_z_score = ROUND((v_puntuacion_bruta - v_media) / v_desviacion, 4);
        SET v_percentil = fn_calcular_percentil(v_z_score);
    ELSE
        SET v_z_score = NULL;
        SET v_percentil = NULL;
    END IF;
    
    -- PASO 5: Calcular cambio vs aplicación anterior del MISMO tipo
    SELECT a.puntuacion_total, a.porcentaje_score, a.nivel_calculado, a.fecha_finalizacion,
           b.orden
    INTO v_punt_anterior, v_pct_anterior, v_nivel_anterior, v_fecha_anterior,
         v_orden_anterior
    FROM Aplicaciones a
    JOIN Tests t ON a.id_test = t.id_test
    LEFT JOIN Baremos b ON b.tipo_test = t.tipo_test AND b.nivel = a.nivel_calculado AND b.activo = TRUE
    WHERE a.id_usuario = v_id_usuario
      AND t.tipo_test = v_tipo_test
      AND a.fecha_finalizacion IS NOT NULL
      AND a.id_aplicacion != p_id_aplicacion
      AND a.completo = TRUE
    ORDER BY a.fecha_finalizacion DESC
    LIMIT 1;
    
    IF v_punt_anterior IS NOT NULL THEN
        SET v_es_primera = FALSE;
        SET v_cambio_absoluto = v_puntuacion_bruta - v_punt_anterior;
        SET v_cambio_pct = ROUND(v_porcentaje_score - v_pct_anterior, 2);
        SET v_dias_diferencia = DATEDIFF(NOW(), v_fecha_anterior);
    ELSE
        SET v_es_primera = TRUE;
        SET v_cambio_absoluto = NULL;
        SET v_cambio_pct = NULL;
        SET v_dias_diferencia = NULL;
    END IF;
    
    -- PASO 6: Detectar riesgo emergente
    IF v_es_primera = FALSE AND v_nivel_anterior IS NOT NULL AND v_orden_anterior IS NOT NULL THEN
        -- Condición 1: Salto de normal/leve a alto/severo en <14 días
        IF v_orden_anterior <= 2 AND v_orden_nivel >= 4 AND v_dias_diferencia < 14 THEN
            SET v_tiene_riesgo_emergente = TRUE;
        END IF;
        
        -- Condición 2: Salto de 2+ niveles
        IF (v_orden_nivel - v_orden_anterior) >= 2 THEN
            SET v_tiene_riesgo_emergente = TRUE;
        END IF;
    END IF;
    
    -- PASO 7: Construir notas de cálculo
    SET v_notas = CONCAT(
        'Baremo: ', IFNULL(v_nivel_calculado, 'N/A'),
        ' | Z-score: ', IFNULL(CAST(v_z_score AS CHAR), 'N/A'),
        ' | Percentil: ', IFNULL(CAST(v_percentil AS CHAR), 'N/A')
    );
    
    IF v_tiene_riesgo_emergente THEN
        SET v_notas = CONCAT(v_notas, ' | ⚠️ RIESGO_EMERGENTE');
    END IF;
    
    -- PASO 8: Actualizar aplicación
    UPDATE Aplicaciones
    SET 
        puntuacion_total = v_puntuacion_bruta,
        puntuacion_maxima = v_puntuacion_maxima,
        porcentaje_score = v_porcentaje_score,
        resultado_nivel = v_resultado_nivel_texto,
        nivel_calculado = v_nivel_calculado,
        z_score = v_z_score,
        percentil = v_percentil,
        cambio_pct = v_cambio_pct,
        cambio_absoluto = v_cambio_absoluto,
        es_primera_aplicacion = v_es_primera,
        completo = TRUE,
        fecha_finalizacion = NOW(),
        notas_calculo = v_notas
    WHERE id_aplicacion = p_id_aplicacion;
    
    -- PASO 9: Marcar sugerencia como completada
    UPDATE Sugerencias
    SET estado = 'visto'
    WHERE id_estudiante = v_id_usuario
      AND id_test = v_id_test
      AND estado = 'pendiente';
    
    -- PASO 10: Retornar resultado
    SELECT 
        'Aplicación procesada exitosamente' AS Mensaje,
        v_puntuacion_bruta AS Puntuacion_Final,
        v_resultado_nivel_texto AS Nivel_Resultado,
        v_porcentaje_score AS Porcentaje,
        v_z_score AS Z_Score,
        v_percentil AS Percentil,
        v_cambio_pct AS Cambio_Porcentual,
        v_tiene_riesgo_emergente AS Riesgo_Emergente;
END //

-- Procedimiento para actualizar estadísticas poblacionales
-- Ejecutar semanalmente vía cron job
DROP PROCEDURE IF EXISTS `sp_actualizar_estadisticas_poblacionales` //
CREATE PROCEDURE `sp_actualizar_estadisticas_poblacionales`()
BEGIN
    DECLARE v_tipo ENUM('estres','ansiedad');
    DECLARE v_media DECIMAL(10,2);
    DECLARE v_desviacion DECIMAL(10,2);
    DECLARE v_n_muestral INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur CURSOR FOR SELECT 'estres' UNION SELECT 'ansiedad';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_tipo;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Calcular estadísticas para este tipo_test
        SELECT 
            AVG(a.puntuacion_total),
            STDDEV(a.puntuacion_total),
            COUNT(*)
        INTO v_media, v_desviacion, v_n_muestral
        FROM Aplicaciones a
        JOIN Tests t ON a.id_test = t.id_test
        WHERE t.tipo_test = v_tipo
          AND a.completo = TRUE
          AND a.fecha_finalizacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
          AND a.fecha_finalizacion IS NOT NULL;
        
        -- Solo insertar si hay suficiente muestra (n >= 30)
        IF v_n_muestral >= 30 AND v_desviacion IS NOT NULL THEN
            -- Desactivar estadística anterior
            UPDATE Estadisticas_Poblacionales
            SET activo = FALSE
            WHERE tipo_test = v_tipo 
              AND id_escuela IS NULL
              AND activo = TRUE;
            
            -- Insertar nueva estadística
            INSERT INTO Estadisticas_Poblacionales 
                (tipo_test, id_escuela, media, desviacion, n_muestral, activo, fecha_calculo)
            VALUES 
                (v_tipo, NULL, v_media, v_desviacion, v_n_muestral, TRUE, NOW());
        END IF;
    END LOOP;
    
    CLOSE cur;
    
    SELECT 'Estadísticas actualizadas' AS Mensaje;
END //

-- Procedimiento para dashboard de estudiante
DROP PROCEDURE IF EXISTS `sp_dashboard_estudiante` //
CREATE PROCEDURE `sp_dashboard_estudiante`(
    IN p_id_usuario INT
)
BEGIN
    -- Última aplicación de ESTRÉS
    SELECT 
        a.id_aplicacion,
        t.nombre AS test_nombre,
        a.puntuacion_total,
        a.puntuacion_maxima,
        a.porcentaje_score,
        a.nivel_calculado,
        a.z_score,
        a.percentil,
        a.cambio_pct,
        a.cambio_absoluto,
        a.es_primera_aplicacion,
        a.fecha_finalizacion,
        'estres' AS tipo_test
    FROM Aplicaciones a
    JOIN Tests t ON a.id_test = t.id_test
    WHERE a.id_usuario = p_id_usuario
      AND t.tipo_test = 'estres'
      AND a.completo = TRUE
      AND a.fecha_finalizacion IS NOT NULL
    ORDER BY a.fecha_finalizacion DESC
    LIMIT 1;
    
    -- Última aplicación de ANSIEDAD
    SELECT 
        a.id_aplicacion,
        t.nombre AS test_nombre,
        a.puntuacion_total,
        a.puntuacion_maxima,
        a.porcentaje_score,
        a.nivel_calculado,
        a.z_score,
        a.percentil,
        a.cambio_pct,
        a.cambio_absoluto,
        a.es_primera_aplicacion,
        a.fecha_finalizacion,
        'ansiedad' AS tipo_test
    FROM Aplicaciones a
    JOIN Tests t ON a.id_test = t.id_test
    WHERE a.id_usuario = p_id_usuario
      AND t.tipo_test = 'ansiedad'
      AND a.completo = TRUE
      AND a.fecha_finalizacion IS NOT NULL
    ORDER BY a.fecha_finalizacion DESC
    LIMIT 1;
    
    -- Estadísticas globales del estudiante
    SELECT 
        COUNT(DISTINCT a.id_aplicacion) AS total_tests,
        DATEDIFF(NOW(), MAX(a.fecha_finalizacion)) AS dias_ultimo_test,
        (SELECT COUNT(*) FROM Aplicaciones a2 
         JOIN Tests t2 ON a2.id_test = t2.id_test
         WHERE a2.id_usuario = p_id_usuario 
           AND a2.completo = TRUE
           AND t2.tipo_test = 'estres') AS total_tests_estres,
        (SELECT COUNT(*) FROM Aplicaciones a3
         JOIN Tests t3 ON a3.id_test = t3.id_test
         WHERE a3.id_usuario = p_id_usuario 
           AND a3.completo = TRUE
           AND t3.tipo_test = 'ansiedad') AS total_tests_ansiedad
    FROM Aplicaciones a
    WHERE a.id_usuario = p_id_usuario
      AND a.completo = TRUE
      AND a.fecha_finalizacion IS NOT NULL;
END //

-- Procedimiento para obtener historial detallado de un estudiante
DROP PROCEDURE IF EXISTS `sp_historial_estudiante` //
CREATE PROCEDURE `sp_historial_estudiante`(
    IN p_id_usuario INT,
    IN p_tipo_test ENUM('estres','ansiedad'),
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        a.id_aplicacion,
        a.fecha_finalizacion,
        t.nombre AS test_nombre,
        a.puntuacion_total,
        a.puntuacion_maxima,
        a.porcentaje_score,
        a.nivel_calculado,
        a.z_score,
        a.percentil,
        a.cambio_pct,
        a.cambio_absoluto,
        a.es_primera_aplicacion,
        a.notas_calculo
    FROM Aplicaciones a
    JOIN Tests t ON a.id_test = t.id_test
    WHERE a.id_usuario = p_id_usuario
      AND t.tipo_test = p_tipo_test
      AND a.fecha_finalizacion BETWEEN p_fecha_inicio AND p_fecha_fin
      AND a.completo = TRUE
    ORDER BY a.fecha_finalizacion DESC;
END //

-- Mantener compatibilidad: alias al procedimiento antiguo
DROP PROCEDURE IF EXISTS `sp_finalizar_aplicacion_y_calcular_puntuacion` //
CREATE PROCEDURE `sp_finalizar_aplicacion_y_calcular_puntuacion`(
    IN p_id_aplicacion INT
)
BEGIN
    CALL sp_procesar_aplicacion(p_id_aplicacion);
END //

-- =============================================
-- ### Grupo 10: Reportes y Métricas de Profesor
-- =============================================

-- Procedimiento para obtener historial de sugerencias realizadas por un profesor
-- Muestra qué tests ha sugerido, a qué cursos, y cuántos estudiantes lo completaron
DROP PROCEDURE IF EXISTS `sp_obtener_historial_sugerencias_profesor` //
CREATE PROCEDURE `sp_obtener_historial_sugerencias_profesor`(
    IN p_id_profesor INT,
    IN p_limite INT
)
BEGIN
    -- Obtener el historial de sugerencias agrupado por curso, test y fecha
    SELECT 
        c.nombre_curso,
        t.nombre AS nombre_test,
        t.tipo_test,
        COUNT(DISTINCT s.id_estudiante) AS estudiantes_sugeridos,
        COUNT(DISTINCT CASE 
            WHEN EXISTS (
                SELECT 1 FROM Aplicaciones a2
                WHERE a2.id_usuario = s.id_estudiante
                AND a2.id_test = s.id_test
                AND a2.completo = TRUE
                AND a2.fecha_finalizacion >= s.fecha_ultima_sugerencia
            ) THEN s.id_estudiante 
        END) AS estudiantes_completaron,
        MIN(s.fecha_sugerencia) AS primera_sugerencia,
        MAX(s.fecha_ultima_sugerencia) AS ultima_sugerencia,
        s.estado
    FROM Sugerencias s
    INNER JOIN Tests t ON s.id_test = t.id_test
    INNER JOIN Usuarios u ON s.id_estudiante = u.id_usuario
    INNER JOIN Usuario_Curso uc ON u.id_usuario = uc.id_usuario
    INNER JOIN Cursos c ON uc.id_curso = c.id_curso
    WHERE c.id_profesor = p_id_profesor
        AND JSON_CONTAINS(s.profesores_ids, CAST(p_id_profesor AS CHAR), '$')
    GROUP BY c.id_curso, c.nombre_curso, t.id_test, t.nombre, t.tipo_test, s.estado
    ORDER BY ultima_sugerencia DESC
    LIMIT p_limite;
END //

-- Procedimiento para obtener promedios por curso del profesor
-- Útil para la tarjeta de "Cursos con niveles altos"
DROP PROCEDURE IF EXISTS `sp_obtener_promedios_cursos_profesor` //
CREATE PROCEDURE `sp_obtener_promedios_cursos_profesor`(
    IN p_id_profesor INT
)
BEGIN
    SELECT 
        c.id_curso,
        c.nombre_curso,
        -- Promedio de estrés
        (SELECT ROUND(AVG(a.puntuacion_total), 1)
         FROM Aplicaciones a
         JOIN Tests t ON a.id_test = t.id_test
         JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
         WHERE uc.id_curso = c.id_curso
           AND t.tipo_test = 'estres'
           AND t.estado_test = 'activo'
           AND a.completo = TRUE
           AND a.fecha_finalizacion IS NOT NULL
        ) AS promedio_estres,
        -- Promedio de ansiedad
        (SELECT ROUND(AVG(a.puntuacion_total), 1)
         FROM Aplicaciones a
         JOIN Tests t ON a.id_test = t.id_test
         JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
         WHERE uc.id_curso = c.id_curso
           AND t.tipo_test = 'ansiedad'
           AND t.estado_test = 'activo'
           AND a.completo = TRUE
           AND a.fecha_finalizacion IS NOT NULL
        ) AS promedio_ansiedad,
        -- Conteo de estudiantes del curso
        (SELECT COUNT(DISTINCT uc2.id_usuario)
         FROM Usuario_Curso uc2
         WHERE uc2.id_curso = c.id_curso
        ) AS total_estudiantes,
        -- Conteo de estudiantes con nivel alto o severo
        (SELECT COUNT(DISTINCT a2.id_usuario)
         FROM Aplicaciones a2
         JOIN Usuario_Curso uc2 ON a2.id_usuario = uc2.id_usuario
         WHERE uc2.id_curso = c.id_curso
           AND a2.nivel_calculado IN ('alto', 'severo')
           AND a2.completo = TRUE
        ) AS estudiantes_riesgo
    FROM Cursos c
    WHERE c.id_profesor = p_id_profesor
    ORDER BY 
        GREATEST(
            COALESCE((SELECT AVG(a.puntuacion_total)
                     FROM Aplicaciones a
                     JOIN Tests t ON a.id_test = t.id_test
                     JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                     WHERE uc.id_curso = c.id_curso
                       AND t.tipo_test = 'estres'
                       AND t.estado_test = 'activo'
                       AND a.completo = TRUE), 0),
            COALESCE((SELECT AVG(a.puntuacion_total)
                     FROM Aplicaciones a
                     JOIN Tests t ON a.id_test = t.id_test
                     JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                     WHERE uc.id_curso = c.id_curso
                       AND t.tipo_test = 'ansiedad'
                       AND t.estado_test = 'activo'
                       AND a.completo = TRUE), 0)
        ) DESC
    LIMIT 10;
END //

-- Procedimiento para obtener estadísticas por escuela/facultad del profesor
-- Devuelve promedios agrupados por facultad para el gráfico de barras
DROP PROCEDURE IF EXISTS `sp_obtener_metricas_facultades_profesor` //
CREATE PROCEDURE `sp_obtener_metricas_facultades_profesor`(
    IN p_id_profesor INT
)
BEGIN
    SELECT 
        e.id_escuela,
        e.nombre_escuela,
        -- Promedio de estrés en la facultad
        ROUND(AVG(CASE WHEN t.tipo_test = 'estres' THEN a.puntuacion_total END), 1) AS avg_estres,
        COUNT(DISTINCT CASE WHEN t.tipo_test = 'estres' THEN a.id_aplicacion END) AS count_estres,
        -- Promedio de ansiedad en la facultad
        ROUND(AVG(CASE WHEN t.tipo_test = 'ansiedad' THEN a.puntuacion_total END), 1) AS avg_ansiedad,
        COUNT(DISTINCT CASE WHEN t.tipo_test = 'ansiedad' THEN a.id_aplicacion END) AS count_ansiedad
    FROM Escuelas e
    JOIN Cursos c ON c.id_escuela = e.id_escuela
    JOIN Usuario_Curso uc ON uc.id_curso = c.id_curso
    JOIN Aplicaciones a ON a.id_usuario = uc.id_usuario
    JOIN Tests t ON a.id_test = t.id_test
    WHERE c.id_profesor = p_id_profesor
      AND a.completo = TRUE
      AND a.fecha_finalizacion IS NOT NULL
      AND t.estado_test = 'activo'
      AND t.tipo_test IN ('estres', 'ansiedad')
    GROUP BY e.id_escuela, e.nombre_escuela
    ORDER BY e.nombre_escuela;
END //