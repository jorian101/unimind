<?php
/**
 * API: Tests con Restricciones
 * Endpoint para obtener los tests que un profesor NO puede sugerir a cada curso
 * debido a la restricción de 1 mes
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../database/Database.php';

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Verificar que sea profesor
$userRole = strtolower($_SESSION['user_role']);
if ($userRole !== 'docente' && $userRole !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo los profesores pueden acceder a este recurso']);
    exit;
}

$profesorId = (int) $_SESSION['user_id'];

try {
    $conn = Database::getInstance()->getConnection();

    // Obtener cursos del profesor
    $stmt = $conn->prepare('SELECT id_curso FROM Cursos WHERE id_profesor = :id_profesor');
    $stmt->execute([':id_profesor' => $profesorId]);
    $cursos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($cursos)) {
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
        exit;
    }

    // Para cada curso, obtener los tests que fueron sugeridos en el último mes
    $placeholders = implode(',', array_fill(0, count($cursos), '?'));
    
    $query = "
        SELECT 
            sc.id_curso,
            sc.id_test,
            sc.fecha_sugerencia,
            DATEDIFF(NOW(), sc.fecha_sugerencia) AS dias_desde_sugerencia,
            DATEDIFF(DATE_ADD(sc.fecha_sugerencia, INTERVAL 1 MONTH), NOW()) AS dias_restantes,
            DATE_ADD(sc.fecha_sugerencia, INTERVAL 1 MONTH) AS puede_sugerir_desde,
            t.nombre AS nombre_test,
            c.nombre_curso
        FROM Sugerencias_Curso sc
        INNER JOIN Tests t ON sc.id_test = t.id_test
        INNER JOIN Cursos c ON sc.id_curso = c.id_curso
        WHERE sc.id_curso IN ($placeholders)
          AND sc.fecha_sugerencia >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ORDER BY sc.id_curso, sc.fecha_sugerencia DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute($cursos);
    $restricciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar por curso para facilitar el acceso en el frontend
    $restriccionesPorCurso = [];
    foreach ($restricciones as $rest) {
        $id_curso = (int)$rest['id_curso'];
        if (!isset($restriccionesPorCurso[$id_curso])) {
            $restriccionesPorCurso[$id_curso] = [
                'nombre_curso' => $rest['nombre_curso'],
                'tests_restringidos' => []
            ];
        }
        
        $restriccionesPorCurso[$id_curso]['tests_restringidos'][] = [
            'id_test' => (int)$rest['id_test'],
            'nombre_test' => $rest['nombre_test'],
            'fecha_sugerencia' => $rest['fecha_sugerencia'],
            'dias_desde_sugerencia' => (int)$rest['dias_desde_sugerencia'],
            'dias_restantes' => (int)$rest['dias_restantes'],
            'puede_sugerir_desde' => $rest['puede_sugerir_desde']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $restriccionesPorCurso
    ]);

} catch (PDOException $e) {
    error_log("Error en tests-restricciones.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al obtener restricciones',
        'message' => $e->getMessage()
    ]);
}
?>
