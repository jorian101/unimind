<?php
/**
 * ProfesorController
 * Controlador MVC para funcionalidades de profesor (métricas, historial)
 */

require_once __DIR__ . '/../database/Database.php';

class ProfesorController {
    
    /**
     * Verificar autenticación y rol de docente
     */
    private function requireProfesorAuth(): int {
        session_start();
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        $role = strtolower($_SESSION['user_role']);
        if ($role !== 'docente' && $role !== 'teacher') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
            exit;
        }

        return (int) $_SESSION['user_id'];
    }

    // ========================================
    // API: MÉTRICAS DE PROFESOR
    // ========================================

    /**
     * API: GET top courses con mayores niveles
     * Query params: ?top_courses=1
     * Usa SP: sp_obtener_promedios_cursos_profesor
     */
    public function handleApiTopCourses(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            
            // Usar el SP optimizado que ya calcula todo
            $stmt = $conn->prepare('CALL sp_obtener_promedios_cursos_profesor(?)');
            $stmt->execute([$profesorId]);
            $top = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            // El SP ya devuelve los datos ordenados por mayor promedio
            // Agregar campos adicionales para la UI
            foreach ($top as &$curso) {
                $curso['promedio_estres'] = $curso['promedio_estres'] !== null ? (float)$curso['promedio_estres'] : null;
                $curso['promedio_ansiedad'] = $curso['promedio_ansiedad'] !== null ? (float)$curso['promedio_ansiedad'] : null;
                $curso['total_estudiantes'] = (int)($curso['total_estudiantes'] ?? 0);
                $curso['estudiantes_riesgo'] = (int)($curso['estudiantes_riesgo'] ?? 0);
            }
            
            echo json_encode(['success' => true, 'top_courses' => $top]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * API: GET métricas completas de profesor
     * Utiliza stored procedures donde sea posible y mantiene queries optimizadas
     */
    public function handleApiMetrics(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            $response = ['success' => false, 'message' => '', 'courses' => []];
            
            // Obtener cursos del profesor usando stored procedure
            $stmt = $conn->prepare('CALL sp_obtener_cursos_por_profesor(?)');
            $stmt->execute([$profesorId]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            foreach ($cursos as $curso) {
                $id_curso = (int) $curso['id_curso'];

                // Total estudiantes
                $stmt = $conn->prepare('SELECT COUNT(*) as total_students FROM Usuario_Curso WHERE id_curso = ?');
                $stmt->execute([$id_curso]);
                $totalStudents = (int) $stmt->fetchColumn();

                // Promedio de tests de estrés/ansiedad
                $sqlAvg = "SELECT AVG(a.puntuacion_total) as avg_score 
                          FROM Aplicaciones a
                          JOIN Tests t ON a.id_test = t.id_test
                          JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                          WHERE uc.id_curso = ? 
                          AND t.tipo_test IN ('estres','ansiedad') 
                          AND t.estado_test = 'activo'";
                $stmt = $conn->prepare($sqlAvg);
                $stmt->execute([$id_curso]);
                $avgScore = $stmt->fetchColumn();
                $avgScore = $avgScore !== null ? round((float)$avgScore, 1) : null;

                // Distribución por nivel usando stored procedure
                $stmt = $conn->prepare('CALL sp_obtener_distribucion_riesgo_por_curso(?)');
                $stmt->execute([$id_curso]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                $distribution = ['Bajo' => 0, 'Moderado' => 0, 'Alto' => 0];
                foreach ($rows as $r) {
                    $nivel = strtolower($r['nivel_riesgo'] ?? '');
                    $cnt = (int) $r['conteo'];
                    
                    // Mapear todos los posibles valores a las 3 categorías
                    if (stripos($nivel, 'bajo') !== false || 
                        stripos($nivel, 'mínimo') !== false || 
                        stripos($nivel, 'normal') !== false ||
                        $nivel === 'leve') {
                        $distribution['Bajo'] += $cnt;
                    } elseif (stripos($nivel, 'moderado') !== false || 
                              stripos($nivel, 'medio') !== false) {
                        $distribution['Moderado'] += $cnt;
                    } elseif (stripos($nivel, 'alto') !== false || 
                              stripos($nivel, 'severo') !== false) {
                        $distribution['Alto'] += $cnt;
                    }
                }

                // Series temporal usando stored procedure
                $stmt = $conn->prepare('CALL sp_obtener_evolucion_temporal_por_curso(?)');
                $stmt->execute([$id_curso]);
                $seriesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                
                $series = [];
                foreach ($seriesRows as $sr) {
                    $series[] = [
                        'date' => $sr['etiqueta_temporal'], 
                        'value' => (float) round($sr['promedio_puntuacion'], 1)
                    ];
                }

                $response['courses'][] = [
                    'id_curso' => $id_curso,
                    'nombre_curso' => $curso['nombre_curso'],
                    'total_students' => $totalStudents,
                    'avg_score' => $avgScore,
                    'distribution' => $distribution,
                    'series' => $series
                ];
            }

            // Métricas por facultad/escuela usando SP optimizado
            $stmt = $conn->prepare('CALL sp_obtener_metricas_facultades_profesor(?)');
            $stmt->execute([$profesorId]);
            $facultades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $response['faculties'] = [];
            foreach ($facultades as $fac) {
                $response['faculties'][] = [
                    'id_escuela' => (int)$fac['id_escuela'],
                    'nombre_escuela' => $fac['nombre_escuela'],
                    'avg_estres' => $fac['avg_estres'] !== null ? (float)$fac['avg_estres'] : null,
                    'count_estres' => (int)($fac['count_estres'] ?? 0),
                    'avg_ansiedad' => $fac['avg_ansiedad'] !== null ? (float)$fac['avg_ansiedad'] : null,
                    'count_ansiedad' => (int)($fac['count_ansiedad'] ?? 0),
                ];
            }

            $response['success'] = true;
            echo json_encode($response);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Router para API de métricas
     */
    public function handleApiMetricsRequest(): void {
        $profesorId = $this->requireProfesorAuth();
        
        // Si pide top courses
        if (isset($_GET['top_courses'])) {
            $this->handleApiTopCourses($profesorId);
            return;
        }
        
        // Métricas completas por defecto
        $this->handleApiMetrics($profesorId);
    }

    // ========================================
    // API: HISTORIAL DE SUGERENCIAS
    // ========================================

    /**
     * API: GET historial de sugerencias del profesor
     * Usa SP: sp_obtener_historial_sugerencias_profesor
     */
    public function handleApiHistorial(): void {
        $profesorId = $this->requireProfesorAuth();
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
            
            // Usar SP optimizado que incluye métricas de completitud
            $stmt = $conn->prepare('CALL sp_obtener_historial_sugerencias_profesor(?, ?)');
            $stmt->execute([$profesorId, $limite]);
            $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Calcular tasa de completitud para cada registro
            foreach ($historial as &$item) {
                $sugeridos = (int)($item['estudiantes_sugeridos'] ?? 0);
                $completaron = (int)($item['estudiantes_completaron'] ?? 0);
                $item['tasa_completitud'] = $sugeridos > 0 ? round(($completaron / $sugeridos) * 100, 1) : 0;
            }

            echo json_encode(['success' => true, 'data' => $historial]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
    }
}
