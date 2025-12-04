<?php
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();
header('Content-Type: application/json');

// Devolver lista de cursos (GET) - opcionalmente filtrar por escuela
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['escuela_id']) && $_GET['escuela_id'] !== '') {
        $id_escuela = intval($_GET['escuela_id']);
        // La columna en la tabla es `nombre_curso`; devolverla como `nombre` para compatibilidad con el frontend
        $stmt = $conn->prepare('SELECT id_curso, nombre_curso AS nombre, id_escuela, id_profesor FROM Cursos WHERE id_escuela = ? ORDER BY nombre_curso');
        $stmt->execute([$id_escuela]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
        exit;
    }
    // devolver todos los cursos
    $stmt = $conn->prepare('SELECT id_curso, nombre_curso AS nombre, id_escuela, id_profesor FROM Cursos ORDER BY nombre_curso');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
    exit;
}

// Crear curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_curso'])) {
    $nombre = $_POST['nombre_curso'];
    $id_escuela = intval($_POST['id_escuela']);
    $id_profesor = intval($_POST['id_profesor']);
    $stmt = $conn->prepare('CALL sp_crear_curso(?, ?, ?)');
    $stmt->execute([$nombre, $id_escuela, $id_profesor]);
    echo json_encode(['Mensaje'=>'Curso creado correctamente']);
    exit;
}

echo json_encode(['error'=>'Acción no válida']);
