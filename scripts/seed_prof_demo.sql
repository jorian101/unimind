-- Seeder SQL para demo del Dashboard de Profesor
-- Inserta un profesor, cursos, alumnos, tests (estres/ansiedad) y aplicaciones con puntuaciones
USE `db_tests_estres_ansiedad`;

-- Profesor
INSERT INTO `Usuarios` (`nombre`,`apellido`,`codigo_usuario`,`password`,`cargo`) VALUES
('Carlos','Gonzalez','DOC100','secret','Docente')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Obtener id del profesor (ejecutar en cliente que soporte variables si se desea)

-- Escuelas
INSERT INTO `Escuelas` (`nombre_escuela`,`telefono`) VALUES ('Instituto Demo','000-000')
ON DUPLICATE KEY UPDATE nombre_escuela=VALUES(nombre_escuela);

-- Cursos
INSERT INTO `Cursos` (`nombre_curso`,`id_escuela`,`id_profesor`) SELECT '2A - Psicología Escolar', e.id_escuela, u.id_usuario
FROM Escuelas e JOIN Usuarios u ON u.codigo_usuario='DOC100' LIMIT 1
ON DUPLICATE KEY UPDATE nombre_curso=VALUES(nombre_curso);

INSERT INTO `Cursos` (`nombre_curso`,`id_escuela`,`id_profesor`) SELECT '3B - Taller de Estudio', e.id_escuela, u.id_usuario
FROM Escuelas e JOIN Usuarios u ON u.codigo_usuario='DOC100' LIMIT 1
ON DUPLICATE KEY UPDATE nombre_curso=VALUES(nombre_curso);

-- Estudiantes demo
INSERT INTO `Usuarios` (`nombre`,`apellido`,`codigo_usuario`,`password`,`cargo`) VALUES
('María','Sánchez','STU101','pwd','Estudiante'),
('Juan','Pérez','STU102','pwd','Estudiante'),
('Ana','Lopez','STU103','pwd','Estudiante')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Inscripciones (Usuario_Curso)
INSERT IGNORE INTO Usuario_Curso (id_usuario,id_curso) SELECT u.id_usuario, c.id_curso FROM Usuarios u, Cursos c WHERE u.codigo_usuario='STU101' AND c.nombre_curso='2A - Psicología Escolar';
INSERT IGNORE INTO Usuario_Curso (id_usuario,id_curso) SELECT u.id_usuario, c.id_curso FROM Usuarios u, Cursos c WHERE u.codigo_usuario='STU102' AND c.nombre_curso='2A - Psicología Escolar';
INSERT IGNORE INTO Usuario_Curso (id_usuario,id_curso) SELECT u.id_usuario, c.id_curso FROM Usuarios u, Cursos c WHERE u.codigo_usuario='STU103' AND c.nombre_curso='3B - Taller de Estudio';

-- Tests de interés
INSERT INTO Tests (nombre, descripcion, num_items) VALUES
('Test de Estrés Escolar','Evalúa niveles de estrés en aula',10),
('Test de Ansiedad Generalizada','Evalúa ansiedad',7)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- Aplicaciones (puntuaciones) - varias fechas
-- Para 2A
INSERT INTO Aplicaciones (id_usuario,id_test,fecha_aplicacion,puntuacion_total,resultado_nivel)
SELECT uc.id_usuario, t.id_test, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 25, 'Bajo' FROM Usuario_Curso uc JOIN Tests t ON t.nombre='Test de Estrés Escolar' WHERE uc.id_curso = (SELECT id_curso FROM Cursos WHERE nombre_curso='2A - Psicología Escolar') LIMIT 1;

INSERT INTO Aplicaciones (id_usuario,id_test,fecha_aplicacion,puntuacion_total,resultado_nivel)
SELECT uc.id_usuario, t.id_test, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 45, 'Moderado' FROM Usuario_Curso uc JOIN Tests t ON t.nombre='Test de Estrés Escolar' WHERE uc.id_curso = (SELECT id_curso FROM Cursos WHERE nombre_curso='2A - Psicología Escolar') LIMIT 1;

INSERT INTO Aplicaciones (id_usuario,id_test,fecha_aplicacion,puntuacion_total,resultado_nivel)
SELECT uc.id_usuario, t.id_test, CURDATE(), 60, 'Moderado' FROM Usuario_Curso uc JOIN Tests t ON t.nombre='Test de Estrés Escolar' WHERE uc.id_curso = (SELECT id_curso FROM Cursos WHERE nombre_curso='2A - Psicología Escolar') LIMIT 1;

-- Para 3B (ansiedad)
INSERT INTO Aplicaciones (id_usuario,id_test,fecha_aplicacion,puntuacion_total,resultado_nivel)
SELECT uc.id_usuario, t.id_test, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 72, 'Alto' FROM Usuario_Curso uc JOIN Tests t ON t.nombre='Test de Ansiedad Generalizada' WHERE uc.id_curso = (SELECT id_curso FROM Cursos WHERE nombre_curso='3B - Taller de Estudio') LIMIT 1;

INSERT INTO Aplicaciones (id_usuario,id_test,fecha_aplicacion,puntuacion_total,resultado_nivel)
SELECT uc.id_usuario, t.id_test, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 68, 'Moderado' FROM Usuario_Curso uc JOIN Tests t ON t.nombre='Test de Ansiedad Generalizada' WHERE uc.id_curso = (SELECT id_curso FROM Cursos WHERE nombre_curso='3B - Taller de Estudio') LIMIT 1;

-- Nota: este script puede generar duplicados mínimos si se ejecuta varias veces; revisa las filas insertadas.
