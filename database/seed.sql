-- Idempotent seed: truncate relational and principal tables to ensure clean state
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE `Respuestas_Aplicacion`;
TRUNCATE TABLE `Aplicaciones`;
TRUNCATE TABLE `Usuario_Curso`;
TRUNCATE TABLE `Usuario_Escuela`;
TRUNCATE TABLE `TiposEscala_Opciones`;
TRUNCATE TABLE `Items`;
TRUNCATE TABLE `Cursos`;
TRUNCATE TABLE `Sugerencias`;
TRUNCATE TABLE `Tests`;
TRUNCATE TABLE `Opciones_Respuesta`;
TRUNCATE TABLE `Tipos_Escalas`;
TRUNCATE TABLE `Usuarios`;
TRUNCATE TABLE `Escuelas`;
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `Escuelas` (`id_escuela`, `nombre_escuela`, `telefono`) VALUES
(1, 'Ingeniería de Sistemas', '555-1001'),
(2, 'Psicología', '555-2002'),
(3, 'Ciencias de la Comunicación', '555-3003')
ON DUPLICATE KEY UPDATE `nombre_escuela`=VALUES(`nombre_escuela`), `telefono`=VALUES(`telefono`);

-- 1.2 Opciones de Respuesta (Escala Likert 0-3)
INSERT INTO `Opciones_Respuesta` (`id_opcion`, `texto_opcion`, `valor_puntuacion`) VALUES
(1, 'Nunca', 0),
(2, 'A veces', 1),
(3, 'Frecuentemente', 2),
(4, 'Siempre', 3)
ON DUPLICATE KEY UPDATE `texto_opcion`=VALUES(`texto_opcion`), `valor_puntuacion`=VALUES(`valor_puntuacion`);

-- Más opciones de respuesta
INSERT INTO `Opciones_Respuesta` (`id_opcion`, `texto_opcion`, `valor_puntuacion`) VALUES
(5, 'Totalmente en desacuerdo', 1),
(6, 'En desacuerdo', 2),
(7, 'De acuerdo', 3),
(8, 'Totalmente de acuerdo', 4),
(9, 'No', 0),
(10, 'Sí', 1),
(11, 'Nada en absoluto', 0),
(12, 'Un poco', 1),
(13, 'Bastante', 2),
(14, 'Mucho', 3),
(15, 'Muy insatisfecho', 1),
(16, 'Insatisfecho', 2),
(17, 'Neutral', 3),
(18, 'Satisfecho', 4),
(19, 'Muy satisfecho', 5)
ON DUPLICATE KEY UPDATE `texto_opcion`=VALUES(`texto_opcion`), `valor_puntuacion`=VALUES(`valor_puntuacion`);
-- Más tipos de escala
INSERT INTO `Tipos_Escalas` (`id_tipo_escala`, `nombre`, `descripcion`) VALUES
(2, 'Likert 5 puntos', 'Totalmente en desacuerdo, En desacuerdo, De acuerdo, Totalmente de acuerdo'),
(3, 'Binario', 'Sí o No'),
(4, 'Intensidad 4 puntos', 'Nada en absoluto, Un poco, Bastante, Mucho'),
(5, 'Satisfacción 5 puntos', 'Muy insatisfecho, Insatisfecho, Neutral, Satisfecho, Muy satisfecho')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `descripcion`=VALUES(`descripcion`);
-- Vinculación de Tipos de Escala con Opciones
-- Likert 5 puntos
INSERT IGNORE INTO `TiposEscala_Opciones` (`id_tipo_escala`, `id_opcion`) VALUES
(2, 5),
(2, 6),
(2, 7),
(2, 8);

-- Binario
INSERT IGNORE INTO `TiposEscala_Opciones` (`id_tipo_escala`, `id_opcion`) VALUES
(3, 9),
(3, 10);

-- Intensidad 4 puntos
INSERT IGNORE INTO `TiposEscala_Opciones` (`id_tipo_escala`, `id_opcion`) VALUES
(4, 11),
(4, 12),
(4, 13),
(4, 14);

-- Satisfacción 5 puntos
INSERT IGNORE INTO `TiposEscala_Opciones` (`id_tipo_escala`, `id_opcion`) VALUES
(5, 15),
(5, 16),
(5, 17),
(5, 18),
(5, 19);

-- Tipos de Escala
INSERT INTO `Tipos_Escalas` (`id_tipo_escala`, `nombre`, `descripcion`) VALUES
(1, 'Likert 4 puntos', 'Nunca, A veces, Frecuentemente, Siempre')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `descripcion`=VALUES(`descripcion`);

-- Vinculación de Tipos de Escala con Opciones
INSERT IGNORE INTO `TiposEscala_Opciones` (`id_tipo_escala`, `id_opcion`) VALUES
(1, 1), -- Nunca
(1, 2), -- A veces
(1, 3), -- Frecuentemente
(1, 4); -- Siempre

INSERT INTO `Usuarios` (`nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`) VALUES
('Ana', 'Admin', 'ADM001', 'admin123', 'Administrador')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`);

INSERT INTO `Usuarios` (`id_usuario`, `nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`) VALUES
(2, 'María', 'López', 'PROF001', 'prof123', 'Docente'),  -- ID 2
(3, 'Carlos', 'Ruiz', 'PROF002', 'prof123', 'Docente')  -- ID 3
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `codigo_usuario`=VALUES(`codigo_usuario`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`);

-- Estudiantes (Creamos 10 para la demo)
INSERT INTO `Usuarios` (`id_usuario`, `nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`, `genero`, `fecha_nacimiento`) VALUES
(4, 'Juan', 'Pérez', 'EST001', 'est123', 'Estudiante', 'Masculino', '2004-05-10'),
(5, 'Lucía', 'Gómez', 'EST002', 'est123', 'Estudiante', 'Femenino', '2004-08-22'),
(6, 'Pedro', 'Díaz', 'EST003', 'est123', 'Estudiante', 'Masculino', '2003-12-01'),
(7, 'Sofía', 'Mora', 'EST004', 'est123', 'Estudiante', 'Femenino', '2005-01-15'),
(8, 'Miguel', 'Torres', 'EST005', 'est123', 'Estudiante', 'Masculino', '2004-03-30'),
(9, 'Elena', 'Vargas', 'EST006', 'est123', 'Estudiante', 'Femenino', '2004-07-07'),
(10, 'David', 'Rios', 'EST007', 'est123', 'Estudiante', 'Masculino', '2003-09-19'),
(11, 'Carmen', 'Soto', 'EST008', 'est123', 'Estudiante', 'Femenino', '2005-02-28'),
(12, 'Raúl', 'Castro', 'EST009', 'est123', 'Estudiante', 'Masculino', '2004-11-11'),
(13, 'Laura', 'Meza', 'EST010', 'est123', 'Estudiante', 'Femenino', '2004-06-05')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `codigo_usuario`=VALUES(`codigo_usuario`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`), `genero`=VALUES(`genero`), `fecha_nacimiento`=VALUES(`fecha_nacimiento`);

INSERT INTO `Usuarios` (`nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`) VALUES
('Jorge', 'SuperAdmin', 'ADM002', 'admin456', 'Administrador'),
('Lucía', 'Gestora', 'ADM003', 'admin789', 'Administrador')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`);

INSERT INTO `Usuarios` (`nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`) VALUES
('Sofía', 'Martínez', 'PROF003', 'prof456', 'Docente'),
('Miguel', 'García', 'PROF004', 'prof789', 'Docente')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `codigo_usuario`=VALUES(`codigo_usuario`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`);

INSERT INTO `Usuarios` (`nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`, `genero`, `fecha_nacimiento`) VALUES
('Andrea', 'Ramírez', 'EST011', 'est456', 'Estudiante', 'Femenino', '2005-03-12'),
('Luis', 'Fernández', 'EST012', 'est789', 'Estudiante', 'Masculino', '2003-10-25'),
('Valeria', 'Cruz', 'EST013', 'est101', 'Estudiante', 'Femenino', '2004-12-30'),
('Pablo', 'Santos', 'EST014', 'est202', 'Estudiante', 'Masculino', '2005-06-18')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `codigo_usuario`=VALUES(`codigo_usuario`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`), `genero`=VALUES(`genero`), `fecha_nacimiento`=VALUES(`fecha_nacimiento`);

-- Estudiantes (Creamos 10 para la demo)
INSERT INTO `Usuarios` (`id_usuario`, `nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`, `genero`, `fecha_nacimiento`) VALUES
(4, 'Juan', 'Pérez', 'EST001', 'est123', 'Estudiante', 'Masculino', '2004-05-10'),
(5, 'Lucía', 'Gómez', 'EST002', 'est123', 'Estudiante', 'Femenino', '2004-08-22'),
(6, 'Pedro', 'Díaz', 'EST003', 'est123', 'Estudiante', 'Masculino', '2003-12-01'),
(7, 'Sofía', 'Mora', 'EST004', 'est123', 'Estudiante', 'Femenino', '2005-01-15'),
(8, 'Miguel', 'Torres', 'EST005', 'est123', 'Estudiante', 'Masculino', '2004-03-30'),
(9, 'Elena', 'Vargas', 'EST006', 'est123', 'Estudiante', 'Femenino', '2004-07-07'),
(10, 'David', 'Rios', 'EST007', 'est123', 'Estudiante', 'Masculino', '2003-09-19'),
(11, 'Carmen', 'Soto', 'EST008', 'est123', 'Estudiante', 'Femenino', '2005-02-28'),
(12, 'Raúl', 'Castro', 'EST009', 'est123', 'Estudiante', 'Masculino', '2004-11-11'),
(13, 'Laura', 'Meza', 'EST010', 'est123', 'Estudiante', 'Femenino', '2004-06-05')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `codigo_usuario`=VALUES(`codigo_usuario`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`), `genero`=VALUES(`genero`), `fecha_nacimiento`=VALUES(`fecha_nacimiento`);

-- 1.4 Tests
INSERT INTO `Tests` (`id_test`, `nombre`, `descripcion`, `num_items`) VALUES
(1, 'Test de Estrés Académico', 'Mide el nivel de sobrecarga percibida.', 5), -- 5 items para demo rápida
(2, 'Test de Ansiedad General', 'Evalúa síntomas psicofisiológicos de ansiedad.', 5)
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `descripcion`=VALUES(`descripcion`), `num_items`=VALUES(`num_items`);

-- ----------------------------------------------------------------
-- 2. TABLAS DEPENDIENTES (NIVEL 1)
-- ----------------------------------------------------------------

-- 2.1 Items (Preguntas de los Tests)
-- Items Test Estrés (ID 1)
INSERT INTO `Items` (`id_item`, `id_test`, `texto_item`, `subescala`, `orden`) VALUES
(1, 1, 'Me siento abrumado por la cantidad de tareas.', 'Sobrecarga', 1),
(2, 1, 'Tengo dificultades para concentrarme en clase.', 'Cognitivo', 2),
(3, 1, 'Siento dolores de cabeza frecuentes.', 'Físico', 3),
(4, 1, 'Me preocupa no cumplir las expectativas.', 'Emocional', 4),
(5, 1, 'Duermo menos de lo necesario por estudiar.', 'Físico', 5)
ON DUPLICATE KEY UPDATE `id_test`=VALUES(`id_test`), `texto_item`=VALUES(`texto_item`), `subescala`=VALUES(`subescala`), `orden`=VALUES(`orden`);

-- Items Test Ansiedad (ID 2)
INSERT INTO `Items` (`id_item`, `id_test`, `texto_item`, `subescala`, `orden`) VALUES
(6, 2, 'Siento nerviosismo o agitación interior.', 'Emocional', 1),
(7, 2, 'Tengo miedo a que suceda algo terrible.', 'Cognitivo', 2),
(8, 2, 'Siento que el corazón me late muy rápido.', 'Físico', 3),
(9, 2, 'Tengo molestias estomacales antes de exámenes.', 'Físico', 4),
(10, 2, 'Me cuesta relajarme incluso en tiempo libre.', 'Conductual', 5)
ON DUPLICATE KEY UPDATE `id_test`=VALUES(`id_test`), `texto_item`=VALUES(`texto_item`), `subescala`=VALUES(`subescala`), `orden`=VALUES(`orden`);

-- 2.2 Cursos (Vinculan Escuela + Profesor)
INSERT INTO `Cursos` (`id_curso`, `nombre_curso`, `id_escuela`, `id_profesor`) VALUES
(1, 'Matemáticas I', 1, 2),        -- Prof María (Sistemas)
(2, 'Programación Web', 1, 2),     -- Prof María (Sistemas)
(3, 'Psicología General', 2, 3),   -- Prof Carlos (Psicología)
(4, 'Taller de Liderazgo', 3, 3)  -- Prof Carlos (Comunicaciones)
ON DUPLICATE KEY UPDATE `nombre_curso`=VALUES(`nombre_curso`), `id_escuela`=VALUES(`id_escuela`), `id_profesor`=VALUES(`id_profesor`);

-- ----------------------------------------------------------------

INSERT IGNORE INTO `Usuario_Curso` (`id_usuario`, `id_curso`) VALUES
(4, 1), (5, 1), (6, 1), (7, 1), (8, 1),
(9, 2), (10, 2), (11, 2), (12, 2), (13, 2),
(4, 3), (6, 3), (10, 3), (12, 3);

-- 3.1.1 Usuario_Escuela (Vinculación de estudiantes con escuelas)
INSERT IGNORE INTO `Usuario_Escuela` (`id_usuario`, `id_escuela`) VALUES
(4, 1), -- Juan -> Ingeniería de Sistemas
(5, 1), -- Lucía -> Ingeniería de Sistemas
(6, 1), -- Pedro -> Ingeniería de Sistemas
(7, 1), -- Sofía -> Ingeniería de Sistemas
(8, 1), -- Miguel -> Ingeniería de Sistemas
(9, 2), -- Elena -> Psicología
(10, 2), -- David -> Psicología
(11, 2), -- Carmen -> Psicología
(12, 2), -- Raúl -> Psicología
(13, 3); -- Laura -> Ciencias de la Comunicación

-- 3.2 Sugerencias (Profesores sugiriendo tests)
INSERT INTO `Sugerencias` (`id_curso`, `id_test`, `id_profesor`, `estado`, `fecha_sugerencia`) VALUES
(1, 1, 2, 'pendiente', '2025-11-20 10:00:00'), -- María sugiere Estrés a Mate
(3, 2, 3, 'visto', '2025-11-19 14:30:00')      -- Carlos sugiere Ansiedad a Psico
ON DUPLICATE KEY UPDATE `estado`=VALUES(`estado`), `fecha_sugerencia`=VALUES(`fecha_sugerencia`);

-- ----------------------------------------------------------------

INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (1, 4, 1, 'uuid-simulado-001', 14, 'Alto')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

INSERT IGNORE INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(1, 1, 4, 3), -- Siempre (3 pts)
(1, 2, 4, 3), -- Siempre (3 pts)
(1, 3, 3, 2), -- Frecuentemente (2 pts)
(1, 4, 4, 3), -- Siempre (3 pts)
(1, 5, 4, 3); -- Siempre (3 pts) -> Total 14
-- Más ejemplos de aplicaciones y vínculos usuario-escuela
-- Alumno Sofía (ID 7) hace Test Estrés -> Moderado
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (6, 7, 1, 'uuid-simulado-006', 9, 'Moderado')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

-- Alumno Carmen (ID 11) hace Test Ansiedad -> Alto
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (7, 11, 2, 'uuid-simulado-007', 13, 'Alto')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

-- Alumno Raúl (ID 12) hace Test Estrés -> Moderado
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (8, 12, 1, 'uuid-simulado-008', 7, 'Moderado')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

-- Alumna Laura (ID 13) hace Test Ansiedad -> Moderado
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (9, 13, 2, 'uuid-simulado-009', 8, 'Moderado')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

-- Alumno Pedro (ID 6) hace Test Ansiedad -> Alto
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (10, 6, 2, 'uuid-simulado-010', 14, 'Alto')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);




-- CASO 2: Alumna Lucía (ID 5) hace Test Estrés -> Resultado BAJO (Muchos 0 y 1)
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (2, 5, 1, 'uuid-simulado-002', 3, 'Bajo')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

-- Detalle de respuestas de Lucía
INSERT IGNORE INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(2, 1, 2, 1), -- A veces
(2, 2, 1, 0), -- Nunca
(2, 3, 1, 0), -- Nunca
(2, 4, 2, 1), -- A veces
(2, 5, 2, 1); -- A veces -> Total 3


-- CASO 3: Alumno Pedro (ID 6) hace Test Estrés -> Resultado MODERADO
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (3, 6, 1, 'uuid-simulado-003', 8, 'Moderado')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

INSERT IGNORE INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(3, 1, 3, 2), (3, 2, 2, 1), (3, 3, 3, 2), (3, 4, 2, 1), (3, 5, 3, 2);


-- CASO 4: Alumna Elena (ID 9) hace Test Ansiedad (ID 2) -> Resultado ALTO
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (4, 9, 2, 'uuid-simulado-004', 15, 'Alto') -- Máximo posible
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

INSERT IGNORE INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(4, 6, 4, 3), -- Items del 6 al 10 corresponden al test 2
(4, 7, 4, 3),
(4, 8, 4, 3),
(4, 9, 4, 3),
(4, 10, 4, 3);

-- CASO 5: Alumno David (ID 10) hace Test Ansiedad -> Resultado BAJO
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `puntuacion_total`, `resultado_nivel`) 
VALUES (5, 10, 2, 'uuid-simulado-005', 2, 'Bajo')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

INSERT IGNORE INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(5, 6, 1, 0), 
(5, 7, 1, 0),
(5, 8, 2, 1),
(5, 9, 1, 0),
(5, 10, 2, 1);