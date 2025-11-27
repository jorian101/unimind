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
    $password = $_POST['editar_password'] ? password_hash($_POST['editar_password'], PASSWORD_DEFAULT) : null;

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
    $password = password_hash($_POST['nuevo_password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('CALL sp_crear_usuario(?,?,?,?,?,?,?)');
    $stmt->execute([$nombre, $apellido, $codigo, $cargo, $fecha_nacimiento, $genero, $password]);
    echo json_encode(['Mensaje'=>'Usuario creado correctamente']);
    exit;
}

// Eliminar usuario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id_usuario'])) {
    $id = intval($_POST['eliminar_id_usuario']);
    $stmt = $conn->prepare('CALL sp_eliminar_usuario(?)');
    $stmt->execute([$id]);
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($msg);
    exit;
}

echo json_encode(['error'=>'Acción no válida']);
