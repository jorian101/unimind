<?php
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();
header('Content-Type: application/json');

// Devolver lista de escuelas (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare('SELECT id_escuela, nombre_escuela, telefono FROM Escuelas ORDER BY nombre_escuela');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener escuelas']);
        exit;
    }
}

// Crear escuela
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_escuela'])) {
    $nombre = $_POST['nombre_escuela'];
    $telefono = $_POST['telefono'] ?: null;
    $stmt = $conn->prepare('CALL sp_crear_escuela(?, ?)');
    $stmt->execute([$nombre, $telefono]);
    echo json_encode(['Mensaje'=>'Escuela creada correctamente']);
    exit;
}

// Placeholder para editar/eliminar si se implementan más adelante
echo json_encode(['error'=>'Acción no válida']);
