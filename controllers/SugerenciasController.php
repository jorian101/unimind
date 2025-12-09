<?php
/**
 * SugerenciasController
 * Controlador MVC para sugerencias de tests (profesores)
 */

require_once __DIR__ . '/../database/Database.php';

class SugerenciasController {
    
    /**
     * Verificar autenticación y rol de docente
     */
    private function requireProfesorAuth(): array {
        session_start();
        
        if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['user_role'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado', 'authenticated' => false]);
            exit;
        }

        $role = strtolower($_SESSION['user_role']);
        if ($role !== 'docente' && $role !== 'teacher') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso denegado. Solo docentes pueden acceder a esta función.']);
            exit;
        }

        return [
            'user_id' => $_SESSION['id_usuario'],
            'role' => $_SESSION['user_role']
        ];
    }

    // ========================================
    // MÉTODOS MVC (para vistas)
    // ========================================

    /**
     * Obtener sugerencias de un profesor
     */
    public function getSugerenciasProfesor(int $profesorId): array {
        $conn = Database::getInstance()->getConnection();
        
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
                NULL AS nombre_curso,
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

        // Obtener nombres de cursos
        $course_names_map = [];
        $all_course_ids = array_values(array_unique(array_filter($all_course_ids)));
        if (count($all_course_ids) > 0) {
            $placeholders = implode(',', array_fill(0, count($all_course_ids), '?'));
            $stmtCourses = $conn->prepare("SELECT id_curso, nombre_curso FROM Cursos WHERE id_curso IN ($placeholders)");
            $stmtCourses->execute($all_course_ids);
            $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);
            foreach ($courses as $c) {
                $course_names_map[(int)$c['id_curso']] = $c['nombre_curso'];
            }
        }

        // Adjuntar nombres de cursos
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
        
        return $sugerencias;
    }

    // ========================================
    // MÉTODOS API (para endpoints REST)
    // ========================================

    /**
     * API: GET listar sugerencias del profesor
     */
    public function handleApiListar(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $sugerencias = $this->getSugerenciasProfesor($profesorId);
            echo json_encode(['sugerencias' => $sugerencias, 'Mensaje' => 'Sugerencias obtenidas correctamente']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener sugerencias: ' . $e->getMessage()]);
        }
    }

    /**
     * API: DELETE/POST eliminar sugerencia
     */
    public function handleApiEliminar(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $payload = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($payload['id_sugerencia'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de sugerencia no proporcionado']);
                return;
            }
            
            $id_sugerencia = $payload['id_sugerencia'];
            $conn = Database::getInstance()->getConnection();
            
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
                echo json_encode(['error' => 'Sugerencia no encontrada']);
                return;
            }
            
            $profesores_ids = json_decode($sugerencia['profesores_ids'], true);
            $cursos_ids = json_decode($sugerencia['cursos_ids'], true);
            
            // Verificar que el profesor actual esté en la lista
            if (!in_array($profesorId, $profesores_ids)) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado para eliminar esta sugerencia']);
                return;
            }
            
            // Si el profesor es el único que sugirió, eliminar completamente
            if (count($profesores_ids) === 1) {
                $stmt = $conn->prepare("DELETE FROM Sugerencias WHERE id_sugerencia = ?");
                $stmt->execute([$id_sugerencia]);
                
                echo json_encode(['Mensaje' => 'Sugerencia eliminada completamente']);
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
                
                echo json_encode(['Mensaje' => 'Tu sugerencia fue removida. Otros profesores aún tienen este test sugerido.']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar sugerencia: ' . $e->getMessage()]);
        }
    }

    /**
     * Router principal para requests API
     */
    public function handleApiRequest(): void {
        $auth = $this->requireProfesorAuth();
        $profesorId = $auth['user_id'];
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        // GET: Listar sugerencias
        if ($method === 'GET' && $action === 'listar') {
            $this->handleApiListar($profesorId);
            return;
        }

        // DELETE o POST: Eliminar sugerencia
        if (($method === 'DELETE') || ($method === 'POST' && $action === 'eliminar')) {
            $this->handleApiEliminar($profesorId);
            return;
        }

        // Acción no válida
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acción no válida']);
    }
}
