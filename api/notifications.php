<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/Database.php';

$response = ['success' => false, 'notifications' => []];

if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (int)$_SESSION['id_usuario'];

try {
    $db = new Database();
    $conn = $db->connect();

    // GET: listar notificaciones (unread first)
    $stmt = $conn->prepare('SELECT id_notificacion, mensaje, metadata, leido, creado_en FROM Notificaciones WHERE id_usuario_destino = :id_usuario ORDER BY leido ASC, creado_en DESC LIMIT 50');
    $stmt->execute([':id_usuario' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['notifications'] = $rows;
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode($response);
    exit;
}

?>
