<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/Database.php';

$response = ['success' => false, 'message' => ''];

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    $response['message'] = 'No autenticado';
    echo json_encode($response);
    exit;
}

// Verificar que sea docente
if ($_SESSION['user_role'] !== 'Docente') {
    http_response_code(403);
    $response['message'] = 'Acceso denegado. Solo docentes pueden acceder a esta función.';
    echo json_encode($response);
    exit;
}

$profesorId = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = new Database();
    $conn = $db->connect();

    // ===========================
    // OBTENER SUGERENCIAS DEL PROFESOR
    // ===========================
    if ($method === 'GET' && $action === 'listar') {
        $stmt = $conn->prepare("
            SELECT 
                s.id_sugerencia,
                s.id_estudiante,
                s.id_test,
                s.profesores_ids,
                s.cursos_ids,
                s.fecha_sugerencia,
                s.fecha_ultima_sugerencia,
                s.estado,
                t.nombre AS nombre_test,
                t.descripcion AS descripcion_test,
                t.num_items,
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_estudiante,
                u.codigo_usuario,
                -- Extraer primer curso del JSON array
                (SELECT c.nombre_curso 
                 FROM Cursos c 
                 WHERE c.id_curso = JSON_UNQUOTE(JSON_EXTRACT(s.cursos_ids, '$[0]'))
                 LIMIT 1) AS nombre_curso,
                -- Verificar si el estudiante completó el test
                (SELECT COUNT(*) 
                 FROM Aplicaciones a 
                 WHERE a.id_usuario = s.id_estudiante 
                   AND a.id_test = s.id_test 
                   AND a.puntuacion_total IS NOT NULL) AS completado
            FROM Sugerencias s
            INNER JOIN Tests t ON s.id_test = t.id_test
            INNER JOIN Usuarios u ON s.id_estudiante = u.id_usuario
            WHERE JSON_CONTAINS(s.profesores_ids, JSON_ARRAY(?))
            ORDER BY s.fecha_ultima_sugerencia DESC
        ");
        
        $stmt->execute([$profesorId]);
        $sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos
        foreach ($sugerencias as &$sug) {
            $sug['profesores_ids'] = json_decode($sug['profesores_ids'], true);
            $sug['cursos_ids'] = json_decode($sug['cursos_ids'], true);
            $sug['completado'] = (int)$sug['completado'] > 0;
        }
        
        $response['success'] = true;
        $response['message'] = 'Sugerencias obtenidas correctamente';
        $response['data'] = $sugerencias;
        echo json_encode($response);
        exit;
    }

    // ===========================
    // ELIMINAR SUGERENCIA
    // ===========================
    if ($method === 'DELETE' || ($method === 'POST' && $action === 'eliminar')) {
        $payload = json_decode(file_get_contents('php://input'), true);
        $id_sugerencia = $payload['id_sugerencia'] ?? null;
        
        if (!$id_sugerencia) {
            http_response_code(400);
            $response['message'] = 'ID de sugerencia no proporcionado';
            echo json_encode($response);
            exit;
        }
        
        // Verificar que la sugerencia pertenece al profesor
        $stmt = $conn->prepare("
            SELECT id_sugerencia, profesores_ids, cursos_ids 
            FROM Sugerencias 
            WHERE id_sugerencia = ?
        ");
        $stmt->execute([$id_sugerencia]);
        $sugerencia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sugerencia) {
            http_response_code(404);
            $response['message'] = 'Sugerencia no encontrada';
            echo json_encode($response);
            exit;
        }
        
        $profesores_ids = json_decode($sugerencia['profesores_ids'], true);
        $cursos_ids = json_decode($sugerencia['cursos_ids'], true);
        
        // Verificar que el profesor actual esté en la lista
        if (!in_array($profesorId, $profesores_ids)) {
            http_response_code(403);
            $response['message'] = 'No autorizado para eliminar esta sugerencia';
            echo json_encode($response);
            exit;
        }
        
        // Si el profesor es el único que sugirió, eliminar completamente
        if (count($profesores_ids) === 1) {
            $stmt = $conn->prepare("DELETE FROM Sugerencias WHERE id_sugerencia = ?");
            $stmt->execute([$id_sugerencia]);
            
            $response['success'] = true;
            $response['message'] = 'Sugerencia eliminada completamente';
        } else {
            // Si hay múltiples profesores, solo remover este profesor de los arrays
            $nuevos_profesores = array_values(array_filter($profesores_ids, fn($id) => $id != $profesorId));
            
            // Encontrar los cursos del profesor y removerlos
            $stmt = $conn->prepare("SELECT id_curso FROM Cursos WHERE id_profesor = ?");
            $stmt->execute([$profesorId]);
            $cursos_profesor = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $nuevos_cursos = array_values(array_filter($cursos_ids, fn($id) => !in_array($id, $cursos_profesor)));
            
            $stmt = $conn->prepare("
                UPDATE Sugerencias 
                SET profesores_ids = ?, cursos_ids = ?
                WHERE id_sugerencia = ?
            ");
            $stmt->execute([
                json_encode($nuevos_profesores),
                json_encode($nuevos_cursos),
                $id_sugerencia
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Tu sugerencia fue removida. Otros profesores aún tienen este test sugerido.';
        }
        
        echo json_encode($response);
        exit;
    }

    // Acción no reconocida
    http_response_code(400);
    $response['message'] = 'Acción no válida';
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error de servidor: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}
?>
