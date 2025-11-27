<?php
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();
header('Content-Type: application/json');

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
// ...editar y eliminar se agregan igual...

echo json_encode(['error'=>'Acción no válida']);
