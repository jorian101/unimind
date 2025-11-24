<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/Database.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Método no permitido';
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    $response['message'] = 'No autenticado';
    echo json_encode($response);
    exit;
}

$profesorId = (int) $_SESSION['user_id'];

$payload = json_decode(file_get_contents('php://input'), true);
$id_test = isset($payload['id_test']) ? (int)$payload['id_test'] : 0;
$id_curso = isset($payload['id_curso']) ? (int)$payload['id_curso'] : 0;

if (!$id_test || !$id_curso) {
    http_response_code(400);
    $response['message'] = 'Faltan parámetros';
    echo json_encode($response);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Validar que el profesor sea responsable del curso
    $stmt = $conn->prepare('SELECT COUNT(*) FROM Cursos WHERE id_curso = :id_curso AND id_profesor = :id_profesor');
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_profesor', $profesorId, PDO::PARAM_INT);
    $stmt->execute();
    $allowed = (int)$stmt->fetchColumn();
    if ($allowed === 0) {
        http_response_code(403);
        $response['message'] = 'No autorizado para sugerir en este curso';
        echo json_encode($response);
        exit;
    }

    // Obtener lista de alumnos del curso
    $stmt = $conn->prepare('SELECT id_usuario FROM Usuario_Curso WHERE id_curso = :id_curso');
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$students) {
        $response['message'] = 'No hay alumnos en el curso';
        echo json_encode($response);
        exit;
    }

    // Insertar una Aplicacion pendiente (sin puntuacion) para cada alumno
    $stmtIns = $conn->prepare('INSERT INTO Aplicaciones (id_usuario, id_test, fecha_aplicacion) VALUES (:id_usuario, :id_test, NOW())');
    $count = 0;
    foreach ($students as $stu) {
        $stmtIns->bindParam(':id_usuario', $stu, PDO::PARAM_INT);
        $stmtIns->bindParam(':id_test', $id_test, PDO::PARAM_INT);
        $stmtIns->execute();
        $count += $stmtIns->rowCount();
    }

    $response['success'] = true;
    $response['message'] = "Sugerencia enviada a $count alumnos";
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error de servidor: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

?>
