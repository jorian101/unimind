
-- Procedure para crear curso
DELIMITER //
CREATE PROCEDURE sp_crear_curso(
    IN p_nombre_curso VARCHAR(150),
    IN p_id_escuela INT,
    IN p_id_profesor INT
)
BEGIN
    INSERT INTO Cursos (nombre_curso, id_escuela, id_profesor)
    VALUES (p_nombre_curso, p_id_escuela, p_id_profesor);
END //
DELIMITER ;

-- Procedure para crear escuela
DELIMITER //
CREATE PROCEDURE sp_crear_escuela(
    IN p_nombre_escuela VARCHAR(150),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO Escuelas (nombre_escuela, telefono)
    VALUES (p_nombre_escuela, p_telefono);
END //
DELIMITER ;

-- Procedure para crear usuario desde el dashboard
DELIMITER //
CREATE PROCEDURE sp_crear_usuario(
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_codigo_usuario VARCHAR(50),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_genero VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO Usuarios (nombre, apellido, codigo_usuario, cargo, fecha_nacimiento, genero, password, fecha_registro)
    VALUES (p_nombre, p_apellido, p_codigo_usuario, p_cargo, p_fecha_nacimiento, p_genero, p_password, NOW());
END //
DELIMITER ;
DELIMITER //

-- Actualizar usuario
CREATE PROCEDURE sp_actualizar_usuario(
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
    UPDATE Usuarios
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

-- Eliminar usuario
CREATE PROCEDURE sp_eliminar_usuario(
    IN p_id_usuario INT
)
BEGIN
    DELETE FROM Usuarios WHERE id_usuario = p_id_usuario;
    SELECT 'Usuario eliminado' AS Mensaje;
END //

DELIMITER ;
CREATE PROCEDURE sp_actualizar_usuario(
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
    UPDATE Usuarios
    SET nombre = p_nombre,
        apellido = p_apellido,
        codigo_usuario = p_codigo_usuario,
        cargo = p_cargo,
        fecha_nacimiento = p_fecha_nacimiento,
        genero = p_genero,
        password = p_password
    WHERE id_usuario = p_id_usuario;
    SELECT 'Usuario actualizado' AS Mensaje;
END;

CREATE PROCEDURE sp_eliminar_usuario(
    IN p_id_usuario INT
)
BEGIN
    DELETE FROM Usuarios WHERE id_usuario = p_id_usuario;
    SELECT 'Usuario eliminado' AS Mensaje;
END;

DELIMITER //
CREATE PROCEDURE sp_crear_usuario(
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_codigo_usuario VARCHAR(50),
    IN p_cargo VARCHAR(30),
    IN p_fecha_nacimiento DATE,
    IN p_genero VARCHAR(20),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO Usuarios (nombre, apellido, codigo_usuario, cargo, fecha_nacimiento, genero, password, fecha_registro)
    VALUES (p_nombre, p_apellido, p_codigo_usuario, p_cargo, p_fecha_nacimiento, p_genero, p_password, NOW());
END //
DELIMITER ;