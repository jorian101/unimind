-- Datos iniciales del sistema prueba
INSERT INTO `Escuelas` (`nombre_escuela`, `telefono`) VALUES
('Civil', '555-1001'),
('Educacion', '555-1002'),
('Artes', '555-1003'),
('Ambiental', '555-1004'),
('Medicina', '555-1005'),
('Biologia', '555-1006'),
('Veterinaria', '555-1007'),
('Alimentaria', '555-1008'),
('Sistemas', '555-1009'),
('Administracion', '555-1010'),
('Psicologia', '555-1011'),
('Obs', '555-1012'),
('Conta', '555-1013');

INSERT INTO `Usuarios` (`nombre`, `apellido`, `codigo_usuario`, `password`, `cargo`, `fecha_nacimiento`, `genero`) VALUES
('Andrea', 'Vargas', 'USR0001', 'password123', 'Estudiante', '2004-03-10', 'Femenino'),
('Javier', 'Ramos', 'USR0002', 'password123', 'Docente', '1985-07-22', 'Masculino'),
('Marta', 'Herrera', 'USR0003', 'password123', 'Administrador', '1979-01-15', 'Femenino'),
('Pedro', 'Guzmán', 'USR0004', 'password123', 'Administrador', '1990-11-28', 'Masculino'),
('Laura', 'Díaz', 'USR0005', 'password123', 'Estudiante', '2003-05-01', 'Femenino'),
('Ricardo', 'Flores', 'USR0006', 'password123', 'Docente', '1970-12-05', 'Masculino'),
('Elena', 'Soto', 'USR0007', 'password123', 'Estudiante', '2006-09-19', 'Femenino'),
('Miguel', 'López', 'USR0008', 'password123', 'Administrador', '1982-04-14', 'Masculino'),
('Fernanda', 'Castro', 'USR0009', 'password123', 'Estudiante', '2002-02-28', 'Femenino'),
('Daniel', 'Ruiz', 'USR0010', 'password123', 'Docente', '1965-08-30', 'Masculino'),
('Carla', 'Mendoza', 'USR0011', 'password123', 'Estudiante', '2005-10-12', 'Femenino'),
('Jorge', 'Paredes', 'USR0012', 'password123', 'Administrador', '1998-06-03', 'Masculino'),
('Silvia', 'Quispe', 'USR0013', 'password123', 'Administrador', '1977-03-07', 'Femenino'),
('Roberto', 'Torres', 'USR0014', 'password123', 'Estudiante', '2007-01-25', 'Masculino'),
('Valeria', 'Núñez', 'USR0015', 'password123', 'Docente', '1989-11-11', 'Femenino'),
('Alejandro', 'Vela', 'USR0016', 'password123', 'Estudiante', '2004-04-04', 'Masculino'),
('Cecilia', 'Baca', 'USR0017', 'password123', 'Administrador', '1993-02-09', 'Femenino'),
('Felipe', 'Cáceres', 'USR0018', 'password123', 'Administrador', '1980-10-21', 'Masculino'),
('Gabriela', 'Linares', 'USR0019', 'password123', 'Estudiante', '2006-12-31', 'Femenino'),
('Héctor', 'Salas', 'USR0020', 'password123', 'Docente', '1972-01-01', 'Masculino'),
('Irene', 'Zapata', 'USR0021', 'password123', 'Estudiante', '2003-08-17', 'Femenino'),
('Juan', 'Alonso', 'USR0022', 'password123', 'Administrador', '1995-05-18', 'Masculino'),
('Kelly', 'Bravo', 'USR0023', 'password123', 'Administrador', '1974-09-02', 'Femenino'),
('Omar', 'Molina', 'USR0024', 'password123', 'Estudiante', '2005-06-20', 'Masculino'),
('Patricia', 'Ríos', 'USR0025', 'password123', 'Docente', '1987-04-26', 'Femenino'),
('Quentin', 'Luna', 'USR0026', 'password123', 'Estudiante', '2002-11-03', 'Masculino'),
('Rosa', 'Vega', 'USR0027', 'password123', 'Administrador', '1991-07-13', 'Femenino'),
('Samuel', 'Yáñez', 'USR0028', 'password123', 'Administrador', '1976-03-29', 'Masculino'),
('Teresa', 'Zúñiga', 'USR0029', 'password123', 'Estudiante', '2004-01-08', 'Femenino'),
('Víctor', 'Acuña', 'USR0030', 'password123', 'Docente', '1968-05-16', 'Masculino');

INSERT INTO `Usuario_Escuela` (`id_usuario`, `id_escuela`) VALUES
(1, 1), (2, 2), (3, 3), (4, 4), (5, 5), (6, 6), (7, 7), (8, 8), (9, 9), (10, 10),
(11, 11), (12, 12), (13, 13), (14, 13), (15, 12), (16, 12), (17, 9), (18, 2), (19, 7), (20, 3),
(21, 8), (22, 7), (23, 5), (24, 4), (25, 10), (26, 2), (27, 1), (28, 4), (29, 9), (30, 6);

INSERT INTO `Tests` (`nombre`, `descripcion`, `num_items`) VALUES
('Test Ansiedad Generalizada 1', 'Inventario para medir la ansiedad general.', 15),
('Test Estrés Académico Corto', 'Mide el nivel de estrés asociado a tareas estudiantiles.', 8),
('Escala de Depresión Hamilton', 'Para evaluar la severidad de la depresión.', 10),
('Inventario de Burnout de Maslach', 'Mide el agotamiento emocional, despersonalización y logros personales.', 22),
('Escala de Resiliencia Connor-Davidson', 'Mide la capacidad de afrontar la adversidad.', 10),
('Cuestionario de Miedos Específicos', 'Evalúa la presencia de fobias específicas.', 12),
('Test de Calidad de Sueño PSQI', 'Evalúa la calidad del sueño a lo largo de un mes.', 7),
('Inventario de Ansiedad Social', 'Mide la ansiedad en situaciones sociales.', 15),
('Escala de Estrés Laboral ELS', 'Específico para el entorno de trabajo.', 10),
('Cuestionario de Autoestima Rosenberg', 'Mide la autoestima global.', 10),
('Test Ansiedad Generalizada 2', 'Versión alternativa para ansiedad.', 15),
('Test Estrés Académico Largo', 'Versión detallada para estrés estudiantil.', 15),
('Escala de Estrés Parental', 'Mide el estrés derivado de la crianza.', 10),
('Cuestionario de Afrontamiento al Estrés (COPE)', 'Evalúa estrategias de afrontamiento.', 60),
('Test de Clima Laboral', 'Evalúa la percepción del ambiente de trabajo.', 10),
('Inventario de Síntomas Somáticos', 'Mide síntomas físicos relacionados con el estrés.', 10),
('Escala de Satisfacción con la Vida', 'Evalúa el bienestar subjetivo.', 5),
('Cuestionario de Perfeccionismo', 'Mide tendencias perfeccionistas.', 10),
('Test de Preocupación Crónica', 'Evalúa la tendencia a la preocupación excesiva.', 10),
('Escala de Riesgo Suicida (BHS)', 'Evalúa el riesgo de autolesión.', 20),
('Test Ansiedad de Examen', 'Específico para situaciones de evaluación.', 10),
('Escala de Estrés Familiar', 'Mide el estrés en el contexto familiar.', 8),
('Inventario de Ira (STAXI)', 'Evalúa la experiencia y expresión de la ira.', 10),
('Cuestionario de Detección de Trauma (PCL)', 'Evalúa síntomas de estrés postraumático.', 20),
('Escala de Apego en Adultos', 'Evalúa patrones de apego.', 10),
('Test de Habilidades Sociales', 'Mide la competencia social.', 10),
('Inventario de Estrategias de Regulación Emocional', 'Evalúa cómo se regulan las emociones.', 10),
('Escala de Soledad UCLA', 'Mide la sensación de soledad.', 20),
('Cuestionario de Estilos de Vida Saludable', 'Evalúa hábitos de salud.', 15),
('Test de Motivación Intrínseca', 'Mide el grado de motivación interna.', 10);

INSERT INTO `Opciones_Respuesta` (`texto_opcion`, `valor_puntuacion`) VALUES
-- Escala 4 puntos (Likert)
('Totalmente en desacuerdo', 1), ('En desacuerdo', 2), ('De acuerdo', 3), ('Totalmente de acuerdo', 4),
-- Escala 5 puntos (Frecuencia)
('Nunca', 0), ('Casi nunca', 1), ('A veces', 2), ('A menudo', 3), ('Siempre', 4),
-- Escala 3 puntos (Intensidad)
('Nada en absoluto', 0), ('Un poco', 1), ('Bastante', 2), ('Mucho', 3),
-- Escala 2 puntos (Sí/No)
('No', 0), ('Sí', 1),
-- Escala 5 puntos (Burnout)
('0 - Nunca', 0), ('1 - Pocas veces al año', 1), ('2 - Una vez al mes', 2), ('3 - Pocas veces al mes', 3), ('4 - Una vez a la semana', 4),
-- Escala 5 puntos (Sueño)
('Muy bueno', 0), ('Bueno', 1), ('Regular', 2), ('Malo', 3), ('Muy malo', 4),
-- Escala 4 puntos (Sentimiento)
('No me siento así', 0), ('Raramente', 1), ('A veces', 2), ('Casi siempre', 3),
-- Escala 5 puntos (Satisfacción)
('Muy insatisfecho', 1), ('Insatisfecho', 2), ('Neutral', 3), ('Satisfecho', 4), ('Muy Satisfecho', 5);

INSERT INTO `Items` (`id_test`, `texto_item`, `subescala`, `orden`) VALUES
-- Test Ansiedad Generalizada 1 (id_test: 1)
(1, 'Me he sentido más nervioso/a o ansioso/a de lo habitual.', 'Ansiedad Emocional', 1),
(1, 'He tenido problemas para relajarme.', 'Ansiedad Tensión', 2),
(1, 'He estado preocupado/a por demasiadas cosas.', 'Preocupación', 3),
(1, 'He sentido que mi corazón latía muy rápido o con fuerza.', 'Síntomas Físicos', 4),
(1, 'He tenido dificultad para conciliar el sueño.', 'Ansiedad Tensión', 5),
(1, 'Me he asustado fácilmente.', 'Ansiedad Emocional', 6),
(1, 'He tenido dificultad para concentrarme.', 'Preocupación', 7),
(1, 'Me he sentido inquieto/a o incapaz de quedarme quieto/a.', 'Ansiedad Tensión', 8),
(1, 'Me he sentido mareado/a o con la cabeza ligera.', 'Síntomas Físicos', 9),
(1, 'He tenido la sensación de que algo terrible iba a pasar.', 'Preocupación', 10),
-- Test Estrés Académico Corto (id_test: 2)
(2, 'La cantidad de tareas me resulta abrumadora.', 'Carga Laboral', 1),
(2, 'Siento presión por obtener buenas calificaciones.', 'Presión Externa', 2),
(2, 'He tenido dificultad para organizar mi tiempo de estudio.', 'Autogestión', 3),
(2, 'Siento que el tiempo no me alcanza para estudiar todo.', 'Carga Laboral', 4),
(2, 'Me preocupo por el resultado de los exámenes.', 'Presión Externa', 5),
(2, 'He tenido dolores de cabeza o estómago por el estudio.', 'Síntomas Físicos', 6),
(2, 'He pospuesto tareas importantes.', 'Autogestión', 7),
(2, 'Me cuesta relajarme después de un día de clases/estudio.', 'Carga Laboral', 8),
-- Escala de Depresión Hamilton (id_test: 3)
(3, 'Tristeza (estado de ánimo).', 'Afectivo', 1),
(3, 'Sentimientos de culpa.', 'Afectivo', 2),
(3, 'Suicidio.', 'Afectivo', 3),
(3, 'Insomnio precoz (dificultad para conciliar el sueño).', 'Somático', 4),
(3, 'Insomnio medio (despertar en la noche).', 'Somático', 5),
(3, 'Insomnio tardío (despertar temprano).', 'Somático', 6),
(3, 'Trabajo y actividades.', 'Conductual', 7),
(3, 'Inhibición / Retraso.', 'Conductual', 8),
(3, 'Agitación.', 'Conductual', 9),
(3, 'Ansiedad psíquica (tensión, miedos).', 'Afectivo', 10),
(3, 'Ansiedad somática (síntomas físicos).', 'Somático', 11),
(3, 'Síntomas somáticos generales (peso, boca seca).', 'Somático', 12);

INSERT INTO `Aplicaciones` (`id_usuario`, `id_test`, `fecha_aplicacion`, `puntuacion_total`, `resultado_nivel`) VALUES
(1, 1, '2025-10-30 09:00:00', 35, 'Ansiedad Moderada'),
(2, 2, '2025-10-30 10:00:00', 18, 'Estrés Bajo'),
(3, 3, '2025-10-30 11:00:00', 25, 'Depresión Leve'),
(4, 1, '2025-10-30 12:00:00', 40, 'Ansiedad Alta'),
(5, 2, '2025-10-31 08:30:00', 12, 'Estrés Mínimo'),
(6, 3, '2025-10-31 09:30:00', 35, 'Depresión Moderada'),
(7, 1, '2025-10-31 10:30:00', 30, 'Ansiedad Media'),
(8, 2, '2025-10-31 11:30:00', 25, 'Estrés Medio'),
(9, 3, '2025-10-31 12:30:00', 45, 'Depresión Severa'),
(10, 1, '2025-11-01 14:00:00', 20, 'Ansiedad Baja'),
(11, 2, '2025-11-01 15:00:00', 30, 'Estrés Alto'),
(12, 3, '2025-11-01 16:00:00', 15, 'Depresión Mínima'),
(13, 1, '2025-11-02 09:00:00', 45, 'Ansiedad Severa'),
(14, 2, '2025-11-02 10:00:00', 16, 'Estrés Bajo'),
(15, 3, '2025-11-02 11:00:00', 28, 'Depresión Leve'),
(16, 1, '2025-11-03 13:00:00', 38, 'Ansiedad Moderada'),
(17, 2, '2025-11-03 14:00:00', 22, 'Estrés Medio'),
(18, 3, '2025-11-03 15:00:00', 30, 'Depresión Moderada'),
(19, 1, '2025-11-04 09:00:00', 25, 'Ansiedad Media'),
(20, 2, '2025-11-04 10:00:00', 10, 'Estrés Mínimo'),
(21, 3, '2025-11-04 11:00:00', 40, 'Depresión Alta'),
(22, 1, '2025-11-05 14:00:00', 32, 'Ansiedad Media'),
(23, 2, '2025-11-05 15:00:00', 28, 'Estrés Alto'),
(24, 3, '2025-11-05 16:00:00', 20, 'Depresión Leve'),
(25, 1, '2025-11-06 08:30:00', 42, 'Ansiedad Alta'),
(26, 2, '2025-11-06 09:30:00', 15, 'Estrés Bajo'),
(27, 3, '2025-11-06 10:30:00', 33, 'Depresión Moderada'),
(28, 1, '2025-11-07 11:00:00', 28, 'Ansiedad Media'),
(29, 2, '2025-11-07 12:00:00', 20, 'Estrés Medio'),
(30, 3, '2025-11-07 13:00:00', 48, 'Depresión Severa');

-- Respuestas por aplicación (separadas para evitar problemas de parsing)
INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(1, 1, 4, 4), (1, 2, 3, 3), (1, 3, 4, 4), (1, 4, 3, 3);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(2, 11, 2, 2), (2, 12, 1, 1);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(3, 13, 3, 3), (3, 14, 2, 2), (3, 15, 3, 3), (3, 16, 2, 2);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(4, 1, 4, 4), (4, 2, 4, 4), (4, 3, 4, 4), (4, 4, 4, 4);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(5, 11, 1, 1), (5, 12, 1, 1);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(6, 13, 4, 4), (6, 14, 3, 3), (6, 15, 4, 4), (6, 16, 3, 3);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(7, 5, 3, 3), (7, 6, 3, 3), (7, 7, 3, 3), (7, 8, 3, 3);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(8, 11, 3, 3), (8, 12, 3, 3);

INSERT INTO `Respuestas_Aplicacion` (`id_aplicacion`, `id_item`, `id_opcion_seleccionada`, `puntuacion_obtenida`) VALUES
(9, 13, 4, 4), (9, 14, 4, 4), (9, 15, 4, 4), (9, 16, 4, 4);

