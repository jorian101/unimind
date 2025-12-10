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
        
        // Si pide lista de cursos
        if (isset($_GET['action']) && $_GET['action'] === 'cursos') {
            $this->handleApiCursosList($profesorId);
            return;
        }
        
        // Si pide detalle de un curso
        if (isset($_GET['action']) && $_GET['action'] === 'detalle_curso' && isset($_GET['id_curso'])) {
            $this->handleApiCursoDetalle($profesorId, (int)$_GET['id_curso']);
            return;
        }
        
        // Métricas completas por defecto
        $this->handleApiMetrics($profesorId);
    }

    /**
     * API: GET lista de cursos del profesor con métricas básicas
     */
    private function handleApiCursosList(int $profesorId): void {
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            
            // Primero obtener los cursos del profesor
            $sql = "SELECT 
                        c.id_curso,
                        c.nombre_curso,
                        e.nombre_escuela
                    FROM Cursos c
                    INNER JOIN Escuelas e ON c.id_escuela = e.id_escuela
                    WHERE c.id_profesor = ?
                    ORDER BY c.nombre_curso";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$profesorId]);
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada curso, calcular métricas
            foreach ($cursos as &$curso) {
                $idCurso = (int)$curso['id_curso'];
                
                // Total estudiantes
                $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuario_Curso WHERE id_curso = ?");
                $stmt->execute([$idCurso]);
                $curso['total_estudiantes'] = (int)$stmt->fetchColumn();
                
                // Tests completados (aplicaciones de tests de estrés y ansiedad)
                $stmt = $conn->prepare("
                    SELECT COUNT(DISTINCT a.id_aplicacion)
                    FROM Aplicaciones a
                    INNER JOIN Tests t ON a.id_test = t.id_test
                    INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                    WHERE uc.id_curso = ?
                    AND t.tipo_test IN ('estres', 'ansiedad')
                ");
                $stmt->execute([$idCurso]);
                $curso['tests_completados'] = (int)$stmt->fetchColumn();
                
                // Tests activos sugeridos (usando Sugerencias_Curso)
                $stmt = $conn->prepare("
                    SELECT COUNT(DISTINCT sc.id_test)
                    FROM Sugerencias_Curso sc
                    WHERE sc.id_curso = ? AND sc.id_profesor = ?
                ");
                $stmt->execute([$idCurso, $profesorId]);
                $curso['tests_activos'] = (int)$stmt->fetchColumn();
                
                // Nivel promedio de estrés
                $stmt = $conn->prepare("
                    SELECT AVG(a.puntuacion_total)
                    FROM Aplicaciones a
                    INNER JOIN Tests t ON a.id_test = t.id_test
                    INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                    WHERE uc.id_curso = ?
                    AND t.tipo_test = 'estres'
                ");
                $stmt->execute([$idCurso]);
                $nivelEstres = $stmt->fetchColumn();
                $curso['nivel_estres_promedio'] = $nivelEstres ? round((float)$nivelEstres, 2) : 0;
                
                // Nivel promedio de ansiedad
                $stmt = $conn->prepare("
                    SELECT AVG(a.puntuacion_total)
                    FROM Aplicaciones a
                    INNER JOIN Tests t ON a.id_test = t.id_test
                    INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                    WHERE uc.id_curso = ?
                    AND t.tipo_test = 'ansiedad'
                ");
                $stmt->execute([$idCurso]);
                $nivelAnsiedad = $stmt->fetchColumn();
                $curso['nivel_ansiedad_promedio'] = $nivelAnsiedad ? round((float)$nivelAnsiedad, 2) : 0;
                
                $curso['id_curso'] = $idCurso;
            }
            
            echo json_encode(['success' => true, 'data' => $cursos]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * API: GET detalle completo de un curso con gráficos
     */
    private function handleApiCursoDetalle(int $profesorId, int $idCurso): void {
        header('Content-Type: application/json');
        
        try {
            $conn = Database::getInstance()->getConnection();
            
            // Verificar que el profesor tenga acceso al curso
            $stmt = $conn->prepare("SELECT COUNT(*) FROM Cursos WHERE id_profesor = ? AND id_curso = ?");
            $stmt->execute([$profesorId, $idCurso]);
            if ($stmt->fetchColumn() == 0) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tienes acceso a este curso']);
                return;
            }
            
            // Información básica del curso
            $stmt = $conn->prepare("
                SELECT c.nombre_curso, e.nombre_escuela 
                FROM Cursos c 
                INNER JOIN Escuelas e ON c.id_escuela = e.id_escuela 
                WHERE c.id_curso = ?
            ");
            $stmt->execute([$idCurso]);
            $cursoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Métricas generales
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT uc.id_usuario) as total_estudiantes,
                    AVG(CASE WHEN t.tipo_test = 'estres' THEN a.puntuacion_total END) as nivel_estres_promedio,
                    AVG(CASE WHEN t.tipo_test = 'ansiedad' THEN a.puntuacion_total END) as nivel_ansiedad_promedio
                FROM Usuario_Curso uc
                LEFT JOIN Aplicaciones a ON uc.id_usuario = a.id_usuario
                LEFT JOIN Tests t ON a.id_test = t.id_test
                WHERE uc.id_curso = ?
            ");
            $stmt->execute([$idCurso]);
            $metricas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Contar tests completados
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT a.id_aplicacion)
                FROM Aplicaciones a
                INNER JOIN Tests t ON a.id_test = t.id_test
                INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                WHERE uc.id_curso = ? AND t.tipo_test IN ('estres', 'ansiedad')
            ");
            $stmt->execute([$idCurso]);
            $metricas['tests_completados'] = (int)$stmt->fetchColumn();
            
            // Contar tests sugeridos
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT sc.id_test)
                FROM Sugerencias_Curso sc
                WHERE sc.id_curso = ? AND sc.id_profesor = ?
            ");
            $stmt->execute([$idCurso, $profesorId]);
            $metricas['tests_sugeridos'] = (int)$stmt->fetchColumn();
            
            // Contar tests activos (tests sugeridos pendientes de completar)
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT sc.id_test) as tests_activos
                FROM Sugerencias_Curso sc
                INNER JOIN Usuario_Curso uc ON sc.id_curso = uc.id_curso
                WHERE sc.id_curso = ? AND sc.id_profesor = ?
                AND NOT EXISTS (
                    SELECT 1 FROM Aplicaciones a 
                    WHERE a.id_usuario = uc.id_usuario AND a.id_test = sc.id_test
                )
            ");
            $stmt->execute([$idCurso, $profesorId]);
            $testsActivos = $stmt->fetchColumn();
            
            // Tendencia mensual (últimos 6 meses)
            $stmt = $conn->prepare("
                SELECT 
                    DATE_FORMAT(a.fecha_aplicacion, '%Y-%m') as mes,
                    AVG(CASE WHEN t.tipo_test = 'estres' THEN a.puntuacion_total END) as nivel_estres,
                    AVG(CASE WHEN t.tipo_test = 'ansiedad' THEN a.puntuacion_total END) as nivel_ansiedad
                FROM Aplicaciones a
                INNER JOIN Tests t ON a.id_test = t.id_test
                INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                WHERE uc.id_curso = ?
                AND a.fecha_aplicacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                AND t.tipo_test IN ('estres', 'ansiedad')
                GROUP BY mes
                ORDER BY mes
            ");
            $stmt->execute([$idCurso]);
            $tendenciaMensual = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Distribución de niveles de estrés
            $stmt = $conn->prepare("
                SELECT 
                    CASE 
                        WHEN a.puntuacion_total <= 1 THEN 'bajo'
                        WHEN a.puntuacion_total <= 2 THEN 'moderado'
                        WHEN a.puntuacion_total <= 3 THEN 'alto'
                        ELSE 'severo'
                    END as nivel,
                    COUNT(DISTINCT a.id_usuario) as cantidad
                FROM Aplicaciones a
                INNER JOIN Tests t ON a.id_test = t.id_test
                INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                WHERE uc.id_curso = ? AND t.tipo_test = 'estres'
                AND a.id_aplicacion IN (
                    SELECT MAX(id_aplicacion) 
                    FROM Aplicaciones a2 
                    INNER JOIN Tests t2 ON a2.id_test = t2.id_test
                    WHERE a2.id_usuario = a.id_usuario AND t2.tipo_test = 'estres'
                    GROUP BY a2.id_usuario
                )
                GROUP BY nivel
            ");
            $stmt->execute([$idCurso]);
            $distribucionEstres = ['bajo' => 0, 'moderado' => 0, 'alto' => 0, 'severo' => 0];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $distribucionEstres[$row['nivel']] = (int)$row['cantidad'];
            }
            
            // Distribución de niveles de ansiedad
            $stmt = $conn->prepare("
                SELECT 
                    CASE 
                        WHEN a.puntuacion_total <= 1 THEN 'bajo'
                        WHEN a.puntuacion_total <= 2 THEN 'moderado'
                        WHEN a.puntuacion_total <= 3 THEN 'alto'
                        ELSE 'severo'
                    END as nivel,
                    COUNT(DISTINCT a.id_usuario) as cantidad
                FROM Aplicaciones a
                INNER JOIN Tests t ON a.id_test = t.id_test
                INNER JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                WHERE uc.id_curso = ? AND t.tipo_test = 'ansiedad'
                AND a.id_aplicacion IN (
                    SELECT MAX(id_aplicacion) 
                    FROM Aplicaciones a2 
                    INNER JOIN Tests t2 ON a2.id_test = t2.id_test
                    WHERE a2.id_usuario = a.id_usuario AND t2.tipo_test = 'ansiedad'
                    GROUP BY a2.id_usuario
                )
                GROUP BY nivel
            ");
            $stmt->execute([$idCurso]);
            $distribucionAnsiedad = ['bajo' => 0, 'moderado' => 0, 'alto' => 0, 'severo' => 0];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $distribucionAnsiedad[$row['nivel']] = (int)$row['cantidad'];
            }
            
            // Tests activos con detalle
            $stmt = $conn->prepare("
                SELECT 
                    te.nombre AS nombre_test,
                    sc.fecha_sugerencia,
                    COUNT(DISTINCT uc.id_usuario) as total_estudiantes,
                    COUNT(DISTINCT a.id_usuario) as estudiantes_completados
                FROM Sugerencias_Curso sc
                INNER JOIN Tests te ON sc.id_test = te.id_test
                INNER JOIN Usuario_Curso uc ON sc.id_curso = uc.id_curso
                LEFT JOIN Aplicaciones a ON a.id_usuario = uc.id_usuario AND a.id_test = sc.id_test
                WHERE sc.id_curso = ? AND sc.id_profesor = ?
                GROUP BY sc.id_sugerencia_curso, te.nombre, sc.fecha_sugerencia
                HAVING COUNT(DISTINCT a.id_usuario) < COUNT(DISTINCT uc.id_usuario)
                ORDER BY sc.fecha_sugerencia DESC
            ");
            $stmt->execute([$idCurso, $profesorId]);
            $testsActivosDetalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Respuesta completa
            $response = [
                'success' => true,
                'data' => [
                    'nombre_curso' => $cursoInfo['nombre_curso'],
                    'nombre_escuela' => $cursoInfo['nombre_escuela'],
                    'total_estudiantes' => (int)$metricas['total_estudiantes'],
                    'tests_completados' => (int)$metricas['tests_completados'],
                    'tests_sugeridos' => (int)$metricas['tests_sugeridos'],
                    'tests_activos' => (int)$testsActivos,
                    'nivel_estres_promedio' => $metricas['nivel_estres_promedio'] ? round((float)$metricas['nivel_estres_promedio'], 2) : 0,
                    'nivel_ansiedad_promedio' => $metricas['nivel_ansiedad_promedio'] ? round((float)$metricas['nivel_ansiedad_promedio'], 2) : 0,
                    'tendencia_mensual' => $tendenciaMensual,
                    'distribucion_estres' => $distribucionEstres,
                    'distribucion_ansiedad' => $distribucionAnsiedad,
                    'tests_activos_detalle' => $testsActivosDetalle
                ]
            ];
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de servidor: ' . $e->getMessage()]);
        }
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
