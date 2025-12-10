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
TRUNCATE TABLE `Recomendaciones`;
TRUNCATE TABLE `Notificaciones`;
-- Truncate nuevas tablas de métricas
TRUNCATE TABLE `Agregaciones`;
TRUNCATE TABLE `Estadisticas_Poblacionales`;
TRUNCATE TABLE `Baremos`;
SET FOREIGN_KEY_CHECKS=1;


-- Más escuelas para simular variedad
INSERT INTO `Escuelas` (`id_escuela`, `nombre_escuela`, `telefono`) VALUES
(1, 'Ingeniería de Sistemas', '555-1001'),
(2, 'Psicología', '555-2002'),
(3, 'Ciencias de la Comunicación', '555-3003'),
(4, 'Administración de Empresas', '555-4004'),
(5, 'Educación', '555-5005'),
(6, 'Derecho', '555-6006'),
(7, 'Medicina', '555-7007')
ON DUPLICATE KEY UPDATE `nombre_escuela`=VALUES(`nombre_escuela`), `telefono`=VALUES(`telefono`);

-- ============================================
-- DATOS INICIALES: BAREMOS PSICOMÉTRICOS
-- ============================================

-- Baremos para ESTRÉS (intervalos semiabiertos)
-- Importante: pct_min es INCLUSIVO, pct_max es EXCLUSIVO (excepto el último)
INSERT INTO `Baremos` (`tipo_test`, `nivel`, `pct_min`, `pct_max`, `descripcion`, `color_hex`, `orden`, `activo`) VALUES
('estres', 'normal',   0.00,  20.00, 'Nivel de estrés dentro de parámetros normales', '#28a745', 1, TRUE),
('estres', 'leve',    20.00,  40.00, 'Estrés leve, manejable con técnicas de relajación', '#ffc107', 2, TRUE),
('estres', 'moderado', 40.00, 60.00, 'Estrés moderado, requiere atención y manejo', '#fd7e14', 3, TRUE),
('estres', 'alto',     60.00, 80.00, 'Estrés alto, se recomienda intervención profesional', '#dc3545', 4, TRUE),
('estres', 'severo',   80.00, 100.01, 'Estrés severo, requiere atención inmediata', '#6f1e23', 5, TRUE)
ON DUPLICATE KEY UPDATE 
    `pct_min`=VALUES(`pct_min`), 
    `pct_max`=VALUES(`pct_max`),
    `descripcion`=VALUES(`descripcion`),
    `color_hex`=VALUES(`color_hex`),
    `orden`=VALUES(`orden`);

-- Baremos para ANSIEDAD (umbrales más bajos, se manifiesta clínicamente antes)
INSERT INTO `Baremos` (`tipo_test`, `nivel`, `pct_min`, `pct_max`, `descripcion`, `color_hex`, `orden`, `activo`) VALUES
('ansiedad', 'normal',   0.00,  15.00, 'Nivel de ansiedad dentro de parámetros normales', '#28a745', 1, TRUE),
('ansiedad', 'leve',    15.00,  35.00, 'Ansiedad leve, síntomas ocasionales', '#ffc107', 2, TRUE),
('ansiedad', 'moderado', 35.00, 55.00, 'Ansiedad moderada, afecta funcionamiento diario', '#fd7e14', 3, TRUE),
('ansiedad', 'alto',     55.00, 75.00, 'Ansiedad alta, interfiere significativamente', '#dc3545', 4, TRUE),
('ansiedad', 'severo',   75.00, 100.01, 'Ansiedad severa, requiere intervención urgente', '#6f1e23', 5, TRUE)
ON DUPLICATE KEY UPDATE 
    `pct_min`=VALUES(`pct_min`), 
    `pct_max`=VALUES(`pct_max`),
    `descripcion`=VALUES(`descripcion`),
    `color_hex`=VALUES(`color_hex`),
    `orden`=VALUES(`orden`);

-- ============================================
-- ESTADÍSTICAS POBLACIONALES INICIALES
-- ============================================
-- Estas son estimaciones basadas en poblaciones universitarias típicas
-- Se actualizarán automáticamente con sp_actualizar_estadisticas_poblacionales

-- Estadísticas globales para ESTRÉS (basadas en escala 0-42 para 14 items × 3 pts)
INSERT INTO `Estadisticas_Poblacionales` (`tipo_test`, `id_escuela`, `media`, `desviacion`, `n_muestral`, `activo`, `fecha_calculo`) VALUES
('estres', NULL, 21.50, 8.30, 150, TRUE, NOW())
ON DUPLICATE KEY UPDATE 
    `media`=VALUES(`media`), 
    `desviacion`=VALUES(`desviacion`),
    `n_muestral`=VALUES(`n_muestral`),
    `activo`=VALUES(`activo`),
    `fecha_calculo`=VALUES(`fecha_calculo`);

-- Estadísticas globales para ANSIEDAD (basadas en escala 0-42 para 14 items × 3 pts)
INSERT INTO `Estadisticas_Poblacionales` (`tipo_test`, `id_escuela`, `media`, `desviacion`, `n_muestral`, `activo`, `fecha_calculo`) VALUES
('ansiedad', NULL, 19.80, 9.10, 150, TRUE, NOW())
ON DUPLICATE KEY UPDATE 
    `media`=VALUES(`media`), 
    `desviacion`=VALUES(`desviacion`),
    `n_muestral`=VALUES(`n_muestral`),
    `activo`=VALUES(`activo`),
    `fecha_calculo`=VALUES(`fecha_calculo`);

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


-- Más profesores
INSERT INTO `Usuarios` (`id_usuario`, `nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`) VALUES
(2, 'María', 'López', 'PROF001', 'prof123', 'Docente'),  -- ID 2
(3, 'Carlos', 'Ruiz', 'PROF002', 'prof123', 'Docente'),  -- ID 3
(14, 'Patricia', 'Mendoza', 'PROF005', 'prof123', 'Docente'),
(15, 'Javier', 'Ortega', 'PROF006', 'prof123', 'Docente'),
(16, 'Rosa', 'Salas', 'PROF007', 'prof123', 'Docente'),
(17, 'Alberto', 'Vega', 'PROF008', 'prof123', 'Docente')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `apellido`=VALUES(`apellido`), `codigo_usuario`=VALUES(`codigo_usuario`), `password`=VALUES(`password`), `cargo`=VALUES(`cargo`);

-- Estudiantes (Creamos 10 para la demo)

-- Más estudiantes (hasta 50)
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
(13, 'Laura', 'Meza', 'EST010', 'est123', 'Estudiante', 'Femenino', '2004-06-05'),
(18, 'Mario', 'Paz', 'EST011', 'est123', 'Estudiante', 'Masculino', '2004-01-12'),
(19, 'Valeria', 'Quispe', 'EST012', 'est123', 'Estudiante', 'Femenino', '2005-04-18'),
(20, 'Andrés', 'Silva', 'EST013', 'est123', 'Estudiante', 'Masculino', '2003-10-09'),
(21, 'Paula', 'Ramos', 'EST014', 'est123', 'Estudiante', 'Femenino', '2004-12-22'),
(22, 'Jorge', 'Mendoza', 'EST015', 'est123', 'Estudiante', 'Masculino', '2005-03-15'),
(23, 'Natalia', 'Cruz', 'EST016', 'est123', 'Estudiante', 'Femenino', '2004-08-30'),
(24, 'Sergio', 'García', 'EST017', 'est123', 'Estudiante', 'Masculino', '2003-11-25'),
(25, 'Camila', 'Flores', 'EST018', 'est123', 'Estudiante', 'Femenino', '2005-06-10'),
(26, 'Ricardo', 'Soto', 'EST019', 'est123', 'Estudiante', 'Masculino', '2004-02-14'),
(27, 'Daniela', 'Vera', 'EST020', 'est123', 'Estudiante', 'Femenino', '2004-09-17'),
(28, 'Hugo', 'Morales', 'EST021', 'est123', 'Estudiante', 'Masculino', '2003-07-21'),
(29, 'Mónica', 'Herrera', 'EST022', 'est123', 'Estudiante', 'Femenino', '2005-05-03'),
(30, 'Pablo', 'Navarro', 'EST023', 'est123', 'Estudiante', 'Masculino', '2004-10-28'),
(31, 'Carla', 'Ortega', 'EST024', 'est123', 'Estudiante', 'Femenino', '2004-03-19'),
(32, 'Luis', 'Reyes', 'EST025', 'est123', 'Estudiante', 'Masculino', '2003-12-12'),
(33, 'Gabriela', 'Campos', 'EST026', 'est123', 'Estudiante', 'Femenino', '2005-07-15'),
(34, 'Oscar', 'Vargas', 'EST027', 'est123', 'Estudiante', 'Masculino', '2004-06-23'),
(35, 'Patricia', 'Luna', 'EST028', 'est123', 'Estudiante', 'Femenino', '2004-11-30'),
(36, 'Martín', 'Peña', 'EST029', 'est123', 'Estudiante', 'Masculino', '2003-08-16'),
(37, 'Rocío', 'Salazar', 'EST030', 'est123', 'Estudiante', 'Femenino', '2005-02-11'),
(38, 'Felipe', 'Guzmán', 'EST031', 'est123', 'Estudiante', 'Masculino', '2004-04-27'),
(39, 'Lorena', 'Mora', 'EST032', 'est123', 'Estudiante', 'Femenino', '2004-09-05'),
(40, 'Tomás', 'Ibarra', 'EST033', 'est123', 'Estudiante', 'Masculino', '2003-10-13'),
(41, 'Silvia', 'Paredes', 'EST034', 'est123', 'Estudiante', 'Femenino', '2005-06-29'),
(42, 'Ramiro', 'Cáceres', 'EST035', 'est123', 'Estudiante', 'Masculino', '2004-01-25'),
(43, 'Marina', 'Bravo', 'EST036', 'est123', 'Estudiante', 'Femenino', '2004-08-08'),
(44, 'Esteban', 'Ríos', 'EST037', 'est123', 'Estudiante', 'Masculino', '2003-11-02'),
(45, 'Luciana', 'Serrano', 'EST038', 'est123', 'Estudiante', 'Femenino', '2005-03-27'),
(46, 'Julián', 'Palacios', 'EST039', 'est123', 'Estudiante', 'Masculino', '2004-07-18'),
(47, 'Alicia', 'Montes', 'EST040', 'est123', 'Estudiante', 'Femenino', '2004-12-09'),
(48, 'Rodrigo', 'Espinoza', 'EST041', 'est123', 'Estudiante', 'Masculino', '2003-09-14'),
(49, 'Teresa', 'Rivas', 'EST042', 'est123', 'Estudiante', 'Femenino', '2005-01-22'),
(50, 'Emilio', 'Saavedra', 'EST043', 'est123', 'Estudiante', 'Masculino', '2004-05-25'),
(51, 'Sandra', 'Aguilar', 'EST044', 'est123', 'Estudiante', 'Femenino', '2004-10-02'),
(52, 'Guillermo', 'Delgado', 'EST045', 'est123', 'Estudiante', 'Masculino', '2003-07-30'),
(53, 'Florencia', 'Mena', 'EST046', 'est123', 'Estudiante', 'Femenino', '2005-06-14'),
(54, 'Maximiliano', 'Ponce', 'EST047', 'est123', 'Estudiante', 'Masculino', '2004-02-19'),
(55, 'Isabel', 'Santos', 'EST048', 'est123', 'Estudiante', 'Femenino', '2004-09-25'),
(56, 'Cristian', 'Vidal', 'EST049', 'est123', 'Estudiante', 'Masculino', '2003-11-17'),
(57, 'Verónica', 'Acosta', 'EST050', 'est123', 'Estudiante', 'Femenino', '2005-04-04')
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


INSERT INTO `Tests` (`id_test`, `nombre`, `descripcion`, `num_items`, `tipo_test`, `estado_test`) VALUES
(1, 'Test de Estrés Académico', 'Mide el nivel de sobrecarga percibida.', 5, 'estres', 'activo'),
(2, 'Test de Ansiedad General', 'Evalúa síntomas psicofisiológicos de ansiedad.', 5, 'ansiedad', 'activo'),
(3, 'Inventario de Depresión', 'Evalúa síntomas depresivos.', 6, 'estres', 'inactivo'),
(4, 'Test de Resiliencia', 'Evalúa la capacidad de adaptación.', 4, 'estres', 'activo'),
(5, 'Cuestionario de Sueño', 'Evalúa calidad del sueño.', 5, 'ansiedad', 'activo'),
(6, 'Test Personalizado Docente', 'Test creado por docente.', 3, 'estres', 'activo')
ON DUPLICATE KEY UPDATE `nombre`=VALUES(`nombre`), `descripcion`=VALUES(`descripcion`), `num_items`=VALUES(`num_items`), `tipo_test`=VALUES(`tipo_test`), `estado_test`=VALUES(`estado_test`);


INSERT INTO `Items` (`id_item`, `id_test`, `texto_item`, `subescala`, `orden`) VALUES
(1, 1, 'Me siento abrumado por la cantidad de tareas.', 'Sobrecarga', 1),
(2, 1, 'Tengo dificultades para concentrarme en clase.', 'Cognitivo', 2),
(3, 1, 'Siento dolores de cabeza frecuentes.', 'Físico', 3),
(4, 1, 'Me preocupa no cumplir las expectativas.', 'Emocional', 4),
(5, 1, 'Duermo menos de lo necesario por estudiar.', 'Físico', 5)
ON DUPLICATE KEY UPDATE `id_test`=VALUES(`id_test`), `texto_item`=VALUES(`texto_item`), `subescala`=VALUES(`subescala`), `orden`=VALUES(`orden`);

INSERT INTO `Items` (`id_item`, `id_test`, `texto_item`, `subescala`, `orden`) VALUES
(6, 2, 'Siento nerviosismo o agitación interior.', 'Emocional', 1),
(7, 2, 'Tengo miedo a que suceda algo terrible.', 'Cognitivo', 2),
(8, 2, 'Siento que el corazón me late muy rápido.', 'Físico', 3),
(9, 2, 'Tengo molestias estomacales antes de exámenes.', 'Físico', 4),
(10, 2, 'Me cuesta relajarme incluso en tiempo libre.', 'Conductual', 5)
ON DUPLICATE KEY UPDATE `id_test`=VALUES(`id_test`), `texto_item`=VALUES(`texto_item`), `subescala`=VALUES(`subescala`), `orden`=VALUES(`orden`);


INSERT INTO `Cursos` (`id_curso`, `nombre_curso`, `id_escuela`, `id_profesor`) VALUES
(1, 'Matemáticas I', 1, 2),
(2, 'Programación Web', 1, 2),
(3, 'Psicología General', 2, 3),
(4, 'Taller de Liderazgo', 3, 3),
(5, 'Gestión Empresarial', 4, 14),
(6, 'Didáctica', 5, 15),
(7, 'Derecho Penal', 6, 16),
(8, 'Anatomía', 7, 17),
(9, 'Estadística', 1, 2),
(10, 'Neuropsicología', 2, 3)
ON DUPLICATE KEY UPDATE `nombre_curso`=VALUES(`nombre_curso`), `id_escuela`=VALUES(`id_escuela`), `id_profesor`=VALUES(`id_profesor`);



INSERT IGNORE INTO `Usuario_Curso` (`id_usuario`, `id_curso`) VALUES
(4, 1), (5, 1), (6, 1), (7, 1), (8, 1), (18, 1), (19, 1), (20, 1), (21, 1), (22, 1),
(9, 2), (10, 2), (11, 2), (12, 2), (13, 2), (23, 2), (24, 2), (25, 2), (26, 2), (27, 2),
(4, 3), (6, 3), (10, 3), (12, 3), (28, 3), (29, 3), (30, 3), (31, 3), (32, 3), (33, 3),
(18, 4), (19, 4), (20, 4), (21, 4), (22, 4), (23, 4), (24, 4), (25, 4), (26, 4), (27, 4),
(28, 5), (29, 5), (30, 5), (31, 5), (32, 5), (33, 5), (34, 5), (35, 5), (36, 5), (37, 5),
(38, 6), (39, 6), (40, 6), (41, 6), (42, 6), (43, 6), (44, 6), (45, 6), (46, 6), (47, 6),
(48, 7), (49, 7), (50, 7), (51, 7), (52, 7), (53, 7), (54, 7), (55, 7), (56, 7), (57, 7),
(4, 8), (5, 8), (6, 8), (7, 8), (8, 8), (9, 8), (10, 8), (11, 8), (12, 8), (13, 8),
(18, 9), (19, 9), (20, 9), (21, 9), (22, 9), (23, 9), (34, 9), (35, 9), (36, 9), (37, 9),
(24, 10), (25, 10), (26, 10), (27, 10), (28, 10), (29, 10), (30, 10), (31, 10), (32, 10), (33, 10);


INSERT IGNORE INTO `Usuario_Escuela` (`id_usuario`, `id_escuela`) VALUES
(4, 1), (4, 2),
(5, 1), (5, 4),
(6, 1), (6, 5),
(7, 1), (7, 3),
(8, 1), (8, 7),
(9, 2), (9, 3),
(10, 2), (10, 6),
(11, 2), (11, 5),
(12, 2), (12, 4),
(13, 3), (13, 7),
(18, 4), (18, 1), (18, 2),
(19, 5), (19, 2),
(20, 6), (20, 1),
(21, 7), (21, 3),
(22, 1), (22, 5),
(23, 2), (23, 4),
(24, 3), (24, 6),
(25, 4), (25, 7),
(26, 5), (26, 1),
(27, 6), (27, 2),
(28, 7), (28, 3),
(29, 1), (29, 4),
(30, 2), (30, 5),
(31, 3), (31, 6),
(32, 4), (32, 7),
(33, 5), (33, 1),
(34, 6), (34, 2),
(35, 7), (35, 3),
(36, 1), (36, 5),
(37, 2), (37, 6),
(38, 3), (38, 7),
(39, 4), (39, 1),
(40, 5), (40, 2),
(41, 6), (41, 3),
(42, 7), (42, 4),
(43, 1), (43, 5),
(44, 2), (44, 6),
(45, 3), (45, 7),
(46, 4), (46, 1),
(47, 5), (47, 2),
(48, 6), (48, 3),
(49, 7), (49, 4),
(50, 1), (50, 5),
(51, 2), (51, 6),
(52, 3), (52, 7),
(53, 4), (53, 1),
(54, 5), (54, 2),
(55, 6), (55, 3),
(56, 7), (56, 4),
(57, 1), (57, 5);
INSERT INTO `Aplicaciones` (`id_usuario`, `id_test`, `client_uuid`, `fecha_aplicacion`, `puntuacion_total`, `resultado_nivel`) VALUES
(18, 2, 'uuid-sim-051', '2023-08-01', 10, 'Moderado'),
(19, 4, 'uuid-sim-052', '2023-07-15', 12, 'Alto'),
(20, 5, 'uuid-sim-053', '2023-06-10', 8, 'Bajo'),
(21, 1, 'uuid-sim-054', '2023-05-20', 11, 'Alto'),
(22, 2, 'uuid-sim-055', '2023-04-25', 7, 'Bajo'),
(23, 4, 'uuid-sim-056', '2023-03-12', 13, 'Alto'),
(24, 5, 'uuid-sim-057', '2023-02-22', 9, 'Moderado'),
(25, 1, 'uuid-sim-058', '2023-01-15', 6, 'Bajo'),
(26, 2, 'uuid-sim-059', '2022-12-10', 12, 'Alto'),
(27, 4, 'uuid-sim-060', '2022-11-05', 8, 'Moderado'),
(28, 5, 'uuid-sim-061', '2022-10-01', 10, 'Bajo'),
(29, 1, 'uuid-sim-062', '2022-09-14', 11, 'Alto'),
(30, 2, 'uuid-sim-063', '2022-08-19', 7, 'Bajo'),
(31, 4, 'uuid-sim-064', '2022-07-23', 13, 'Alto'),
(32, 5, 'uuid-sim-065', '2022-06-17', 9, 'Moderado'),
(33, 1, 'uuid-sim-066', '2022-05-11', 6, 'Bajo'),
(34, 2, 'uuid-sim-067', '2022-04-06', 12, 'Alto'),
(35, 4, 'uuid-sim-068', '2022-03-01', 8, 'Moderado'),
(36, 5, 'uuid-sim-069', '2022-02-10', 10, 'Bajo'),
(37, 1, 'uuid-sim-070', '2022-01-05', 11, 'Alto'),
(38, 2, 'uuid-sim-071', '2021-12-20', 7, 'Bajo'),
(39, 4, 'uuid-sim-072', '2021-11-11', 13, 'Alto'),
(40, 5, 'uuid-sim-073', '2021-10-01', 9, 'Moderado'),
(41, 1, 'uuid-sim-074', '2021-09-14', 6, 'Bajo'),
(42, 2, 'uuid-sim-075', '2021-08-19', 12, 'Alto'),
(43, 4, 'uuid-sim-076', '2021-07-23', 8, 'Moderado'),
(44, 5, 'uuid-sim-077', '2021-06-17', 10, 'Bajo'),
(45, 1, 'uuid-sim-078', '2021-05-11', 11, 'Alto'),
(46, 2, 'uuid-sim-079', '2021-04-06', 7, 'Bajo'),
(47, 4, 'uuid-sim-080', '2021-03-01', 13, 'Alto'),
(48, 5, 'uuid-sim-081', '2021-02-10', 9, 'Moderado'),
(49, 1, 'uuid-sim-082', '2021-01-05', 6, 'Bajo'),
(50, 2, 'uuid-sim-083', '2021-01-20', 12, 'Alto')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `fecha_aplicacion`=VALUES(`fecha_aplicacion`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);

-- Nuevo diseño: cada sugerencia es por estudiante individual
-- profesores_ids y cursos_ids son arrays JSON para rastrear múltiples orígenes

-- Test 1 sugerido por Profesor 2 en Curso 1 (estudiantes: 4,5,6,7,8,18,19,20,21,22)
INSERT INTO `Sugerencias` (`id_estudiante`, `id_test`, `profesores_ids`, `cursos_ids`, `estado`, `fecha_sugerencia`) VALUES
(4, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(5, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(6, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(7, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(8, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(18, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(19, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(20, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(21, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(22, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),

-- Test 2 sugerido por Profesor 2 en Curso 1 (mismos estudiantes)
(4, 2, '[2]', '[1]', 'pendiente', '2025-11-21 09:00:00'),
(5, 2, '[2]', '[1]', 'pendiente', '2025-11-21 09:00:00'),
(6, 2, '[2]', '[1]', 'pendiente', '2025-11-21 09:00:00'),
(7, 2, '[2]', '[1]', 'pendiente', '2025-11-21 09:00:00'),
(8, 2, '[2]', '[1]', 'pendiente', '2025-11-21 09:00:00'),

-- Test 1 sugerido por Profesor 2 en Curso 2 (estudiantes: 9,10,11,12,13,23,24,25,26,27)
(9, 1, '[2]', '[2]', 'pendiente', '2025-11-19 15:00:00'),
(10, 1, '[2]', '[2]', 'pendiente', '2025-11-19 15:00:00'),
(11, 1, '[2]', '[2]', 'pendiente', '2025-11-19 15:00:00'),
(12, 1, '[2]', '[2]', 'pendiente', '2025-11-19 15:00:00'),
(13, 1, '[2]', '[2]', 'pendiente', '2025-11-19 15:00:00')

ON DUPLICATE KEY UPDATE 
    `profesores_ids` = VALUES(`profesores_ids`),
    `cursos_ids` = VALUES(`cursos_ids`),
    `estado` = VALUES(`estado`),
    `fecha_ultima_sugerencia` = VALUES(`fecha_sugerencia`);

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

-- Simulación de aplicaciones históricas (últimos 12 meses, puntajes variados, distintos tests y cursos)
-- Genera muchas aplicaciones para alimentar dashboards

-- Más aplicaciones históricas (simulación masiva, fechas y puntajes variados)
INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `fecha_aplicacion`, `puntuacion_total`, `resultado_nivel`) VALUES
(6, 7, 1, 'uuid-simulado-006', '2025-11-01', 9, 'Moderado'),
(7, 11, 2, 'uuid-simulado-007', '2025-10-15', 13, 'Alto'),
(8, 12, 1, 'uuid-simulado-008', '2025-09-10', 7, 'Moderado'),
(9, 13, 2, 'uuid-simulado-009', '2025-08-20', 8, 'Moderado'),
(10, 6, 2, 'uuid-simulado-010', '2025-07-05', 14, 'Alto'),
(11, 5, 4, 'uuid-simulado-011', '2025-06-12', 10, 'Bajo'),
(12, 8, 5, 'uuid-simulado-012', '2025-05-18', 12, 'Alto'),
(13, 9, 1, 'uuid-simulado-013', '2025-04-22', 6, 'Bajo'),
(14, 10, 2, 'uuid-simulado-014', '2025-03-30', 11, 'Moderado'),
(15, 4, 5, 'uuid-simulado-015', '2025-02-14', 9, 'Moderado'),
(16, 7, 4, 'uuid-simulado-016', '2025-01-10', 8, 'Bajo'),
(17, 11, 1, 'uuid-simulado-017', '2024-12-05', 13, 'Alto'),
(18, 12, 2, 'uuid-simulado-018', '2024-11-21', 7, 'Moderado'),
(19, 13, 4, 'uuid-simulado-019', '2024-10-11', 10, 'Alto'),
(20, 6, 5, 'uuid-simulado-020', '2024-09-09', 12, 'Alto'),
(21, 5, 1, 'uuid-simulado-021', '2024-08-15', 5, 'Bajo'),
(22, 8, 2, 'uuid-simulado-022', '2024-07-19', 9, 'Moderado'),
(23, 9, 4, 'uuid-simulado-023', '2024-06-23', 11, 'Alto'),
(24, 10, 5, 'uuid-simulado-024', '2024-05-28', 8, 'Bajo'),
(25, 4, 2, 'uuid-simulado-025', '2024-04-02', 13, 'Alto'),
(26, 7, 5, 'uuid-simulado-026', '2024-03-15', 7, 'Moderado'),
(27, 11, 4, 'uuid-simulado-027', '2024-02-10', 10, 'Moderado'),
(28, 12, 1, 'uuid-simulado-028', '2024-01-05', 6, 'Bajo'),
(29, 13, 5, 'uuid-simulado-029', '2023-12-20', 12, 'Alto'),
(30, 6, 4, 'uuid-simulado-030', '2023-11-11', 9, 'Moderado'),
-- 20 aplicaciones más, estudiantes y tests variados, fechas recientes
(31, 18, 1, 'uuid-sim-031', '2025-10-01', 8, 'Bajo'),
(32, 19, 2, 'uuid-sim-032', '2025-09-15', 12, 'Alto'),
(33, 20, 4, 'uuid-sim-033', '2025-08-10', 10, 'Moderado'),
(34, 21, 5, 'uuid-sim-034', '2025-07-20', 7, 'Bajo'),
(35, 22, 1, 'uuid-sim-035', '2025-06-25', 11, 'Alto'),
(36, 23, 2, 'uuid-sim-036', '2025-05-30', 9, 'Moderado'),
(37, 24, 4, 'uuid-sim-037', '2025-04-18', 6, 'Bajo'),
(38, 25, 5, 'uuid-sim-038', '2025-03-12', 13, 'Alto'),
(39, 26, 1, 'uuid-sim-039', '2025-02-22', 10, 'Moderado'),
(40, 27, 2, 'uuid-sim-040', '2025-01-15', 8, 'Bajo'),
(41, 28, 4, 'uuid-sim-041', '2024-12-10', 12, 'Alto'),
(42, 29, 5, 'uuid-sim-042', '2024-11-05', 7, 'Moderado'),
(43, 30, 1, 'uuid-sim-043', '2024-10-01', 9, 'Bajo'),
(44, 31, 2, 'uuid-sim-044', '2024-09-14', 11, 'Alto'),
(45, 32, 4, 'uuid-sim-045', '2024-08-19', 8, 'Moderado'),
(46, 33, 5, 'uuid-sim-046', '2024-07-23', 10, 'Bajo'),
(47, 34, 1, 'uuid-sim-047', '2024-06-17', 12, 'Alto'),
(48, 35, 2, 'uuid-sim-048', '2024-05-11', 6, 'Bajo'),
(49, 36, 4, 'uuid-sim-049', '2024-04-06', 13, 'Alto'),
(50, 37, 5, 'uuid-sim-050', '2024-03-01', 9, 'Moderado')
ON DUPLICATE KEY UPDATE `client_uuid`=VALUES(`client_uuid`), `fecha_aplicacion`=VALUES(`fecha_aplicacion`), `puntuacion_total`=VALUES(`puntuacion_total`), `resultado_nivel`=VALUES(`resultado_nivel`);




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


INSERT INTO `Citas` (`id_cita`, `id_alumno`, `fecha_cita`, `motivo`, `estado`)
VALUES
(1, 4, '2025-11-30 10:00:00', 'Orientación académica', 'pendiente'),
(2, 5, '2025-11-30 12:30:00', 'Problema personal', 'confirmada'),
(3, 6, '2025-12-01 09:00:00', 'Revisión de test', 'pendiente'),
(4, 7, '2025-12-01 11:00:00', 'Consulta de resultados', 'cancelada'),
(5, 9, '2025-12-02 14:00:00', 'Seguimiento académico', 'pendiente'),
(6, 10, '2025-12-02 16:00:00', 'Problema familiar', 'confirmada');

-- ----------------------------------------------------------------
-- Additional seed: more Aplicaciones across dates to produce richer charts
-- ----------------------------------------------------------------
-- We'll add applications for test 1 (Estrés) in Curso 1 and test 2 (Ansiedad) in Curso 2
-- Dates span 2025-11-21 .. 2025-12-02 to create time series
-- Aplicaciones con nuevo campo 'origen' y métricas psicométricas

INSERT INTO `Aplicaciones` (`id_aplicacion`, `id_usuario`, `id_test`, `client_uuid`, `fecha_aplicacion`, `puntuacion_total`, `resultado_nivel`, `origen`, `completo`, `fecha_finalizacion`, `puntuacion_maxima`, `porcentaje_score`, `nivel_calculado`) VALUES
-- 2025-11-21
(11, 4, 1, 'uuid-011', '2025-11-21 09:10:00', 5, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-21 09:25:00', 42, 11.90, 'normal'),
(12, 9, 2, 'uuid-012', '2025-11-21 10:30:00', 11, 'Moderado', 'profesor_sugerencia', TRUE, '2025-11-21 10:45:00', 42, 26.19, 'leve'),
-- 2025-11-22
(13, 5, 1, 'uuid-013', '2025-11-22 08:45:00', 8, 'Moderado', 'estudiante_voluntario', TRUE, '2025-11-22 09:00:00', 42, 19.05, 'normal'),
(14, 10, 2, 'uuid-014', '2025-11-22 11:20:00', 4, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-22 11:35:00', 42, 9.52, 'normal'),
-- 2025-11-23
(15, 6, 1, 'uuid-015', '2025-11-23 13:05:00', 12, 'Alto', 'profesor_sugerencia', TRUE, '2025-11-23 13:20:00', 42, 28.57, 'leve'),
(16, 11, 2, 'uuid-016', '2025-11-23 14:40:00', 9, 'Moderado', 'estudiante_voluntario', TRUE, '2025-11-23 14:55:00', 42, 21.43, 'leve'),
-- 2025-11-24
(17, 7, 1, 'uuid-017', '2025-11-24 09:50:00', 7, 'Moderado', 'estudiante_voluntario', TRUE, '2025-11-24 10:05:00', 42, 16.67, 'normal'),
(18, 12, 2, 'uuid-018', '2025-11-24 10:15:00', 2, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-24 10:30:00', 42, 4.76, 'normal'),
-- 2025-11-25
(19, 8, 1, 'uuid-019', '2025-11-25 15:30:00', 14, 'Alto', 'profesor_sugerencia', TRUE, '2025-11-25 15:45:00', 42, 33.33, 'leve'),
(20, 13, 2, 'uuid-020', '2025-11-25 16:45:00', 13, 'Alto', 'estudiante_voluntario', TRUE, '2025-11-25 17:00:00', 42, 30.95, 'leve'),
-- 2025-11-26
(21, 4, 1, 'uuid-021', '2025-11-26 09:05:00', 6, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-26 09:20:00', 42, 14.29, 'normal'),
(22, 9, 2, 'uuid-022', '2025-11-26 11:10:00', 10, 'Moderado', 'estudiante_voluntario', TRUE, '2025-11-26 11:25:00', 42, 23.81, 'leve'),
-- 2025-11-27
(23, 5, 1, 'uuid-023', '2025-11-27 08:30:00', 9, 'Moderado', 'estudiante_voluntario', TRUE, '2025-11-27 08:45:00', 42, 21.43, 'leve'),
(24, 10, 2, 'uuid-024', '2025-11-27 12:00:00', 5, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-27 12:15:00', 42, 11.90, 'normal'),
-- 2025-11-28
(25, 6, 1, 'uuid-025', '2025-11-28 14:20:00', 11, 'Alto', 'estudiante_voluntario', TRUE, '2025-11-28 14:35:00', 42, 26.19, 'leve'),
(26, 11, 2, 'uuid-026', '2025-11-28 15:55:00', 7, 'Moderado', 'profesor_sugerencia', TRUE, '2025-11-28 16:10:00', 42, 16.67, 'leve'),
-- 2025-11-29
(27, 7, 1, 'uuid-027', '2025-11-29 09:40:00', 4, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-29 09:55:00', 42, 9.52, 'normal'),
(28, 12, 2, 'uuid-028', '2025-11-29 10:50:00', 3, 'Bajo', 'estudiante_voluntario', TRUE, '2025-11-29 11:05:00', 42, 7.14, 'normal'),
-- 2025-11-30
(29, 8, 1, 'uuid-029', '2025-11-30 16:10:00', 13, 'Alto', 'estudiante_voluntario', TRUE, '2025-11-30 16:25:00', 42, 30.95, 'leve'),
(30, 13, 2, 'uuid-030', '2025-11-30 17:25:00', 12, 'Alto', 'estudiante_voluntario', TRUE, '2025-11-30 17:40:00', 42, 28.57, 'leve'),
-- 2025-12-01
(31, 4, 1, 'uuid-031', '2025-12-01 09:00:00', 8, 'Moderado', 'estudiante_voluntario', TRUE, '2025-12-01 09:15:00', 42, 19.05, 'normal'),
(32, 9, 2, 'uuid-032', '2025-12-01 11:30:00', 6, 'Moderado', 'estudiante_voluntario', TRUE, '2025-12-01 11:45:00', 42, 14.29, 'normal'),
-- 2025-12-02
(33, 5, 1, 'uuid-033', '2025-12-02 08:15:00', 10, 'Moderado', 'profesor_sugerencia', TRUE, '2025-12-02 08:30:00', 42, 23.81, 'leve'),
(34, 10, 2, 'uuid-034', '2025-12-02 12:45:00', 2, 'Bajo', 'estudiante_voluntario', TRUE, '2025-12-02 13:00:00', 42, 4.76, 'normal')
ON DUPLICATE KEY UPDATE 
    client_uuid=VALUES(client_uuid), 
    puntuacion_total=VALUES(puntuacion_total), 
    resultado_nivel=VALUES(resultado_nivel), 
    fecha_aplicacion=VALUES(fecha_aplicacion),
    origen=VALUES(origen),
    completo=VALUES(completo),
    fecha_finalizacion=VALUES(fecha_finalizacion),
    puntuacion_maxima=VALUES(puntuacion_maxima),
    porcentaje_score=VALUES(porcentaje_score),
    nivel_calculado=VALUES(nivel_calculado);

-- Notificaciones de ejemplo para usuarios
INSERT INTO `Notificaciones` (`id_usuario`, `titulo`, `mensaje`, `tipo`, `estado`, `fecha_creacion`) VALUES
(4, 'Bienvenido', '¡Hola Juan! Tienes un nuevo test sugerido.', 'info', 'nueva', NOW()),
(4, 'Test pendiente', 'Tienes un test pendiente de completar antes del viernes.', 'warning', 'nueva', NOW()),
(4, 'Test completado', '¡Felicidades! Has completado el Test de Estrés.', 'success', 'leida', NOW()),
(5, 'Recordatorio', 'Recuerda completar el Test de Estrés Académico.', 'warning', 'nueva', NOW()),
(5, 'Mensaje del docente', 'Tu profesor ha dejado un comentario en tu test.', 'info', 'nueva', NOW()),
(6, 'Resultado disponible', 'Tu resultado del Test de Ansiedad ya está listo.', 'success', 'nueva', NOW()),
(6, 'Actualización de curso', 'Se ha actualizado el material del curso Programación Web.', 'info', 'nueva', NOW()),
(2, 'Nuevo curso asignado', 'Has sido asignado como docente en Matemáticas I.', 'info', 'nueva', NOW()),
(2, 'Aviso de sistema', 'El sistema estará en mantenimiento el sábado.', 'warning', 'nueva', NOW()),
(1, 'Actualización', 'Se han actualizado los datos de la plataforma.', 'info', 'nueva', NOW()),
(1, 'Alerta de seguridad', 'Se detectó un inicio de sesión desde un nuevo dispositivo.', 'error', 'nueva', NOW()),
(7, 'Bienvenida', '¡Bienvenida Sofía! Explora los recursos disponibles.', 'info', 'nueva', NOW()),
(8, 'Test sugerido', 'Se te ha sugerido el Test de Resiliencia.', 'info', 'nueva', NOW()),
(9, 'Cita agendada', 'Tu cita con orientación está confirmada para el lunes.', 'success', 'nueva', NOW()),
(10, 'Material nuevo', 'Hay nuevo material disponible en tu curso de Estadística.', 'info', 'nueva', NOW())
ON DUPLICATE KEY UPDATE `mensaje`=VALUES(`mensaje`), `tipo`=VALUES(`tipo`), `estado`=VALUES(`estado`), `fecha_creacion`=VALUES(`fecha_creacion`);

-- Recomendaciones personalizadas según niveles de estrés/ansiedad
INSERT INTO `Recomendaciones` (`titulo`, `descripcion`, `categoria`, `tipo_test`, `nivel_minimo`, `nivel_maximo`, `prioridad`, `activa`) VALUES
-- Nivel 1-2: Normal/Leve
('Sesión de Mindfulness y Respiración', 'Practica técnicas de respiración profunda durante 10 minutos al día. Enfócate en inhalar por 4 segundos, mantener por 4, y exhalar por 6 segundos. Esto ayuda a reducir la activación del sistema nervioso y promueve la calma.', 'mental', 'ambos', 1, 2, 2, TRUE),
('Actividad Física Regular', 'Realiza al menos 30 minutos de ejercicio cardiovascular 3 veces por semana. El gimnasio del campus tiene horarios flexibles. El ejercicio libera endorfinas que mejoran el estado de ánimo.', 'fisica', 'ambos', 1, 2, 2, TRUE),
('Grupo de Apoyo Estudiantil', 'Únete a las sesiones de grupo de apoyo donde puedes compartir experiencias con otros estudiantes en situaciones similares. Las reuniones son los martes y jueves a las 5pm.', 'social', 'ambos', 1, 2, 1, TRUE),

-- Nivel 3: Moderado
('Taller de Gestión del Tiempo', 'Asiste al taller semanal sobre técnicas de organización y priorización de tareas académicas. Aprenderás métodos como Pomodoro, Eisenhower Matrix y técnicas de planificación efectiva. Se realiza los miércoles a las 4pm.', 'academica', 'estres', 2, 3, 3, TRUE),
('Sesiones de Relajación Guiada', 'Participa en sesiones de relajación muscular progresiva y visualización guiada. Estas técnicas han demostrado reducir significativamente los niveles de ansiedad. Disponible en el Centro de Bienestar, lunes y viernes a las 3pm.', 'mental', 'ansiedad', 2, 3, 3, TRUE),
('Asesoría Académica Personalizada', 'Agenda sesiones con tu asesor académico para revisar tu carga de trabajo y establecer metas realistas. Pueden ayudarte a reorganizar tu horario y priorizar actividades.', 'academica', 'ambos', 2, 4, 3, TRUE),
('Yoga y Meditación', 'Únete a las clases de yoga enfocadas en reducción de estrés. Combina posturas físicas con técnicas de respiración y meditación. Clases disponibles martes y jueves a las 6am y 7pm.', 'fisica', 'ambos', 2, 3, 2, TRUE),

-- Nivel 4: Alto
('Consulta Psicológica', 'Agenda una cita con el servicio de orientación psicológica del campus. Un profesional evaluará tu situación y te brindará estrategias específicas. Disponible de lunes a viernes de 8am a 6pm. Llama al ext. 5500.', 'profesional', 'ambos', 3, 4, 4, TRUE),
('Taller de Manejo de Ansiedad', 'Participa en el taller intensivo de técnicas cognitivo-conductuales para el manejo de la ansiedad. Aprenderás a identificar pensamientos automáticos negativos y reestructurarlos. Duración: 4 sesiones de 2 horas.', 'mental', 'ansiedad', 3, 4, 4, TRUE),
('Programa de Resiliencia', 'Inscríbete en el programa de 6 semanas para desarrollar habilidades de afrontamiento y resiliencia frente al estrés académico. Incluye técnicas de resolución de problemas y autoeficacia.', 'mental', 'estres', 3, 4, 4, TRUE),
('Revisión de Hábitos de Sueño', 'Consulta con el especialista en higiene del sueño. El sueño inadecuado amplifica el estrés y la ansiedad. Recibirás un plan personalizado para mejorar tu calidad de sueño.', 'fisica', 'ambos', 3, 5, 3, TRUE),

-- Nivel 5: Severo/Crítico
('Consulta de Urgencia con Psicólogo', 'Se recomienda agendar una cita de urgencia con el psicólogo del campus. Tu nivel requiere atención inmediata. Disponible de lunes a viernes de 8am a 6pm. Para emergencias fuera de horario, contacta a la línea de crisis estudiantil 24/7 al 1-800-AYUDA.', 'profesional', 'ambos', 4, 5, 5, TRUE),
('Evaluación Psiquiátrica', 'Considera una evaluación con el psiquiatra del servicio de salud estudiantil para determinar si requieres apoyo farmacológico complementario. Esta evaluación es confidencial y profesional.', 'profesional', 'ambos', 5, 5, 5, TRUE),
('Reducción Temporal de Carga Académica', 'Consulta con tu asesor académico sobre la posibilidad de reducir temporalmente tu carga de cursos o solicitar extensiones en tus trabajos. Tu bienestar es prioridad.', 'academica', 'ambos', 4, 5, 5, TRUE),
('Terapia Individual Intensiva', 'Inicia terapia psicológica individual con frecuencia de 2 sesiones por semana. El servicio de salud mental puede ayudarte a procesar y manejar la situación actual de manera efectiva.', 'profesional', 'ambos', 4, 5, 5, TRUE),
('Red de Apoyo Familiar', 'Considera involucrar a tu red de apoyo familiar o personas cercanas. Hablar con tus seres queridos sobre lo que estás experimentando puede ser muy beneficioso. El servicio de orientación puede facilitarte guías de comunicación.', 'social', 'ambos', 4, 5, 4, TRUE),
('Plan de Autocuidado Inmediato', 'Implementa un plan de autocuidado diario que incluya: 8 horas de sueño, alimentación balanceada, 30 min de actividad física ligera, descansos frecuentes de 10 min cada 2 horas de estudio, y evitar estimulantes como cafeína.', 'fisica', 'ambos', 4, 5, 4, TRUE)
ON DUPLICATE KEY UPDATE 
    `descripcion`=VALUES(`descripcion`), 
    `categoria`=VALUES(`categoria`),
    `tipo_test`=VALUES(`tipo_test`),
    `nivel_minimo`=VALUES(`nivel_minimo`),
    `nivel_maximo`=VALUES(`nivel_maximo`),
    `prioridad`=VALUES(`prioridad`),
    `activa`=VALUES(`activa`);

-- End of additional seed data