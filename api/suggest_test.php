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

// Verificar que sea profesor (puede ser 'docente' o 'Docente')
$userRole = strtolower($_SESSION['user_role']);
if ($userRole !== 'docente' && $userRole !== 'teacher') {
    http_response_code(403);
    $response['message'] = 'Solo los profesores pueden sugerir tests';
    echo json_encode($response);
    exit;
}

$profesorId = (int) $_SESSION['user_id'];

$payload = json_decode(file_get_contents('php://input'), true);
$id_test = isset($payload['id_test']) ? (int)$payload['id_test'] : 0;
$id_curso = isset($payload['id_curso']) ? (int)$payload['id_curso'] : 0;
// Optional metadata to create a test
$test_name = isset($payload['test_name']) ? trim($payload['test_name']) : '';
$test_description = isset($payload['test_description']) ? trim($payload['test_description']) : '';
$num_items = isset($payload['num_items']) ? (int)$payload['num_items'] : 0;

if (!$id_curso) {
    http_response_code(400);
    $response['message'] = 'Falta id_curso';
    echo json_encode($response);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

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

    // Si no se proporcionó id_test, tratar de crear uno si viene metadata válida
    if (!$id_test) {
        if ($test_name && $num_items > 0) {
            // Verificar si ya existe un Test con ese nombre y que esté activo
            $chk = $conn->prepare('SELECT id_test FROM Tests WHERE nombre = :nombre AND estado_test = "activo" LIMIT 1');
            $chk->execute([':nombre' => $test_name]);
            $found = $chk->fetch(PDO::FETCH_ASSOC);
            if ($found && isset($found['id_test'])) {
                $id_test = (int)$found['id_test'];
            } else {
                // Insertar nuevo test
                $insT = $conn->prepare('INSERT INTO Tests (nombre, descripcion, num_items) VALUES (:nombre, :descripcion, :num_items)');
                $insT->execute([':nombre' => $test_name, ':descripcion' => $test_description, ':num_items' => $num_items]);
                $id_test = (int)$conn->lastInsertId();
            }
        } else {
            http_response_code(400);
            $response['message'] = 'Faltan parámetros: id_test o datos para crear test';
            echo json_encode($response);
            exit;
        }
    }

    // Verificar que el test existe y está activo
    $stmt = $conn->prepare('SELECT COUNT(*) FROM Tests WHERE id_test = :id_test AND estado_test = "activo"');
    $stmt->execute([':id_test' => $id_test]);
    if ((int)$stmt->fetchColumn() === 0) {
        http_response_code(400);
        $response['message'] = 'El test no existe o no está activo';
        echo json_encode($response);
        exit;
    }

    // Usar el procedimiento almacenado para sugerir el test
    // El nuevo sp_sugerir_test ahora crea sugerencias individuales por estudiante
    $stmt = $conn->prepare('CALL sp_sugerir_test(:id_curso, :id_test, :id_profesor)');
    $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
    $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
    $stmt->bindParam(':id_profesor', $profesorId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Verificar si hay error de restricción temporal (1 mes)
    if (isset($result['Mensaje']) && $result['Mensaje'] === 'ERROR_RESTRICCION_TEMPORAL') {
        http_response_code(400);
        $dias_restantes = isset($result['dias_restantes']) ? (int)$result['dias_restantes'] : 0;
        $puede_sugerir_desde = isset($result['puede_sugerir_desde']) ? $result['puede_sugerir_desde'] : '';
        
        $response['success'] = false;
        $response['message'] = "No puedes sugerir el mismo test al mismo curso hasta que pase 1 mes desde la última sugerencia.";
        $response['error_code'] = 'RESTRICCION_TEMPORAL';
        $response['data'] = [
            'dias_restantes' => $dias_restantes,
            'puede_sugerir_desde' => $puede_sugerir_desde
        ];
        echo json_encode($response);
        exit;
    }

    $count = isset($result['estudiantes_afectados']) ? (int)$result['estudiantes_afectados'] : 0;

    $response['success'] = true;
    $response['message'] = "Test sugerido correctamente a $count estudiantes del curso.";
    $response['data'] = [
        'estudiantes_afectados' => $count
    ];
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error de servidor: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

?>
