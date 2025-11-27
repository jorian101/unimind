<?php
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();
header('Content-Type: application/json');

// Crear escuela
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_escuela'])) {
    $nombre = $_POST['nombre_escuela'];
    $telefono = $_POST['telefono'] ?: null;
    $stmt = $conn->prepare('CALL sp_crear_escuela(?, ?)');
    $stmt->execute([$nombre, $telefono]);
    echo json_encode(['Mensaje'=>'Escuela creada correctamente']);
    exit;
}
// ...editar y eliminar se agregan igual...

echo json_encode(['error'=>'Acción no válida']);
