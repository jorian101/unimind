<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/Database.php';

$response = ['success' => false, 'data' => []];

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'No autenticado';
    echo json_encode($response);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Obtener historial de sugerencias del profesor
    $stmt = $conn->prepare("
        SELECT 
            c.nombre_curso,
            t.nombre as nombre_test,
            COUNT(DISTINCT a.id_usuario) as cant_estudiantes,
            a.fecha_aplicacion
        FROM Aplicaciones a
        JOIN Tests t ON a.id_test = t.id_test
        JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
        JOIN Cursos c ON uc.id_curso = c.id_curso
        WHERE c.id_profesor = :id_profesor
        AND a.origen = 'profesor_sugerencia'
        GROUP BY c.nombre_curso, t.nombre, a.fecha_aplicacion
        ORDER BY a.fecha_aplicacion DESC
        LIMIT 50
    ");
    $stmt->bindParam(':id_profesor', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $historial;
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error de servidor';
    echo json_encode($response);
    exit;
}
?>
