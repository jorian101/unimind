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
    $conn->beginTransaction();
    try {
        $stmtIns = $conn->prepare('SELECT id_aplicacion FROM Aplicaciones WHERE id_usuario = :id_usuario AND id_test = :id_test AND resultado_nivel IS NULL LIMIT 1');
        $stmtCreate = $conn->prepare('INSERT INTO Aplicaciones (id_usuario, id_test, fecha_aplicacion, sugerido_por, origen) VALUES (:id_usuario, :id_test, NOW(), :sugerido_por, :origen)');
        $stmtNotif = $conn->prepare('INSERT INTO Notificaciones (id_usuario_destino, mensaje, metadata, leido, creado_en) VALUES (:id_usuario_destino, :mensaje, :metadata, 0, NOW())');
        $count = 0;
        foreach ($students as $stu) {
            // evitar duplicados: si ya existe aplicación pendiente, saltar
            $stmtIns->execute([':id_usuario' => $stu, ':id_test' => $id_test]);
            $exists = $stmtIns->fetch(PDO::FETCH_ASSOC);
            if ($exists) continue;

            $stmtCreate->execute([':id_usuario' => $stu, ':id_test' => $id_test, ':sugerido_por' => $profesorId, ':origen' => 'profesor_sugerencia']);
            $count += $stmtCreate->rowCount();

            // Crear notificación in-app para el alumno
            $mensaje = 'Tu profesor te ha sugerido un test: ' . ($test_name ?: 'Test sugerido');
            $meta = json_encode(['id_test' => $id_test, 'id_curso' => $id_curso, 'sugerido_por' => $profesorId]);
            $stmtNotif->execute([':id_usuario_destino' => $stu, ':mensaje' => $mensaje, ':metadata' => $meta]);
        }
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollBack();
        throw $e;
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
