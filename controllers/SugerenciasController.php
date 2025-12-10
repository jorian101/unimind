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
     * Obtener sugerencias de un profesor (vista agregada por test+curso)
     */
    public function getSugerenciasProfesor(int $profesorId): array {
        $conn = Database::getInstance()->getConnection();
        
        // Query optimizada usando Sugerencias_Curso (nueva tabla que trackea sugerencias por curso)
        $stmt = $conn->prepare("
            SELECT 
                sc.id_curso,
                sc.id_test,
                sc.fecha_sugerencia,
                t.nombre AS nombre_test,
                t.descripcion AS descripcion_test,
                c.nombre_curso,
                COUNT(DISTINCT uc.id_usuario) AS total_estudiantes,
                COUNT(DISTINCT CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM Aplicaciones a 
                        WHERE a.id_usuario = uc.id_usuario 
                          AND a.id_test = sc.id_test 
                          AND a.puntuacion_total IS NOT NULL
                    ) THEN uc.id_usuario 
                END) AS estudiantes_completados,
                'Activo' AS estado
            FROM Sugerencias_Curso sc
            INNER JOIN Tests t ON sc.id_test = t.id_test
            INNER JOIN Cursos c ON sc.id_curso = c.id_curso
            INNER JOIN Usuario_Curso uc ON uc.id_curso = sc.id_curso
            WHERE sc.id_profesor = ?
            GROUP BY sc.id_curso, sc.id_test, sc.fecha_sugerencia, t.nombre, t.descripcion, c.nombre_curso
            ORDER BY sc.fecha_sugerencia DESC
        ");
        
        $stmt->execute([$profesorId]);
        $sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir a enteros y formato
        foreach ($sugerencias as &$sug) {
            $sug['id_curso'] = (int)$sug['id_curso'];
            $sug['id_test'] = (int)$sug['id_test'];
            $sug['total_estudiantes'] = (int)$sug['total_estudiantes'];
            $sug['estudiantes_completados'] = (int)$sug['estudiantes_completados'];
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
            echo json_encode([
                'success' => true,
                'data' => $sugerencias,
                'message' => 'Sugerencias obtenidas correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener sugerencias: ' . $e->getMessage()
            ]);
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
                echo json_encode([
                    'success' => false,
                    'error' => 'ID de sugerencia no proporcionado'
                ]);
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
                echo json_encode([
                    'success' => false,
                    'error' => 'Sugerencia no encontrada'
                ]);
                return;
            }
            
            $profesores_ids = json_decode($sugerencia['profesores_ids'], true);
            $cursos_ids = json_decode($sugerencia['cursos_ids'], true);
            
            // Verificar que el profesor actual esté en la lista
            if (!in_array($profesorId, $profesores_ids)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'No autorizado para eliminar esta sugerencia'
                ]);
                return;
            }
            
            // Si el profesor es el único que sugirió, eliminar completamente
            if (count($profesores_ids) === 1) {
                $stmt = $conn->prepare("DELETE FROM Sugerencias WHERE id_sugerencia = ?");
                $stmt->execute([$id_sugerencia]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Sugerencia eliminada completamente'
                ]);
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
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Tu sugerencia fue removida. Otros profesores aún tienen este test sugerido.'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al eliminar sugerencia: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: POST cancelar sugerencia por curso+test
     */
    public function handleApiCancelar(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $payload = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($payload['id_curso']) || !isset($payload['id_test'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID de curso e ID de test son requeridos'
                ]);
                return;
            }
            
            $id_curso = (int)$payload['id_curso'];
            $id_test = (int)$payload['id_test'];
            $conn = Database::getInstance()->getConnection();
            
            // Verificar que la sugerencia existe y pertenece al profesor
            $stmt = $conn->prepare("
                SELECT id_sugerencia_curso 
                FROM Sugerencias_Curso 
                WHERE id_curso = ? AND id_test = ? AND id_profesor = ?
            ");
            $stmt->execute([$id_curso, $id_test, $profesorId]);
            $sugerencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sugerencia) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Sugerencia no encontrada o no autorizada'
                ]);
                return;
            }
            
            // Iniciar transacción
            $conn->beginTransaction();
            
            try {
                // 1. Eliminar el registro de Sugerencias_Curso
                $stmt = $conn->prepare("
                    DELETE FROM Sugerencias_Curso 
                    WHERE id_curso = ? AND id_test = ? AND id_profesor = ?
                ");
                $stmt->execute([$id_curso, $id_test, $profesorId]);
                
                // 2. Obtener estudiantes del curso
                $stmt = $conn->prepare("SELECT id_usuario FROM Usuario_Curso WHERE id_curso = ?");
                $stmt->execute([$id_curso]);
                $estudiantes = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // 3. Para cada estudiante, actualizar o eliminar registro en Sugerencias
                foreach ($estudiantes as $id_estudiante) {
                    $stmt = $conn->prepare("
                        SELECT id_sugerencia, profesores_ids, cursos_ids 
                        FROM Sugerencias 
                        WHERE id_estudiante = ? AND id_test = ?
                    ");
                    $stmt->execute([$id_estudiante, $id_test]);
                    $sug = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($sug) {
                        $profesores_ids = json_decode($sug['profesores_ids'], true);
                        $cursos_ids = json_decode($sug['cursos_ids'], true);
                        
                        // Remover este profesor y curso
                        $nuevos_profesores = array_values(array_filter($profesores_ids, fn($id) => $id != $profesorId));
                        $nuevos_cursos = array_values(array_filter($cursos_ids, fn($id) => $id != $id_curso));
                        
                        if (empty($nuevos_profesores) || empty($nuevos_cursos)) {
                            // Si no quedan profesores/cursos, eliminar sugerencia
                            $stmt = $conn->prepare("DELETE FROM Sugerencias WHERE id_sugerencia = ?");
                            $stmt->execute([$sug['id_sugerencia']]);
                        } else {
                            // Actualizar arrays
                            $stmt = $conn->prepare("
                                UPDATE Sugerencias 
                                SET profesores_ids = ?, cursos_ids = ?
                                WHERE id_sugerencia = ?
                            ");
                            $stmt->execute([
                                json_encode($nuevos_profesores),
                                json_encode($nuevos_cursos),
                                $sug['id_sugerencia']
                            ]);
                        }
                    }
                }
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Sugerencia cancelada correctamente para todo el curso'
                ]);
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al cancelar sugerencia: ' . $e->getMessage()
            ]);
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

        // POST: Cancelar sugerencia por curso+test
        if ($method === 'POST' && $action === 'cancelar') {
            $this->handleApiCancelar($profesorId);
            return;
        }

        // Acción no válida
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Acción no válida'
        ]);
    }
}
