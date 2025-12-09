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
     */
    public function handleApiTopCourses(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            
            // Obtener todos los cursos del profesor
            $stmt = $conn->prepare('SELECT id_curso, nombre_curso FROM Cursos WHERE id_profesor = ?');
            $stmt->execute([$profesorId]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $top = [];
            foreach ($cursos as $curso) {
                $id_curso = (int) $curso['id_curso'];
                
                // Promedio estrés
                $sqlEstres = "SELECT AVG(a.puntuacion_total) as avg_score FROM Aplicaciones a
                    JOIN Tests t ON a.id_test = t.id_test
                    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                    WHERE uc.id_curso = ? AND LOWER(t.nombre) LIKE '%estres%'";
                $stmt2 = $conn->prepare($sqlEstres);
                $stmt2->execute([$id_curso]);
                $avgEstres = $stmt2->fetchColumn();
                $avgEstres = $avgEstres !== null ? round((float)$avgEstres, 1) : null;
                
                // Promedio ansiedad
                $sqlAns = "SELECT AVG(a.puntuacion_total) as avg_score FROM Aplicaciones a
                    JOIN Tests t ON a.id_test = t.id_test
                    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                    WHERE uc.id_curso = ? AND LOWER(t.nombre) LIKE '%ansiedad%'";
                $stmt2 = $conn->prepare($sqlAns);
                $stmt2->execute([$id_curso]);
                $avgAns = $stmt2->fetchColumn();
                $avgAns = $avgAns !== null ? round((float)$avgAns, 1) : null;
                
                $top[] = [
                    'id_curso' => $id_curso,
                    'nombre_curso' => $curso['nombre_curso'],
                    'promedio_estres' => $avgEstres,
                    'promedio_ansiedad' => $avgAns
                ];
            }
            
            // Ordenar por mayor promedio
            usort($top, function($a, $b) {
                $maxA = max($a['promedio_estres'] ?? 0, $a['promedio_ansiedad'] ?? 0);
                $maxB = max($b['promedio_estres'] ?? 0, $b['promedio_ansiedad'] ?? 0);
                return $maxB <=> $maxA;
            });
            
            echo json_encode(['success' => true, 'top_courses' => $top]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * API: GET métricas completas de profesor
     */
    public function handleApiMetrics(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            $response = ['success' => false, 'message' => '', 'courses' => []];
            
            // Obtener cursos del profesor
            $stmt = $conn->prepare('SELECT id_curso, nombre_curso FROM Cursos WHERE id_profesor = ?');
            $stmt->execute([$profesorId]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cursos as $curso) {
                $id_curso = (int) $curso['id_curso'];

                // Total estudiantes
                $stmt = $conn->prepare('SELECT COUNT(*) as total_students FROM Usuario_Curso WHERE id_curso = ?');
                $stmt->execute([$id_curso]);
                $totalStudents = (int) $stmt->fetchColumn();

                // Promedio de tests de estrés/ansiedad
                $sqlAvg = "SELECT AVG(a.puntuacion_total) as avg_score FROM Aplicaciones a
                       JOIN Tests t ON a.id_test = t.id_test
                       JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                       WHERE uc.id_curso = ? AND t.tipo_test IN ('estres','ansiedad') AND t.estado_test = 'activo'";
                $stmt = $conn->prepare($sqlAvg);
                $stmt->execute([$id_curso]);
                $avgScore = $stmt->fetchColumn();
                $avgScore = $avgScore !== null ? round((float)$avgScore, 1) : null;

                // Distribución por nivel
                $sqlDist = "SELECT a.resultado_nivel as nivel, COUNT(*) as cnt FROM Aplicaciones a
                        JOIN Tests t ON a.id_test = t.id_test
                        JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                        WHERE uc.id_curso = ? AND t.tipo_test IN ('estres','ansiedad') AND t.estado_test = 'activo'
                        GROUP BY a.resultado_nivel";
                $stmt = $conn->prepare($sqlDist);
                $stmt->execute([$id_curso]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $distribution = ['Bajo' => 0, 'Moderado' => 0, 'Alto' => 0];
                foreach ($rows as $r) {
                    $nivel = $r['nivel'] ?? '';
                    $cnt = (int) $r['cnt'];
                    if (stripos($nivel, 'bajo') !== false) $distribution['Bajo'] += $cnt;
                    elseif (stripos($nivel, 'moderado') !== false) $distribution['Moderado'] += $cnt;
                    elseif (stripos($nivel, 'alto') !== false) $distribution['Alto'] += $cnt;
                }

                // Si no hay distribución por texto, clasificar por puntuaciones
                $totalDist = array_sum($distribution);
                if ($totalDist === 0 && $totalStudents > 0) {
                    $sqlScoreDist = "SELECT a.puntuacion_total as score FROM Aplicaciones a
                                      JOIN Tests t ON a.id_test = t.id_test
                                      JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                                      WHERE uc.id_curso = ? AND t.tipo_test IN ('estres','ansiedad') AND t.estado_test = 'activo'";
                    $stmt = $conn->prepare($sqlScoreDist);
                    $stmt->execute([$id_curso]);
                    $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($scores as $s) {
                        $s = (float)$s;
                        if ($s < 30) $distribution['Bajo']++;
                        elseif ($s < 70) $distribution['Moderado']++;
                        else $distribution['Alto']++;
                    }
                }

                // Series temporal (últimos 12 días)
                $sqlSeries = "SELECT DATE(a.fecha_aplicacion) as fecha, AVG(a.puntuacion_total) as avg_score FROM Aplicaciones a
                          JOIN Tests t ON a.id_test = t.id_test
                          JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                          WHERE uc.id_curso = ? AND t.tipo_test IN ('estres','ansiedad') AND t.estado_test = 'activo'
                          GROUP BY DATE(a.fecha_aplicacion)
                          ORDER BY DATE(a.fecha_aplicacion) ASC
                          LIMIT 12";
                $stmt = $conn->prepare($sqlSeries);
                $stmt->execute([$id_curso]);
                $seriesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $series = [];
                foreach ($seriesRows as $sr) {
                    $series[] = ['date' => $sr['fecha'], 'value' => (float) round($sr['avg_score'],1)];
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

            // Métricas por facultad/escuela
            $stmt = $conn->prepare('SELECT DISTINCT e.id_escuela, e.nombre_escuela
                                    FROM Escuelas e
                                    JOIN Cursos c ON c.id_escuela = e.id_escuela
                                    WHERE c.id_profesor = ?');
            $stmt->execute([$profesorId]);
            $facultades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['faculties'] = [];
            foreach ($facultades as $fac) {
                $id_esc = (int) $fac['id_escuela'];

                // Promedio estrés en facultad
                $sqlFacEst = "SELECT AVG(a.puntuacion_total) as avg_score, COUNT(*) as cnt FROM Aplicaciones a
                       JOIN Tests t ON a.id_test = t.id_test
                       JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                       JOIN Cursos c ON uc.id_curso = c.id_curso
                       WHERE c.id_escuela = ? AND t.tipo_test = 'estres' AND t.estado_test = 'activo'";
                $stmt = $conn->prepare($sqlFacEst);
                $stmt->execute([$id_esc]);
                $facEst = $stmt->fetch(PDO::FETCH_ASSOC);

                // Promedio ansiedad en facultad
                $sqlFacAns = "SELECT AVG(a.puntuacion_total) as avg_score, COUNT(*) as cnt FROM Aplicaciones a
                       JOIN Tests t ON a.id_test = t.id_test
                       JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                       JOIN Cursos c ON uc.id_curso = c.id_curso
                       WHERE c.id_escuela = ? AND t.tipo_test = 'ansiedad' AND t.estado_test = 'activo'";
                $stmt = $conn->prepare($sqlFacAns);
                $stmt->execute([$id_esc]);
                $facAns = $stmt->fetch(PDO::FETCH_ASSOC);

                $response['faculties'][] = [
                    'id_escuela' => $id_esc,
                    'nombre_escuela' => $fac['nombre_escuela'],
                    'avg_estres' => $facEst['avg_score'] !== null ? round((float)$facEst['avg_score'],1) : null,
                    'count_estres' => (int) ($facEst['cnt'] ?? 0),
                    'avg_ansiedad' => $facAns['avg_score'] !== null ? round((float)$facAns['avg_score'],1) : null,
                    'count_ansiedad' => (int) ($facAns['cnt'] ?? 0),
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
     */
    public function handleApiHistorial(): void {
        $profesorId = $this->requireProfesorAuth();
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            
            $stmt = $conn->prepare("
                SELECT 
                    c.nombre_curso,
                    t.nombre as nombre_test,
                    COUNT(DISTINCT a.id_usuario) as cant_estudiantes,
                    a.fecha_aplicacion
                FROM Aplicaciones a
                JOIN Tests t ON a.id_test = t.id_test
                JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                JOIN Cursos c ON uc.id_curso = c.id_curso
                WHERE c.id_profesor = ?
                AND a.origen = 'profesor_sugerencia'
                GROUP BY c.nombre_curso, t.nombre, a.fecha_aplicacion
                ORDER BY a.fecha_aplicacion DESC
                LIMIT 50
            ");
            $stmt->execute([$profesorId]);
            $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $historial]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor']);
        }
    }
}
