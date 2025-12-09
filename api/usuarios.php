<?php
/**
 * API Endpoint: Usuarios
 * Refactorizado con APIFacade + Database Singleton
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../database/Database.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Obtener usuario por ID
if ($method === 'GET' && isset($_GET['id'])) {
    APIFacade::execute(function() {
        $id = intval($_GET['id']);
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare('SELECT * FROM Usuarios WHERE id_usuario = ?');
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            APIFacade::sendSuccess($usuario);
        } else {
            APIFacade::sendNotFound('Usuario no encontrado');
        }
    });
}

// POST: Editar usuario
if ($method === 'POST' && isset($_POST['editar_id_usuario'])) {
    $params = APIFacade::validateParams([
        'editar_id_usuario', 'editar_nombre', 'editar_apellido', 
        'editar_codigo_usuario', 'editar_cargo'
    ], $_POST);
    
    APIFacade::execute(function() use ($params) {
        $conn = Database::getInstance()->getConnection();
        
        $id = intval($params['editar_id_usuario']);
        $fecha_nacimiento = $_POST['editar_fecha_nacimiento'] ?: null;
        $genero = $_POST['editar_genero'] ?: null;
        $password = $_POST['editar_password'] ?: null;

        // Si no se envía password, obtener el actual
        if (!$password) {
            $stmt = $conn->prepare('SELECT password FROM Usuarios WHERE id_usuario = ?');
            $stmt->execute([$id]);
            $password = $stmt->fetchColumn();
        }

        $stmt = $conn->prepare('CALL sp_actualizar_usuario(?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $id,
            $params['editar_nombre'],
            $params['editar_apellido'],
            $params['editar_codigo_usuario'],
            $params['editar_cargo'],
            $fecha_nacimiento,
            $genero,
            $password
        ]);
        
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        APIFacade::sendSuccess($msg);
    });
}


// POST: Crear usuario
if ($method === 'POST' && isset($_POST['crear_usuario'])) {
    $params = APIFacade::validateParams([
        'nuevo_nombre', 'nuevo_apellido', 'nuevo_codigo_usuario', 
        'nuevo_cargo', 'nuevo_password'
    ], $_POST);
    
    APIFacade::execute(function() use ($params) {
        $conn = Database::getInstance()->getConnection();
        
        $fecha_nacimiento = $_POST['nuevo_fecha_nacimiento'] ?: null;
        $genero = $_POST['nuevo_genero'] ?: null;
        $id_escuela = isset($_POST['nuevo_escuela']) && $_POST['nuevo_escuela'] !== '' 
            ? intval($_POST['nuevo_escuela']) : null;
        $id_curso = isset($_POST['nuevo_curso']) && $_POST['nuevo_curso'] !== '' 
            ? intval($_POST['nuevo_curso']) : null;

        $conn->beginTransaction();

        // Insert into Usuarios
        $insert = $conn->prepare(
            'INSERT INTO Usuarios (nombre, apellido, codigo_usuario, password, cargo, 
             fecha_nacimiento, genero, fecha_registro) 
             VALUES (?,?,?,?,?,?,?, NOW())'
        );
        $insert->execute([
            $params['nuevo_nombre'],
            $params['nuevo_apellido'],
            $params['nuevo_codigo_usuario'],
            $params['nuevo_password'],
            $params['nuevo_cargo'],
            $fecha_nacimiento,
            $genero
        ]);
        $nuevoId = intval($conn->lastInsertId());

        // If student and course provided, enroll
        if ($params['nuevo_cargo'] === 'Estudiante' && $id_curso) {
            $insUc = $conn->prepare('INSERT INTO Usuario_Curso (id_usuario, id_curso) VALUES (?, ?)');
            $insUc->execute([$nuevoId, $id_curso]);
        }

        // If teacher and course provided, assign professor to the course
        if ($params['nuevo_cargo'] === 'Docente' && $id_curso) {
            $upd = $conn->prepare('UPDATE Cursos SET id_profesor = ? WHERE id_curso = ?');
            $upd->execute([$nuevoId, $id_curso]);
        }

        // Optionally link escuela
        if ($id_escuela) {
            $insUe = $conn->prepare('INSERT IGNORE INTO Usuario_Escuela (id_usuario, id_escuela) VALUES (?, ?)');
            $insUe->execute([$nuevoId, $id_escuela]);
        }

        $conn->commit();
        APIFacade::sendSuccess([
            'Mensaje' => 'Usuario creado correctamente', 
            'Nuevo_ID_Usuario' => $nuevoId
        ]);
    });
}

// POST: Eliminar usuario
if ($method === 'POST' && isset($_POST['eliminar_id_usuario'])) {
    APIFacade::execute(function() {
        $id = intval($_POST['eliminar_id_usuario']);
        $conn = Database::getInstance()->getConnection();
        
        // Revisar si el usuario es Docente y tiene cursos asignados
        $s = $conn->prepare('SELECT cargo FROM Usuarios WHERE id_usuario = ?');
        $s->execute([$id]);
        $cargoUser = $s->fetchColumn();
        
        if ($cargoUser === 'Docente') {
            $q = $conn->prepare('SELECT COUNT(*) FROM Cursos WHERE id_profesor = ?');
            $q->execute([$id]);
            $cnt = intval($q->fetchColumn());
            
            if ($cnt > 0) {
                APIFacade::sendError(
                    'El docente tiene cursos asignados. Reasigna o elimina los cursos antes de eliminar al docente.',
                    400
                );
            }
        }

        // Safe to delete
        $stmt = $conn->prepare('CALL sp_eliminar_usuario(?)');
        $stmt->execute([$id]);
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        
        APIFacade::sendSuccess($msg);
    });
}

// Acción no válida
APIFacade::sendError('Acción no válida', 400);
