<?php
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();
header('Content-Type: application/json');

// Obtener usuario por ID (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare('SELECT * FROM Usuarios WHERE id_usuario = ?');
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($usuario);
    exit;
}

// Editar usuario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id_usuario'])) {
    $id = intval($_POST['editar_id_usuario']);
    $nombre = $_POST['editar_nombre'];
    $apellido = $_POST['editar_apellido'];
    $codigo = $_POST['editar_codigo_usuario'];
    $cargo = $_POST['editar_cargo'];
    $fecha_nacimiento = $_POST['editar_fecha_nacimiento'] ?: null;
    $genero = $_POST['editar_genero'] ?: null;
    // No encriptar contraseñas - guardar tal cual
    $password = $_POST['editar_password'] ?: null;

    // Si no se envía password, no lo actualices
    if ($password) {
        $stmt = $conn->prepare('CALL sp_actualizar_usuario(?,?,?,?,?,?,?,?)');
        $stmt->execute([$id, $nombre, $apellido, $codigo, $cargo, $fecha_nacimiento, $genero, $password]);
    } else {
        // Obtener el password actual
        $stmt = $conn->prepare('SELECT password FROM Usuarios WHERE id_usuario = ?');
        $stmt->execute([$id]);
        $actual = $stmt->fetchColumn();
        $stmt = $conn->prepare('CALL sp_actualizar_usuario(?,?,?,?,?,?,?,?)');
        $stmt->execute([$id, $nombre, $apellido, $codigo, $cargo, $fecha_nacimiento, $genero, $actual]);
    }
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($msg);
    exit;
}


// Crear usuario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = $_POST['nuevo_nombre'];
    $apellido = $_POST['nuevo_apellido'];
    $codigo = $_POST['nuevo_codigo_usuario'];
    $cargo = $_POST['nuevo_cargo'];
    $fecha_nacimiento = $_POST['nuevo_fecha_nacimiento'] ?: null;
    $genero = $_POST['nuevo_genero'] ?: null;
    // No encriptar contraseñas - guardar tal cual
    $password = isset($_POST['nuevo_password']) ? $_POST['nuevo_password'] : '';

    // Optional fields sent from the modal
    $id_escuela = isset($_POST['nuevo_escuela']) && $_POST['nuevo_escuela'] !== '' ? intval($_POST['nuevo_escuela']) : null;
    $id_curso = isset($_POST['nuevo_curso']) && $_POST['nuevo_curso'] !== '' ? intval($_POST['nuevo_curso']) : null;

    try {
        // Use a transaction and direct INSERTs to avoid stored-procedure quirks
        $conn->beginTransaction();

        // Ensure password is provided (DB requires NOT NULL)
        if ($password === '') {
            // rollback and return error
            $conn->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Password requerido']);
            exit;
        }

        // Insert into Usuarios
        $insert = $conn->prepare('INSERT INTO Usuarios (nombre, apellido, codigo_usuario, password, cargo, fecha_nacimiento, genero, fecha_registro) VALUES (?,?,?,?,?,?,?, NOW())');
        $insert->execute([$nombre, $apellido, $codigo, $password, $cargo, $fecha_nacimiento, $genero]);
        $nuevoId = intval($conn->lastInsertId());

        // If student and course provided, enroll
        if ($cargo === 'Estudiante' && $id_curso) {
            $insUc = $conn->prepare('INSERT INTO Usuario_Curso (id_usuario, id_curso) VALUES (?, ?)' );
            $insUc->execute([$nuevoId, $id_curso]);
        }

        // If teacher and course provided, assign professor to the course
        if ($cargo === 'Docente' && $id_curso) {
            $upd = $conn->prepare('UPDATE Cursos SET id_profesor = ? WHERE id_curso = ?');
            $upd->execute([$nuevoId, $id_curso]);
        }

        // Optionally link escuela
        if ($id_escuela) {
            // try to create a link in Usuario_Escuela if table exists
            $insUe = $conn->prepare('INSERT IGNORE INTO Usuario_Escuela (id_usuario, id_escuela) VALUES (?, ?)');
            $insUe->execute([$nuevoId, $id_escuela]);
        }

        $conn->commit();

        echo json_encode(['Mensaje' => 'Usuario creado correctamente', 'Nuevo_ID_Usuario' => $nuevoId]);
        exit;
    } catch (PDOException $e) {
        // Return the DB error message for debugging (can be sanitized in production)
        http_response_code(500);
        if ($conn && $conn->inTransaction()) $conn->rollBack();
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Eliminar usuario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id_usuario'])) {
    $id = intval($_POST['eliminar_id_usuario']);
    try {
        // revisar si el usuario es Docente y tiene cursos asignados
        $s = $conn->prepare('SELECT cargo FROM Usuarios WHERE id_usuario = ?');
        $s->execute([$id]);
        $cargoUser = $s->fetchColumn();
        if ($cargoUser === 'Docente') {
            $q = $conn->prepare('SELECT COUNT(*) FROM Cursos WHERE id_profesor = ?');
            $q->execute([$id]);
            $cnt = intval($q->fetchColumn());
            if ($cnt > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'El docente tiene cursos asignados. Reasigna o elimina los cursos antes de eliminar al docente.']);
                exit;
            }
        }

        // safe to delete
        $stmt = $conn->prepare('CALL sp_eliminar_usuario(?)');
        $stmt->execute([$id]);
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($msg);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB error', 'message' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['error'=>'Acción no válida']);
