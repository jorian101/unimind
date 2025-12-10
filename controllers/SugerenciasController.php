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
                CASE 
                    WHEN COUNT(DISTINCT uc.id_usuario) = COUNT(DISTINCT CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM Aplicaciones a 
                            WHERE a.id_usuario = uc.id_usuario 
                              AND a.id_test = sc.id_test 
                              AND a.puntuacion_total IS NOT NULL
                        ) THEN uc.id_usuario 
                    END) AND COUNT(DISTINCT uc.id_usuario) > 0 
                    THEN 'Completado' 
                    ELSE 'Activo' 
                END AS estado
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
     * API: GET detalles del progreso de un curso en un test
     */
    public function handleApiDetalles(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_GET['id_curso']) || !isset($_GET['id_test'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'ID de curso e ID de test son requeridos'
                ]);
                return;
            }
            
            $id_curso = (int)$_GET['id_curso'];
            $id_test = (int)$_GET['id_test'];
            $conn = Database::getInstance()->getConnection();
            
            // Verificar que la sugerencia pertenece al profesor
            $stmt = $conn->prepare("
                SELECT id_sugerencia_curso 
                FROM Sugerencias_Curso 
                WHERE id_curso = ? AND id_test = ? AND id_profesor = ?
            ");
            $stmt->execute([$id_curso, $id_test, $profesorId]);
            
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'No autorizado para ver estos detalles'
                ]);
                return;
            }
            
            // Obtener información general del curso y test
            $stmt = $conn->prepare("
                SELECT 
                    c.nombre_curso,
                    t.nombre AS nombre_test,
                    t.descripcion AS descripcion_test,
                    t.tipo_test,
                    t.num_items,
                    sc.fecha_sugerencia
                FROM Sugerencias_Curso sc
                INNER JOIN Cursos c ON sc.id_curso = c.id_curso
                INNER JOIN Tests t ON sc.id_test = t.id_test
                WHERE sc.id_curso = ? AND sc.id_test = ? AND sc.id_profesor = ?
            ");
            $stmt->execute([$id_curso, $id_test, $profesorId]);
            $info_general = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener lista de estudiantes con su progreso
            $stmt = $conn->prepare("
                SELECT 
                    u.id_usuario,
                    CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                    u.codigo_usuario,
                    u.genero,
                    a.id_aplicacion,
                    a.fecha_aplicacion,
                    a.puntuacion_total,
                    a.puntuacion_maxima,
                    a.porcentaje_score,
                    a.nivel_calculado,
                    a.resultado_nivel,
                    a.percentil,
                    a.z_score,
                    CASE WHEN a.id_aplicacion IS NOT NULL THEN TRUE ELSE FALSE END AS completado
                FROM Usuario_Curso uc
                INNER JOIN Usuarios u ON uc.id_usuario = u.id_usuario
                LEFT JOIN Aplicaciones a ON a.id_usuario = u.id_usuario AND a.id_test = ?
                WHERE uc.id_curso = ?
                ORDER BY completado DESC, u.apellido ASC, u.nombre ASC
            ");
            $stmt->execute([$id_test, $id_curso]);
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular métricas agregadas
            $total_estudiantes = count($estudiantes);
            $completados = array_filter($estudiantes, fn($e) => $e['completado']);
            $num_completados = count($completados);
            $num_pendientes = $total_estudiantes - $num_completados;
            
            // Estadísticas de puntuaciones (solo completados)
            $puntuaciones = array_map(fn($e) => (int)$e['puntuacion_total'], $completados);
            $promedio = $num_completados > 0 ? round(array_sum($puntuaciones) / $num_completados, 2) : null;
            $puntuacion_min = $num_completados > 0 ? min($puntuaciones) : null;
            $puntuacion_max = $num_completados > 0 ? max($puntuaciones) : null;
            
            // Distribución por nivel
            $niveles = ['normal' => 0, 'leve' => 0, 'moderado' => 0, 'alto' => 0, 'severo' => 0];
            foreach ($completados as $est) {
                $nivel = strtolower($est['nivel_calculado'] ?: $est['resultado_nivel'] ?: 'normal');
                if (isset($niveles[$nivel])) {
                    $niveles[$nivel]++;
                }
            }
            
            // Distribución por género (solo completados)
            $generos = ['Masculino' => 0, 'Femenino' => 0, 'Otro' => 0];
            foreach ($completados as $est) {
                $genero = $est['genero'] ?: 'Otro';
                if (isset($generos[$genero])) {
                    $generos[$genero]++;
                } else {
                    $generos['Otro']++;
                }
            }
            
            // Formatear estudiantes
            foreach ($estudiantes as &$est) {
                $est['id_usuario'] = (int)$est['id_usuario'];
                $est['completado'] = (bool)$est['completado'];
                $est['puntuacion_total'] = $est['puntuacion_total'] ? (int)$est['puntuacion_total'] : null;
                $est['porcentaje_score'] = $est['porcentaje_score'] ? (float)$est['porcentaje_score'] : null;
                $est['percentil'] = $est['percentil'] ? (float)$est['percentil'] : null;
                $est['z_score'] = $est['z_score'] ? (float)$est['z_score'] : null;
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'info_general' => $info_general,
                    'metricas' => [
                        'total_estudiantes' => $total_estudiantes,
                        'completados' => $num_completados,
                        'pendientes' => $num_pendientes,
                        'porcentaje_completado' => $total_estudiantes > 0 ? round(($num_completados / $total_estudiantes) * 100, 1) : 0,
                        'promedio_puntuacion' => $promedio,
                        'puntuacion_minima' => $puntuacion_min,
                        'puntuacion_maxima' => $puntuacion_max
                    ],
                    'distribucion_niveles' => $niveles,
                    'distribucion_genero' => $generos,
                    'estudiantes' => $estudiantes
                ],
                'message' => 'Detalles obtenidos correctamente'
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener detalles: ' . $e->getMessage()
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

        // POST: Sugerir test a curso (usado por el modal)
        if ($method === 'POST' && empty($action)) {
            header('Content-Type: application/json');
            $payload = json_decode(file_get_contents('php://input'), true);
            $curso_id = isset($payload['curso_id']) ? (int)$payload['curso_id'] : 0;
            $id_test = isset($payload['id_test']) ? (int)$payload['id_test'] : 0;
            if (!$curso_id || !$id_test) {
                http_response_code(400);
                echo json_encode(['success' => false, 'mensaje' => 'Curso y test son requeridos.']);
                return;
            }
            try {
                $conn = Database::getInstance()->getConnection();
                // Llama al procedimiento almacenado para sugerir test
                $stmt = $conn->prepare('CALL sp_sugerir_test(?, ?, ?)');
                $stmt->execute([$curso_id, $id_test, $profesorId]);
                echo json_encode(['success' => true, 'mensaje' => 'Sugerencia enviada correctamente.']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'mensaje' => 'Error al sugerir test: ' . $e->getMessage()]);
            }
            return;
        }

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

        // GET: Detalles del progreso
        if ($method === 'GET' && $action === 'detalles') {
            $this->handleApiDetalles($profesorId);
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
