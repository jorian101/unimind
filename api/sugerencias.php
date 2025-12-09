<?php
/**
 * API Endpoint: Sugerencias de Tests (Profesores)
 * Refactorizado con APIFacade + Database Singleton
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../database/Database.php';

// Verificar autenticación y rol
$auth = APIFacade::checkAuth();
if (!$auth['authenticated']) {
    APIFacade::sendUnauthorized();
}

// Verificar que sea docente
if (!isset($auth['role']) || strtolower($auth['role']) !== 'docente') {
    APIFacade::sendError('Acceso denegado. Solo docentes pueden acceder a esta función.', 403);
}

$profesorId = $auth['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$conn = Database::getInstance()->getConnection();

// ===========================
// OBTENER SUGERENCIAS DEL PROFESOR
// ===========================
if ($method === 'GET' && $action === 'listar') {
    APIFacade::execute(function() use ($conn, $profesorId) {
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
                -- Extraer todos los cursos del JSON array y unir sus nombres separados por coma
                NULL AS nombre_curso,
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
        
        // Formatear datos y preparar mapeo de cursos
        $all_course_ids = [];
        foreach ($sugerencias as &$sug) {
            $sug['profesores_ids'] = json_decode($sug['profesores_ids'], true);
            $sug['cursos_ids'] = json_decode($sug['cursos_ids'], true);
            if (is_array($sug['cursos_ids'])) {
                foreach ($sug['cursos_ids'] as $cid) {
                    $all_course_ids[] = (int)$cid;
                }
            }
            $sug['completado'] = (int)$sug['completado'] > 0;
        }

        $course_names_map = [];
        $all_course_ids = array_values(array_unique(array_filter($all_course_ids)));
        if (count($all_course_ids) > 0) {
            // Build placeholders
            $placeholders = implode(',', array_fill(0, count($all_course_ids), '?'));
            $stmtCourses = $conn->prepare("SELECT id_curso, nombre_curso FROM Cursos WHERE id_curso IN ($placeholders)");
            $stmtCourses->execute($all_course_ids);
            $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);
            foreach ($courses as $c) {
                $course_names_map[(int)$c['id_curso']] = $c['nombre_curso'];
            }
        }

        // Attach curso names (comma-separated) to each suggestion
        foreach ($sugerencias as &$sug) {
            $names = [];
            if (is_array($sug['cursos_ids'])) {
                foreach ($sug['cursos_ids'] as $cid) {
                    $cidInt = (int)$cid;
                    if (isset($course_names_map[$cidInt])) {
                        $names[] = $course_names_map[$cidInt];
                    }
                }
            }
            $sug['nombre_curso'] = count($names) ? implode(', ', $names) : null;
        }
        
        APIFacade::sendSuccess([
            'sugerencias' => $sugerencias
        ], 'Sugerencias obtenidas correctamente');
    });
}

// ===========================
// ELIMINAR SUGERENCIA
// ===========================
if ($method === 'DELETE' || ($method === 'POST' && $action === 'eliminar')) {
    APIFacade::execute(function() use ($conn, $profesorId) {
        $payload = APIFacade::getJsonBody();
        
        if (!isset($payload['id_sugerencia'])) {
            APIFacade::sendError('ID de sugerencia no proporcionado', 400);
        }
        
        $id_sugerencia = $payload['id_sugerencia'];
        
        // Verificar que la sugerencia pertenece al profesor
        $stmt = $conn->prepare("
            SELECT id_sugerencia, profesores_ids, cursos_ids 
            FROM Sugerencias 
            WHERE id_sugerencia = ?
        ");
        $stmt->execute([$id_sugerencia]);
        $sugerencia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sugerencia) {
            APIFacade::sendNotFound('Sugerencia no encontrada');
        }
        
        $profesores_ids = json_decode($sugerencia['profesores_ids'], true);
        $cursos_ids = json_decode($sugerencia['cursos_ids'], true);
        
        // Verificar que el profesor actual esté en la lista
        if (!in_array($profesorId, $profesores_ids)) {
            APIFacade::sendError('No autorizado para eliminar esta sugerencia', 403);
        }
        
        // Si el profesor es el único que sugirió, eliminar completamente
        if (count($profesores_ids) === 1) {
            $stmt = $conn->prepare("DELETE FROM Sugerencias WHERE id_sugerencia = ?");
            $stmt->execute([$id_sugerencia]);
            
            APIFacade::sendSuccess([], 'Sugerencia eliminada completamente');
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
            
            APIFacade::sendSuccess([], 'Tu sugerencia fue removida. Otros profesores aún tienen este test sugerido.');
        }
    });
}

// Acción no reconocida
APIFacade::sendError('Acción no válida', 400);
